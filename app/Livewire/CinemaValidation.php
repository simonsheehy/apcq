<?php

namespace App\Livewire;

use App\Enums\ProjectionType;
use App\Enums\ProjectorBrand;
use App\Models\Cinema;
use App\Models\Room;
use Illuminate\Validation\Rule;
use Livewire\Component;

class CinemaValidation extends Component
{
    public Cinema $cinema;

    /**
     * @var array<string, mixed>
     */
    public array $personal = [];

    /**
     * @var array<string, mixed>
     */
    public array $details = [];

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $rooms = [];

    public bool $personalInfoConfirmed = false;

    public bool $cinemaInfoConfirmed = false;

    public ?string $lastSavedAt = null;

    public function mount(string $token): void
    {
        $this->cinema = Cinema::query()
            ->where('access_token', $token)
            ->with(['rooms' => fn ($query) => $query->orderBy('id')])
            ->firstOrFail();

        $this->hydrateForm();
    }

    public function updatedPersonal(mixed $value, ?string $key = null): void
    {
        if (! is_string($key)) {
            return;
        }

        $this->persistCinemaField('personal', $key);
    }

    public function updatedDetails(mixed $value, ?string $key = null): void
    {
        if (! is_string($key)) {
            return;
        }

        $this->persistCinemaField('details', $key);
    }

    public function updatedRooms(mixed $value, ?string $key = null): void
    {
        if (! is_string($key) || ! str_contains($key, '.')) {
            return;
        }

        [$index, $field] = explode('.', $key, 2);
        $this->persistRoomField((int) $index, $field);
    }

    public function updatedPersonalInfoConfirmed(bool $value): void
    {
        $this->cinema->update([
            'personal_info_validated_at' => $value ? now() : null,
        ]);

        $this->cinema->refresh();
        $this->markSaved();
    }

    public function updatedCinemaInfoConfirmed(bool $value): void
    {
        $this->cinema->update([
            'cinema_info_validated_at' => $value ? now() : null,
        ]);

        $this->cinema->refresh();
        $this->markSaved();
    }

    public function addRoom(): void
    {
        $room = $this->cinema->rooms()->create([
            'name' => 'Salle '.($this->cinema->rooms()->count() + 1),
        ]);

        $this->rooms[] = $this->roomToArray($room);
        $this->markSaved();
    }

    public function removeRoom(int $index): void
    {
        $id = $this->rooms[$index]['id'] ?? null;

        if ($id) {
            Room::query()
                ->where('cinema_id', $this->cinema->id)
                ->whereKey($id)
                ->delete();
        }

        unset($this->rooms[$index]);
        $this->rooms = array_values($this->rooms);
        $this->markSaved();
    }

    public function render()
    {
        return view('livewire.cinema-validation', [
            'projectorBrands' => ProjectorBrand::cases(),
            'projectionTypes' => ProjectionType::cases(),
        ])->layout('layouts.app', [
            'title' => 'Validation — '.$this->cinema->name,
        ]);
    }

    protected function hydrateForm(): void
    {
        $this->personal = [
            'primary_contact_name' => $this->cinema->primary_contact_name ?? '',
            'primary_contact_phone' => $this->cinema->primary_contact_phone ?? '',
            'primary_contact_email' => $this->cinema->primary_contact_email ?? '',
            'secondary_contact_name' => $this->cinema->secondary_contact_name ?? '',
            'secondary_contact_phone' => $this->cinema->secondary_contact_phone ?? '',
            'secondary_contact_email' => $this->cinema->secondary_contact_email ?? '',
        ];

        $this->details = [
            'name' => $this->cinema->name ?? '',
            'legal_company_name' => $this->cinema->legal_company_name ?? '',
            'address' => $this->cinema->address ?? '',
            'city' => $this->cinema->city ?? '',
            'postal_code' => $this->cinema->postal_code ?? '',
            'phone' => $this->cinema->phone ?? '',
            'email' => $this->cinema->email ?? '',
            'website' => $this->cinema->website ?? '',
            'pos_software' => $this->cinema->pos_software ?? '',
            'edelivery' => $this->cinema->edelivery ?? '',
            'cash_registers_count' => $this->cinema->cash_registers_count,
            'ticket_booths_count' => $this->cinema->ticket_booths_count,
            'alcohol_permit' => (bool) $this->cinema->alcohol_permit,
        ];

        $this->personalInfoConfirmed = $this->cinema->personal_info_validated_at !== null;
        $this->cinemaInfoConfirmed = $this->cinema->cinema_info_validated_at !== null;
        $this->rooms = $this->cinema->rooms
            ->map(fn (Room $room): array => $this->roomToArray($room))
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    protected function roomToArray(Room $room): array
    {
        return [
            'id' => $room->id,
            'name' => $room->name ?? '',
            'projector_brand' => $room->projector_brand?->value ?? '',
            'projector_brand_other' => $room->projector_brand_other ?? '',
            'projector_model' => $room->projector_model ?? '',
            'server_model' => $room->server_model ?? '',
            'projection_type' => $room->projection_type?->value ?? '',
            'installation_year' => $room->installation_year,
            'screen_size' => $room->screen_size ?? '',
        ];
    }

    protected function persistCinemaField(string $group, string $field): void
    {
        $rules = [
            'personal' => [
                'primary_contact_name' => ['nullable', 'string', 'max:255'],
                'primary_contact_phone' => ['nullable', 'string', 'max:255'],
                'primary_contact_email' => ['nullable', 'email', 'max:255'],
                'secondary_contact_name' => ['nullable', 'string', 'max:255'],
                'secondary_contact_phone' => ['nullable', 'string', 'max:255'],
                'secondary_contact_email' => ['nullable', 'email', 'max:255'],
            ],
            'details' => [
                'name' => ['required', 'string', 'max:255'],
                'legal_company_name' => ['nullable', 'string', 'max:255'],
                'address' => ['nullable', 'string', 'max:255'],
                'city' => ['nullable', 'string', 'max:255'],
                'postal_code' => ['nullable', 'string', 'max:255'],
                'phone' => ['nullable', 'string', 'max:255'],
                'email' => ['nullable', 'email', 'max:255'],
                'website' => ['nullable', 'string', 'max:255'],
                'pos_software' => ['nullable', 'string', 'max:255'],
                'edelivery' => ['nullable', 'string', 'max:255'],
                'cash_registers_count' => ['nullable', 'integer', 'min:0', 'max:1000'],
                'ticket_booths_count' => ['nullable', 'integer', 'min:0', 'max:1000'],
                'alcohol_permit' => ['boolean'],
            ],
        ];

        if (! isset($rules[$group][$field])) {
            return;
        }

        $errorKey = "{$group}.{$field}";
        $hadError = $this->getErrorBag()->has($errorKey);

        $this->validateOnly($errorKey, [
            $errorKey => $rules[$group][$field],
        ]);

        $value = $this->{$group}[$field] ?? null;

        if (in_array($field, ['cash_registers_count', 'ticket_booths_count'], true)) {
            $value = $this->nullableInt($value);
        } elseif ($field === 'alcohol_permit') {
            $value = (bool) $value;
        } else {
            $value = $this->blankToNull($value);
        }

        $this->cinema->update([$field => $value]);
        $this->finishPersist($hadError);
    }

    protected function persistRoomField(int $index, string $field): void
    {
        if (! isset($this->rooms[$index]['id'])) {
            return;
        }

        $yearMax = (int) now()->year + 1;
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'projector_brand' => ['nullable', Rule::enum(ProjectorBrand::class)],
            'projector_brand_other' => ['nullable', 'string', 'max:255'],
            'projector_model' => ['nullable', 'string', 'max:255'],
            'server_model' => ['nullable', 'string', 'max:255'],
            'projection_type' => ['nullable', Rule::enum(ProjectionType::class)],
            'installation_year' => ['nullable', 'integer', 'min:1950', 'max:'.$yearMax],
            'screen_size' => ['nullable', 'string', 'max:255'],
        ];

        if (! isset($rules[$field])) {
            return;
        }

        $errorKey = "rooms.{$index}.{$field}";
        $hadError = $this->getErrorBag()->has($errorKey);

        if ($field === 'installation_year' && $this->isIncompleteYear($this->rooms[$index][$field] ?? null)) {
            $this->resetErrorBag($errorKey);
            $this->skipRenderUnlessClearingError($hadError);

            return;
        }

        $this->validateOnly($errorKey, [
            $errorKey => $rules[$field],
        ]);

        $value = $this->rooms[$index][$field] ?? null;

        if ($field === 'installation_year') {
            $value = $this->nullableInt($value);
        } else {
            $value = $this->blankToNull($value);
        }

        $attributes = [$field => $value];

        if ($field === 'projector_brand' && $value !== ProjectorBrand::Other->value) {
            $attributes['projector_brand_other'] = null;
            $this->rooms[$index]['projector_brand_other'] = '';
        }

        Room::query()
            ->where('cinema_id', $this->cinema->id)
            ->whereKey($this->rooms[$index]['id'])
            ->firstOrFail()
            ->update($attributes);

        $this->finishPersist($hadError);
    }

    protected function finishPersist(bool $hadError): void
    {
        $this->skipRenderUnlessClearingError($hadError);
        $this->markSaved();
    }

    protected function skipRenderUnlessClearingError(bool $hadError): void
    {
        if (! $hadError) {
            $this->skipRender();
        }
    }

    protected function markSaved(): void
    {
        $this->lastSavedAt = now()->timezone('America/Toronto')->format('H:i');

        $this->dispatch('cinema-saved', at: $this->lastSavedAt);
    }

    protected function isIncompleteYear(mixed $value): bool
    {
        if ($value === '' || $value === null) {
            return false;
        }

        return strlen((string) $value) < 4;
    }

    protected function blankToNull(mixed $value): mixed
    {
        return $value === '' ? null : $value;
    }

    protected function nullableInt(mixed $value): ?int
    {
        if ($value === '' || $value === null) {
            return null;
        }

        return (int) $value;
    }
}
