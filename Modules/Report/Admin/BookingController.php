<?php
namespace Modules\Report\Admin;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\AdminController;
use Modules\Booking\Emails\NewBookingEmail;
use Modules\Booking\Events\BookingUpdatedEvent;
use Modules\Booking\Models\Booking;

use Modules\Booking\Models\BookingPassenger;
use Modules\Booking\Models\BookingRoute;

class BookingController extends AdminController
{
    public function __construct()
    {
        $this->setActiveMenu(route('report.admin.booking'));
    }

    public function index(Request $request)
    {
        $this->checkPermission('booking_view');
        $query = Booking::where('status', '!=', 'draft');
        if (!empty($request->s)) {
            if( is_numeric($request->s) ){
                $query->Where('id', '=', $request->s);
            }else{
                $query->where(function ($query) use ($request) {
                    $query->where('first_name', 'like', '%' . $request->s . '%')
                        ->orWhere('last_name', 'like', '%' . $request->s . '%')
                        ->orWhere('email', 'like', '%' . $request->s . '%')
                        ->orWhere('phone', 'like', '%' . $request->s . '%')
                        ->orWhere('address', 'like', '%' . $request->s . '%')
                        ->orWhere('address2', 'like', '%' . $request->s . '%');
                });
            }
        }
        if ($this->hasPermission('booking_manage_others')) {
            if (!empty($request->vendor_id)) {
                $query->where('vendor_id', $request->vendor_id);
            }
        } else {
            $query->where('vendor_id', Auth::id());
        }
        $query->whereIn('object_model', array_keys(get_bookable_services()));
        $query->whereIn('object_model', array_keys(get_bookable_services()));
        $query->orderBy('id','desc');
//        dd($query);
//        return $query->get();
        $data = [
            'rows'                  => $query->paginate(20),
            'page_title'            => __("All Bookings"),
            'booking_manage_others' => $this->hasPermission('booking_manage_others'),
            'booking_update'        => $this->hasPermission('booking_update'),
            'statues'               => config('booking.statuses')
        ];
        return view('Report::admin.booking.index', $data);
    }

    public function bulkEdit(Request $request)
    {
        $ids = $request->input('ids');
        $action = $request->input('action');
        if (empty($ids) or !is_array($ids)) {
            return redirect()->back()->with('error', __('No items selected'));
        }
        if (empty($action)) {
            return redirect()->back()->with('error', __('Please select action'));
        }
        if ($action == "delete") {
            foreach ($ids as $id) {
                $query = Booking::where("id", $id);
                if (!$this->hasPermission('booking_manage_others')) {
                    $query->where("vendor_id", Auth::id());
                }
                $row = $query->first();
                if(!empty($row)){
                    $row->delete();
                    event(new BookingUpdatedEvent($row));

                }
            }
        } else {
            foreach ($ids as $id) {
                $query = Booking::where("id", $id);
                if (!$this->hasPermission('booking_manage_others')) {
                    $query->where("vendor_id", Auth::id());
                    $this->checkPermission('booking_update');
                }
                $item = $query->first();
                if(!empty($item)){
                    $item->status = $action;
                    $item->save();

                    if($action == Booking::CANCELLED) $item->tryRefundToWallet();
                    event(new BookingUpdatedEvent($item));
                }
            }
        }
        return redirect()->back()->with('success', __('Update success'));
    }

    public function email_preview(Request $request, $id)
    {
        $booking = Booking::find($id);
        return (new NewBookingEmail($booking))->render();
    }

    public function invoice($id)
    {
        $booking = Booking::where('id', $id)->first();
        if (!is_admin() and $booking->vendor_id != auth()->id() and $booking->customer_id != auth()->id()) abort(404);
        $booking['passengers'] = BookingPassenger::where('booking_id', $booking->id)->get();
        $booking['routes'] = BookingRoute::where('booking_id', $booking->id)->get();

        //$user_id = Auth::id();
        // if (empty($booking)) {
        //     return redirect('user/booking-history');
        // }

        $data = [
            'booking'    => $booking,
            'service'    => $booking->service,
            'page_title' => __("Invoice")
        ];
        return view('User::frontend.bookingInvoice', $data);
    }

    public function ticket($id)
    {
        $booking = Booking::where('id', $id)->first();
        if (!$booking) abort(404);
//        if (!is_admin() && $booking->vendor_id != auth()->id() && $booking->customer_id != auth()->id()) abort(404);

        // pnr_raw_data থেকেই সব নেওয়া হবে — আলাদা table query দরকার নেই
        $data = [
            'booking'    => $booking,
            'page_title' => __("E-Ticket") . ' - ' . $booking->pnr_id,
        ];

        return view('User::frontend.ticket', $data);
    }


//    public function ticket($id)
//    {
//        $booking = Booking::where('id', $id)->first();
//        if (!is_admin() and $booking->vendor_id != auth()->id() and $booking->customer_id != auth()->id()) abort(404);
//        $booking['passengers'] = BookingPassenger::where('booking_id', $booking->id)->get();
//        $booking['routes'] = BookingRoute::where('booking_id', $booking->id)->get();
//
//        //$user_id = Auth::id();
//        // if (empty($booking)) {
//        //     return redirect('user/booking-history');
//        // }
//        $data = [
//            'booking'    => $booking,
//            'service'    => $booking->service,
//            'page_title' => __("Invoice")
//        ];
////        return view('User::frontend.bookingInvoice', $data);
//        return view('User::frontend.ticket', $data);
//    }
}
