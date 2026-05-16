/**
 * VisitorTracker
 * ─────────────────────────────────────────────────────────────
 * প্রতিটা page এ include করতে হবে।
 * Blade layout এ একবার রাখলেই সব page এ কাজ করবে।
 *
 * Blade এ যোগ করুন (layout/app.blade.php এর </body> এর আগে):
 *
 *   <script>
 *     window.VisitorTrackerConfig = {
 *       pageEnterUrl  : "{{ route('visitor.page.enter') }}",
 *       pageExitUrl   : "{{ route('visitor.page.exit') }}",
 *       activityUrl   : "{{ route('visitor.activity') }}",
 *       csrfToken     : "{{ csrf_token() }}",
 *       currentPage   : "{{ url()->current() }}",
 *       pageTitle     : "{{ config('app.name') }}",
 *       sessionData   : {!! json_encode(session()->except(['_token', 'password', '_flash'])) !!},
 *     };
 *   </script>
 *   <script src="{{ asset('js/visitor-tracker.js') }}"></script>
 */

(function () {
    'use strict';

    // ─── Config ──────────────────────────────────────────────────
    const config = window.VisitorTrackerConfig || {};

    if (!config.pageEnterUrl) {
        console.warn('VisitorTracker: config not found');
        return;
    }

    // ─── State ───────────────────────────────────────────────────
    let pageLogId       = null;
    let pageEnterTime   = Date.now();
    let maxScrollDepth  = 0;
    let isTracking      = false;
    let activityQueue   = [];       // offline হলে queue করে রাখো
    let isOnline        = navigator.onLine;

    // ─── Utility ─────────────────────────────────────────────────

    function post(url, data) {
        return fetch(url, {
            method      : 'POST',
            headers     : {
                'Content-Type' : 'application/json',
                'X-CSRF-TOKEN' : config.csrfToken,
                'Accept'       : 'application/json',
            },
            body        : JSON.stringify(data),
            keepalive   : true,   // page unload এর সময়ও পাঠাবে
        }).then(r => r.json()).catch(() => null);
    }

    function getTimeSpent() {
        return Math.floor((Date.now() - pageEnterTime) / 1000);
    }

    // ─── Page Enter ──────────────────────────────────────────────

    async function trackPageEnter() {
        if (isTracking) return;
        isTracking = true;

        const data = {
            page_url            : config.currentPage || window.location.href,
            page_title          : document.title,
            referrer            : document.referrer || null,
            // আগের page এর data (SPA navigation এর জন্য)
            previous_time_spent   : 0,
            previous_scroll_depth : 0,
        };

        const result = await post(config.pageEnterUrl, data);

        if (result && result.success) {
            pageLogId = result.page_log_id;
            pageEnterTime = Date.now();
        }
    }

    // ─── Page Exit ───────────────────────────────────────────────

    function trackPageExit() {
        const data = {
            page_log_id  : pageLogId,
            time_spent   : getTimeSpent(),
            scroll_depth : maxScrollDepth,
        };

        // ✅ FormData দিয়ে পাঠাও — CSRF token সহ
        const formData = new FormData();
        formData.append('_token', config.csrfToken);
        formData.append('page_log_id', pageLogId);
        formData.append('time_spent', getTimeSpent());
        formData.append('scroll_depth', maxScrollDepth);

        navigator.sendBeacon(config.pageExitUrl, formData);
    }

    // ─── Activity Tracker ────────────────────────────────────────

    async function trackActivity(type, extraData = {}) {
        const data = {
            page_log_id   : pageLogId,
            page_url      : config.currentPage || window.location.href,
            activity_type : type,
            activity_data : extraData,
            // PHP session এর current snapshot (page load এর সময় inject করা)
            // click এর সময় JS এ নতুন কিছু থাকলে সেটাও যোগ করো
            ...extraData,
        };

        if (!isOnline) {
            activityQueue.push(data);
            return;
        }

        await post(config.activityUrl, data);
    }

    // ─── Scroll Tracking ─────────────────────────────────────────

    function trackScroll() {
        const scrollTop    = window.scrollY || document.documentElement.scrollTop;
        const docHeight    = document.documentElement.scrollHeight - window.innerHeight;
        const scrollPercent = docHeight > 0 ? Math.round((scrollTop / docHeight) * 100) : 0;

        if (scrollPercent > maxScrollDepth) {
            maxScrollDepth = scrollPercent;
        }
    }

    // ─── Click Tracking ──────────────────────────────────────────

    function handleClick(event) {
        const target = event.target.closest('a, button, [data-track]') || event.target;

        // শুধু meaningful clicks track করো
        if (!target || target === document.body) return;

        const elementData = {
            element_id   : target.id || null,
            element_text : (target.innerText || target.value || target.getAttribute('aria-label') || '').trim().substring(0, 100),
            element_tag  : target.tagName.toLowerCase(),
            href         : target.href || null,
            data_track   : target.getAttribute('data-track') || null,
        };

        // Flight search button এ click
        if (target.closest('[data-track="flight-search"]') || target.closest('#flight-search-btn')) {
            trackFlightSearch(elementData);
            return;
        }

        // Regular click
        trackActivity('click', elementData);
    }

    // ─── Flight Search Tracking ──────────────────────────────────
    // Flight search form submit হলে automatically detect করবে

    function trackFlightSearch(clickData = {}) {
        // Flight search form এর data collect করো
        const flightData = collectFlightFormData();

        trackActivity('flight_search', {
            ...clickData,
            flight_search : flightData,
            // JS session storage এ থাকলে সেটাও নাও
            js_storage    : getJsStorageData(),
        });
    }

    function collectFlightFormData() {
        // আপনার flight search form এর field names অনুযায়ী adjust করুন
        const fields = [
            'from', 'to', 'origin', 'destination',
            'departure_date', 'return_date', 'depart_date',
            'adults', 'children', 'infants', 'passengers',
            'class', 'cabin_class', 'trip_type',
        ];

        const data = {};
        fields.forEach(field => {
            const el = document.querySelector(`[name="${field}"], #${field}, [data-field="${field}"]`);
            if (el && el.value) {
                data[field] = el.value;
            }
        });

        return data;
    }

    // ─── Search Tracking ─────────────────────────────────────────

    function setupSearchTracking() {
        // Search form submit
        document.querySelectorAll('form[data-track-search], form.search-form, #search-form').forEach(form => {
            form.addEventListener('submit', function (e) {
                const query = this.querySelector('input[type="search"], input[name="q"], input[name="search"]');
                trackActivity('search', {
                    search_query : query ? query.value : '',
                    form_action  : this.action,
                });
            });
        });
    }

    // ─── JS Storage Data ─────────────────────────────────────────
    // sessionStorage থেকে relevant data collect করো

    function getJsStorageData() {
        const data = {};
        try {
            // আপনার app এ যা যা sessionStorage এ রাখেন
            const keysToCapture = [
                'reissue_data',
                'search_params',
                'selected_flight',
            ];

            keysToCapture.forEach(key => {
                const val = sessionStorage.getItem(key);
                if (val) {
                    try { data[key] = JSON.parse(val); }
                    catch { data[key] = val; }
                }
            });
        } catch (e) {}
        return data;
    }

    // ─── Form Submit Tracking ────────────────────────────────────

    function setupFormTracking() {
        document.querySelectorAll('form:not([data-no-track])').forEach(form => {
            form.addEventListener('submit', function (e) {
                // Password field বাদ দিয়ে form data collect করো
                const formData = {};
                new FormData(this).forEach((value, key) => {
                    if (!key.toLowerCase().includes('password') && key !== '_token') {
                        formData[key] = value;
                    }
                });

                trackActivity('form_submit', {
                    form_id     : this.id || null,
                    form_action : this.action,
                    form_data   : formData,
                });
            });
        });
    }

    // ─── Online/Offline Queue ────────────────────────────────────

    function flushQueue() {
        if (activityQueue.length === 0) return;
        const queue = [...activityQueue];
        activityQueue = [];
        queue.forEach(data => post(config.activityUrl, data));
    }

    // ─── Init ────────────────────────────────────────────────────

    function init() {
        // Page enter track করো
        trackPageEnter();

        // Scroll track করো (throttled)
        let scrollTimer;
        window.addEventListener('scroll', () => {
            clearTimeout(scrollTimer);
            scrollTimer = setTimeout(trackScroll, 200);
        }, { passive: true });

        // Click track করো
        document.addEventListener('click', handleClick, true);

        // Search form setup
        setupSearchTracking();

        // Form submit setup
        setupFormTracking();

        // Page exit
        window.addEventListener('beforeunload', trackPageExit);
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) trackPageExit();
        });

        // Online/offline
        window.addEventListener('online',  () => { isOnline = true;  flushQueue(); });
        window.addEventListener('offline', () => { isOnline = false; });

        // Public API — manually call করতে পারবেন
        window.VisitorTracker = {
            trackActivity,
            trackFlightSearch,
            getTimeSpent,
        };
    }

    // DOM ready হলে init করো
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
