<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Nationality extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name_ar',
        'name_en',
        'slug',
        'code',
        'country_code',
        'flag',
        'description',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * The CV panel addresses nationalities by ISO code (/nationality/et),
     * falling back to the id. Resolution still accepts the slug so the
     * existing public routes keep working.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        if ($field !== null) {
            return parent::resolveRouteBinding($value, $field);
        }

        $value = (string) $value;

        return static::query()
            ->where(function (Builder $query) use ($value) {
                $query->whereRaw('LOWER(code) = ?', [mb_strtolower($value)])
                    ->orWhere('slug', $value);

                if (ctype_digit($value)) {
                    $query->orWhere('id', (int) $value);
                }
            })
            ->first();
    }

    /** The key the panel builds public URLs with. */
    public function getPublicKeyAttribute(): string
    {
        return $this->code ? mb_strtolower($this->code) : (string) $this->id;
    }

    /** Translated display name, falling back to the stored Arabic name. */
    public function getDisplayNameAttribute(): string
    {
        if ($this->code) {
            $key = 'nationalities.'.mb_strtoupper($this->code);

            if (($translated = __($key)) !== $key) {
                return $translated;
            }
        }

        return $this->name_ar;
    }

    /**
     * Flag or cover image for this nationality.
     *
     * Falls back to the bundled SVG named after the ISO code, so a nationality
     * with no uploaded flag still shows one.
     */
    public function photoUrl(): ?string
    {
        if ($this->flag_url) {
            return $this->flag_url;
        }

        if (! $this->code) {
            return null;
        }

        $relative = 'images/flags/'.mb_strtolower($this->code).'.svg';

        return is_file(public_path($relative)) ? asset($relative) : null;
    }

    public function admins(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'admin_nationality', 'nationality_id', 'admin_id')
            ->withTimestamps();
    }

    public function workers(): HasMany
    {
        return $this->hasMany(Worker::class);
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(Candidate::class);
    }

    public function recruitmentRequests(): HasMany
    {
        return $this->hasMany(RecruitmentRequest::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name_ar');
    }

    public function getFlagUrlAttribute(): ?string
    {
        return $this->flag ? storage_url($this->flag) : null;
    }

    public function getNameAttribute(): string
    {
        return $this->name_ar;
    }
}
