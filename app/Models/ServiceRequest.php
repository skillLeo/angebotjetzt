<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\URL;

class ServiceRequest extends Model
{
    use HasFactory;

    protected $table = 'requests';

    protected $fillable = [
        'request_number', 'user_id', 'service_type_id', 'vehicle_make', 'vehicle_model',
        'first_registration', 'mileage', 'vin', 'fuel_type', 'transmission',
        'plz', 'ort', 'strasse', 'preferred_date', 'alternative_date', 'notes', 'answers',
        'contact_name', 'contact_email', 'contact_phone', 'status', 'matched_count', 'expires_at',
        'first_offer_at', 'offer_reminder_sent_at', 'offer_final_reminder_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'preferred_date' => 'date',
            'alternative_date' => 'date',
            'expires_at' => 'datetime',
            'first_offer_at' => 'datetime',
            'offer_reminder_sent_at' => 'datetime',
            'offer_final_reminder_sent_at' => 'datetime',
            'answers' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(RequestPhoto::class, 'request_id');
    }

    public function matches(): HasMany
    {
        return $this->hasMany(RequestMatch::class, 'request_id');
    }

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class, 'request_id');
    }

    public function booking(): HasOne
    {
        return $this->hasOne(Booking::class, 'request_id');
    }

    /**
     * A stable "Anbieter A/B/C" label per offer, ranked by submission order
     * (offer id ascending) so the same provider gets the same letter
     * everywhere a page displays this request's offers, regardless of that
     * page's own sort order (price, recency, ...) — used to keep a
     * provider's identity anonymous to the customer until their offer is
     * accepted or otherwise decided.
     *
     * @return array<int, string>
     */
    public function offerLabels(): array
    {
        return $this->offers()->orderBy('id')->pluck('id')
            ->mapWithKeys(fn ($id, $i) => [$id => 'Anbieter '.chr(65 + $i)])
            ->all();
    }

    public function offerLabel(int $offerId): string
    {
        return $this->offerLabels()[$offerId] ?? 'Anbieter';
    }

    /**
     * A signed "view your offers" link, safe to email to a guest who has no
     * account yet — the signature itself is what proves whoever clicks it
     * actually reached the inbox this request's emails go to, since there's
     * no session/login to check for someone in that position.
     */
    public function offersViewUrl(): string
    {
        return URL::temporarySignedRoute('offers.view', now()->addDays(30), ['serviceRequest' => $this->id]);
    }

    /**
     * Same reasoning as offersViewUrl() — a signed "view my requests" link
     * for the initial confirmation email, safe to send before any account
     * exists.
     */
    public function myRequestsViewUrl(): string
    {
        return URL::temporarySignedRoute('requests.view', now()->addDays(30), ['serviceRequest' => $this->id]);
    }

    /**
     * AJ + 2-digit year + 2-digit month + 4-digit sequence (e.g. AJ26080005
     * for August 2026) — replaces the old AJ-YYYY-NNNNNN format for every
     * new request going forward. Existing records keep their old-format
     * number untouched; this only affects numbers generated from here on.
     * The sequence resets each month: the LIKE match is scoped to the
     * current prefix, so a new month always starts from a fresh random
     * jump rather than continuing the previous month's count, and the old
     * hyphenated format never matches this prefix so the two can't collide
     * or cross-contaminate the "last" lookup.
     */
    public static function nextRequestNumber(): string
    {
        $prefix = 'AJ'.now()->format('ym');
        $last = static::where('request_number', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->value('request_number');
        $seq = $last ? ((int) substr($last, -4)) + random_int(3, 10) : random_int(3, 10);

        return sprintf('%s%04d', $prefix, $seq);
    }

    /**
     * Numbers are assigned as a random +3..+10 jump from the last one, so two
     * concurrent submissions reading the same "last" value could compute the
     * same number. request_number carries a DB-level unique constraint, so a
     * genuine collision surfaces as an integrity-constraint violation here —
     * caught and retried with a freshly-drawn number rather than serializing
     * every request submission behind a lock.
     */
    public static function createWithUniqueNumber(array $attributes): self
    {
        for ($attempt = 1; $attempt <= 5; $attempt++) {
            try {
                return static::create([...$attributes, 'request_number' => static::nextRequestNumber()]);
            } catch (\Illuminate\Database\QueryException $e) {
                if ($attempt === 5 || $e->getCode() !== '23000') {
                    throw $e;
                }
            }
        }

        throw new \RuntimeException('Could not generate a unique request number.');
    }
}
