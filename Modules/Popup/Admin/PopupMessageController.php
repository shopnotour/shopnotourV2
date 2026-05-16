<?php


namespace Modules\Popup\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Popup\Models\PopupMessage;

class PopupMessageController extends Controller
{
    public function index()
    {
        $popups = PopupMessage::with('creator', 'updater')
            ->orderBy('updated_at', 'desc')
            ->get();
        $pageKeys = PopupMessage::pageKeys();

        return view('Popup::admin.popup_messages.index', compact('popups', 'pageKeys'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'page_key' => 'required|string',
            'title' => 'nullable|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:info,success,warning,danger',
            'is_active' => 'nullable|in:0,1',
            'show_once' => 'nullable|in:0,1',
        ]);

        $popup = PopupMessage::create([
            ...$request->only('page_key', 'title', 'message', 'type'),
            'is_active' => (bool)($request->input('is_active', 0)),
            'show_once' => (bool)($request->input('show_once', 0)),
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Popup message created successfully.'),
                'popup' => $popup->load('creator', 'updater'),
            ]);
        }

        return back()->with('success', __('Popup message created successfully.'));
    }

    public function update(Request $request, PopupMessage $popupMessage)
    {
        $request->validate([
            'page_key' => 'required|string',
            'title' => 'nullable|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:info,success,warning,danger',
            'is_active' => 'nullable|in:0,1',
            'show_once' => 'nullable|in:0,1',
        ]);

        $popupMessage->update([
            ...$request->only('page_key', 'title', 'message', 'type'),
            'is_active' => (bool)($request->input('is_active', 0)),
            'show_once' => (bool)($request->input('show_once', 0)),
            'updated_by' => auth()->id(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Popup message updated successfully.'),
                'popup' => $popupMessage->fresh()->load('creator', 'updater'),
            ]);
        }

        return back()->with('success', __('Popup message updated successfully.'));
    }

    public function destroy(PopupMessage $popupMessage)
    {
        $popupMessage->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Popup message deleted.'),
            ]);
        }

        return back()->with('success', __('Popup message deleted.'));
    }

    public function toggle(PopupMessage $popupMessage)
    {
        $popupMessage->update([
            'is_active' => !$popupMessage->is_active,
            'updated_by' => auth()->id(),
        ]);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'is_active' => $popupMessage->is_active,
                'message' => __('Status updated.'),
            ]);
        }

        return back()->with('success', __('Status updated.'));
    }
}
