<?php

namespace App\Models;

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
        'screen_size',
    ];

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
}
