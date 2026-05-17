<?php

namespace Modules\Popup\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class PopupMessage extends Model
{
    protected $fillable = [
        'page_key',
        'title',
        'message',
        'media',
        'media_links',
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

    // ── Constants ──────────────────────────────────────────────
    const MEDIA_TYPE_IMAGE = 'image';
    const MEDIA_TYPE_VIDEO = 'video';
    const MEDIA_TYPE_YOUTUBE_LINK = 'youtube_link';

    /**
     * Get all available media types
     */
    public static function getMediaTypes(): array
    {
        return [
            self::MEDIA_TYPE_IMAGE,
            self::MEDIA_TYPE_VIDEO,
            self::MEDIA_TYPE_YOUTUBE_LINK,
        ];
    }

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

    public function scopeWithMedia($query)
    {
        return $query->whereNotNull('media');
    }

    public function scopeOfMediaType($query, string $mediaType)
    {
        return $query->where('media', $mediaType);
    }

    // ── Accessors & Mutators ───────────────────────────────────
    
    /**
     * Get media URL based on media type
     */
    public function getMediaUrlAttribute()
    {
        if ($this->media === self::MEDIA_TYPE_YOUTUBE_LINK) {
            return $this->media_links;
        }
        
        if ($this->media_links) {
            return asset('popup_message/' . $this->media_links);
        }
        
        return null;
    }

    /**
     * Get embedded YouTube URL
     */
    public function getYoutubeEmbedUrlAttribute()
    {
        if ($this->media !== self::MEDIA_TYPE_YOUTUBE_LINK || !$this->media_links) {
            return null;
        }
        
        return $this->convertToYouTubeEmbedUrl($this->media_links);
    }

    /**
     * Get media file path (for image/video files)
     */
    public function getMediaFilePathAttribute()
    {
        if (in_array($this->media, [self::MEDIA_TYPE_IMAGE, self::MEDIA_TYPE_VIDEO]) && $this->media_links) {
            return storage_path('app/public/popup_message/' . $this->media_links);
        }
        
        return null;
    }

    /**
     * Check if media is an image file
     */
    public function getIsImageFileAttribute()
    {
        if ($this->media !== self::MEDIA_TYPE_IMAGE || !$this->media_links) {
            return false;
        }
        
        $extension = strtolower(pathinfo($this->media_links, PATHINFO_EXTENSION));
        return in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    }
    
    /**
     * Check if media is a video file
     */
    public function getIsVideoFileAttribute()
    {
        if ($this->media !== self::MEDIA_TYPE_VIDEO || !$this->media_links) {
            return false;
        }
        
        $extension = strtolower(pathinfo($this->media_links, PATHINFO_EXTENSION));
        return in_array($extension, ['mp4', 'webm', 'ogg', 'mov']);
    }

    /**
     * Check if media has valid content
     */
    public function getHasValidMediaAttribute(): bool
    {
        if (!$this->media || !$this->media_links) {
            return false;
        }

        switch ($this->media) {
            case self::MEDIA_TYPE_IMAGE:
            case self::MEDIA_TYPE_VIDEO:
                return file_exists($this->media_file_path);
            case self::MEDIA_TYPE_YOUTUBE_LINK:
                return filter_var($this->media_links, FILTER_VALIDATE_URL) !== false;
            default:
                return false;
        }
    }

    // ── Helper Methods ─────────────────────────────────────────

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

    /**
     * Get validation rules for media fields
     */
    public static function getMediaValidationRules(bool $isRequired = false): array
    {
        $rules = [];
        
        if ($isRequired) {
            $rules['media'] = ['required', Rule::in(self::getMediaTypes())];
            $rules['media_links'] = ['required', 'string'];
        } else {
            $rules['media'] = ['nullable', Rule::in(self::getMediaTypes())];
            $rules['media_links'] = ['nullable', 'string'];
        }
        
        return $rules;
    }

    /**
     * Get conditional validation rules based on media type
     */
    public static function getConditionalMediaValidationRules(): array
    {
        return [
            'media' => ['nullable', Rule::in(self::getMediaTypes())],
            'media_links' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    $mediaType = request()->input('media');
                    
                    if (!$mediaType || !$value) {
                        return;
                    }
                    
                    switch ($mediaType) {
                        case self::MEDIA_TYPE_IMAGE:
                            // For image uploads, handle separately in controller
                            break;
                        case self::MEDIA_TYPE_VIDEO:
                            // For video uploads, handle separately in controller
                            break;
                        case self::MEDIA_TYPE_YOUTUBE_LINK:
                            if (!filter_var($value, FILTER_VALIDATE_URL)) {
                                $fail('The YouTube link must be a valid URL.');
                            }
                            if (!preg_match('/(youtube\.com|youtu\.be)/', $value)) {
                                $fail('The URL must be a valid YouTube link.');
                            }
                            break;
                    }
                },
            ],
        ];
    }

    /**
     * Convert YouTube URL to embed URL
     */
    private function convertToYouTubeEmbedUrl(string $url): ?string
    {
        $pattern = '/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/';
        
        if (preg_match($pattern, $url, $matches)) {
            return 'https://www.youtube.com/embed/' . $matches[1];
        }
        
        return null;
    }

    /**
     * Get YouTube video ID
     */
    public function getYoutubeVideoIdAttribute(): ?string
    {
        if ($this->media !== self::MEDIA_TYPE_YOUTUBE_LINK || !$this->media_links) {
            return null;
        }
        
        $pattern = '/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/';
        
        if (preg_match($pattern, $this->media_links, $matches)) {
            return $matches[1];
        }
        
        return null;
    }

    /**
     * Determine if the popup has media content
     */
    public function hasMedia(): bool
    {
        return !is_null($this->media) && !is_null($this->media_links);
    }

    /**
     * Get media type label
     */
    public function getMediaTypeLabelAttribute(): string
    {
        $labels = [
            self::MEDIA_TYPE_IMAGE => 'Image',
            self::MEDIA_TYPE_VIDEO => 'Video',
            self::MEDIA_TYPE_YOUTUBE_LINK => 'YouTube Link',
        ];
        
        return $labels[$this->media] ?? 'None';
    }
}