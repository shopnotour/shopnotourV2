<?php

namespace Modules\Media\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Modules\Media\Models\Banner;

class BannerController extends Controller
{
    public function index()
    {
        $rows = Banner::orderBy('order', 'asc')->get();
        $data = [
            'rows'       => $rows,
            'page_title' => __('Banner Management'),
            'breadcrumbs' => [
                [
                    'name'  => __('Banners'),
                    'class' => 'active'
                ]
            ]
        ];
        return view('Media::admin.banner.index', $data);
    }

    public function create()
    {
        $data = [
            'row'        => new Banner(),
            'page_title' => __('Add Banner'),
            'breadcrumbs' => [
                [
                    'name' => __('Banners'),
                    'url'  => route('banner.admin.index')
                ],
                [
                    'name'  => __('Add Banner'),
                    'class' => 'active'
                ]
            ]
        ];
        return view('Media::admin.banner.detail', $data);
    }

    public function edit($id)
    {
        $row = Banner::findOrFail($id);
        $data = [
            'row'        => $row,
            'page_title' => __('Edit Banner'),
            'breadcrumbs' => [
                [
                    'name' => __('Banners'),
                    'url'  => route('banner.admin.index')
                ],
                [
                    'name'  => __('Edit: :name', ['name' => $row->title]),
                    'class' => 'active'
                ]
            ]
        ];
        return view('Media::admin.banner.detail', $data);
    }

    public function store(Request $request, $id)
    {
        // Validation
        $rules = [
            'type' => 'required|in:image,video',
            'video' => 'nullable|file|mimes:mp4,avi,mov,wmv,flv,mkv|max:102400', // Max 100MB
        ];
        
        if ($request->input('type') == 'video') {
            $rules['video'] = 'required|file|mimes:mp4,avi,mov,wmv,flv,mkv|max:102400';
        }
        
        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        // If type is video, deactivate all other active videos
        if ($request->input('type') == 'video' && $request->status == 'active') {

            // Deactivate all other active videos
            Banner::where('type', 'video')
                ->where('status', 'active')
                ->update(['status' => 'inactive']);

            // Deactivate all images
            Banner::where('type', 'image')
                ->update(['status' => 'inactive']);
        }
        
        if($id > 0){
            $row = Banner::findOrFail($id);
        } else {
            $row = new Banner();
            $row->create_user = auth()->id();
        }
        
        // Handle video upload
        if ($request->hasFile('video')) {
            $videoFile = $request->file('video');
            $videoName = time() . '_' . uniqid() . '.' . $videoFile->getClientOriginalExtension();
            
            // Move file to public/uploads/video
            $destinationPath = public_path('uploads/video');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }
            
            $videoFile->move($destinationPath, $videoName);
            
            // Delete old video if exists
            if ($row->video && file_exists(public_path('uploads/video/' . $row->video))) {
                unlink(public_path('uploads/video/' . $row->video));
            }
            
            $row->video = $videoName;
        } elseif ($request->input('type') == 'image') {
            // If switching to image type, clear video
            if ($row->video && file_exists(public_path('uploads/video/' . $row->video))) {
                unlink(public_path('uploads/video/' . $row->video));
            }
            $row->video = null;
        }
        
        $row->fill($request->except(['video', 'type']));
        $row->type = $request->input('type', 'image');
        $row->update_user = auth()->id();
        $row->save();
        
        // Auto manage image status based on video status
        $activeVideoExists = Banner::where('type', 'video')
            ->where('status', 'active')
            ->exists();

        if ($activeVideoExists) {

            // If video active => all images inactive
            Banner::where('type', 'image')
                ->update(['status' => 'inactive']);

        } else {

            // If no video active => all images active
            Banner::where('type', 'image')
                ->update(['status' => 'active']);
        }

        // If type is image and there's no video, ensure type is set correctly
        if ($row->type == 'image') {
            $row->video = null;
            $row->save();
        }
        
        // Cache clear করো
        Cache::forget('flight_bg_images');
        
        if($id > 0){
            return back()->with('success', __('Banner updated!'));
        }
        return redirect(route('banner.admin.index'))
            ->with('success', __('Banner created!'));
    }
    
    public function bulkEdit(Request $request)
    {
        $ids    = $request->input('ids');
        $action = $request->input('action');
        
        if(empty($ids) or !is_array($ids)){
            return redirect()->back()->with('error', __('No items selected!'));
        }
        
        if(!in_array($action, ['delete', 'active', 'inactive'])){
            return redirect()->back()->with('error', __('Please select an action!'));
        }
        
        if($action == 'delete'){
            foreach($ids as $id){
                $banner = Banner::find($id);
                if ($banner && $banner->video && file_exists(public_path('uploads/video/' . $banner->video))) {
                    unlink(public_path('uploads/video/' . $banner->video));
                }
                Banner::destroy($id);
            }
        } else {
            // If activating a video banner, deactivate all other video banners
            if ($action == 'active') {

                $banners = Banner::whereIn('id', $ids)->get();

                foreach ($banners as $banner) {

                    if ($banner->type == 'video') {

                        // Deactivate all videos first
                        Banner::where('type', 'video')
                            ->update(['status' => 'inactive']);

                        // Activate selected video
                        $banner->update(['status' => 'active']);

                        // Deactivate all images
                        Banner::where('type', 'image')
                            ->update(['status' => 'inactive']);

                    } else {

                        // Activate image only if no active video exists
                        $activeVideoExists = Banner::where('type', 'video')
                            ->where('status', 'active')
                            ->exists();

                        if (!$activeVideoExists) {
                            $banner->update(['status' => 'active']);
                        }
                    }
                }

            } else {

                Banner::whereIn('id', $ids)->update(['status' => $action]);

                // If all videos inactive => activate all images
                $activeVideoExists = Banner::where('type', 'video')
                    ->where('status', 'active')
                    ->exists();

                if (!$activeVideoExists) {
                    Banner::where('type', 'image')
                        ->update(['status' => 'active']);
                }
            }
            Banner::whereIn('id', $ids)->update(['status' => $action]);
        }
        // Cache clear করো
        Cache::forget('flight_bg_images');
        
        return redirect()->back()->with('success', __('Update success!'));
    }
}