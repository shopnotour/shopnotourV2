<?php

namespace Modules\Popup\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class PopupMessage extends Model
{
    protected $fillable = [
        'page_key',
        'title',
        'message',
        'type',
        'is_active',
        'show_once',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'show_once' => 'boolean',
    ];

    // ── Relations ──────────────────────────────────────────────
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // ── Scopes ─────────────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForPage($query, string $pageKey)
    {
        return $query->where('page_key', $pageKey);
    }

    // ── Helpers ────────────────────────────────────────────────

    /**
     * Available page keys — এখানে নতুন page যোগ করুন
     */
    public static function pageKeys(): array
    {
        return [
            'dashboard'            => 'Dashboard',
            'void_requests'        => 'Void Requests',
            'refund_requests'      => 'Refund Requests',
            'reissue_requests'     => 'Reissue Requests',
            'ssr_requests'         => 'SSR Requests',
            'credit_transactions'  => 'Credit Transactions',
            'booking_history'      => 'Booking History',
            'home'                 => 'home',
            'search'               => 'search',
            'cart'                 => 'cart',
            'checkout'             => 'checkout',
        ];
    }
}
