<?php

namespace App\Exports;

use App\Models\Cinema;
use App\Models\Room;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CinemaExcelExport
{
    public function download(Builder|Collection $source): BinaryFileResponse
    {
        $path = $this->writeToTemp($source);
        $fileName = 'cinemas-'.now()->timezone('America/Toronto')->format('Y-m-d').'.xlsx';

        return response()
            ->download($path, $fileName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])
            ->deleteFileAfterSend();
    }

    protected function writeToTemp(Builder|Collection $source): string
    {
        $cinemas = $this->cinemas($source);
        $path = sys_get_temp_dir().'/cinemas-'.Str::uuid().'.xlsx';

        $writer = new Writer;
        $writer->setCreator('APCQ');
        $writer->openToFile($path);

        $headerStyle = (new Style)->setFontBold();

        $writer->getCurrentSheet()->setName('Cinémas');
        $writer->addRow(Row::fromValues($this->cinemaHeaders(), $headerStyle));

        foreach ($cinemas as $cinema) {
            $writer->addRow(Row::fromValues($this->cinemaRow($cinema)));
        }

        $writer->addNewSheetAndMakeItCurrent()->setName('Salles');
        $writer->addRow(Row::fromValues($this->roomHeaders(), $headerStyle));

        foreach ($cinemas as $cinema) {
            foreach ($cinema->rooms as $room) {
                $writer->addRow(Row::fromValues($this->roomRow($cinema, $room)));
            }
        }

        $writer->close();

        return $path;
    }

    /**
     * @return Collection<int, Cinema>
     */
    protected function cinemas(Builder|Collection $source): Collection
    {
        $eagerLoad = [
            'group',
            'administrativeRegion',
            'rooms' => fn (HasMany $rooms) => $rooms->orderBy('id'),
        ];

        if ($source instanceof Builder) {
            return $source
                ->clone()
                ->with($eagerLoad)
                ->orderBy('name')
                ->get();
        }

        return Cinema::query()
            ->withTrashed()
            ->whereKey($source->modelKeys())
            ->with($eagerLoad)
            ->orderBy('name')
            ->get();
    }

    /**
     * @return list<string>
     */
    protected function cinemaHeaders(): array
    {
        return [
            'Nom',
            'Compagnie légale',
            'Groupe',
            'Région',
            'Adresse',
            'Ville',
            'Code postal',
            'Téléphone',
            'Courriel',
            'Site web',
            'Contact primaire — nom',
            'Contact primaire — téléphone',
            'Contact primaire — courriel',
            'Contact secondaire — nom',
            'Contact secondaire — téléphone',
            'Contact secondaire — courriel',
            'Logiciel de caisse',
            'eDelivery',
            'Nombre de caisses',
            'Nombre de guichets',
            'Permis d’alcool',
            'Nombre de salles',
            'Infos personnelles validées',
            'Infos cinéma validées',
        ];
    }

    /**
     * @return list<string|int>
     */
    protected function cinemaRow(Cinema $cinema): array
    {
        return [
            $this->cell($cinema->name),
            $this->cell($cinema->legal_company_name),
            $this->cell($cinema->group?->name),
            $this->cell($cinema->administrativeRegion?->name),
            $this->cell($cinema->address),
            $this->cell($cinema->city),
            $this->cell($cinema->postal_code),
            $this->cell($cinema->phone),
            $this->cell($cinema->email),
            $this->cell($cinema->website),
            $this->cell($cinema->primary_contact_name),
            $this->cell($cinema->primary_contact_phone),
            $this->cell($cinema->primary_contact_email),
            $this->cell($cinema->secondary_contact_name),
            $this->cell($cinema->secondary_contact_phone),
            $this->cell($cinema->secondary_contact_email),
            $this->cell($cinema->pos_software),
            $this->cell($cinema->edelivery),
            $this->cell($cinema->cash_registers_count),
            $this->cell($cinema->ticket_booths_count),
            $this->boolean($cinema->alcohol_permit),
            $cinema->rooms->count(),
            $this->date($cinema->personal_info_validated_at),
            $this->date($cinema->cinema_info_validated_at),
        ];
    }

    /**
     * @return list<string>
     */
    protected function roomHeaders(): array
    {
        return [
            'Cinéma',
            'Nom de la salle',
            'Marque du projecteur',
            'Nom de la marque',
            'Modèle du projecteur',
            'Modèle du serveur',
            'Type de projection',
            'Année d’installation',
            'Grandeur de l’écran',
        ];
    }

    /**
     * @return list<string|int>
     */
    protected function roomRow(Cinema $cinema, Room $room): array
    {
        return [
            $this->cell($cinema->name),
            $this->cell($room->name),
            $this->cell($room->projector_brand?->getLabel()),
            $this->cell($room->projector_brand_other),
            $this->cell($room->projector_model),
            $this->cell($room->server_model),
            $this->cell($room->projection_type?->getLabel()),
            $this->cell($room->installation_year),
            $this->cell($room->screen_size),
        ];
    }

    protected function cell(mixed $value): string|int
    {
        if ($value === null || $value === '') {
            return '';
        }

        if (is_int($value)) {
            return $value;
        }

        return (string) $value;
    }

    protected function boolean(?bool $value): string
    {
        return $value ? 'Oui' : 'Non';
    }

    protected function date(mixed $value): string
    {
        if (! $value) {
            return '';
        }

        return $value->timezone('America/Toronto')->format('Y-m-d H:i');
    }
}
