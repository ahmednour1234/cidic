<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminNotification extends Model
{
    use HasFactory;

    public const TYPE_CV_RESERVED = 'cv_reserved';
    public const TYPE_CV_UPLOADED = 'worker_cv_uploaded';
    public const TYPE_ASSIGNED = 'worker_assigned';
    public const TYPE_UNASSIGNED = 'worker_unassigned';
    public const TYPE_TAMARA_PAID = 'worker_tamara_paid';

    /** The types the CV panel's notification screens cover. */
    public const PANEL_TYPES = [
        self::TYPE_CV_RESERVED,
        self::TYPE_CV_UPLOADED,
        self::TYPE_ASSIGNED,
        self::TYPE_UNASSIGNED,
        self::TYPE_TAMARA_PAID,
    ];

    protected $fillable = [
        'admin_id',
        'type',
        'title',
        'body',
        'url',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function isUnread(): bool
    {
        return $this->read_at === null;
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    public function scopePanelTypes(Builder $query): Builder
    {
        return $query->whereIn('type', self::PANEL_TYPES);
    }

    // Hex values, so views must apply these with inline style="" - a hex in a
    // class attribute renders without colour.

    public function getIconBgAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_CV_RESERVED => '#e6f0f9',
            self::TYPE_CV_UPLOADED => '#e9eef4',
            self::TYPE_ASSIGNED => '#e8f6ef',
            self::TYPE_UNASSIGNED => '#fef2f2',
            self::TYPE_TAMARA_PAID => '#fdf3e3',
            default => '#f1f5f9',
        };
    }

    public function getIconColorAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_CV_RESERVED => '#0060a8',
            self::TYPE_CV_UPLOADED => '#003f74',
            self::TYPE_ASSIGNED => '#1a9d63',
            self::TYPE_UNASSIGNED => '#b91c1c',
            self::TYPE_TAMARA_PAID => '#d38b1a',
            default => '#475569',
        };
    }

    public function getIconSvgAttribute(): string
    {
        $paths = match ($this->type) {
            // bookmark
            self::TYPE_CV_RESERVED => '<path d="M6 4h12v16l-6-4-6 4z"/>',
            // upload arrow
            self::TYPE_CV_UPLOADED => '<path d="M12 16V5m0 0L8 9m4-4 4 4"/><path d="M5 19h14"/>',
            // check
            self::TYPE_ASSIGNED => '<path d="m5 13 4 4L19 7"/>',
            // undo
            self::TYPE_UNASSIGNED => '<path d="M9 14 4 9l5-5"/><path d="M4 9h11a5 5 0 0 1 0 10H9"/>',
            // wallet
            self::TYPE_TAMARA_PAID => '<path d="M3 7h15a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M16 13h2"/>',
            default => '<circle cx="12" cy="12" r="8"/>',
        };

        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" '
            .'stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">'.$paths.'</svg>';
    }
}
