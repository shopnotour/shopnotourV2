<?php

namespace Modules\Popup\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Popup\Models\PopupMessage;
use Illuminate\Support\Facades\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;

class PopupMessageController extends Controller
{
    public function index()
    {
        $popups = PopupMessage::with('creator', 'updater')
            ->orderBy('updated_at', 'desc')
            ->get();
        $pageKeys = PopupMessage::pageKeys();
        $mediaTypes = PopupMessage::getMediaTypes();

        return view('Popup::admin.popup_messages.index', compact('popups', 'pageKeys', 'mediaTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'page_key' => 'required|string',
            'title' => 'nullable|string|max:255',
            'message' => 'required|string',
            'media_type' => 'nullable|in:image,video,youtube_link',
            'media_link' => 'nullable|string|max:500',
            'media_file' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,mp4,webm,ogg,mov|max:20480', // 20MB max
            'type' => 'required|in:info,success,warning,danger',
            'is_active' => 'nullable|in:0,1',
            'show_once' => 'nullable|in:0,1',
        ]);

        // Handle media based on type
        $mediaType = $request->input('media_type');
        $mediaLinks = null;

        if ($mediaType === 'youtube_link' && $request->filled('media_link')) {
            $mediaLinks = $this->validateYouTubeUrl($request->input('media_link'));
        } elseif (in_array($mediaType, ['image', 'video']) && $request->hasFile('media_file')) {
            $mediaLinks = $this->uploadMedia($request->file('media_file'));
        }

        $popup = PopupMessage::create([
            'page_key' => $request->input('page_key'),
            'title' => $request->input('title'),
            'message' => $request->input('message'),
            'media' => $mediaType,
            'media_links' => $mediaLinks,
            'type' => $request->input('type'),
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
            'media_type' => 'nullable|in:image,video,youtube_link',
            'media_link' => 'nullable|string|max:500',
            'media_file' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,mp4,webm,ogg,mov|max:20480',
            'type' => 'required|in:info,success,warning,danger',
            'is_active' => 'nullable|in:0,1',
            'show_once' => 'nullable|in:0,1',
            'remove_media' => 'nullable|boolean',
        ]);

        $mediaType = $request->input('media_type');
        $mediaLinks = $popupMessage->media_links;

        // Handle media removal
        if ($request->input('remove_media')) {
            $this->deleteMediaFile($popupMessage);
            $mediaType = null;
            $mediaLinks = null;
        } 
        // Handle new media upload
        elseif ($mediaType === 'youtube_link' && $request->filled('media_link')) {
            $mediaLinks = $this->validateYouTubeUrl($request->input('media_link'));
            // Delete old file if exists
            if ($popupMessage->media_links && in_array($popupMessage->media, ['image', 'video'])) {
                $this->deleteMediaFile($popupMessage);
            }
        } 
        elseif (in_array($mediaType, ['image', 'video']) && $request->hasFile('media_file')) {
            // Delete old media if exists
            if ($popupMessage->media_links && in_array($popupMessage->media, ['image', 'video'])) {
                $this->deleteMediaFile($popupMessage);
            }
            $mediaLinks = $this->uploadMedia($request->file('media_file'));
        }
        // Keep existing media
        elseif ($popupMessage->media === $mediaType && $popupMessage->media_links) {
            $mediaLinks = $popupMessage->media_links;
        }
        // If media type changed but no new file/link provided
        elseif ($mediaType && !$request->hasFile('media_file') && !$request->filled('media_link')) {
            $mediaType = null;
            $mediaLinks = null;
        }

        $popupMessage->update([
            'page_key' => $request->input('page_key'),
            'title' => $request->input('title'),
            'message' => $request->input('message'),
            'media' => $mediaType,
            'media_links' => $mediaLinks,
            'type' => $request->input('type'),
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
        // Delete media file if exists
        $this->deleteMediaFile($popupMessage);

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

    private function uploadMedia(UploadedFile $file)
    {
        // Ensure directory exists
        $destinationPath = public_path('popup_message');
        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0755, true);
        }
        
        // Generate unique filename
        $extension = $file->getClientOriginalExtension();
        $filename = time() . '_' . uniqid() . '.' . $extension;
        
        // Move file to public/popup_message
        $file->move($destinationPath, $filename);
        
        return $filename;
    }

    private function deleteMediaFile(PopupMessage $popupMessage)
    {
        // Only delete if it's a file (not YouTube link)
        if (in_array($popupMessage->media, ['image', 'video']) && $popupMessage->media_links) {
            $filePath = public_path('popup_message/' . $popupMessage->media_links);
            if (File::exists($filePath)) {
                File::delete($filePath);
            }
        }
    }

    private function validateYouTubeUrl(string $url): string
    {
        // Extract video ID and convert to watch URL format
        $pattern = '/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/';
        
        if (preg_match($pattern, $url, $matches)) {
            return 'https://www.youtube.com/watch?v=' . $matches[1];
        }
        
        // If URL is already in correct format or invalid, return as is
        return $url;
    }
}
