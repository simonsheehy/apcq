<?php

namespace App\Console\Commands;

use App\Models\Cinema;
use App\Models\Room;
use Illuminate\Console\Command;
use OpenSpout\Reader\XLSX\Reader;
use Throwable;

class ImportCinemasCommand extends Command
{
    protected $signature = 'cinemas:import
        {--path=membres.xlsx : Chemin du fichier Excel relatif au dossier storage}
        {--truncate : Supprimer les cinémas et salles existants avant l\'import}';

    protected $description = 'Importe les cinémas (et leurs salles) depuis le fichier storage/membres.xlsx';

    public function handle(): int
    {
        $path = storage_path((string) $this->option('path'));

        if (! is_file($path)) {
            $this->error("Fichier introuvable: {$path}");

            return self::FAILURE;
        }

        if ($this->option('truncate')) {
            Room::query()->forceDelete();
            Cinema::query()->forceDelete();
            $this->warn('Cinémas et salles existants supprimés.');
        }

        try {
            $rows = $this->readRows($path);
        } catch (Throwable $exception) {
            $this->error('Impossible de lire le fichier Excel: '.$exception->getMessage());

            return self::FAILURE;
        }

        $cinemaCount = 0;
        $roomCount = 0;

        foreach ($rows as $row) {
            $data = $this->parseRow($row);

            if ($data === null) {
                continue;
            }

            $cinema = Cinema::updateOrCreate(
                [
                    'name' => $data['name'],
                    'city' => $data['city'],
                ],
                [
                    'primary_contact_name' => $data['primary_contact_name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'],
                ],
            );

            $roomCount += $this->syncRooms($cinema, $data['screens']);
            $cinemaCount++;
        }

        $this->info("Import terminé: {$cinemaCount} cinémas et {$roomCount} salles traités.");

        return self::SUCCESS;
    }

    /**
     * @return list<list<mixed>>
     */
    protected function readRows(string $path): array
    {
        $reader = new Reader;
        $reader->open($path);

        $rows = [];

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $rows[] = array_map(
                    static fn ($cell) => $cell->getValue(),
                    $row->getCells(),
                );
            }

            break;
        }

        $reader->close();

        return $rows;
    }

    /**
     * Retourne les données d'un cinéma ou null si la ligne n'en est pas un
     * (titre, en-tête, séparateur de section ou ligne de total).
     *
     * @param  list<mixed>  $row
     * @return array{name: string, city: ?string, primary_contact_name: ?string, email: ?string, phone: ?string, screens: int}|null
     */
    protected function parseRow(array $row): ?array
    {
        $name = $this->stringOrNull($row[0] ?? null);
        $screens = $row[5] ?? null;

        if ($name === null || ! is_numeric($screens)) {
            return null;
        }

        return [
            'name' => $name,
            'city' => $this->stringOrNull($row[1] ?? null),
            'primary_contact_name' => $this->stringOrNull($row[2] ?? null),
            'email' => $this->stringOrNull($row[3] ?? null),
            'phone' => $this->stringOrNull($row[4] ?? null),
            'screens' => max(0, (int) $screens),
        ];
    }

    protected function syncRooms(Cinema $cinema, int $screens): int
    {
        $created = 0;

        for ($number = 1; $number <= $screens; $number++) {
            $room = Room::withTrashed()->firstOrNew([
                'cinema_id' => $cinema->id,
                'name' => "Salle {$number}",
            ]);

            if ($room->trashed()) {
                $room->restore();
            }

            if (! $room->exists) {
                $room->save();
                $created++;
            }
        }

        return $created;
    }

    protected function stringOrNull(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
