<?php

namespace App\Models;

use App\Enums\EmployeeStatus;
use App\Notifications\EmployeeInvitation;
use App\Notifications\EmployeeLoginLink;
use App\Notifications\EmployeeResetPassword;
use Carbon\CarbonInterface;
use Database\Factories\EmployeeFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

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
        'login_token_hash',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'invited_at' => 'datetime',
            'invitation_accepted_at' => 'datetime',
            'deactivated_at' => 'datetime',
            'last_login_at' => 'datetime',
            'login_token_expires_at' => 'datetime',
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
            ->whereNotNull('invitation_accepted_at')
            ->whereHas('cinema');
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

    /**
     * Employees of a cinema removed from the administration lose access to the portal.
     */
    public function canAccessCinema(): bool
    {
        return $this->cinema !== null;
    }

    public function canLogIn(): bool
    {
        return ! $this->isDeactivated() && $this->hasAcceptedInvitation() && $this->canAccessCinema();
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
            ['employee' => $this, 'fingerprint' => $this->invitationFingerprint()],
        );
    }

    /**
     * Ties an invitation link to the email and sending time, so resending the invitation or
     * correcting the email invalidates links sent earlier.
     */
    public function invitationFingerprint(): string
    {
        return hash_hmac('sha256', $this->email.'|'.$this->invited_at?->getTimestamp(), config('app.key'));
    }

    public function hasInvitationFingerprint(?string $fingerprint): bool
    {
        return is_string($fingerprint) && hash_equals($this->invitationFingerprint(), $fingerprint);
    }

    public function acceptInvitation(): void
    {
        $this->forceFill(['invitation_accepted_at' => now()])->save();
    }

    /**
     * Emails a single-use login link, valid for a few minutes.
     */
    public function sendLoginLink(): void
    {
        $this->notify(new EmployeeLoginLink(
            $this->createLoginUrl(now()->addMinutes(config('trainings.login_link_expires_in_minutes'))),
        ));
    }

    /**
     * Link an administrator can copy and send to the employee: the invitation link while the
     * invitation is pending, otherwise a new single-use login link valid for a day.
     */
    public function loginUrlForAdministrator(): string
    {
        if (! $this->hasAcceptedInvitation()) {
            return $this->invitationUrl();
        }

        return $this->createLoginUrl(now()->addHours(config('trainings.administrator_login_link_expires_in_hours')));
    }

    /**
     * Creates a single-use login link. Only a hash of the token is stored, and creating a new
     * link invalidates the previous one.
     */
    public function createLoginUrl(CarbonInterface $expiresAt): string
    {
        $token = Str::random(64);

        $this->forceFill([
            'login_token_hash' => hash('sha256', $token),
            'login_token_expires_at' => $expiresAt,
        ])->save();

        return route('portal.login-link.show', [
            'employee' => $this,
            'token' => $token,
        ]);
    }

    public function hasValidLoginToken(string $token): bool
    {
        return $this->login_token_hash !== null
            && $this->login_token_expires_at?->isFuture()
            && hash_equals($this->login_token_hash, hash('sha256', $token));
    }

    /**
     * Uses up the login token: the link cannot be used again.
     */
    public function consumeLoginToken(): void
    {
        $this->forceFill([
            'login_token_hash' => null,
            'login_token_expires_at' => null,
        ])->save();
    }

    public function recordLogin(): void
    {
        $this->forceFill(['last_login_at' => now()])->save();
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
