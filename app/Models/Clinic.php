<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

/**
 * The Clinic model represents a clinic entity.
 */
class Clinic extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'contact_no',
        'timezone_id',
        'is_online_booking',
        'booking_link',
        'average_checkup_fee',
        'six_month_recall_sms',
        'software_id',
        'other_software',
        'mobile_no',
        'software_notify_requested',
        'is_approved',
        'disapproval_reason',
        'software_notify_requested',
        'is_suspended',
        'disapproved_at',
        'disapproved_by',
        'state_id',
        'registered_at',
        'dedicated_number',
        'dedicated_country_code',
        'status',
        'approved_at',
    ];

    protected $casts = [
        'registered_at' => 'datetime',
    ];

    /**
     * Get the user associated with the clinic.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the software associated with the clinic.
     *
     * @return BelongsTo
     */
    public function software(): BelongsTo
    {
        return $this->belongsTo(Software::class);
    }

    /**
     * Get the subscription plan associated with the clinic's user.
     *
     * @return mixed
     */
    public function subscriptionPlan()
    {
        return $this->user->userSubscriptionPlan() ?? null;
    }

    /**
     * Get the driver's license associated with the clinic through the user.
     *
     * @return HasOneThrough
     */
    public function driverLicence(): HasOneThrough
    {
        return $this->hasOneThrough(
            DriverLicence::class,
            User::class,
            'id',
            'user_id',
            'user_id',
            'id'
        );
    }

    /**
     * Get the admin who disapproved the clinic.
     *
     * @return BelongsTo
     */
    public function disapprovedByAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disapproved_by');
    }

    /**
     * Get the messages associated with the clinic.
     *
     * @return HasMany
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Get the insurances associated with the clinic.
     *
     * @return HasMany
     */
    public function insurances(): HasMany
    {
        return $this->hasMany(Insurance::class);
    }

    /**
     * Relation for all patients of clinic.
     *
     * @return HasMany
     */
    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class);
    }

    /**
     * Relation for all import of clinic.
     *
     * @return HasMany
     */
    public function import(): HasMany
    {
        return $this->hasMany(Import::class);
    }

    /**
     * Get the state associated with the clinic.
     *
     * @return BelongsTo
     */
    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    /**
     * Get the current date and time in the clinic's state timezone.
     *
     * @return string Current datetime formatted as 'Y-m-d H:i:s'.
     */
    public function getCurrentTimeAttribute(): string
    {
        $timezone = $this->state->timezone ?? config('app.timezone'); // fallback if missing

        return now()->timezone($timezone)->format('Y-m-d H:i:s');
    }

    /**
     * Get the registered_at timestamp.
     *
     * @return string|null
     */
    public function getRegisteredAtAttribute(): ?string
    {
        $datetime = $this->attributes['registered_at'] ?? null;

        if (! $datetime) {
            return null;
        }

        return Carbon::parse($datetime)
            ->timezone(config('constants.CLINICS.REGISTERED_DATE_TIMEZONE'))
            ->format('h:i A, M d, Y');
    }

    /**
     * Get the dedicated number with country code in formatted style (e.g., +61 431 434 593).
     *
     * @return string|null The formatted dedicated number with country code, or null if data is incomplete.
     */
    public function getDedicatedNumberWithCountryCodeAttribute(): ?string
    {
        return ($this->dedicated_country_code && $this->dedicated_number)
            ? '0'.
                substr($this->dedicated_number, 0, 3).' '.
                substr($this->dedicated_number, 3, 3).' '.
                substr($this->dedicated_number, 6)
            : null;
    }

    /**
     * Get the latest import record for the model.
     *
     * @return HasOne
     */
    public function latestImport(): HasOne
    {
        return $this->hasOne(Import::class)->latestOfMany();
    }

    /**
     * Get all notifications related to the model, ordered by latest first.
     *
     * @return HasMany
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class)->orderByDesc('id');
    }

    /**
     * Get all unread notifications related to the model, ordered by latest first.
     *
     * @return HasMany
     */
    public function unReadNotifications(): HasMany
    {
        return $this->hasMany(Notification::class)
            ->where('is_read', 0)
            ->orderByDesc('id');
    }

    /**
     * Get all notes associated with the model.
     *
     * @return HasMany
     */
    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }

    /**
     * Get the latest import record for the model.
     *
     * @return HasOne
     */
    public function latestCompletedImport(): HasOne
    {
        return $this->hasOne(Import::class)->whereNotNull('end_time')->latestOfMany('end_time');
    }
}
