<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Jenssegers\Agent\Agent;
use Modules\User\Models\VisitorLog;

class TrackVisitor
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->is('admin/*') && !setting_item('track_admin_visits', false)) {
            return $next($request);
        }

        if ($request->ajax() && !setting_item('track_ajax_visits', false)) {
            return $next($request);
        }

        $this->trackVisitor($request);

        return $next($request);
    }

    protected function trackVisitor(Request $request)
    {
        try {
            $sessionId = session()->getId();
            $ipAddress = $request->ip();
            $userId    = Auth::id();

            $visitor = VisitorLog::where('session_id', $sessionId)
                ->where('is_online', true)
                ->first();

            if ($visitor) {
                $visitor->updateActivity($request->fullUrl());
            } else {
                // ── পরিবর্তন ১: create এর return value নিয়ে session এ save করো ──
                $visitor = $this->createVisitorLog($request, $sessionId, $ipAddress, $userId);
            }

            // ── পরিবর্তন ২: visitor_log_id session এ রাখো ──
            // VisitorTrackingController এ এটা দিয়ে visitor খুঁজে পাবে
            if ($visitor) {
                session(['visitor_log_id' => $visitor->id]);
            }

            if (rand(1, 100) == 1) {
                VisitorLog::markInactiveAsOffline();
            }

        } catch (\Exception $e) {
            \Log::error('Visitor tracking error: ' . $e->getMessage());
        }
    }

    // ── পরিবর্তন ৩: return $visitor যোগ করো ──
    protected function createVisitorLog($request, $sessionId, $ipAddress, $userId)
    {
        $agent = new Agent();
        $agent->setUserAgent($request->userAgent());

        $locationData = $this->getLocationData($ipAddress);
        $deviceType   = $this->getDeviceType($agent);

        return VisitorLog::create([  // ← return যোগ হয়েছে
            'user_id'          => $userId,
            'session_id'       => $sessionId,
            'ip_address'       => $ipAddress,
            'user_agent'       => $request->userAgent(),
            'device_type'      => $deviceType,
            'browser'          => $agent->browser(),
            'platform'         => $agent->platform(),
            'country'          => $locationData['country']      ?? null,
            'country_code'     => $locationData['country_code'] ?? null,
            'region'           => $locationData['region']       ?? null,
            'city'             => $locationData['city']         ?? null,
            'latitude'         => $locationData['latitude']     ?? null,
            'longitude'        => $locationData['longitude']    ?? null,
            'visited_at'       => now(),
            'last_activity_at' => now(),
            'landing_page'     => $request->fullUrl(),
            'current_page'     => $request->fullUrl(),
            'is_online'        => true,
            'status'           => 'active',
        ]);
    }

    protected function getLocationData($ipAddress)
    {
        if ($this->isPrivateIP($ipAddress)) {
            return [
                'country'      => 'Local',
                'country_code' => 'LOC',
                'region'       => 'Local',
                'city'         => 'Localhost',
            ];
        }

        try {
            $response = @file_get_contents("http://ip-api.com/json/{$ipAddress}");

            if ($response) {
                $data = json_decode($response, true);

                if ($data && $data['status'] == 'success') {
                    return [
                        'country'      => $data['country']     ?? null,
                        'country_code' => $data['countryCode'] ?? null,
                        'region'       => $data['regionName']  ?? null,
                        'city'         => $data['city']        ?? null,
                        'latitude'     => $data['lat']         ?? null,
                        'longitude'    => $data['lon']         ?? null,
                    ];
                }
            }
        } catch (\Exception $e) {
            \Log::warning('IP Geolocation failed: ' . $e->getMessage());
        }

        return [];
    }

    protected function isPrivateIP($ip)
    {
        return in_array($ip, ['127.0.0.1', '::1', 'localhost']) ||
            filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
    }

    protected function getDeviceType($agent)
    {
        if ($agent->isMobile())       return 'mobile';
        elseif ($agent->isTablet())   return 'tablet';
        elseif ($agent->isDesktop())  return 'desktop';
        return 'unknown';
    }
}
