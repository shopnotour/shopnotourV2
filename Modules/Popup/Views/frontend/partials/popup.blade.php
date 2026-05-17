@php
    use Modules\Popup\Models\PopupMessage;
    $popup = PopupMessage::active()
        ->forPage($pageKey ?? '')
        ->latest()
        ->first();
@endphp

@if($popup)
    <div id="userPopupOverlay"
         style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:99999;align-items:center;justify-content:center;padding:16px;">

        @php
            $colors = [
                'info'    => ['bg' => '#eff6ff', 'border' => '#3b82f6', 'icon' => '#3b82f6',  'iconClass' => 'fa-info-circle'],
                'success' => ['bg' => '#f0fdf4', 'border' => '#16a34a', 'icon' => '#16a34a',  'iconClass' => 'fa-check-circle'],
                'warning' => ['bg' => '#fffbeb', 'border' => '#f59e0b', 'icon' => '#f59e0b',  'iconClass' => 'fa-exclamation-triangle'],
                'danger'  => ['bg' => '#fef2f2', 'border' => '#dc2626', 'icon' => '#dc2626',  'iconClass' => 'fa-exclamation-circle'],
            ];
            $c = $colors[$popup->type] ?? $colors['info'];
        @endphp

        <div style="background:#fff;border-radius:14px;width:100%;max-width:500px;margin:0 auto;
                box-shadow:0 20px 60px rgba(0,0,0,0.3);overflow:hidden;position:relative;">

            {{-- Colored top bar --}}
            <div style="height:4px;background:{{ $c['border'] }}"></div>

            {{-- Close button (X) at top right --}}
            <button onclick="closeUserPopup()"
                    style="position:absolute;top:12px;right:12px;background:rgba(0,0,0,0.1);
                           border:none;border-radius:50%;width:30px;height:30px;
                           cursor:pointer;color:#666;font-size:18px;
                           display:flex;align-items:center;justify-content:center;
                           transition:all 0.2s;z-index:1;"
                    onmouseover="this.style.background='rgba(0,0,0,0.2)'"
                    onmouseout="this.style.background='rgba(0,0,0,0.1)'">
                <i class="fa fa-times"></i>
            </button>

            <div style="padding:24px 24px 20px">

                {{-- Media section (image, video, or YouTube) --}}
                @if($popup->hasMedia())
                    <div style="margin-bottom:20px;border-radius:8px;overflow:hidden;background:#f9fafb;">
                        
                        {{-- Image Media --}}
                        @if($popup->media === 'image' && $popup->media_url)
                            <img src="{{ $popup->media_url }}" 
                                 alt="{{ $popup->title ?? 'Popup media' }}"
                                 style="width:100%;height:auto;display:block;"
                                 onclick="viewFullMedia('{{ $popup->media_url }}', 'image')"
                                 onmouseover="this.style.cursor='pointer'">
                        
                        {{-- Video File Media --}}
                        @elseif($popup->media === 'video' && $popup->media_url)
                            <video controls 
                                   style="width:100%;height:auto;display:block;max-height:400px;">
                                <source src="{{ $popup->media_url }}" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        
                        {{-- YouTube Link Media --}}
                        @elseif($popup->media === 'youtube_link' && $popup->youtube_embed_url)
                            <div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;">
                                <iframe src="{{ $popup->youtube_embed_url }}"
                                        style="position:absolute;top:0;left:0;width:100%;height:100%;border:0;"
                                        frameborder="0"
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                        allowfullscreen>
                                </iframe>
                            </div>
                        @endif
                        
                    </div>
                @endif

                {{-- Icon + Title + Message --}}
                <div style="display:flex;align-items:flex-start;gap:12px;margin-bottom:20px">
                    <i class="fa {{ $c['iconClass'] }}"
                       style="font-size:1.4rem;color:{{ $c['icon'] }};margin-top:2px;flex-shrink:0"></i>
                    <div style="flex:1">
                        @if($popup->title)
                            <h4 style="font-size:1.1rem;font-weight:700;color:#111827;margin:0 0 8px">
                                {{ $popup->title }}
                            </h4>
                        @endif
                        <p style="font-size:0.9rem;color:#374151;margin:0;line-height:1.6">
                            {{ $popup->message }}
                        </p>
                    </div>
                </div>

                {{-- Close button --}}
                <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:8px;">
                    <button onclick="closeUserPopup()"
                            style="padding:8px 24px;border-radius:6px;border:none;
                                   background:{{ $c['border'] }};color:#fff;
                                   font-size:.85rem;font-weight:600;cursor:pointer;
                                   transition:opacity 0.2s;"
                            onmouseover="this.style.opacity='0.9'"
                            onmouseout="this.style.opacity='1'">
                        {{ __('OK, Got it') }}
                    </button>
                </div>

            </div>
        </div>
    </div>

    {{-- Fullscreen Media Viewer Modal --}}
    <div id="fullMediaViewer" 
         style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.95);z-index:100000;
                align-items:center;justify-content:center;cursor:pointer;">
        <button onclick="closeFullMediaViewer()"
                style="position:absolute;top:20px;right:30px;background:none;border:none;
                       color:#fff;font-size:40px;cursor:pointer;z-index:100001;
                       font-family:sans-serif;transition:opacity 0.2s;"
                onmouseover="this.style.opacity='0.7'"
                onmouseout="this.style.opacity='1'">
            &times;
        </button>
        <div id="fullMediaContent" style="max-width:90vw;max-height:90vh;margin:auto;">
        </div>
    </div>

    <script>
        (function () {
            const popupId = 'popup_seen_{{ $popup->id }}';
            const showOnce = {{ $popup->show_once ? 'true' : 'false' }};
            const overlay = document.getElementById('userPopupOverlay');
            
            // Store popup ID for localStorage
            const storageKey = 'popup_seen_{{ $popup->id }}_{{ $popup->updated_at->timestamp }}';
            
            // Check if popup should be shown
            if (showOnce) {
                // Check both old and new keys for backward compatibility
                const oldKeySeen = localStorage.getItem(popupId);
                const newKeySeen = localStorage.getItem(storageKey);
                if (oldKeySeen || newKeySeen) return;
            }

            // Show popup after delay (or immediately for better UX)
            const delay = {{ $popup->hasMedia() ? 800 : 600 }};
            setTimeout(() => {
                overlay.style.display = 'flex';
                document.body.style.overflow = 'hidden'; // Prevent background scrolling
            }, delay);

            // Close popup function
            window.closeUserPopup = function () {
                overlay.style.display = 'none';
                document.body.style.overflow = '';
                
                // Store in localStorage if 'show_once' is enabled
                if (showOnce) {
                    try {
                        localStorage.setItem(storageKey, '1');
                        // Also set old key for backward compatibility
                        localStorage.setItem(popupId, '1');
                    } catch(e) {
                        console.warn('localStorage not available');
                    }
                }
            };

            // Close on backdrop click
            overlay.addEventListener('click', function (e) {
                if (e.target === overlay) closeUserPopup();
            });

            // Close on ESC key
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && overlay.style.display === 'flex') {
                    closeUserPopup();
                }
            });

            // Prevent body scroll when popup is open
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.attributeName === 'style') {
                        if (overlay.style.display === 'flex') {
                            document.body.style.overflow = 'hidden';
                        } else {
                            document.body.style.overflow = '';
                        }
                    }
                });
            });
            observer.observe(overlay, { attributes: true });
        })();

        // Fullscreen media viewer
        function viewFullMedia(url, type) {
            const viewer = document.getElementById('fullMediaViewer');
            const content = document.getElementById('fullMediaContent');
            
            if (type === 'image') {
                content.innerHTML = `<img src="${url}" style="max-width: 90vw; max-height: 90vh; object-fit: contain; border-radius: 8px;">`;
            } else if (type === 'video') {
                content.innerHTML = `<video controls autoplay style="max-width: 90vw; max-height: 90vh; border-radius: 8px;">
                                        <source src="${url}" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>`;
            }
            
            viewer.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeFullMediaViewer() {
            const viewer = document.getElementById('fullMediaViewer');
            const content = document.getElementById('fullMediaContent');
            viewer.style.display = 'none';
            content.innerHTML = '';
            document.body.style.overflow = '';
        }

        // Close fullscreen viewer on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeFullMediaViewer();
            }
        });

        // Close fullscreen viewer on click
        document.getElementById('fullMediaViewer').addEventListener('click', function(e) {
            if (e.target === this) closeFullMediaViewer();
        });
    </script>

    {{-- Optional: Add responsive styles for mobile --}}
    <style>
        @media (max-width: 640px) {
            #userPopupOverlay > div {
                max-width: 95% !important;
                margin: 0 auto !important;
            }
            
            #userPopupOverlay > div > div {
                padding: 20px !important;
            }
            
            #userPopupOverlay h4 {
                font-size: 1rem !important;
            }
            
            #userPopupOverlay p {
                font-size: 0.85rem !important;
            }
        }
        
        /* Animation for popup */
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        #userPopupOverlay > div {
            animation: slideUp 0.3s ease-out;
        }
        
        /* Smooth transition for overlay */
        #userPopupOverlay {
            transition: all 0.3s ease;
        }
    </style>
@endif