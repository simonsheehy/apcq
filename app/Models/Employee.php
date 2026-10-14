<?php

namespace App\Models;

use App\Enums\EmployeeStatus;
use App\Notifications\EmployeeInvitation;
use App\Notifications\EmployeeResetPassword;
use Database\Factories\EmployeeFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\URL;

class Employee extends Authenticatable
{
    /** @use HasFactory<EmployeeFactory> */
    use HasFactory;

    use Notifiable;

    protected $fillable = [
        'cinema_id',
        'first_name',
        'last_name',
        'email',
        'password',
        'invited_at',
        'invitation_accepted_at',
        'deactivated_at',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'invited_at' => 'datetime',
            'invitation_accepted_at' => 'datetime',
            'deactivated_at' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }

    public function cinema(): BelongsTo
    {
        return $this->belongsTo(Cinema::class);
    }

    public function trainingProgress(): HasMany
    {
        return $this->hasMany(TrainingProgress::class);
    }

    public function documentDownloads(): HasMany
    {
        return $this->hasMany(TrainingDocumentDownload::class);
    }

    protected function fullName(): Attribute
    {
        return Attribute::get(fn (): string => trim("{$this->first_name} {$this->last_name}"));
    }

    protected function status(): Attribute
    {
        return Attribute::get(fn (): EmployeeStatus => match (true) {
            $this->isDeactivated() => EmployeeStatus::Deactivated,
            $this->hasAcceptedInvitation() => EmployeeStatus::Active,
            default => EmployeeStatus::Invited,
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->whereNull('deactivated_at')
            ->whereNotNull('invitation_accepted_at');
    }

    public function scopeWithStatus(Builder $query, EmployeeStatus $status): Builder
    {
        return match ($status) {
            EmployeeStatus::Deactivated => $query->whereNotNull('deactivated_at'),
            EmployeeStatus::Active => $query->active(),
            EmployeeStatus::Invited => $query->whereNull('deactivated_at')->whereNull('invitation_accepted_at'),
        };
    }

    public function isDeactivated(): bool
    {
        return $this->deactivated_at !== null;
    }

    public function hasAcceptedInvitation(): bool
    {
        return $this->invitation_accepted_at !== null;
    }

    public function canLogIn(): bool
    {
        return ! $this->isDeactivated() && $this->hasAcceptedInvitation();
    }

    public function sendInvitation(): void
    {
        $this->forceFill(['invited_at' => now()])->save();

        $this->notify(new EmployeeInvitation);
    }

    public function invitationUrl(): string
    {
        return URL::temporarySignedRoute(
            'portal.invitation.show',
            now()->addDays(config('trainings.invitation_expires_in_days')),
            ['employee' => $this],
        );
    }

    public function acceptInvitation(string $password): void
    {
        $this->forceFill([
            'password' => $password,
            'invitation_accepted_at' => now(),
        ])->save();
    }

    public function deactivate(): void
    {
        $this->forceFill(['deactivated_at' => now()])->save();
    }

    public function reactivate(): void
    {
        $this->forceFill(['deactivated_at' => null])->save();
    }

    /**
     * @param  string  $token
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new EmployeeResetPassword($token));
    }
}
