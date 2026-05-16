{{-- ===================== FOOTER BRAND SECTION ===================== --}}
<div class="footer-brand-section">

    <div class="container">

        {{-- ================= TOP AREA ================= --}}
        <div class="footer-brand-wrap">

            {{-- Left Content --}}
            <div class="footer-brand-left">

                @php $logo_id = setting_item('logo_id'); @endphp

                <div class="footer-logo-wrap">
                    @if ($logo_id)
                        <img src="{{ get_file_url($logo_id, 'full') }}" alt="{{ setting_item('site_title') }}"
                            class="footer-logo-img">
                    @else
                        <span class="footer-logo-text">
                            {{ setting_item('site_title') }}
                        </span>
                    @endif
                </div>

                <div class="footer-brand-text">
                    <strong>{{ setting_item('site_title') }}</strong>
                    is your trusted travel partner.
                    <br>
                    Smart bookings, competitive deals, and reliable support — all in one platform.
                </div>

            </div>

            {{-- Divider --}}
            <div class="footer-divider"></div>

            {{-- Right Logos --}}
            <div class="footer-brand-right">

                {{-- Verified --}}
                <div class="brand-item">
                    <span class="brand-label">{{ __('Verified By') }}</span>

                    <img src="https://tripfindy-public.s3.ap-southeast-1.amazonaws.com/event/dig.svg" alt="Verified By"
                        class="brand-logo">
                </div>

                {{-- IATA --}}
                <div class="brand-item">
                    <span class="brand-label">{{ __('Authorised By') }}</span>

                    <img src="https://tripfindy-public.s3.ap-southeast-1.amazonaws.com/event/iata.svg"
                        alt="Authorised By" class="brand-logo">
                </div>

                {{-- BASIS --}}
                <div class="brand-item">
                    <span class="brand-label">{{ __('Member of') }}</span>

                    <img src="https://tripfindy-public.s3.ap-southeast-1.amazonaws.com/event/basis.svg" alt="Member of"
                        class="brand-logo basis-logo">
                </div>

            </div>

        </div>

    </div>

    {{-- ================= BOTTOM AREA ================= --}}
    <div class="footer-bottom-area">

        {{-- Social Icons --}}
        <div class="footer-socials">

            <a href="#" target="_blank">
                <img src="{{ asset('images/icons/png/fb.png') }}" alt="Facebook">
            </a>

            <a href="#" target="_blank">
                <img src="{{ asset('images/icons/png/insta.png') }}" alt="Instagram">
            </a>

            <a href="#" target="_blank">
                <img src="{{ asset('images/icons/png/x.png') }}" alt="Twitter">
            </a>

            <a href="#" target="_blank">
                <img src="{{ asset('images/icons/png/lin.png') }}" alt="Linkedin">
            </a>

            <a href="#" target="_blank">
                <img src="{{ asset('images/icons/png/yt.png') }}" alt="Youtube">
            </a>

        </div>

        {{-- Footer Links (from footer_content_right → Support) --}}
        @if ($footerWidgets = setting_item_with_lang('footer_content_right'))
            @php
                $footerWidgets = json_decode($footerWidgets);
                $support = collect($footerWidgets)->firstWhere('title', 'Support');
            @endphp
            @if ($support)
                <div class="footer-links">
                    {!! $support->content !!}
                </div>
            @endif
        @endif

        {{-- Sister Concern --}}
        {{-- <div class="footer-company">

            <span>A sister concern of</span>

            <a href="https://www.impressivebd.com" target="_blank">

                <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAeIAAAIACAYAAABejW+3AAAACXBIWXMAAAsTAAALEwEAmpwYAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAmqSURBVHgB7d3LcRRXGEDh25QCIITJwMrACoEQRASw8RocAd55KTIgBEjABY7ACoEMxt2WKFM22EKPOc3091X9NZoE5tR9dGsaAKzKr7/9dP5ov78YbMD+8tEAADJCDAAhIQaAkBADQEiIASAkxAAQEmIACAkxAISEGABCQgwAISEGgJAQA0BIiAEgJMQAEBJiAAgJMQCEhBgAQkIMACEhBoCQEANASIgBICTEABASYgAICTEAhIQYAEJCDAAhIQaAkBADQEiIASAkxAAQEmIACAkxAISEGABCQgwAISEGgNDJfr8/GwDr93Gapg8DjszJPG8HwPq9nufpgCNjaxoAQkIMACEhBoCQEANASIgBICTEABASYgAICTEAhIQYAEJCDAAhIQaAkBADQEiIASAkxAAQEmIACAkxAISEGABCQgwAISEGgJAQA0BIiAEgJMQAEBJiAAgJMQCEhBgAQkIMACEhBoCQEANASIgBICTEABASYgAICTEAhIQYAEJCDAAhIQaAkBADQEiIASAkxAAQEmIACAkxAISEGABCQgwAISEGgJAQA0BIiAEgJMQAEBJiAAgJMQCEhBgAQkIMACEhBoCQEANASIgBICTEABASYgAICTEAhIQYAEJCDAAhIQaAkBADQEiIASAkxAAQEmIACAkxAISEGABCQgwAISEGgJAQA0BIiAEgJMQAEBJiAAgJMQCEhBgAQkIMACEhBoCQEANASIgBICTEABASYgAICTEAhIQYAEJCDAAhIQaAkBADQEiIASAkxAAQEmIACAkxAISEGABC0342ANbv9TRNTwccGStiAAgJMQCEhBgAQkIMACEhBoCQEANASIgBICTEABASYgAICTEAhIQYAEJCDAAhIQaAkBADQEiIASAkxAAQEmIACAkxAISEGABCQgwAISEGgJAQA0BIiAEgJMQAEBJiAAgJMQCEhBgAQkIMACEhBoCQEANASIgBICTEABASYgAInQy25ud5Lgd8fy4HHCEh3p530zS9GwCsgq1pAAgJMQCEhBgAQkIMACEhBoCQEANASIgBICTEABASYgAICTEAhIQYAEJCDAAhIQaAkBADQEiIASAkxAAQEmIACAkxAISEGABCQgwAISEGgJAQA0BIiAEgJMQAEDoZAKzKfr/fzR9ngy34KMQA63M2z8VgCy5tTQNASIgBICTEABASYgAICTEAhIQYAEJCDAAhIQaAkBADQEiIASAkxAAQEmIACAkxAISEGABCQgwAISEGgJAQA0BIiAEgJMQAEBJiAAgJMQCEhBgAQkIMACEhBoCQEANASIgBICTEABASYgAICTEAhIQYAEJCDAAhIQaAkBADQEiIASAkxAAQEmIACAkxAISEGABCQgwAISEGgJAQA0BIiAEgJMQAEBJiAAgJMQCEhBgAQkIMACEhBoCQEANASIgBICTEABASYgAI/Qkwas8DvBXb5QAAAABJRU5ErkJggg=="
                    alt="Impressive Group">

                <strong>Impressive Group</strong>

            </a>

        </div> --}}

        {{-- Copyright --}}
        <div class="footer-copyright">
            {!! setting_item_with_lang('footer_text_left') ?? '' !!}
        </div>

    </div>

</div>

<style>
    /* ================= MAIN SECTION ================= */

    .footer-brand-section {
        padding-top: 40px;
    }

    /* Main Layout */
    .footer-brand-wrap {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: nowrap;
        position: relative;
        padding-bottom: 35px;
        gap: 40px;
    }

    /* Equal Width */
    .footer-brand-left,
    .footer-brand-right {
        flex: 0 0 50%;
        width: 50%;
    }

    /* ================= LEFT SIDE ================= */

    .footer-brand-left {
        padding-right: 55px;
    }

    .footer-logo-wrap {
        margin-bottom: 18px;
    }

    .footer-logo-img {
        max-width: 180px;
        width: auto;
        height: auto;
        display: block;
    }

    .footer-logo-text {
        font-size: 30px;
        font-weight: 700;
        color: white;
    }

    .footer-brand-text {
        color: rgba(255, 255, 255, .92);
        font-size: 17px;
        line-height: 1.8;
        font-weight: 400;
        max-width: 580px;
    }

    .footer-brand-text strong {
        color: white;
        font-weight: 700;
    }

    /* ================= DIVIDER ================= */

    .footer-divider {
        position: absolute;
        left: 50%;
        top: 0;
        bottom: 0;
        width: 1px;
        background: rgba(255, 255, 255, .30);
        transform: translateX(-50%);
    }

    /* ================= RIGHT SIDE ================= */

    .footer-brand-right {
        padding-left: 55px;

        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 40px;
    }

    .brand-item {
        /* text-align: center; */
        flex: 1;
    }

    .brand-label {
        display: block;
        color: white;
        font-size: 15px;
        font-weight: 600;
        margin-bottom: 14px;
        line-height: 1.4;
    }

    .brand-logo {
        max-width: 120px;
        width: 100%;
        height: auto;
        object-fit: contain;
    }

    .basis-logo {
        max-width: 165px;
    }

    /* ================= BOTTOM AREA ================= */

    .footer-bottom-area {
        border-top: 1px solid rgba(255, 255, 255, .08);
        padding: 30px 20px;
        text-align: center;
    }

    /* ================= SOCIAL ICONS ================= */

    .footer-socials {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 18px;
        flex-wrap: wrap;
        margin-bottom: 22px;
    }

    .footer-socials a img {
        width: 22px;
        height: 22px;
        object-fit: contain;
        opacity: .9;
        transition: .2s ease;
    }

    .footer-socials a:hover img {
        opacity: 1;
        transform: translateY(-2px);
    }

    /* ================= FOOTER LINKS ================= */

    .footer-links {
        display: flex;
        justify-content: center;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 18px;
    }

    .footer-links a,
    .footer-links span {
        color: rgba(255, 255, 255, .82);
        font-size: 15px;
        text-decoration: none;
        transition: .2s ease;
    }

    .footer-links a:hover {
        color: white;
    }

    /* ================= COMPANY ================= */

    .footer-company {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 12px;

        color: rgba(255, 255, 255, .75);
        font-size: 15px;
    }

    .footer-company a {
        display: inline-flex;
        align-items: center;
        gap: 8px;

        color: white;
        text-decoration: none;
        font-weight: 700;
    }

    .footer-company img {
        width: 16px;
        height: 16px;
        object-fit: contain;
    }

    /* ================= COPYRIGHT ================= */

    .footer-copyright {
        color: rgba(255, 255, 255, .65);
        font-size: 14px !important;
        ;
    }

    /* ================= TABLET ================= */

    @media (max-width: 991px) {

        .footer-brand-wrap {
            flex-direction: column;
            align-items: flex-start;
            gap: 24px;
            padding-bottom: 24px;
        }

        .footer-brand-left,
        .footer-brand-right {
            width: 100%;
            flex: 0 0 100%;
            padding: 0;
        }

        .footer-divider {
            position: relative;
            left: auto;
            top: auto;
            bottom: auto;
            transform: none;

            width: 100%;
            height: 1px;
        }

        .footer-brand-right {
            width: 100%;
            display: flex;
            justify-content: space-between;
            gap: 20px;
        }

        .brand-item {
            flex: 1;
        }
    }

    /* ================= MOBILE ================= */

    @media (max-width: 767px) {

        .footer-brand-section {
            padding-top: 18px;
        }

        .footer-brand-wrap {
            gap: 18px;
            padding-bottom: 18px;
        }

        /* LEFT — logo + text side by side */
        .footer-brand-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .footer-logo-wrap {
            margin-bottom: 0;
            flex-shrink: 0;
        }

        .footer-logo-img {
            max-width: 105px;
        }

        .footer-logo-text {
            font-size: 20px;
        }

        .footer-brand-text {
            font-size: 12px;
            line-height: 1.5;
        }

        /* RIGHT */
        .footer-brand-right {
            width: 100%;
            display: flex;
            flex-wrap: nowrap;
            justify-content: space-between;
            align-items: flex-start;
            gap: 6px;
            padding-left: 0;
        }

        .brand-item {
            flex: 1;
            min-width: 0;
            /* text-align: center; */
        }

        .brand-label {
            font-size: 9px;
            margin-bottom: 5px;
            line-height: 1.2;
        }

        .brand-logo {
            width: 100%;
            max-width: 58px;
            height: auto;
            object-fit: contain;
        }

        .basis-logo {
            max-width: 75px;
        }

        /* SOCIAL */
        .footer-socials {
            gap: 10px;
            margin-bottom: 14px;
        }

        .footer-socials a img {
            width: 16px;
            height: 16px;
        }

        /* LINKS */
        .footer-links {
            gap: 4px;
            margin-bottom: 10px;
        }

        .footer-links a,
        .footer-links span {
            font-size: 11px;
        }

        /* COMPANY */
        .footer-company {
            font-size: 11px;
            gap: 5px;
            margin-bottom: 8px;
        }

        .footer-company img {
            width: 12px;
            height: 12px;
        }

        /* COPYRIGHT */
        .footer-copyright {
            font-size: 10px !important;
        }

        /* BOTTOM */
        .footer-bottom-area {
            padding: 18px 10px;
        }
    }

    /* ================= EXTRA SMALL MOBILE ================= */

    @media (max-width: 420px) {

        .footer-brand-section {
            padding-top: 14px;
        }

        .footer-brand-left {
            gap: 10px;
        }

        .footer-logo-img {
            max-width: 90px;
        }

        .footer-brand-text {
            font-size: 11px;
        }

        .brand-label {
            font-size: 8px;
        }

        .brand-logo {
            max-width: 48px;
        }

        .basis-logo {
            max-width: 65px;
        }

        .footer-links a,
        .footer-links span,
        .footer-company,
        .footer-copyright {
            font-size: 10px !important;
        }

        .footer-socials a img {
            width: 14px;
            height: 14px;
        }
    }
</style>
