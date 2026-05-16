<?php
//
//namespace Modules\Flight\Admin;
//
//use Illuminate\Http\Request;
//use Modules\AdminController;
//use Modules\Flight\Models\FlightCallingStructure;
//use Modules\Flight\Models\Airline;
//use Modules\Flight\Models\Airport;
//
//class CallingStructureController extends AdminController
//{
//    public function __construct()
//    {
////        parent::__construct();
//    }
//
//    public function index(Request $request)
//    {
////        $this->checkPermission('flight_manage_settings');
//
//        $query = FlightCallingStructure::with(['airline', 'departureAirport', 'arrivalAirport']);
//
//        // Search by route
//        if ($request->filled('departure_code') || $request->filled('arrival_code')) {
//            if ($request->filled('departure_code')) {
//                $query->where('departure_code', $request->departure_code);
//            }
//            if ($request->filled('arrival_code')) {
//                $query->where('arrival_code', $request->arrival_code);
//            }
//        }
//
//        $rows = $query->orderBy('departure_code')
//            ->orderBy('arrival_code')
//            ->orderBy('priority', 'asc')
//            ->paginate(20);
//
//        $airports = Airport::orderBy('name')->get();
//
//        $data = [
//            'rows' => $rows,
//            'airports' => $airports,
//            'breadcrumbs' => [
//                [
//                    'name' => __('Flight Calling Structure'),
//                    'url'  => route('flight.admin.calling.index')
//                ]
//            ],
//            'page_title' => __('Flight Calling Structure')
//        ];
//
//        return view('Flight::admin.calling_structure.index', $data);
//    }
//
//    public function create()
//    {
////        $this->checkPermission('flight_manage_settings');
//
//        $airports = Airport::orderBy('name')->get();
//        $airlines = Airline::orderBy('name')->get();
//
//        $data = [
//            'airports' => $airports,
//            'airlines' => $airlines,
//            'breadcrumbs' => [
//                [
//                    'name' => __('Flight Calling Structure'),
//                    'url'  => route('flight.admin.calling.index')
//                ],
//                [
//                    'name' => __('Add Route'),
//                    'class' => 'active'
//                ]
//            ],
//            'page_title' => __('Add Route Calling Structure')
//        ];
//
//        return view('Flight::admin.calling_structure.create', $data);
//    }
//
//    public function store(Request $request)
//    {
////        $this->checkPermission('flight_manage_settings');
//
//        $rules = [
//            'departure_code' => 'nullable',
//            'arrival_code' => 'nullable|different:departure_code',
//            'airlines' => 'required|array|min:1',
//            'airlines.*' => 'required',
////            'airlines.*' => 'required|exists:bravo_airlines,id',
//        ];
//
//        $messages = [
//            'departure_code.required' => __('Departure airport is required'),
//            'arrival_code.required' => __('Arrival airport is required'),
//            'arrival_code.different' => __('Arrival must be different from departure'),
//            'airlines.required' => __('Please select at least one airline'),
//        ];
//
//        $this->validate($request, $rules, $messages);
//
//        $departureCode = $request->departure_code;
//        $arrivalCode = $request->arrival_code;
//        $airlines = $request->airlines; // Array of airline IDs
//
//        // Delete existing entries for this route
//        FlightCallingStructure::where('departure_code', $departureCode)
//            ->where('arrival_code', $arrivalCode)
//            ->delete();
//
//        // Insert new entries
//        foreach ($airlines as $index => $airlineId) {
//            FlightCallingStructure::create([
//                'departure_code' => $departureCode,
//                'arrival_code' => $arrivalCode,
//                'airline_id' => $airlineId,
//                'priority' => $index, // Auto priority based on selection order
//                'status' => 'active'
//            ]);
//        }
//
//        return redirect()->route('flight.admin.calling.index')
//            ->with('success', __('Route calling structure created successfully'));
//    }
//
//    public function edit($id)
//    {
////        $this->checkPermission('flight_manage_settings');
//
//        $row = FlightCallingStructure::with(['airline', 'departureAirport', 'arrivalAirport'])->find($id);
//
//        if (empty($row)) {
//            return redirect()->back()->with('error', __('Record not found'));
//        }
//
//        // Get all airlines for this route
//        $routeAirlines = FlightCallingStructure::where('departure_code', $row->departure_code)
//            ->where('arrival_code', $row->arrival_code)
//            ->with('airline')
//            ->orderBy('priority', 'asc')
//            ->get();
//
//        $airports = Airport::orderBy('name')->get();
//        $airlines = Airline::orderBy('name')->get();
//
//        $data = [
//            'row' => $row,
//            'routeAirlines' => $routeAirlines,
//            'airports' => $airports,
//            'airlines' => $airlines,
//            'breadcrumbs' => [
//                [
//                    'name' => __('Flight Calling Structure'),
//                    'url'  => route('flight.admin.calling.index')
//                ],
//                [
//                    'name' => __('Edit Route'),
//                    'class' => 'active'
//                ]
//            ],
//            'page_title' => __('Edit Route Calling Structure')
//        ];
//
//        return view('Flight::admin.calling_structure.edit', $data);
//    }
//
//    public function update(Request $request, $id)
//    {
////        $this->checkPermission('flight_manage_settings');
//
//        $row = FlightCallingStructure::find($id);
//
//        if (empty($row)) {
//            return redirect()->back()->with('error', __('Record not found'));
//        }
//
//        $rules = [
//            'airlines' => 'required|array|min:1',
//            'airlines.*' => 'required',
//        ];
//
//        $messages = [
//            'airlines.required' => __('Please select at least one airline'),
//        ];
//
//        $this->validate($request, $rules, $messages);
//
//        $departureCode = $row->departure_code;
//        $arrivalCode = $row->arrival_code;
//        $airlines = $request->airlines; // Array of airline IDs
//
//        // Delete all existing entries for this route
//        FlightCallingStructure::where('departure_code', $departureCode)
//            ->where('arrival_code', $arrivalCode)
//            ->delete();
//
//        // Insert updated entries with auto priority
//        foreach ($airlines as $index => $airlineId) {
//            FlightCallingStructure::create([
//                'departure_code' => $departureCode,
//                'arrival_code' => $arrivalCode,
//                'airline_id' => $airlineId,
//                'priority' => $index, // Auto priority based on selection order
//                'status' => 'active'
//            ]);
//        }
//        return redirect()->route('flight.admin.calling.index')->with('success', __('Route calling structure updated successfully'));
//    }
//
//    public function destroy($id)
//    {
//        $this->checkPermission('flight_manage_settings');
//
//        $row = FlightCallingStructure::find($id);
//
//        if (empty($row)) {
//            return redirect()->back()->with('error', __('Record not found'));
//        }
//
//        $row->delete();
//
//        return redirect()->back()->with('success', __('Record deleted successfully'));
//    }
//
//    public function bulkEdit(Request $request)
//    {
////        $this->checkPermission('flight_manage_settings');
//
//        $ids = $request->input('ids');
//        $action = $request->input('action');
//
//        if (empty($ids) or !is_array($ids)) {
//            return redirect()->back()->with('error', __('Please select at least one item'));
//        }
//
//        switch ($action) {
//            case "delete":
//                FlightCallingStructure::whereIn('id', $ids)->delete();
//                return redirect()->back()->with('success', __('Deleted successfully'));
//
//            case "activate":
//                FlightCallingStructure::whereIn('id', $ids)->update(['status' => 'active']);
//                return redirect()->back()->with('success', __('Activated successfully'));
//
//            case "deactivate":
//                FlightCallingStructure::whereIn('id', $ids)->update(['status' => 'inactive']);
//                return redirect()->back()->with('success', __('Deactivated successfully'));
//        }
//
//        return redirect()->back();
//    }
//}


namespace Modules\Flight\Admin;

use Illuminate\Http\Request;
use Modules\AdminController;
use Modules\Flight\Models\FlightApi;
use Modules\Flight\Models\FlightCallingStructure;
use Modules\Flight\Models\Airline;
use Modules\Flight\Models\Airport;
use Modules\Flight\Models\Gds;

class CallingStructureController extends AdminController
{
    public function __construct()
    {
//        parent::__construct();
    }

    public function index(Request $request)
    {
//        $this->checkPermission('flight_manage_settings');

        $query = FlightCallingStructure::query();

        // Search by route
        if ($request->filled('departure_code')) {
            $query->where('departure_code', $request->departure_code);
        }
        if ($request->filled('arrival_code')) {
            $query->where('arrival_code', $request->arrival_code);
        }

        $rows = $query->orderBy('priority', 'asc')
            ->orderBy('departure_code')
            ->orderBy('arrival_code')
            ->paginate(20);

        $airports = Airport::orderBy('name')->get();
        $gdsoptions  = FlightApi::pluck('name', 'provider');

        $data = [
            'rows' => $rows,
            'airports' => $airports,
            'gdsoptions' => $gdsoptions,
            'breadcrumbs' => [
                [
                    'name' => __('Flight Calling Structure'),
                    'url' => route('flight.admin.calling.index')
                ]
            ],
            'page_title' => __('Flight Calling Structure')
        ];

        return view('Flight::admin.calling_structure.index', $data);
    }

    public function create()
    {
//        $this->checkPermission('flight_manage_settings');

        $airports = Airport::orderBy('name')->get();
        $airlines = Airline::orderBy('name')->get();
        $gdsOptions = FlightApi::pluck('name', 'provider');

        $data = [
            'airports' => $airports,
            'airlines' => $airlines,
            'gdsoptions' => $gdsOptions,
            'breadcrumbs' => [
                [
                    'name' => __('Flight Calling Structure'),
                    'url' => route('flight.admin.calling.index')
                ],
                [
                    'name' => __('Add Route'),
                    'class' => 'active'
                ]
            ],
            'page_title' => __('Add Route Calling Structure')
        ];
//       return $data;

        return view('Flight::admin.calling_structure.create', $data);
    }

    public function store(Request $request)
    {
//        $this->checkPermission('flight_manage_settings');

        $rules = [
            'departure_code' => 'nullable|string|size:3',
            'arrival_code' => 'nullable|string|size:3|different:departure_code',
            'airline_codes' => 'required|array|min:1',
            'airline_codes.*' => 'required|string|size:2',
            'priority' => 'required|integer|min:0',
            'notes' => 'nullable|string',
        ];

        $messages = [
            'arrival_code.different' => __('Arrival must be different from departure'),
            'airline_codes.required' => __('Please select at least one airline'),
            'airline_codes.*.size' => __('Invalid airline code'),
        ];

        $this->validate($request, $rules, $messages);

        // ✅ Check if exact route already exists
        $exists = FlightCallingStructure::where('departure_code', $request->departure_code)
            ->where('arrival_code', $request->arrival_code)
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->withInput()
                ->with('error', __('This route already exists. Please edit the existing one.'));
        }

        FlightCallingStructure::create([
            'departure_code' => $request->departure_code,
            'arrival_code' => $request->arrival_code,
            'airline_codes' => $request->airline_codes, // ✅ JSON array
            'priority' => $request->priority,
            'gds' => $request->gds,
            'status' => 'inactive',
            'notes' => $request->notes
        ]);

        return redirect()->route('flight.admin.calling.index')
            ->with('success', __('Route calling structure created successfully'));
    }

    public function edit($id)
    {
//        $this->checkPermission('flight_manage_settings');

        $row = FlightCallingStructure::find($id);

        if (empty($row)) {
            return redirect()->back()->with('error', __('Record not found'));
        }

        $airports = Airport::orderBy('name')->get();
        $airlines = Airline::orderBy('name')->get();
        $gdsOptions = FlightApi::pluck('name', 'provider');

        $data = [
            'row' => $row,
            'airports' => $airports,
            'airlines' => $airlines,
            'gdsoptions' => $gdsOptions,
            'breadcrumbs' => [
                [
                    'name' => __('Flight Calling Structure'),
                    'url' => route('flight.admin.calling.index')
                ],
                [
                    'name' => __('Edit Route'),
                    'class' => 'active'
                ]
            ],
            'page_title' => __('Edit Route Calling Structure')
        ];

        return view('Flight::admin.calling_structure.edit', $data);
    }

    public function statusChange($id)
    {
        $item = FlightCallingStructure::findOrFail($id);

        $item->status = $item->status == 'active' ? 'inactive' : 'active';
        $item->save();

        return redirect()->route('flight.admin.calling.index')
            ->with('success', __('Status Update successfully'));
    }

    public function update(Request $request, $id)
    {
//        $this->checkPermission('flight_manage_settings');

        $row = FlightCallingStructure::find($id);

        if (empty($row)) {
            return redirect()->back()->with('error', __('Record not found'));
        }

        $rules = [
            'airline_codes' => 'required|array|min:1',
            'airline_codes.*' => 'required|string|size:2',
            'priority' => 'required|integer|min:0',
            'notes' => 'nullable|string',
        ];

        $messages = [
            'airline_codes.required' => __('Please select at least one airline'),
        ];

        $this->validate($request, $rules, $messages);

        $row->update([
            'airline_codes' => $request->airline_codes, // ✅ JSON array
            'priority' => $request->priority,
            'notes' => $request->notes,
            'gds' => $request->gds,
            'status' => $request->status
        ]);



        return redirect()->route('flight.admin.calling.index')
            ->with('success', __('Route calling structure updated successfully'));
    }

    public function destroy($id)
    {
//        $this->checkPermission('flight_manage_settings');

        $row = FlightCallingStructure::find($id);
//return $row;
        if (empty($row)) {
            return redirect()->back()->with('error', __('Record not found'));
        }

        // ✅ Clear cache before delete
        FlightCallingStructure::clearRouteCache(
            $row->departure_code,
            $row->arrival_code
        );

        $row->delete();

        return redirect()->back()->with('success', __('Record deleted successfully'));
    }

    public function bulkEdit(Request $request)
    {
//        $this->checkPermission('flight_manage_settings');

        $ids = $request->input('ids');
        $action = $request->input('action');

        if (empty($ids) or !is_array($ids)) {
            return redirect()->back()->with('error', __('Please select at least one item'));
        }

        switch ($action) {
            case "delete":
                // ✅ Clear all cache when bulk delete
                FlightCallingStructure::clearAllCache();
                FlightCallingStructure::whereIn('id', $ids)->delete();
                return redirect()->back()->with('success', __('Deleted successfully'));

            case "activate":
                FlightCallingStructure::whereIn('id', $ids)->update(['status' => 'active']);
                // ✅ Clear cache
                FlightCallingStructure::clearAllCache();
                return redirect()->back()->with('success', __('Activated successfully'));

            case "deactivate":
                FlightCallingStructure::whereIn('id', $ids)->update(['status' => 'inactive']);
                // ✅ Clear cache
                FlightCallingStructure::clearAllCache();
                return redirect()->back()->with('success', __('Deactivated successfully'));
        }

        return redirect()->back();
    }
}
