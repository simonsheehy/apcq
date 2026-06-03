<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cinema extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'group_id',
        'administrative_region_id',
        'name',
        'legal_company_name',
        'address',
        'city',
        'postal_code',
        'phone',
        'email',
        'website',
        'primary_contact_name',
        'primary_contact_phone',
        'primary_contact_email',
        'secondary_contact_name',
        'secondary_contact_phone',
        'secondary_contact_email',
        'pos_software',
        'cash_registers_count',
        'ticket_booths_count',
        'alcohol_permit',
        'edelivery',
    ];

    protected function casts(): array
    {
        return [
            'alcohol_permit' => 'boolean',
            'cash_registers_count' => 'integer',
            'ticket_booths_count' => 'integer',
        ];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function administrativeRegion(): BelongsTo
    {
        return $this->belongsTo(AdministrativeRegion::class);
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }
}
