<?php


    namespace Modules\Flight\Admin;


    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Validator;
    use Illuminate\Validation\Rule;
    use Modules\AdminController;
    use Modules\Flight\Models\Flight;
    use Modules\Flight\Models\Airline;
    use Modules\Flight\Models\FlightCharge;
    use Modules\Flight\Models\FlightDiscount;
    use Stripe\Discount;

    class FlightChargeController extends AdminController
    {
        public function __construct()
        {
            $this->setActiveMenu('admin/module/flight');
        }

        public function index()
        {
//            $this->checkPermission('flight_manage_others');

            $rows = FlightCharge::orderBy('type', 'asc')->get();

            $data = [
                'rows' => $rows,
                'page_title' => __('Booking Charges Management')
            ];

            return view('Flight::admin.flight_charge.index', $data);
        }

        public function edit($id)
        {
//            $this->checkPermission('flight_manage_others');

            $row = FlightCharge::find($id);

            if (empty($row)) {
                return redirect()->route('flight.admin.flight_charges.index')
                    ->with('error', __('Charge not found'));
            }

            $data = [
                'row' => $row,
                'page_title' => __('Edit Booking Charges')
            ];

            return view('Flight::admin.flight_charge.edit', $data);
        }

        public function update(Request $request, $id)
        {
//            $this->checkPermission('flight_manage_others');

            $row = FlightCharge::find($id);

            if (empty($row)) {
                return redirect()->route('flight.admin.flight_charges.index')
                    ->with('error', __('Charge not found'));
            }

            $validator = Validator::make($request->all(), [
                'ait_charge' => 'required|decimal:0,2|min:0',
                'service_charge' => 'required|numeric|min:0',
                'segment_discount' => 'required|numeric|min:0',
                'status' => 'required|in:active,inactive'
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $row->ait_charge = $request->input('ait_charge');
            $row->service_charge = $request->input('service_charge');
            $row->segment_discount = $request->input('segment_discount');
            $row->status = $request->input('status');
            $row->save();

            return redirect()->route('flight.admin.flight_charges.index')
                ->with('success', __('Charges updated successfully'));
        }
    }
