<?php

namespace App\Console\Commands;

use App\Models\Member;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class ImportApcqMembersCommand extends Command
{
    protected $signature = 'members:import-apcq {--truncate : Supprimer tous les membres existants avant l\'import}';

    protected $description = 'Importe les membres depuis https://apcq.ca/membres/';

    public function handle(): int
    {
        if ($this->option('truncate')) {
            Member::query()->delete();
            $this->warn('Tous les membres existants ont ete supprimes (soft delete).');
        }

        try {
            $html = Http::timeout(30)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (compatible; APCQImporter/1.0; +https://apcq.ca/)',
                    'Accept-Language' => 'fr-CA,fr;q=0.9,en;q=0.8',
                ])
                ->get('https://apcq.ca/membres/')
                ->throw()
                ->body();
        } catch (Throwable $exception) {
            $this->error('Impossible de recuperer la page des membres: '.$exception->getMessage());

            return self::FAILURE;
        }

        $members = $this->extractMembersFromHtml($html);

        if ($members === []) {
            $this->warn('Aucun membre detecte dans la page source.');

            return self::SUCCESS;
        }

        $importedCount = 0;

        foreach ($members as $index => $memberData) {
            $member = Member::withTrashed()->firstOrNew([
                'name' => $memberData['name'],
                'cinema_name' => $memberData['cinema_name'],
            ]);

            $member->fill([
                'address' => $memberData['address'],
                'city' => $memberData['city'],
                'phone' => $memberData['phone'],
                'email' => $memberData['email'],
                'website' => $memberData['website'],
                'sort_order' => $index + 1,
            ]);

            if ($member->trashed()) {
                $member->restore();
            }

            $member->save();
            $this->syncMemberLogo($member, $memberData['logo_url'] ?? null);
            $importedCount++;
        }

        $this->info("Import termine: {$importedCount} membres traites.");

        return self::SUCCESS;
    }

    /**
     * @return list<array{
     *     name: string,
     *     cinema_name: string,
     *     address: ?string,
     *     city: ?string,
     *     phone: ?string,
     *     email: ?string,
     *     website: ?string,
     *     logo_url: ?string
     * }>
     */
    protected function extractMembersFromHtml(string $html): array
    {
        $document = new DOMDocument;
        libxml_use_internal_errors(true);
        $document->loadHTML($html);
        libxml_clear_errors();

        $xpath = new DOMXPath($document);
        $cards = $xpath->query('//div[contains(concat(" ", normalize-space(@class), " "), " member-card ")]');

        if (! $cards) {
            return [];
        }

        $members = [];

        foreach ($cards as $card) {
            if (! $card instanceof DOMElement) {
                continue;
            }

            $nameNode = $xpath->query('.//h3[1]', $card)?->item(0);
            $detailsNode = $xpath->query('.//div[contains(concat(" ", normalize-space(@class), " "), " wpb_text_column ")]//p[1]', $card)?->item(0);

            $cinemaName = $this->normalizeText($nameNode?->textContent ?? '');
            if ($cinemaName === '') {
                continue;
            }

            $collectedLines = $this->extractLinesFromDetailsNode($detailsNode);
            $website = $this->extractWebsiteFromDetailsNode($detailsNode);
            $logoUrl = $this->extractLogoUrlFromCard($card, $xpath);

            if ($collectedLines === [] && $website === null) {
                continue;
            }

            $address = $collectedLines[0] ?? null;
            $city = $collectedLines[1] ?? null;
            $phone = $this->extractPhone($collectedLines);
            $email = $this->extractEmail($collectedLines);

            $members[] = [
                'name' => $cinemaName,
                'cinema_name' => $cinemaName,
                'address' => $address,
                'city' => $city,
                'phone' => $phone,
                'email' => $email,
                'website' => $website,
                'logo_url' => $logoUrl,
            ];
        }

        return $members;
    }

    /**
     * @return list<string>
     */
    protected function extractLinesFromDetailsNode(?DOMElement $detailsNode): array
    {
        if (! $detailsNode) {
            return [];
        }

        $html = '';
        foreach ($detailsNode->childNodes as $childNode) {
            $html .= $detailsNode->ownerDocument?->saveHTML($childNode) ?? '';
        }

        $text = str_ireplace(['<br>', '<br/>', '<br />'], "\n", $html);
        $text = strip_tags($text);

        $lines = [];
        foreach (preg_split('/\R+/u', $text) ?: [] as $line) {
            $normalizedLine = $this->normalizeText($line);
            if ($normalizedLine !== '') {
                $lines[] = $normalizedLine;
            }
        }

        return $lines;
    }

    protected function extractWebsiteFromDetailsNode(?DOMElement $detailsNode): ?string
    {
        if (! $detailsNode) {
            return null;
        }

        foreach ($detailsNode->getElementsByTagName('a') as $link) {
            if (! $link instanceof DOMElement) {
                continue;
            }

            $href = trim((string) $link->getAttribute('href'));

            if ($this->isValidWebsiteUrl($href)) {
                return $this->normalizeWebsite($href);
            }
        }

        return null;
    }

    protected function extractLogoUrlFromCard(DOMElement $card, DOMXPath $xpath): ?string
    {
        $logoNode = $xpath->query('.//div[contains(concat(" ", normalize-space(@class), " "), " member-logo ")]//img[1]', $card)?->item(0);

        if (! $logoNode instanceof DOMElement) {
            return null;
        }

        $source = trim((string) $logoNode->getAttribute('src'));

        return $this->isValidWebsiteUrl($source) ? $this->normalizeWebsite($source) : null;
    }

    protected function syncMemberLogo(Member $member, ?string $logoUrl): void
    {
        if ($logoUrl === null) {
            return;
        }

        $existingLogo = $member->getFirstMedia('logo');
        $existingSourceUrl = $existingLogo?->getCustomProperty('source_url');

        if (is_string($existingSourceUrl) && $existingSourceUrl === $logoUrl) {
            return;
        }

        try {
            $filename = $this->buildLogoFilename($member, $logoUrl);

            $member->addMediaFromUrl($logoUrl)
                ->usingFileName($filename)
                ->withCustomProperties(['source_url' => $logoUrl])
                ->toMediaCollection('logo');
        } catch (Throwable $exception) {
            $this->warn("Logo non importe pour {$member->cinema_name}: {$exception->getMessage()}");
        }
    }

    protected function buildLogoFilename(Member $member, string $logoUrl): string
    {
        $path = parse_url($logoUrl, PHP_URL_PATH);
        $extension = is_string($path) ? pathinfo($path, PATHINFO_EXTENSION) : '';
        $extension = is_string($extension) && $extension !== '' ? strtolower($extension) : 'jpg';

        return Str::slug($member->cinema_name).'-logo.'.$extension;
    }

    /**
     * @param  list<string>  $lines
     */
    protected function extractPhone(array $lines): ?string
    {
        foreach ($lines as $line) {
            if (preg_match('/\+?\d[\d\-\s().]{6,}/u', $line, $match) === 1) {
                return trim($match[0]);
            }
        }

        return null;
    }

    /**
     * @param  list<string>  $lines
     */
    protected function extractEmail(array $lines): ?string
    {
        foreach ($lines as $line) {
            if (preg_match('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/iu', $line, $match) === 1) {
                return strtolower(trim($match[0]));
            }
        }

        return null;
    }

    protected function isValidWebsiteUrl(string $href): bool
    {
        return preg_match('/^(https?:\/\/|www\.)/i', $href) === 1;
    }

    protected function normalizeWebsite(string $href): string
    {
        if (str_starts_with(strtolower($href), 'www.')) {
            return 'https://'.$href;
        }

        return $href;
    }

    protected function normalizeText(string $text): string
    {
        $normalized = preg_replace('/\s+/u', ' ', trim($text));

        return is_string($normalized) ? $normalized : '';
    }
}
