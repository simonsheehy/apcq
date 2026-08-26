<?php

namespace App\Models;

use App\Enums\ProjectionType;
use App\Enums\ProjectorBrand;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'cinema_id',
        'room_type_id',
        'name',
        'sound_processor',
        'projector',
        'projector_brand',
        'projector_brand_other',
        'projector_model',
        'server_model',
        'projection_type',
        'installation_year',
        'screen_size',
    ];

    protected function casts(): array
    {
        return [
            'projector_brand' => ProjectorBrand::class,
            'projection_type' => ProjectionType::class,
            'installation_year' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Room $room): void {
            $displayName = $room->projectorDisplayName();

            if (filled($displayName)) {
                $room->projector = $displayName;
            }

            if ($room->projector_brand !== ProjectorBrand::Other) {
                $room->projector_brand_other = null;
            }
        });
    }

    public function cinema(): BelongsTo
    {
        return $this->belongsTo(Cinema::class);
    }

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    public function imageTechnologies(): BelongsToMany
    {
        return $this->belongsToMany(ImageTechnology::class);
    }

    public function soundTechnologies(): BelongsToMany
    {
        return $this->belongsToMany(SoundTechnology::class);
    }

    public function seatTypes(): BelongsToMany
    {
        return $this->belongsToMany(SeatType::class, 'room_seat_type')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    /**
     * Répartition des places par type de siège (modèle pivot pour la saisie).
     */
    public function seatAllocations(): HasMany
    {
        return $this->hasMany(RoomSeatType::class);
    }

    public function getTotalSeatsAttribute(): int
    {
        return (int) $this->seatAllocations->sum('quantity');
    }

    public function projectorDisplayName(): ?string
    {
        $brand = match ($this->projector_brand) {
            ProjectorBrand::Other => $this->projector_brand_other,
            null => null,
            default => $this->projector_brand->getLabel(),
        };

        $parts = array_filter([$brand, $this->projector_model]);

        return $parts === [] ? null : implode(' ', $parts);
    }
}
