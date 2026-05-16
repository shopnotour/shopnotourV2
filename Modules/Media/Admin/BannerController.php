<?php

namespace Modules\Media\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
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
//        return 'sarowar';
        if($id > 0){
            $row = Banner::findOrFail($id);
        } else {
            $row = new Banner();
            $row->create_user = auth()->id();
        }

        $row->fill($request->all());
        $row->update_user = auth()->id();
        $row->save();

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
                Banner::destroy($id);
            }
        } else {
            Banner::whereIn('id', $ids)->update(['status' => $action]);
        }
        // Cache clear করো
        Cache::forget('flight_bg_images');

        return redirect()->back()->with('success', __('Update success!'));
    }
}
