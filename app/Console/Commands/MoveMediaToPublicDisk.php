<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGeneratorFactory;
use Throwable;

class MoveMediaToPublicDisk extends Command
{
    protected $signature = 'media:move-to-public
        {--collection= : Ne traiter qu\'une collection (ex: logo)}
        {--keep-source : Conserver les fichiers sur le disque d\'origine}
        {--dry-run : Afficher ce qui serait fait sans rien modifier}';

    protected $description = 'Deplace les media stockes sur un disque prive vers le disque public et met a jour la base.';

    public function handle(): int
    {
        $target = 'public';
        $dryRun = (bool) $this->option('dry-run');

        $query = Media::query()->where('disk', '!=', $target);

        if ($collection = $this->option('collection')) {
            $query->where('collection_name', $collection);
        }

        $media = $query->get();

        if ($media->isEmpty()) {
            $this->info('Aucun media a deplacer : tout est deja sur le disque "public".');

            return self::SUCCESS;
        }

        $this->info(sprintf('%d media a deplacer vers le disque "%s".', $media->count(), $target));

        $moved = 0;
        $failed = 0;

        foreach ($media as $item) {
            try {
                $this->moveItem($item, $target, $dryRun);
                $moved++;
            } catch (Throwable $e) {
                $failed++;
                $this->error(sprintf('Media #%d (%s) : %s', $item->id, $item->file_name, $e->getMessage()));
            }
        }

        $this->newLine();
        $this->info(sprintf('Termine : %d deplaces, %d en echec.%s', $moved, $failed, $dryRun ? ' (dry-run)' : ''));

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function moveItem(Media $media, string $target, bool $dryRun): void
    {
        $source = $media->disk;
        $pathGenerator = PathGeneratorFactory::create($media);

        // Dossier principal + dossier des conversions, relatifs a la racine du disque.
        $dirs = [
            rtrim($pathGenerator->getPath($media), '/'),
            rtrim($pathGenerator->getPathForConversions($media), '/'),
            rtrim($pathGenerator->getPathForResponsiveImages($media), '/'),
        ];

        $from = Storage::disk($source);
        $to = Storage::disk($target);

        foreach (array_unique(array_filter($dirs)) as $dir) {
            foreach ($from->files($dir) as $file) {
                if ($dryRun) {
                    $this->line("  [dry-run] {$source}:{$file} -> {$target}:{$file}");

                    continue;
                }

                $to->put($file, $from->get($file), 'public');
                $this->line("  {$file}");
            }
        }

        if ($dryRun) {
            return;
        }

        $media->disk = $target;
        if ($media->conversions_disk && $media->conversions_disk !== $target) {
            $media->conversions_disk = $target;
        }
        $media->save();

        if (! $this->option('keep-source')) {
            foreach (array_unique(array_filter($dirs)) as $dir) {
                $from->deleteDirectory($dir);
            }
        }
    }
}
