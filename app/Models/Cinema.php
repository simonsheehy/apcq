<?php

namespace App\Models;

use App\Mail\CinemaValidationRequestMail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class Cinema extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'access_token',
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
        'personal_info_validated_at',
        'cinema_info_validated_at',
    ];

    protected function casts(): array
    {
        return [
            'alcohol_permit' => 'boolean',
            'cash_registers_count' => 'integer',
            'ticket_booths_count' => 'integer',
            'personal_info_validated_at' => 'datetime',
            'cinema_info_validated_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Cinema $cinema): void {
            if (blank($cinema->access_token)) {
                $cinema->access_token = (string) Str::uuid();
            }
        });
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

    public function scopeInformationValidated(Builder $query): Builder
    {
        return $query
            ->whereNotNull('personal_info_validated_at')
            ->whereNotNull('cinema_info_validated_at');
    }

    public function scopeInformationNotValidated(Builder $query): Builder
    {
        return $query->where(function (Builder $query): void {
            $query
                ->whereNull('personal_info_validated_at')
                ->orWhereNull('cinema_info_validated_at');
        });
    }

    public function validationUrl(): string
    {
        return route('cinemas.validation', $this->access_token);
    }

    /**
     * @return list<string>
     */
    public function validationRequestRecipients(): array
    {
        return collect([$this->primary_contact_email, $this->email])
            ->filter(fn (mixed $email): bool => is_string($email) && filled($email))
            ->map(fn (string $email): string => trim($email))
            ->filter(fn (string $email): bool => filter_var($email, FILTER_VALIDATE_EMAIL) !== false)
            ->unique()
            ->values()
            ->all();
    }

    public function sendValidationRequest(): bool
    {
        $recipients = $this->validationRequestRecipients();

        if ($recipients === []) {
            return false;
        }

        Mail::to($recipients)->send(new CinemaValidationRequestMail($this));

        return true;
    }
}
