<?php

namespace Modules\User\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',
        'icon',
        'is_active',
        'scroll_speed',
        'bg_color',
        'display_order',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'is_active' => 'integer', // Cast to integer (0 or 1)
        'scroll_speed' => 'integer',
        'display_order' => 'integer',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    /**
     * Default values
     */
    protected $attributes = [
        'icon' => '🌟',
        'is_active' => 1,
        'scroll_speed' => 40,
        'bg_color' => 'blue',
        'display_order' => 0,
    ];

    /**
     * Scope to get only active announcements
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', 1)
            ->where(function ($q) {
                $q->whereNull('start_date')
                    ->orWhere('start_date', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            })
            ->orderBy('display_order', 'asc');
    }

    /**
     * Get active announcements for display
     */
    public static function getActiveAnnouncements()
    {
        return self::active()->get();
    }

    /**
     * Get background gradient class based on color
     */
    public function getBackgroundGradient()
    {
        $gradients = [
            'blue' => 'linear-gradient(90deg, #1e3c72 0%, #2a5298 50%, #1e3c72 100%)',
            'green' => 'linear-gradient(90deg, #134e5e 0%, #71b280 50%, #134e5e 100%)',
            'purple' => 'linear-gradient(90deg, #667eea 0%, #764ba2 50%, #667eea 100%)',
            'orange' => 'linear-gradient(90deg, #f12711 0%, #f5af19 50%, #f12711 100%)',
            'dark' => 'linear-gradient(90deg, #232526 0%, #414345 50%, #232526 100%)',
        ];

        return $gradients[$this->bg_color] ?? $gradients['blue'];
    }

    /**
     * Check if announcement is currently valid (date-wise)
     */
    public function isValid()
    {
        $now = now();

        $startValid = !$this->start_date || $this->start_date <= $now;
        $endValid = !$this->end_date || $this->end_date >= $now;

        return $this->is_active == 1 && $startValid && $endValid;
    }

    /**
     * Get formatted content with icon
     */
    public function getFormattedContent()
    {
        return ($this->icon ? $this->icon . ' ' : '') . $this->content;
    }

    /**
     * Check if active (helper method)
     */
    public function isActive()
    {
        return $this->is_active == 1;
    }

    /**
     * Activate this announcement
     */
    public function activate()
    {
        return $this->update(['is_active' => 1]);
    }

    /**
     * Deactivate this announcement
     */
    public function deactivate()
    {
        return $this->update(['is_active' => 0]);
    }
}
