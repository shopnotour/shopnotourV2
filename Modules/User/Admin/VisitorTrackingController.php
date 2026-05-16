<?php

namespace Modules\User\Admin;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\User\Models\VisitorLog;
use Modules\User\Models\VisitorPageLog;
use Modules\User\Models\VisitorActivity;

class VisitorTrackingController extends Controller
{
    /**
     * Page এ ঢোকার সময় call হবে
     * POST /visitor/page-enter
     */
    public function pageEnter(Request $request)
    {

        $request->validate([
            'page_url'    => 'required|string|max:500',
            'page_title'  => 'nullable|string|max:191',
            'referrer'    => 'nullable|string|max:500',
        ]);

        // বর্তমান visitor_log খুঁজো (Middleware এ set করা থাকে)
        $visitorLog = $this->getVisitorLog($request);
return $request;
        if (!$visitorLog) {
            return response()->json(['success' => false, 'message' => 'Visitor log not found'], 404);
        }

        // আগের page এর tracking শেষ করো (যদি থাকে)
        $previousPageLogId = session('current_page_log_id');
        if ($previousPageLogId) {
            $previousPage = VisitorPageLog::find($previousPageLogId);
            if ($previousPage) {
                $previousPage->finishTracking(
                    $request->input('previous_time_spent', 0),
                    $request->input('previous_scroll_depth', 0)
                );
            }
        }

        // PHP session এর important data collect করো
        $sessionSnapshot = $this->collectSessionData();

        // নতুন page log তৈরি করো
        $pageLog = VisitorPageLog::startTracking([
            'visitor_log_id'   => $visitorLog->id,
            'session_id'       => session()->getId(),
            'user_id'          => auth()->id(),
            'page_url'         => $request->page_url,
            'page_title'       => $request->page_title,
            'referrer_url'     => $request->referrer,
            'session_snapshot' => $sessionSnapshot,
        ]);

        // current page log id session এ রাখো
        session(['current_page_log_id' => $pageLog->id]);

        return response()->json([
            'success'     => true,
            'page_log_id' => $pageLog->id,
        ]);
    }

    /**
     * Page ছাড়ার সময় call হবে
     * POST /visitor/page-exit
     */
    public function pageExit(Request $request)
    {
        // sendBeacon FormData হিসেবে আসে, JSON না
        $pageLogId = $request->input('page_log_id') ?? session('current_page_log_id');

        if (!$pageLogId) {
            return response()->json(['success' => false]);
        }

        $pageLog = VisitorPageLog::find($pageLogId);

        if ($pageLog) {
            $pageLog->finishTracking(
                (int) $request->input('time_spent', 0),
                (int) $request->input('scroll_depth', 0)
            );
        }

        return response()->json(['success' => true]);
    }

    /**
     * যেকোনো activity/event track করো
     * POST /visitor/activity
     */
    public function trackActivity(Request $request)
    {
        $request->validate([
            'activity_type' => 'required|string',
            'page_url'      => 'required|string|max:500',
        ]);

        $visitorLog = $this->getVisitorLog($request);

        if (!$visitorLog) {
            return response()->json(['success' => false], 404);
        }

        // Click হলে page log এর click count বাড়াও
        $pageLogId = $request->input('page_log_id') ?? session('current_page_log_id');

        if ($pageLogId && $request->activity_type === 'click') {
            $pageLog = VisitorPageLog::find($pageLogId);
            if ($pageLog) {
                $pageLog->incrementClicks();
            }
        }

        // Activity data + session data একসাথে save করো
        $activity = VisitorActivity::log([
            'visitor_log_id' => $visitorLog->id,
            'page_log_id'    => $pageLogId,
            'session_id'     => session()->getId(),
            'user_id'        => auth()->id(),
            'page_url'       => $request->page_url,
            'activity_type'  => $request->activity_type,
            'element_id'     => $request->input('element_id'),
            'element_text'   => $request->input('element_text'),
            'activity_data'  => $request->input('activity_data'), // JS থেকে আসা data
            'session_data'   => $this->collectSessionData(),       // PHP session এর সব data
        ]);

        return response()->json([
            'success'     => true,
            'activity_id' => $activity->id,
        ]);
    }

    /**
     * PHP Session থেকে relevant data collect করো
     * (flight search, search queries যা session এ আছে সব আসবে)
     */
    private function collectSessionData(): array
    {
        $sessionData = [];

        // সব session data নাও (sensitive data বাদ দিয়ে)
        $allSession = session()->all();

        // এগুলো বাদ দাও (security)
        $excludeKeys = ['_token', 'password', 'login_web', '_flash', 'PHPSESSID'];

        foreach ($allSession as $key => $value) {
            if (!in_array($key, $excludeKeys) && !str_starts_with($key, '_')) {
                $sessionData[$key] = $value;
            }
        }

        // আপনার project এর specific session keys আলাদা করে রাখো
        $sessionData['_tracked'] = [
            'reissue_data'    => session('reissue_data'),
            'flight_search_params'      => session('flight_search_params'),
            'selected_flight' => session('selected_flight'),
        ];

        return $sessionData;
    }

    /**
     * বর্তমান visitor log খুঁজো
     */
    private function getVisitorLog(Request $request): ?VisitorLog
    {
        $visitorLogId = session('visitor_log_id');

        if ($visitorLogId) {
            $log = VisitorLog::find($visitorLogId);
            if ($log) return $log;
        }

        // পুরনো id delete হয়ে গেলে session_id দিয়ে নতুন খুঁজো
        $log = VisitorLog::where('session_id', session()->getId())
            ->latest()
            ->first();

        if ($log) {
            session(['visitor_log_id' => $log->id]); // session update
        }

        return $log;
    }
}

