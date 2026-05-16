<?php

namespace Modules\Flight\Admin;

use Illuminate\Http\Request;
use Modules\AdminController;
use Modules\Flight\Models\FlightApi;

class FlightApiController extends AdminController
{
    public function __construct()
    {
//        parent::__construct();
    }

    public function index(Request $request)
    {
        $this->checkPermission('flight_api_settings');

        $query = FlightApi::query();

        // Search
        if ($request->filled('s')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->s . '%')
                    ->orWhere('provider', 'like', '%' . $request->s . '%');
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $rows = $query->orderBy('priority', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $data = [
            'rows' => $rows,
            'breadcrumbs' => [
                [
                    'name' => __('Flight APIs'),
                    'url'  => route('flight.admin.api.index')
                ]
            ],
            'page_title' => __('Flight API Management')
        ];

        return view('Flight::admin.api.index', $data);
    }

    public function create()
    {
        $this->checkPermission('flight_api_settings');

        $data = [
            'row' => new FlightApi(),
            'breadcrumbs' => [
                [
                    'name' => __('Flight APIs'),
                    'url'  => route('flight.admin.api.index')
                ],
                [
                    'name' => __('Add New API'),
                    'class' => 'active'
                ]
            ],
            'page_title' => __('Add New Flight API')
        ];

        return view('Flight::admin.api.detail', $data);
    }

    public function edit($id)
    {
        $this->checkPermission('flight_api_settings');

        $row = FlightApi::find($id);

        if (empty($row)) {
            return redirect()->back()->with('error', __('API not found'));
        }

        $data = [
            'row' => $row,
            'breadcrumbs' => [
                [
                    'name' => __('Flight APIs'),
                    'url'  => route('flight.admin.api.index')
                ],
                [
                    'name' => __('Edit API'),
                    'class' => 'active'
                ]
            ],
            'page_title' => __('Edit Flight API: ') . $row->name
        ];

        return view('Flight::admin.api.detail', $data);
    }

    public function store(Request $request, $id = -1)
    {
        $this->checkPermission('flight_api_settings');

        // Validation
        $rules = [
            'name' => 'required|max:255',
            'provider' => 'required|max:255',
            'api_key' => 'nullable|max:500',
            'api_secret' => 'nullable|max:500',
            'api_url' => 'nullable|url|max:500',
            'endpoint' => 'nullable|max:500',
            'status' => 'required|in:active,inactive',
            'priority' => 'nullable|integer',
            'description' => 'nullable|max:1000',
            'configuration' => 'nullable'
        ];

        $messages = [
            'name.required' => __('API name is required'),
            'provider.required' => __('Provider is required'),
            'status.required' => __('Status is required'),
            'api_url.url' => __('Please enter a valid URL')
        ];

        $this->validate($request, $rules, $messages);

        if ($id > 0) {
            $row = FlightApi::find($id);
            if (empty($row)) {
                return redirect()->back()->with('error', __('API not found'));
            }
        } else {
            $row = new FlightApi();
            $row->created_at = now();
        }

        $row->name = $request->name;
        $row->provider = $request->provider;
        $row->api_key = $request->api_key;
        $row->api_secret = $request->api_secret;
        $row->api_url = $request->api_url;
        $row->endpoint = $request->endpoint;
        $row->status = $request->status;
        $row->priority = $request->priority ?? 0;
        $row->description = $request->description;
        $row->configuration = $request->configuration;
        $row->updated_at = now();

        $row->save();

        if ($id > 0) {
            return redirect()->back()->with('success', __('API updated successfully'));
        }

        return redirect()->route('flight.admin.api.index')->with('success', __('API created successfully'));
    }

    public function update(Request $request, $id)
    {
        $this->checkPermission('flight_api_settings');
        return $this->store($request, $id);
    }

    public function destroy($id)
    {
        $this->checkPermission('flight_api_settings');

        $row = FlightApi::find($id);

        if (empty($row)) {
            return redirect()->back()->with('error', __('API not found'));
        }

        $row->delete();

        return redirect()->back()->with('success', __('API deleted successfully'));
    }

    public function bulkEdit(Request $request)
    {
        $this->checkPermission('flight_api_settings');

        $ids = $request->input('ids');
        $action = $request->input('action');

        if (empty($ids) or !is_array($ids)) {
            return redirect()->back()->with('error', __('Please select at least one item'));
        }

        switch ($action) {
            case "delete":
                foreach ($ids as $id) {
                    $row = FlightApi::find($id);
                    if (!empty($row)) {
                        $row->delete();
                    }
                }
                return redirect()->back()->with('success', __('Deleted successfully'));
                break;
            case "activate":
                foreach ($ids as $id) {
                    $row = FlightApi::find($id);
                    if (!empty($row)) {
                        $row->status = 'active';
                        $row->save();
                    }
                }
                return redirect()->back()->with('success', __('Updated successfully'));
                break;
            case "deactivate":
                foreach ($ids as $id) {
                    $row = FlightApi::find($id);
                    if (!empty($row)) {
                        $row->status = 'inactive';
                        $row->save();
                    }
                }
                return redirect()->back()->with('success', __('Updated successfully'));
                break;
        }

        return redirect()->back();
    }


    public function manage()
    {
        $this->checkPermission('flight_calling_settings');

        $apis = FlightApi::where('status', '=', 'active')->get();

        return view('Flight::admin.api.manage', compact('apis'));
    }

    public function updateSettings(Request $request)
    {
        $this->checkPermission('flight_calling_settings');

        $apis = $request->input('apis', []);

        foreach ($apis as $id => $data) {
            FlightApi::where('id', $id)->update([
                'is_enabled' => isset($data['is_enabled']) ? 1 : 0,
                'priority' => $data['priority'] ?? 0
            ]);
        }

        return redirect()->back()->with('success', __('API settings updated successfully'));
    }
}
