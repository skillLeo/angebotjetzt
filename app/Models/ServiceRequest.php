<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ServiceRequest extends Model
{
    use HasFactory;

    protected $table = 'requests';

    protected $fillable = [
        'request_number', 'user_id', 'service_type_id', 'vehicle_make', 'vehicle_model',
        'first_registration', 'mileage', 'vin', 'fuel_type', 'transmission',
        'plz', 'ort', 'strasse', 'preferred_date', 'alternative_date', 'notes', 'answers',
        'contact_name', 'contact_email', 'contact_phone', 'status', 'matched_count', 'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'preferred_date' => 'date',
            'alternative_date' => 'date',
            'expires_at' => 'datetime',
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

    public static function nextRequestNumber(): string
    {
        $year = now()->year;
        $last = static::where('request_number', 'like', "AJ-{$year}-%")
            ->orderByDesc('id')
            ->value('request_number');
        $seq = $last ? ((int) substr($last, -6)) + random_int(3, 10) : random_int(3, 10);

        return sprintf('AJ-%d-%06d', $year, $seq);
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
