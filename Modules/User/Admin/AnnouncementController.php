<?php

namespace Modules\User\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\User\Models\Announcement;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of announcements
     */
    public function index()
    {
        $announcements = Announcement::orderBy('display_order', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('User::admin.announcements.index', compact('announcements'));
    }

    /**
     * Show the form for creating a new announcement
     */
    public function create()
    {
        return view('User::admin.announcements.form');
    }

    /**
     * Store a newly created announcement
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:500',
            'icon' => 'nullable|string|max:10',
            'scroll_speed' => 'nullable|integer|min:10|max:100',
            'bg_color' => 'nullable|in:blue,green,purple,orange,dark',
            'display_order' => 'nullable|integer|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        // Handle checkbox - convert to 1 or 0
//        $validated['is_active'] = $request->has('is_active') ? 1 : 0;
//
//        // Set defaults if not provided
//        $validated['icon'] = $validated['icon'] ?? '🌟';
//        $validated['scroll_speed'] = $validated['scroll_speed'] ?? 40;
//        $validated['bg_color'] = $validated['bg_color'] ?? 'blue';
//        $validated['display_order'] = $validated['display_order'] ?? 0;

        Announcement::create($validated);

        return redirect()->route('announcements.index')
            ->with('success', 'Announcement created successfully!');
    }

    /**
     * Show the form for editing the specified announcement
     */
    public function edit(Announcement $announcement)
    {
        return view('User::admin.announcements.form', compact('announcement'));
    }

    /**
     * Update the specified announcement
     */
    public function update(Request $request, Announcement $announcement)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:500',
            'icon' => 'nullable|string|max:10',
            'scroll_speed' => 'nullable|integer|min:10|max:100',
            'bg_color' => 'nullable|in:blue,green,purple,orange,dark',
            'display_order' => 'nullable|integer|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        // Handle checkbox - convert to 1 or 0
        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        // Set defaults if not provided
        $validated['icon'] = $validated['icon'] ?? '🌟';
        $validated['scroll_speed'] = $validated['scroll_speed'] ?? 40;
        $validated['bg_color'] = $validated['bg_color'] ?? 'blue';
        $validated['display_order'] = $validated['display_order'] ?? 0;

        $announcement->update($validated);

        return redirect()->route('announcements.index')
            ->with('success', 'Announcement updated successfully!');
    }

    /**
     * Remove the specified announcement
     */
    public function destroy(Announcement $announcement)
    {
        $announcement->delete();

        return redirect()->route('announcements.index')
            ->with('success', 'Announcement deleted successfully!');
    }

    /**
     * Toggle active status
     */
    public function toggleActive(Announcement $announcement)
    {
        $announcement->update([
            'is_active' => $announcement->is_active ? 0 : 1 // Toggle between 0 and 1
        ]);

        $status = $announcement->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Announcement {$status} successfully!");
    }

    /**
     * Bulk delete announcements
     */
    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);

        // Decode if it's JSON string
        if (is_string($ids)) {
            $ids = json_decode($ids, true);
        }

        if (empty($ids)) {
            return back()->with('error', 'No announcements selected!');
        }

        Announcement::whereIn('id', $ids)->delete();

        return back()->with('success', count($ids) . ' announcement(s) deleted successfully!');
    }
}
