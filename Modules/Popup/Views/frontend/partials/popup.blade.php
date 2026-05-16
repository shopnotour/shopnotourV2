@php
    use Modules\Popup\Models\PopupMessage;$popup = PopupMessage::active()
        ->forPage($pageKey ?? '')
        ->latest()
        ->first();
@endphp

@if($popup)
    <div id="userPopupOverlay"
         style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:99999;align-items:center;justify-content:center">

        @php
            $colors = [
                'info'    => ['bg' => '#eff6ff', 'border' => '#3b82f6', 'icon' => '#3b82f6',  'iconClass' => 'fa-info-circle'],
                'success' => ['bg' => '#f0fdf4', 'border' => '#16a34a', 'icon' => '#16a34a',  'iconClass' => 'fa-check-circle'],
                'warning' => ['bg' => '#fffbeb', 'border' => '#f59e0b', 'icon' => '#f59e0b',  'iconClass' => 'fa-exclamation-triangle'],
                'danger'  => ['bg' => '#fef2f2', 'border' => '#dc2626', 'icon' => '#dc2626',  'iconClass' => 'fa-exclamation-circle'],
            ];
            $c = $colors[$popup->type] ?? $colors['info'];
        @endphp

        <div style="background:#fff;border-radius:14px;width:100%;max-width:480px;margin:16px;
                box-shadow:0 20px 60px rgba(0,0,0,0.2);overflow:hidden">

            {{-- Colored top bar --}}
            <div style="height:4px;background:{{ $c['border'] }}"></div>

            <div style="padding:24px 24px 20px">

                {{-- Icon + Title --}}
                <div style="display:flex;align-items:flex-start;gap:12px;margin-bottom:14px">
                    <i class="fa {{ $c['iconClass'] }}"
                       style="font-size:1.4rem;color:{{ $c['icon'] }};margin-top:2px;flex-shrink:0"></i>
                    <div>
                        @if($popup->title)
                            <h4 style="font-size:1rem;font-weight:700;color:#111827;margin:0 0 4px">
                                {{ $popup->title }}
                            </h4>
                        @endif
                        <p style="font-size:0.875rem;color:#374151;margin:0;line-height:1.6">
                            {{ $popup->message }}
                        </p>
                    </div>
                </div>

                {{-- Close button --}}
                <div style="display:flex;justify-content:flex-end">
                    <button onclick="closeUserPopup()"
                            style="padding:7px 20px;border-radius:6px;border:none;
                               background:{{ $c['border'] }};color:#fff;
                               font-size:.85rem;font-weight:600;cursor:pointer">
                        {{ __('OK, Got it') }}
                    </button>
                </div>

            </div>
        </div>
    </div>

    <script>
        (function () {
            const popupId = 'popup_seen_{{ $popup->id }}';
            const showOnce = {{ $popup->show_once ? 'true' : 'false' }};
            const overlay = document.getElementById('userPopupOverlay');

            // show_once = true হলে localStorage check করে
            if (showOnce && localStorage.getItem(popupId)) return;

            // Page load হওয়ার 600ms পর দেখাও
            setTimeout(() => {
                overlay.style.display = 'flex';
            }, 600);

            window.closeUserPopup = function () {
                overlay.style.display = 'none';
                if (showOnce) localStorage.setItem(popupId, '1');
            };

            // Backdrop click এ close
            overlay.addEventListener('click', function (e) {
                if (e.target === this) closeUserPopup();
            });

            // ESC key এ close
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') closeUserPopup();
            });
        })();
    </script>
@endif
















