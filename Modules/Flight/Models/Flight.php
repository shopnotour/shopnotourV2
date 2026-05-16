<?php

namespace Modules\Flight\Models;

use App\Currency;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Http\Response;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use League\Flysystem\Adapter\Local;
use Modules\Booking\Models\Bookable;
use Modules\Booking\Models\Booking;
use Modules\Booking\Traits\CapturesService;
use Modules\Core\Models\Attributes;
use Modules\Core\Models\SEO;
use Modules\Core\Models\Terms;
use Modules\Flight\Factories\FlightFactory;
use Modules\Media\Helpers\FileHelper;
use Modules\Review\Models\Review;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\User\Models\UserWishList;
use Modules\Location\Models\Location;

class Flight extends Bookable
{
    use Notifiable;
    use SoftDeletes;
    use CapturesService;
    use HasFactory;

    protected $table                              = 'bravo_flight';
    public    $type                               = 'flight';
    public    $checkout_booking_detail_file       = 'Flight::frontend.booking.detail';
    public    $checkout_booking_detail_modal_file = 'Flight::frontend.booking.detail-modal';
    public    $set_paid_modal_file                = 'Flight::frontend.booking.set-paid-modal';
    public    $email_new_booking_file             = 'Flight::emails.new_booking_detail';
    public    $review_scope                       = false;

    protected $fillable = [
        'title',
        'code',
        'departure_time',
        'arrival_time',
        'duration',
        'airport_from',
        'airport_to',
        'airline_id',
        'status',
        'min_price',
    ];
    protected $seo_type = 'flight';

    protected $casts   = [
        'departure_time' => 'datetime',
        'arrival_time'   => 'datetime',
    ];
    protected $appends = ['can_book'];
    /**
     * @var Booking
     */
    protected $bookingClass;
    /**
     * @var Review
     */
    protected $reviewClass;

    /**
     * @var FlightTerm
     */
    protected $FlightTermClass;

    /**
     * @var FlightTerm
     */
    protected $userWishListClass;

    protected $tmp_dates = [];
    /**
     * @var string
     */
    private $termClass;


    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->bookingClass = Booking::class;
        $this->termClass = FlightTerm::class;
    }

    public static function getModelName()
    {
        return __("Flight");
    }

    /**
     * Get SEO fop page list
     *
     * @return mixed
     */
    static public function getSeoMetaForPageList()
    {
        $meta['seo_title'] = __("Search for Flights");
        if (!empty($title = setting_item_with_lang("flight_page_list_seo_title", false))) {
            $meta['seo_title'] = $title;
        } else {
            if (!empty($title = setting_item_with_lang("flight_page_search_title"))) {
                $meta['seo_title'] = $title;
            }
        }
        $meta['seo_image'] = null;
        if (!empty($title = setting_item("flight_page_list_seo_image"))) {
            $meta['seo_image'] = $title;
        } else {
            if (!empty($title = setting_item("flight_page_search_banner"))) {
                $meta['seo_image'] = $title;
            }
        }
        $meta['seo_desc'] = setting_item_with_lang("flight_page_list_seo_desc");
        $meta['seo_share'] = setting_item_with_lang("flight_page_list_seo_share");
        $meta['full_url'] = url()->current();
        return $meta;
    }

    protected static function newFactory()
    {
        return FlightFactory::new();
    }

    public function terms()
    {
        return $this->hasMany($this->termClass, "target_id");
    }

    public function getDetailUrl($include_param = true)
    {
        return "#";
        $param = [];
        $urlDetail = app_get_locale(false, false, '/') . config('flight.flight_route_prefix') . "/" . $this->id;
        if (!empty($param)) {
            $urlDetail .= "?" . http_build_query($param);
        }
        return url($urlDetail);
    }

    public static function getLinkForPageSearch($locale = false, $param = [])
    {

        return url(app_get_locale(false, false, '/') . config('flight.flight_route_prefix') . "?" . http_build_query($param));
    }

    public function getEditUrl()
    {
        return url(route('flight.admin.edit', ['id' => $this->id]));
    }

    public function getDiscountPercentAttribute()
    {
        if (
            !empty($this->price) and $this->price > 0
            and !empty($this->sale_price) and $this->sale_price > 0
            and $this->price > $this->sale_price
        ) {
            $percent = 100 - ceil($this->sale_price / ($this->price / 100));
            return $percent . "%";
        }
    }

    public function fill(array $attributes)
    {
        if (!empty($attributes)) {
            foreach ($this->fillable as $item) {
                $attributes[$item] = $attributes[$item] ?? null;
            }
        }
        return parent::fill($attributes); // TODO: Change the autogenerated stub
    }




    public function isBookable()
    {
        if ($this->status != 'publish') {
            return false;
        }
        return parent::isBookable();
    }



    public function addToCart(Request $request)
    {
//return $request;
        // Validate
        $res = $this->addToCartValidate($request);
        if ($res !== true) {
            return $res;
        }

        $fd = $request->flight_data ?? [];
        $rd = $request->request_data ?? [];

        // Helpers to read prepared-or-legacy data safely
        $first = function ($arr, $fallback = null) {
            if (is_array($arr) && count($arr)) return reset($arr);
            return $fallback;
        };

        $getCurrency = function () use ($fd) {
            return $fd['price']['currency']
                ?? $fd['currency']
                ?? ($fd['travelerPricings'][0]['currency'] ?? $fd['traveler_pricings'][0]['currency'] ?? 'BDT');
        };

        $num = function ($v, $fallback = 0) {
            if (is_numeric($v)) return $v + 0;
            // allow "1,234.50" or formatted strings
            if (is_string($v)) {
                $clean = preg_replace('/[^\d\.\-]/', '', $v);
                return is_numeric($clean) ? ($clean + 0) : $fallback;
            }
            return $fallback;
        };

        // Price pieces (prefer prepared price block)
        $priceBlock = $fd['price'] ?? [];
        $base        = $num($priceBlock['base']          ?? $fd['base']        ?? $fd['base_fee'] ?? $fd['base_fare'] ?? 0);
        $tax         = $num($priceBlock['tax']           ?? $fd['tax_amount']  ?? 0);
        $supplierFee = $num($priceBlock['supplierFee']   ?? $fd['supplier_fee'] ?? 0);
        $ticketFee   = $num($priceBlock['ticketingFee']  ?? $fd['ticketing_fee'] ?? 0);
        $grand       = $num($priceBlock['grandTotal']    ?? $fd['grandTotal']  ?? $fd['price'] ?? 0); // your UI sometimes sends price as total
        $currency    = $getCurrency();

        // Guests
        $adults  = (int)($rd['adults']   ?? 0);
        $children = (int)($rd['children'] ?? 0);
        $infants = (int)($rd['infants']  ?? 0);
        $total_guests = $adults + $children + $infants;

        // Totals before platform fees
        $total_before_fees = $grand > 0 ? $grand : ($base + $tax + $supplierFee + $ticketFee);

        // Buyer fees (admin) and service fees (vendor)
        $total_buyer_fee = 0;
        $list_buyer_fees = null;
        if (!empty($list_buyer_fees = setting_item('flight_booking_buyer_fees'))) {
            $list_fees = json_decode($list_buyer_fees, true);
            $total_buyer_fee = $this->calculateServiceFees($list_fees, $total_before_fees, $total_guests);
        }

        $total_service_fee = 0;
        $list_service_fee = null;
        if (!empty($this->enable_service_fee) && !empty($list_service_fee = $this->service_fee)) {
            $total_service_fee = $this->calculateServiceFees($list_service_fee, $total_before_fees, $total_guests);
        }

        $total = $total_before_fees + $total_buyer_fee + $total_service_fee;

        // Airline code
        $validatingCodes = $fd['validatingAirlineCodes'] ?? [];
        if (!is_array($validatingCodes) && $validatingCodes) $validatingCodes = [$validatingCodes];
        $airlineCode = $first($validatingCodes, $fd['airline_code'] ?? 'N/A');

        // Seats
        $bookableSeats =
            (int)($fd['capacity']['bookableSeats'] ?? 0) ?:
            (int)($fd['numberOfBookableSeats']     ?? 0) ?:
            (int)($fd['bookable_seats']            ?? 0);

        // Determine flight type (default oneway; will try to infer from request/segments)
        $flightType = 'oneway';
        $tripTypeRaw = $rd['trip_type']
            ?? $rd['tripType']
            ?? $fd['trip_type']
            ?? $fd['tripType']
            ?? ($fd['flags']['tripType'] ?? null);
        if (!empty($tripTypeRaw)) {
            $tripTypeNormalized = strtolower(str_replace(['-', '_', ' '], '', $tripTypeRaw));
            if (in_array($tripTypeNormalized, ['round', 'roundtrip', 'return', 'rt'])) {
                $flightType = 'return';
            } elseif (in_array($tripTypeNormalized, ['oneway', 'single', 'singleway'])) {
                $flightType = 'oneway';
            }
        } else {
            $oneWayFlag = $fd['flags']['oneWay'] ?? $fd['oneWay'] ?? null;
            if ($oneWayFlag !== null) {
                $flightType = filter_var($oneWayFlag, FILTER_VALIDATE_BOOLEAN) ? 'oneway' : 'return';
            }
        }

        $booking = new $this->bookingClass();
        $booking->status = 'draft';
        $booking->object_id    = $request->input('service_id');
        $booking->object_model = $request->input('service_type');
        $booking->customer_id  = Auth::id();

        // Extract actual flight dates from flight_data
        $start_date = null;
        $end_date = null;

        $routes = $fd['routeSummary'] ?? $fd['flight_routes'] ?? $fd['itineraries'] ?? [];
        $allSegments = [];
        $normalizedSegments = [];

        // Collect all segments from all routes (handles both one-way and round-trip)
        foreach ($routes as $r) {
            if (isset($r['segments']) && is_array($r['segments'])) {
                foreach ($r['segments'] as $seg) {
                    $allSegments[] = $seg;
                    $normalizedSegments[] = [
                        'departure_code' => $seg['departure_iata_code'] ?? ($seg['departure']['iataCode'] ?? null),
                        'arrival_code'   => $seg['arrival_iata_code']   ?? ($seg['arrival']['iataCode']   ?? null),
                        'departure_at'   => $seg['departure_at']        ?? ($seg['departure']['at']       ?? null),
                        'arrival_at'     => $seg['arrival_at']          ?? ($seg['arrival']['at']         ?? null),
                    ];
                }
            } else {
                // Legacy structure - treat the route as a single segment
                $allSegments[] = $r;
                $normalizedSegments[] = [
                    'departure_code' => $r['departure_iata_code'] ?? null,
                    'arrival_code'   => $r['arrival_iata_code']   ?? null,
                    'departure_at'   => $r['departure_at']        ?? null,
                    'arrival_at'     => $r['arrival_at']          ?? null,
                ];
            }
        }

        // Helper to extract date from segment - try all possible field variations
        $getSegmentDate = function($seg, $type = 'departure') {
            if (!is_array($seg)) {
                return null;
            }

            if ($type === 'departure') {
                // Try all possible departure field names
                return $seg['departure_at']
                    ?? $seg['departure']['at']
                    ?? $seg['departureAt']
                    ?? $seg['departureDateTime']
                    ?? (isset($seg['departure']) && is_string($seg['departure']) ? $seg['departure'] : null)
                    ?? (isset($seg['departure']) && is_array($seg['departure']) && isset($seg['departure']['at']) ? $seg['departure']['at'] : null)
                    ?? null;
            } else {
                // Try all possible arrival field names
                return $seg['arrival_at']
                    ?? $seg['arrival']['at']
                    ?? $seg['arrivalAt']
                    ?? $seg['arrivalDateTime']
                    ?? (isset($seg['arrival']) && is_string($seg['arrival']) ? $seg['arrival'] : null)
                    ?? (isset($seg['arrival']) && is_array($seg['arrival']) && isset($seg['arrival']['at']) ? $seg['arrival']['at'] : null)
                    ?? null;
            }
        };

        // Get first segment's departure time for start_date
        if (!empty($allSegments)) {
            $firstSegment = $allSegments[0];
            $start_date = $getSegmentDate($firstSegment, 'departure');

            // Get last segment's arrival time for end_date
            // For multi-segment flights: this should be the final flight's arrival
            $lastIndex = count($allSegments) - 1;
            $lastSegment = $allSegments[$lastIndex];
            $end_date = $getSegmentDate($lastSegment, 'arrival');

            // Debug: Log if we couldn't get end_date from last segment
            if (empty($end_date)) {
                // Try to find any segment with a valid arrival time
                for ($i = $lastIndex; $i >= 0; $i--) {
                    $testSeg = $allSegments[$i];
                    $testEnd = $getSegmentDate($testSeg, 'arrival');
                    if (!empty($testEnd)) {
                        $end_date = $testEnd;
                        break;
                    }
                }
            }
        }

        // If end_date is still null, try to get from the last route's last segment
        if (empty($end_date) && !empty($routes)) {
            $lastRouteIndex = count($routes) - 1;
            $lastRoute = $routes[$lastRouteIndex];

            if (isset($lastRoute['segments']) && is_array($lastRoute['segments']) && !empty($lastRoute['segments'])) {
                $lastRouteSegmentIndex = count($lastRoute['segments']) - 1;
                $lastRouteSegment = $lastRoute['segments'][$lastRouteSegmentIndex];
                $end_date = $getSegmentDate($lastRouteSegment, 'arrival');
            } elseif (isset($lastRoute['arrival_at']) || isset($lastRoute['arrival'])) {
                $end_date = $lastRoute['arrival_at']
                    ?? $lastRoute['arrival']['at']
                    ?? $lastRoute['arrivalAt']
                    ?? (isset($lastRoute['arrival']) && is_string($lastRoute['arrival']) ? $lastRoute['arrival'] : null)
                    ?? null;
            }
        }

        // Final fallback: iterate through all segments in reverse to find last valid arrival
        if (empty($end_date) && !empty($allSegments)) {
            for ($i = count($allSegments) - 1; $i >= 0; $i--) {
                $seg = $allSegments[$i];
                $potentialEnd = $getSegmentDate($seg, 'arrival');
                if (!empty($potentialEnd)) {
                    $end_date = $potentialEnd;
                    break;
                }
            }
        }

        // Helper function to parse and format date to Y-m-d H:i:s format
        $parseDate = function($dateValue, $defaultValue) {
            if (empty($dateValue)) {
                return $defaultValue;
            }

            try {
                // If already a DateTime or Carbon instance
                if ($dateValue instanceof \DateTime || $dateValue instanceof \Carbon\Carbon) {
                    return $dateValue->format('Y-m-d H:i:s');
                }

                // If it's a string, try to parse it
                if (is_string($dateValue) && !empty(trim($dateValue))) {
                    // Remove any timezone info if present and parse
                    $dateString = trim($dateValue);

                    // Handle ISO 8601 format with timezone (e.g., "2025-11-25T19:45:00+00:00")
                    if (preg_match('/^(\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}:\d{2})/', $dateString, $matches)) {
                        $dateString = str_replace('T', ' ', $matches[1]);
                    }

                    // Parse the date
                    $parsed = \Carbon\Carbon::parse($dateString);

                    // Format to Y-m-d H:i:s (e.g., "2025-11-25 19:45:00")
                    return $parsed->format('Y-m-d H:i:s');
                }
            } catch (\Exception $e) {
                // Log error for debugging (optional)
                // \Log::warning('Failed to parse flight date: ' . $e->getMessage(), ['date' => $dateValue]);
            }

            return $defaultValue;
        };

        // Get default values (only use if we couldn't extract from flight_data)
        $defaultStart = $this->departure_time ? $this->departure_time->format('Y-m-d H:i:s') : date('Y-m-d H:i:s');
        $defaultEnd = $this->arrival_time ? $this->arrival_time->format('Y-m-d H:i:s') : date('Y-m-d H:i:s');

        // Parse and set dates
        $start_date = $parseDate($start_date, $defaultStart);
        $end_date = $parseDate($end_date, $defaultEnd);

        // Ensure end_date is after start_date and different from start_date
        try {
            $startCarbon = \Carbon\Carbon::parse($start_date);
            $endCarbon = \Carbon\Carbon::parse($end_date);

            // If end_date is same as start_date or before it, we need to fix it
            if ($endCarbon->lte($startCarbon) || $end_date === $start_date) {
                // Try one more time to get the real end_date from segments
                if (!empty($allSegments) && count($allSegments) > 1) {
                    // Get the actual last segment's arrival
                    $lastSeg = $allSegments[count($allSegments) - 1];
                    $realEnd = $getSegmentDate($lastSeg, 'arrival');
                    if (!empty($realEnd)) {
                        $end_date = $parseDate($realEnd, $defaultEnd);
                        $endCarbon = \Carbon\Carbon::parse($end_date);
                    }
                }

                // If still invalid, adjust end to be after start (minimum 1 hour)
                if ($endCarbon->lte($startCarbon) || $end_date === $start_date) {
                    $end_date = $startCarbon->copy()->addHours(1)->format('Y-m-d H:i:s');
                }
            }
        } catch (\Exception $e) {
            // If comparison fails, ensure end_date is at least 1 hour after start_date
            try {
                $startCarbon = \Carbon\Carbon::parse($start_date);
                $endCarbon = \Carbon\Carbon::parse($end_date);
                if ($end_date === $start_date || $endCarbon->lte($startCarbon)) {
                    $end_date = $startCarbon->copy()->addHours(1)->format('Y-m-d H:i:s');
                }
            } catch (\Exception $e2) {
                // Final fallback
                if ($end_date === $start_date) {
                    $end_date = date('Y-m-d H:i:s', strtotime($start_date . ' +1 hour'));
                }
            }
        }

        $booking->start_date = $start_date;
        $booking->end_date   = $end_date;

        // Infer trip type from normalized segments if still defaulting to oneway
        $segmentCount = count($normalizedSegments);
        if ($flightType === 'oneway' && $segmentCount >= 2) {
            $firstDepartureCode = strtoupper($normalizedSegments[0]['departure_code'] ?? '');
            $lastArrivalCode    = strtoupper($normalizedSegments[$segmentCount - 1]['arrival_code'] ?? '');

            // If 3+ segments with different destinations, it's multicity
            if ($segmentCount >= 3) {
                $flightType = 'multicity';
            }
            // If 2 segments and returning to origin, it's return/roundtrip
            elseif ($firstDepartureCode && $lastArrivalCode && $firstDepartureCode === $lastArrivalCode) {
                $flightType = 'return';
            }
            // Otherwise keep as oneway (2 segments, different destinations)
        }

        $booking->airline = $airlineCode;


// ===== DISCOUNT LOGIC START =====
// Extract discount from request
        $discount = max(0, $request->discount); // Negative prevent
//        $finalPrice = $finalPrice;


// Apply discount to get final total
        $final_total = $request->finalPrice ?: max(0, $total_before_fees - $discount);

// Set booking amounts
        $booking->total = $final_total;                          // Final payable amount
//        $booking->total_before_discount = $total_before_discount; // Total before discount
        $booking->coupon_amount = $discount;                      // Discount amount
// ===== DISCOUNT LOGIC END =====

//        $booking->total = $total;
        $booking->total_guests = $total_guests;
        $booking->currency = $currency;

        $booking->vendor_service_fee_amount = $total_service_fee ?: 0;
        $booking->vendor_service_fee = $list_service_fee ?: '';
        $booking->buyer_fees = $list_buyer_fees ?: '';
        $booking->total_before_fees = $total_before_fees;
        $booking->total_before_discount = $total_before_fees;

        $booking->seat_class = $rd['travelClass'] ?? 'ECONOMY';
        $booking->flight_from = $rd['from_where'] ?? '';
        $booking->flight_to   = $rd['to_where'] ?? '';
        $booking->source      = $fd['source'] ?? 'N/A';

        $booking->flight_type   = $flightType;
        $booking->bookable_seats = $bookableSeats;
        $booking->vendor_id = $this->author_id;

        // Persist fee components for reporting
        $booking->total_fee = $supplierFee + $ticketFee;
        $booking->base_fee  = $base;
        $booking->supplier_fee  = $supplierFee;
        $booking->ticketing_fee = $ticketFee;

        $booking->adult_count  = $adults;
        $booking->child_count  = $children;
        $booking->infant_count = $infants;

        $booking->calculateCommission();

        // Deposit logic unchanged, just using $total_before_fees where required
        if ($this->isDepositEnable()) {
            $booking_deposit_fomular = $this->getDepositFomular();
            $tmp_price_total = ($booking_deposit_fomular == "deposit_and_fee") ? $booking->total_before_fees : $booking->total;

            switch ($this->getDepositType()) {
                case "percent":
                    $booking->deposit = $tmp_price_total * $this->getDepositAmount() / 100;
                    break;
                default:
                    $booking->deposit = $this->getDepositAmount();
                    break;
            }
            if ($booking_deposit_fomular == "deposit_and_fee") {
                $booking->deposit = $booking->deposit + $total_buyer_fee + $total_service_fee;
            }
        }

        $check = $booking->save();
        if (!$check) {
            return $this->sendError(__("Can not check availability"));
        }

        // Clear other drafts
        $this->bookingClass::clearDraftBookings();

        // Meta
        $booking->addMeta('duration', $this->duration);
        $booking->addMeta('base_price', $base);
        $booking->addMeta('sale_price', $base);
        $booking->addMeta('guests', $total_guests);

        // Store valid booking time (lastTicketingDate) from flight_data
        $validBookingTime = $fd['validBookingTime'] ?? ($fd['lastTicketingDateTime'] ?? ($fd['lastTicketingDate'] ?? null));
        if ($validBookingTime) {
            $booking->addMeta('valid_booking_time', $validBookingTime);
            $booking->addMeta('last_ticketing_date', $validBookingTime);
        }
        if ($this->isDepositEnable()) {
            $booking->addMeta('deposit_info', [
                'type'    => $this->getDepositType(),
                'amount'  => $this->getDepositAmount(),
                'fomular' => $this->getDepositFomular(),
            ]);
        }

        // Passengers (UPDATED: Create multiple passengers based on passenger_type_pricing)
        $passengerTypePricing = $fd['passenger_type_pricing'] ?? [];

        // Create passengers if we have passenger_type_pricing OR flight_seat OR travelerPricings
        $hasPassengerData = !empty($passengerTypePricing)
            || !empty($request->flight_seat)
            || !empty($fd['travelerPricings'])
            || !empty($fd['traveler_pricings']);

        if ($hasPassengerData) {
            // Map Sabre passenger type codes to full names for backward compatibility
            $passengerTypeMap = [
                'ADT' => 'ADULT',
                'CHD' => 'CHILD',
                'CNN' => 'CHILD',
                'C02' => 'CHILD',
                'C03' => 'CHILD',
                'C04' => 'CHILD',
                'C05' => 'CHILD',
                'C06' => 'CHILD',
                'C07' => 'CHILD',
                'C08' => 'CHILD',
                'C09' => 'CHILD',
                'C10' => 'CHILD',
                'C11' => 'CHILD',
                'INF' => 'INFANT',
                'INS' => 'INFANT'
            ];

            // Use new passenger_type_pricing structure if available
            if (!empty($passengerTypePricing) && is_array($passengerTypePricing)) {
                // Prepare traveler pricing lookups (carry fare/class info)
                $rawTravelerPricings = $fd['traveler_pricings'] ?? $fd['travelerPricings'] ?? [];
                $travelerPricingBuckets = [];
                if (is_array($rawTravelerPricings)) {
                    foreach ($rawTravelerPricings as $tp) {
                        $typeCode = strtoupper($tp['traveler_type'] ?? $tp['travelerType'] ?? 'ADT');
                        $travelerPricingBuckets[$typeCode][] = $tp;
                    }
                }

                foreach ($passengerTypePricing as $paxType) {
                    $typeCode = $paxType['type'] ?? 'ADT';
                    $count = (int)($paxType['count'] ?? 1);
                    $mappedType = $passengerTypeMap[$typeCode] ?? 'ADULT';

                    // Pull detailed pricing for this type if available
                    $tpData = null;
                    if (!empty($travelerPricingBuckets[$typeCode])) {
                        $tpData = array_shift($travelerPricingBuckets[$typeCode]);
                    }
                    $tpPrice       = $tpData['price'] ?? [];
                    $tpFareDetails = $tpData['fareDetailsBySegment'][0] ?? $tpData['fare_details_by_segment'][0] ?? null;

                    // Extract dynamic class code (priority: fareDetailsBySegment > tpData > direct from fd)
                    $passengerClass = null;
                    if (!empty($tpFareDetails['class'])) {
                        $passengerClass = trim($tpFareDetails['class']);
                    } elseif (!empty($tpData['class'])) {
                        $passengerClass = trim($tpData['class']);
                    } elseif (isset($fd['travelerPricings'][0]['fareDetailsBySegment'][0]['class'])) {
                        $passengerClass = trim($fd['travelerPricings'][0]['fareDetailsBySegment'][0]['class']);
                    } elseif (isset($fd['traveler_pricings'][0]['class'])) {
                        $passengerClass = trim($fd['traveler_pricings'][0]['class']);
                    } elseif (isset($fd['itineraries'][0]['segments'][0]['class'])) {
                        $passengerClass = trim($fd['itineraries'][0]['segments'][0]['class']);
                    }
                    if (empty($passengerClass) || !is_string($passengerClass)) {
                        $passengerClass = 'Y';
                    } else {
                        $passengerClass = strtoupper($passengerClass);
                    }

                    // Create multiple passengers for this type
                    for ($i = 0; $i < $count; $i++) {
                        $bp = new BookingPassengers();
                        $bp->fillByAttr([
                            'flight_id',
                            'flight_seat_id',
                            'booking_id',
                            'seat_type',
                            'email',
                            'first_name',
                            'last_name',
                            'phone',
                            'dob',
                            'price',
                            'id_card',
                            'traveler_type',
                            'passenger_type_code',
                            'fare_option',
                            'currency',
                            'total',
                            'base',
                            'cabin',
                            'fare_basis',
                            'class'
                        ], [
                            'flight_id'      => $this->id,
                            'flight_seat_id' => 1,
                            'booking_id'     => $booking->id,
                            'seat_type'      => $rd['travelClass'] ?? 'ECONOMY',

                            // Empty by default - will be filled in checkout
                            'email'          => '',
                            'first_name'     => '',
                            'last_name'      => '',
                            'phone'          => '',

                            'dob'            => '',
                            'price'          => $num($paxType['total'] ?? 0),
                            'id_card'        => '',

                            'traveler_type'      => $mappedType,
                            'passenger_type_code' => $typeCode, // Store original code (ADT, C03, C07, etc.)
                            'fare_option'        => $tpData['fare_option'] ?? $tpData['fareOption'] ?? 'STANDARD',
                            'currency'           => $paxType['currency'] ?? ($tpData['currency'] ?? ($tpPrice['currency'] ?? $currency)),
                            'total'              => $num($paxType['total'] ?? ($tpData['total'] ?? ($tpPrice['total'] ?? 0))),
                            'base'               => $num($paxType['base_fare'] ?? ($tpData['base'] ?? ($tpPrice['base'] ?? 0))),
                            'cabin'              => $tpData['cabin'] ?? ($tpFareDetails['cabin'] ?? ($rd['travelClass'] ?? 'ECONOMY')),
                            'fare_basis'         => $tpData['fare_basis'] ?? ($tpFareDetails['fareBasis'] ?? 'N/A'),
                            'class'              => $passengerClass,
                        ]);
                        $bp->save();
                    }
                }
            } else {
                // Fallback to old travelerPricings structure
                $travelerPricings = $fd['travelerPricings'] ?? $fd['traveler_pricings'] ?? [];
                if (is_array($travelerPricings)) {
                    foreach ($travelerPricings as $tp) {
                        $rawType = $tp['travelerType'] ?? $tp['traveler_type'] ?? 'ADT';
                        $mappedType = $passengerTypeMap[$rawType] ?? $rawType;

                        // Extract dynamic class code for this traveler
                        $travelerClass = null;
                        if (!empty($tp['class'])) {
                            $travelerClass = trim(strtoupper($tp['class']));
                        } elseif (!empty($tp['fareDetailsBySegment'][0]['class'])) {
                            $travelerClass = trim(strtoupper($tp['fareDetailsBySegment'][0]['class']));
                        } elseif (!empty($tp['fare_details_by_segment'][0]['class'])) {
                            $travelerClass = trim(strtoupper($tp['fare_details_by_segment'][0]['class']));
                        } elseif (isset($fd['travelerPricings'][0]['fareDetailsBySegment'][0]['class'])) {
                            $travelerClass = trim(strtoupper($fd['travelerPricings'][0]['fareDetailsBySegment'][0]['class']));
                        } elseif (isset($fd['traveler_pricings'][0]['class'])) {
                            $travelerClass = trim(strtoupper($fd['traveler_pricings'][0]['class']));
                        } elseif (isset($fd['itineraries'][0]['segments'][0]['class'])) {
                            $travelerClass = trim(strtoupper($fd['itineraries'][0]['segments'][0]['class']));
                        }
                        if (empty($travelerClass)) {
                            $travelerClass = 'Y';
                        }

                        $bp = new BookingPassengers();
                        $bp->fillByAttr([
                            'flight_id',
                            'flight_seat_id',
                            'booking_id',
                            'seat_type',
                            'email',
                            'first_name',
                            'last_name',
                            'phone',
                            'dob',
                            'price',
                            'id_card',
                            'traveler_type',
                            'passenger_type_code',
                            'fare_option',
                            'currency',
                            'total',
                            'base',
                            'cabin',
                            'fare_basis',
                            'class'
                        ], [
                            'flight_id'      => $this->id,
                            'flight_seat_id' => 1,
                            'booking_id'     => $booking->id,
                            'seat_type'      => $rd['travelClass'] ?? 'ECONOMY',

                            // Empty by default - will be filled in checkout
                            'email'          => '',
                            'first_name'     => '',
                            'last_name'      => '',
                            'phone'          => '',

                            'dob'            => '',
                            'price'          => 0,
                            'id_card'        => '',

                            'traveler_type'      => $mappedType,
                            'passenger_type_code' => $rawType,
                            'fare_option'        => $tp['fareOption'] ?? $tp['fare_option'] ?? 'STANDARD',
                            'currency'           => $tp['currency'] ?? ($tp['price']['currency'] ?? $currency),
                            'total'              => $num($tp['total'] ?? ($tp['price']['total'] ?? 0)),
                            'base'               => $num($tp['base']  ?? ($tp['price']['base']  ?? 0)),
                            'cabin'              => $tp['cabin'] ?? ($tp['fareDetailsBySegment'][0]['cabin'] ?? 'ECONOMY'),
                            'fare_basis'         => $tp['fare_basis'] ?? ($tp['fareDetailsBySegment'][0]['fareBasis'] ?? 'N/A'),
                            'class'              => $travelerClass,
                        ]);
                        $bp->save();
                    }
                }
            }
        } else {
            // If no passenger data in flight_data, create passengers based on total_guests
            // This handles cases where flight_seat was removed from frontend
            if ($total_guests > 0) {
                $passengerTypeMap = [
                    'ADULT' => 'ADT',
                    'CHILD' => 'CHD',
                    'INFANT' => 'INF'
                ];

                // Create passengers based on adults, children, infants
                $adults = (int)($rd['adults'] ?? $total_guests);
                $children = (int)($rd['children'] ?? 0);
                $infants = (int)($rd['infants'] ?? 0);

                // Extract dynamic class code from Sabre data (priority order)
                $dynamicClass = null;

                // Method 1: From travelerPricings fareDetailsBySegment (most accurate - exact path from your data)
                if (isset($fd['travelerPricings'][0]['fareDetailsBySegment'][0]['class'])) {
                    $dynamicClass = trim($fd['travelerPricings'][0]['fareDetailsBySegment'][0]['class']);
                }
                // Also check fare_details_by_segment (snake_case variant)
                if (empty($dynamicClass) && isset($fd['travelerPricings'][0]['fare_details_by_segment'][0]['class'])) {
                    $dynamicClass = trim($fd['travelerPricings'][0]['fare_details_by_segment'][0]['class']);
                }
                // Check traveler_pricings (snake_case key)
                if (empty($dynamicClass) && isset($fd['traveler_pricings'][0]['fareDetailsBySegment'][0]['class'])) {
                    $dynamicClass = trim($fd['traveler_pricings'][0]['fareDetailsBySegment'][0]['class']);
                }
                if (empty($dynamicClass) && isset($fd['traveler_pricings'][0]['fare_details_by_segment'][0]['class'])) {
                    $dynamicClass = trim($fd['traveler_pricings'][0]['fare_details_by_segment'][0]['class']);
                }

                // Method 2: From traveler_pricings direct class (processed data - your data shows "class" => "V" here)
                if (empty($dynamicClass) && isset($fd['traveler_pricings'][0]['class'])) {
                    $dynamicClass = trim($fd['traveler_pricings'][0]['class']);
                }
                if (empty($dynamicClass) && isset($fd['travelerPricings'][0]['class'])) {
                    $dynamicClass = trim($fd['travelerPricings'][0]['class']);
                }

                // Method 3: From itineraries segments (original Sabre data - your data shows "class" => "V" here)
                if (empty($dynamicClass) && isset($fd['itineraries'][0]['segments'][0]['class'])) {
                    $dynamicClass = trim($fd['itineraries'][0]['segments'][0]['class']);
                }

                // Method 4: From fare_basis first character
                if (empty($dynamicClass) && isset($fd['passenger_type_pricing'][0]['fare_basis'])) {
                    $fareBasis = $fd['passenger_type_pricing'][0]['fare_basis'];
                    if (is_string($fareBasis) && strlen($fareBasis) > 0) {
                        $dynamicClass = strtoupper(substr($fareBasis, 0, 1));
                    }
                }

                // Final fallback: only use 'Y' if absolutely nothing found
                if (empty($dynamicClass) || !is_string($dynamicClass) || strlen($dynamicClass) == 0) {
                    $dynamicClass = 'Y';
                } else {
                    $dynamicClass = strtoupper(trim($dynamicClass));
                }

                // Create adult passengers
                for ($i = 0; $i < $adults; $i++) {
                    $bp = new BookingPassengers();
                    $bp->fillByAttr([
                        'flight_id',
                        'flight_seat_id',
                        'booking_id',
                        'seat_type',
                        'email',
                        'first_name',
                        'last_name',
                        'phone',
                        'dob',
                        'price',
                        'id_card',
                        'traveler_type',
                        'passenger_type_code',
                        'fare_option',
                        'currency',
                        'total',
                        'base',
                        'cabin',
                        'fare_basis',
                        'class'
                    ], [
                        'flight_id'      => $this->id,
                        'flight_seat_id' => 1,
                        'booking_id'     => $booking->id,
                        'seat_type'      => $rd['travelClass'] ?? 'ECONOMY',
                        'email'          => '',
                        'first_name'     => '',
                        'last_name'      => '',
                        'phone'          => '',
                        'dob'            => '',
                        'price'          => 0,
                        'id_card'        => '',
                        'traveler_type'      => 'ADULT',
                        'passenger_type_code' => 'ADT',
                        'fare_option'        => 'STANDARD',
                        'currency'           => $currency,
                        'total'              => 0,
                        'base'               => 0,
                        'cabin'              => $rd['travelClass'] ?? 'ECONOMY',
                        'fare_basis'         => 'N/A',
                        'class'              => 'Y',
                    ]);
                    $bp->save();
                }

                // Create child passengers
                for ($i = 0; $i < $children; $i++) {
                    $bp = new BookingPassengers();
                    $bp->fillByAttr([
                        'flight_id',
                        'flight_seat_id',
                        'booking_id',
                        'seat_type',
                        'email',
                        'first_name',
                        'last_name',
                        'phone',
                        'dob',
                        'price',
                        'id_card',
                        'traveler_type',
                        'passenger_type_code',
                        'fare_option',
                        'currency',
                        'total',
                        'base',
                        'cabin',
                        'fare_basis',
                        'class'
                    ], [
                        'flight_id'      => $this->id,
                        'flight_seat_id' => 1,
                        'booking_id'     => $booking->id,
                        'seat_type'      => $rd['travelClass'] ?? 'ECONOMY',
                        'email'          => '',
                        'first_name'     => '',
                        'last_name'      => '',
                        'phone'          => '',
                        'dob'            => '',
                        'price'          => 0,
                        'id_card'        => '',
                        'traveler_type'      => 'CHILD',
                        'passenger_type_code' => 'CHD',
                        'fare_option'        => 'STANDARD',
                        'currency'           => $currency,
                        'total'              => 0,
                        'base'               => 0,
                        'cabin'              => $rd['travelClass'] ?? 'ECONOMY',
                        'fare_basis'         => 'N/A',
                        'class'              => 'Y',
                    ]);
                    $bp->save();
                }

                // Create infant passengers
                for ($i = 0; $i < $infants; $i++) {
                    $bp = new BookingPassengers();
                    $bp->fillByAttr([
                        'flight_id',
                        'flight_seat_id',
                        'booking_id',
                        'seat_type',
                        'email',
                        'first_name',
                        'last_name',
                        'phone',
                        'dob',
                        'price',
                        'id_card',
                        'traveler_type',
                        'passenger_type_code',
                        'fare_option',
                        'currency',
                        'total',
                        'base',
                        'cabin',
                        'fare_basis',
                        'class'
                    ], [
                        'flight_id'      => $this->id,
                        'flight_seat_id' => 1,
                        'booking_id'     => $booking->id,
                        'seat_type'      => $rd['travelClass'] ?? 'ECONOMY',
                        'email'          => '',
                        'first_name'     => '',
                        'last_name'      => '',
                        'phone'          => '',
                        'dob'            => '',
                        'price'          => 0,
                        'id_card'        => '',
                        'traveler_type'      => 'INFANT',
                        'passenger_type_code' => 'INF',
                        'fare_option'        => 'STANDARD',
                        'currency'           => $currency,
                        'total'              => 0,
                        'base'               => 0,
                        'cabin'              => $rd['travelClass'] ?? 'ECONOMY',
                        'fare_basis'         => 'N/A',
                        'class'              => $dynamicClass,
                    ]);
                    $bp->save();
                }
            }
        }


        // Routes - Create routes for all flight bookings
        // Also use this opportunity to ensure we have the correct end_date
        if ($request->input('service_type') == 'flight') {
            $routes = $fd['routeSummary'] ?? $fd['flight_routes'] ?? $fd['itineraries'] ?? [];

            // Build a map of segment classes from original itineraries data (most accurate)
            $segmentClassMap = [];
            $itineraries = $fd['itineraries'] ?? [];
            if (is_array($itineraries) && !empty($itineraries)) {
                foreach ($itineraries as $itinerary) {
                    $segments = $itinerary['segments'] ?? [];
                    if (is_array($segments)) {
                        foreach ($segments as $origSeg) {
                            // Create a unique key from carrier + flight number + departure
                            $carrier = $origSeg['carrierCode'] ?? $origSeg['carrier_code'] ?? '';
                            $flightNum = $origSeg['number'] ?? $origSeg['flight_number'] ?? '';
                            $departure = $origSeg['departure']['iataCode'] ?? $origSeg['departure_iata_code'] ?? '';
                            $departureAt = $origSeg['departure']['at'] ?? $origSeg['departure_at'] ?? '';

                            if ($carrier && $flightNum && $departure) {
                                $segmentKey = strtoupper($carrier . '_' . $flightNum . '_' . $departure . '_' . $departureAt);
                                $segmentClass = $origSeg['class'] ?? null;
                                if (!empty($segmentClass)) {
                                    $segmentClassMap[$segmentKey] = strtoupper(trim($segmentClass));
                                }
                            }
                        }
                    }
                }
            }

            // Queue booking classes per segment using traveler pricing info (fallback)
            $segmentClassQueue = [];
            $segmentClassIndex = 0;
            $travelerPricingSource = null;
            $rawTravelerPricings = $fd['traveler_pricings'] ?? $fd['travelerPricings'] ?? [];
            if (is_array($rawTravelerPricings) && !empty($rawTravelerPricings)) {
                $travelerPricingSource = $rawTravelerPricings[0];
            }
            if ($travelerPricingSource) {
                $segmentDetails = $travelerPricingSource['fare_details_by_segment']
                    ?? $travelerPricingSource['fareDetailsBySegment']
                    ?? [];
                if (is_array($segmentDetails)) {
                    foreach ($segmentDetails as $detail) {
                        if (!empty($detail['class'])) {
                            $segmentClassQueue[] = $detail['class'];
                        }
                    }
                }
            }
            $fallbackClassFromFareBasis = function () use (&$passengerTypePricing) {
                foreach ((array)$passengerTypePricing as $pax) {
                    if (!empty($pax['fare_basis']) && is_string($pax['fare_basis'])) {
                        return strtoupper(substr($pax['fare_basis'], 0, 1));
                    }
                }
                return null;
            };

            // Function to get class for a segment (route-wise)
            $getSegmentClass = function ($seg) use (&$segmentClassMap, &$segmentClassQueue, &$segmentClassIndex, $fallbackClassFromFareBasis) {
                // Method 1: Try to match from original itineraries segment class map
                $carrier = $seg['carrier_code'] ?? $seg['carrierCode'] ?? '';
                $flightNum = $seg['flight_number'] ?? $seg['number'] ?? '';
                $departure = $seg['departure_iata_code'] ?? $seg['departure']['iataCode'] ?? '';
                $departureAt = $seg['departure_at'] ?? $seg['departure']['at'] ?? '';

                if ($carrier && $flightNum && $departure) {
                    $segmentKey = strtoupper($carrier . '_' . $flightNum . '_' . $departure . '_' . $departureAt);
                    if (isset($segmentClassMap[$segmentKey])) {
                        return $segmentClassMap[$segmentKey];
                    }
                }

                // Method 2: Check segment's own class field (from processed data)
                $segClass = $seg['class'] ?? $seg['bookingCode'] ?? $seg['booking_class'] ?? null;
                if (!empty($segClass)) {
                    $segClass = strtoupper(trim($segClass));
                    // Validate: must be single letter A-Z (not 'E' from "ECONOMY" unless it's a valid booking class)
                    // Note: 'E' can be a valid booking class, but we want to ensure it's not from "ECONOMY" string
                    if (strlen($segClass) == 1 && preg_match('/^[A-Z]$/', $segClass)) {
                        return $segClass;
                    }
                }

                // Method 3: Use queue from traveler pricing (by segment index)
                if (array_key_exists($segmentClassIndex, $segmentClassQueue)) {
                    $class = $segmentClassQueue[$segmentClassIndex++];
                    return $class;
                }
                $segmentClassIndex++;

                // Method 4: Fallback to fare_basis
                $fallback = $fallbackClassFromFareBasis();
                if ($fallback) {
                    return $fallback;
                }

                // Method 5: Last resort - use segment class even if 'Y'
                if (!empty($segClass)) {
                    return strtoupper(trim($segClass));
                }

                return 'Y';
            };

            // Collect all arrival times from segments to ensure we get the last one
            $allArrivalTimes = [];

            // Reorder routes to ensure logical journey order (not just chronological)
            // Critical for Sabre PNR creation - segments must be in logical journey order
            $routesArray = is_array($routes) ? $routes : $routes->toArray();

            // Step 1: Flatten all segments from all routes into a single array
            $allSegments = [];
            foreach ($routesArray as $r) {
                $routeSegments = isset($r['segments']) && is_array($r['segments']) ? $r['segments'] : [$r];
                foreach ($routeSegments as $seg) {
                    $allSegments[] = $seg;
                }
            }

            if (count($allSegments) <= 1) {
                // One segment - no reordering needed
                $orderedSegments = $allSegments;
            } else {
                // Multiple segments - need proper journey ordering

                // Step 2: Find the earliest departure (starting point of outbound journey)
                $firstSegmentIdx = 0;
                $firstDepartureTime = PHP_INT_MAX;
                $firstDepartureLocation = null;
                foreach ($allSegments as $idx => $seg) {
                    $depTime = !empty($seg['departure_at']) ? strtotime($seg['departure_at']) : (!empty($seg['departure']['at']) ? strtotime($seg['departure']['at']) : PHP_INT_MAX);
                    if ($depTime < $firstDepartureTime) {
                        $firstDepartureTime = $depTime;
                        $firstSegmentIdx = $idx;
                        $firstDepartureLocation = strtoupper(trim($seg['departure_iata_code'] ?? ($seg['departure']['iataCode'] ?? '')));
                    }
                }

                // Step 3: Build outbound journey by following connections from start
                $orderedSegments = [];
                $used = [];

                // Start with the earliest segment (outbound journey start)
                $currentSegment = $allSegments[$firstSegmentIdx];
                $orderedSegments[] = $currentSegment;
                $used[$firstSegmentIdx] = true;
                $currentArrival = strtoupper(trim($currentSegment['arrival_iata_code'] ?? ($currentSegment['arrival']['iataCode'] ?? '')));
                $outboundEndLocation = $currentArrival; // Track where outbound ends
                $outboundEndTime = !empty($currentSegment['arrival_at']) ? strtotime($currentSegment['arrival_at']) : (!empty($currentSegment['arrival']['at']) ? strtotime($currentSegment['arrival']['at']) : 0);

                // Continue building outbound journey by finding connected segments
                $foundConnected = true;
                while ($foundConnected && count($used) < count($allSegments)) {
                    $foundConnected = false;

                    // Look for a segment that departs from where we just arrived
                    foreach ($allSegments as $idx => $seg) {
                        if (isset($used[$idx])) continue;

                        $segDeparture = strtoupper(trim($seg['departure_iata_code'] ?? ($seg['departure']['iataCode'] ?? '')));
                        $segDepartureTime = !empty($seg['departure_at']) ? strtotime($seg['departure_at']) : (!empty($seg['departure']['at']) ? strtotime($seg['departure']['at']) : 0);
                        $currentArrivalTime = !empty($currentSegment['arrival_at']) ? strtotime($currentSegment['arrival_at']) : (!empty($currentSegment['arrival']['at']) ? strtotime($currentSegment['arrival']['at']) : 0);

                        // If this segment departs from our current arrival location AND departs after we arrived
                        // (or within reasonable time window for connections), it's connected
                        if ($segDeparture === $currentArrival && !empty($currentArrival)) {
                            // Check if departure time is reasonable (after arrival or within 24 hours for connections)
                            $timeDiff = $segDepartureTime - $currentArrivalTime;
                            if ($timeDiff >= -3600 && $timeDiff <= 86400) { // Allow 1 hour before to 24 hours after
                                $orderedSegments[] = $seg;
                                $used[$idx] = true;
                                $currentSegment = $seg;
                                $currentArrival = strtoupper(trim($seg['arrival_iata_code'] ?? ($seg['arrival']['iataCode'] ?? '')));
                                $outboundEndLocation = $currentArrival; // Update outbound end
                                $outboundEndTime = !empty($seg['arrival_at']) ? strtotime($seg['arrival_at']) : (!empty($seg['arrival']['at']) ? strtotime($seg['arrival']['at']) : 0);
                                $foundConnected = true;
                                break;
                            }
                        }
                    }
                }

                // Step 4: Build return journey (segments that start from where outbound ended)
                // Find return journey segments - they should start from outboundEndLocation
                // OR be clearly return segments (depart much later than outbound end)
                $returnCandidates = [];
                foreach ($allSegments as $idx => $seg) {
                    if (isset($used[$idx])) continue;

                    $segDeparture = strtoupper(trim($seg['departure_iata_code'] ?? ($seg['departure']['iataCode'] ?? '')));
                    $segDepartureTime = !empty($seg['departure_at']) ? strtotime($seg['departure_at']) : (!empty($seg['departure']['at']) ? strtotime($seg['departure']['at']) : 0);

                    // Return journey starts from where outbound ended, OR from a later time
                    if ($segDeparture === $outboundEndLocation ||
                        ($segDepartureTime > $outboundEndTime + 3600)) { // At least 1 hour after outbound ends
                        $returnCandidates[] = ['segment' => $seg, 'idx' => $idx, 'time' => $segDepartureTime];
                    }
                }

                // Step 5: Order return journey segments
                if (!empty($returnCandidates)) {
                    // Find the return journey starting segment (departs from outboundEndLocation)
                    $returnStartIdx = null;
                    foreach ($returnCandidates as $candidate) {
                        $candidateDeparture = strtoupper(trim($candidate['segment']['departure_iata_code'] ?? ($candidate['segment']['departure']['iataCode'] ?? '')));
                        if ($candidateDeparture === $outboundEndLocation) {
                            $returnStartIdx = $candidate['idx'];
                            break;
                        }
                    }

                    // If no segment starts from outboundEndLocation, use earliest return candidate
                    if ($returnStartIdx === null) {
                        usort($returnCandidates, function($a, $b) {
                            return $a['time'] <=> $b['time'];
                        });
                        $returnStartIdx = $returnCandidates[0]['idx'];
                    }

                    // Build return journey starting from the identified segment
                    $currentSegment = $allSegments[$returnStartIdx];
                    $orderedSegments[] = $currentSegment;
                    $used[$returnStartIdx] = true;
                    $currentArrival = strtoupper(trim($currentSegment['arrival_iata_code'] ?? ($currentSegment['arrival']['iataCode'] ?? '')));

                    // Continue building return journey by finding connected segments
                    $foundConnected = true;
                    while ($foundConnected && count($used) < count($allSegments)) {
                        $foundConnected = false;

                        foreach ($allSegments as $idx => $seg) {
                            if (isset($used[$idx])) continue;

                            $segDeparture = strtoupper(trim($seg['departure_iata_code'] ?? ($seg['departure']['iataCode'] ?? '')));
                            $segDepartureTime = !empty($seg['departure_at']) ? strtotime($seg['departure_at']) : (!empty($seg['departure']['at']) ? strtotime($seg['departure']['at']) : 0);
                            $currentArrivalTime = !empty($currentSegment['arrival_at']) ? strtotime($currentSegment['arrival_at']) : (!empty($currentSegment['arrival']['at']) ? strtotime($currentSegment['arrival']['at']) : 0);

                            if ($segDeparture === $currentArrival && !empty($currentArrival)) {
                                $timeDiff = $segDepartureTime - $currentArrivalTime;
                                if ($timeDiff >= -3600 && $timeDiff <= 86400) {
                                    $orderedSegments[] = $seg;
                                    $used[$idx] = true;
                                    $currentSegment = $seg;
                                    $currentArrival = strtoupper(trim($seg['arrival_iata_code'] ?? ($seg['arrival']['iataCode'] ?? '')));
                                    $foundConnected = true;
                                    break;
                                }
                            }
                        }
                    }
                }

                // Step 6: Add any remaining segments (multi-city or edge cases)
                foreach ($allSegments as $idx => $seg) {
                    if (!isset($used[$idx])) {
                        // Sort remaining by departure time
                        $orderedSegments[] = $seg;
                    }
                }
            }

            // Rebuild routes array with properly ordered segments
            // Group ordered segments back into route structure
            $orderedRoutes = [];
            if (!empty($orderedSegments)) {
                $orderedRoutes[] = ['segments' => $orderedSegments];
            }

            $routes = $orderedRoutes;

            foreach ($routes as $r) {
                // Handle new Sabre prepared structure with segments array
                if (isset($r['segments']) && is_array($r['segments'])) {
                    // Process each segment as a separate route
                    foreach ($r['segments'] as $seg) {
                        // Extract critical route data from Sabre response
                        $departure_iata = $seg['departure_iata_code'] ?? ($seg['departure']['iataCode'] ?? ($seg['departure']['iata'] ?? null));
                        $arrival_iata   = $seg['arrival_iata_code']   ?? ($seg['arrival']['iataCode']   ?? ($seg['arrival']['iata'] ?? null));
                        $departure_at   = $seg['departure_at']        ?? ($seg['departure']['at']       ?? ($seg['departureDateTime'] ?? null));
                        $arrival_at     = $seg['arrival_at']          ?? ($seg['arrival']['at']         ?? ($seg['arrivalDateTime'] ?? null));

                        // Validate critical fields
                        if (empty($departure_iata) || empty($arrival_iata) || empty($departure_at) || empty($arrival_at)) {
                            \Log::error('Flight Route: Missing critical segment data', [
                                'segment' => $seg,
                                'departure_iata' => $departure_iata,
                                'arrival_iata' => $arrival_iata,
                                'departure_at' => $departure_at,
                                'arrival_at' => $arrival_at
                            ]);
                            continue; // Skip invalid segment
                        }

                        // Collect arrival times for end_date extraction
                        if (!empty($arrival_at)) {
                            $allArrivalTimes[] = $arrival_at;
                        }

                        // Extract carrier code - try multiple field variations
                        $carrier_code = $seg['carrier_code'] ?? ($seg['carrierCode'] ?? ($seg['carrier']['code'] ?? ($seg['marketingCarrier']['code'] ?? null)));
                        // Extract flight number - try multiple field variations
                        $flight_number = $seg['flight_number'] ?? ($seg['number'] ?? ($seg['flightNumber'] ?? ($seg['carrier']['flightNumber'] ?? null)));
                        // Extract aircraft code (optional field)
                        $aircraft_code = $seg['aircraft_code'] ?? ($seg['aircraft']['code'] ?? ($seg['equipment']['code'] ?? 'N/A'));
                        $duration = $seg['duration'] ?? ($seg['durationIso'] ?? null);
                        // Extract booking class route-wise from original itineraries data
                        $booking_class = $getSegmentClass($seg);

                        // Validate carrier code and flight number (critical fields)
                        if (empty($carrier_code) || empty($flight_number)) {
                            \Log::error('Flight Route: Missing carrier code or flight number', [
                                'carrier_code' => $carrier_code,
                                'flight_number' => $flight_number,
                                'segment' => $seg
                            ]);
                            continue; // Skip invalid segment
                        }

                        // Ensure string values (not arrays) for critical fields
                        if (is_array($carrier_code)) {
                            $carrier_code = $carrier_code['code'] ?? $carrier_code['marketing'] ?? null;
                        }
                        if (is_array($flight_number)) {
                            $flight_number = $flight_number['number'] ?? $flight_number['marketingFlightNumber'] ?? null;
                        }
                        if (is_array($aircraft_code)) {
                            $aircraft_code = $aircraft_code['code'] ?? 'N/A';
                        }
                        if (is_array($duration)) {
                            $duration = null;
                        }

                        // Final validation
                        if (empty($carrier_code) || empty($flight_number)) {
                            \Log::error('Flight Route: Failed to extract carrier or flight number', [
                                'carrier_code' => $carrier_code,
                                'flight_number' => $flight_number
                            ]);
                            continue;
                        }

                        // Store dates exactly as Sabre provides (no validation/fixing)
                        // Sabre dates may be in different timezones, so we preserve them as-is
                        // Only validate format, don't fix dates (timezone differences are normal)

//                        \Log::info('Flight Route: Storing dates as received from Sabre', [
//                            'departure_iata' => $departure_iata,
//                            'arrival_iata' => $arrival_iata,
//                            'departure_at' => $departure_at,
//                            'arrival_at' => $arrival_at,
//                            'carrier_code' => $carrier_code,
//                            'flight_number' => $flight_number
//                        ]);

                        $br = new BookingRoutes();
                        $br->fillByAttr([
                            'booking_id',
                            'departure_iata_code',
                            'departure_at',
                            'arrival_iata_code',
                            'arrival_at',
                            'carrier_code',
                            'aircraft_code',
                            'flight_number',
                            'duration',
                            'class'
                        ], [
                            'booking_id' => $booking->id,
                            'departure_iata_code' => $departure_iata, // Store exactly as Sabre provides
                            'departure_at'        =>   \Carbon\Carbon::parse($departure_at)->format('Y-m-d\TH:i:s'), // Store exactly as Sabre provides (no modification)
                            'arrival_iata_code'   => $arrival_iata, // Store exactly as Sabre provides
                            'arrival_at'          => $arrival_at, // Store exactly as Sabre provides (no modification)
                            'carrier_code'        => $carrier_code, // Store exactly as Sabre provides
                            'aircraft_code'       => $aircraft_code, // Store exactly as Sabre provides
                            'duration'            => $duration, // Store exactly as Sabre provides (no str_replace)
                            'flight_number'       => $flight_number, // Store exactly as Sabre provides
                            'class'               => $booking_class, // Store booking class (Y/N/V/W etc) for PNR creation
                        ]);
                        $br->save();
                    }
                } else {
                    // Legacy structure without segments array - use first segment
                    $seg0 = $r['segments'][0] ?? null;


                    // Extract from legacy structure
                    $departure_iata = $r['departure_iata_code'] ?? ($seg0['departure']['iataCode'] ?? ($seg0['departure']['iata'] ?? null));
                    $arrival_iata   = $r['arrival_iata_code']   ?? ($seg0['arrival']['iataCode']   ?? ($seg0['arrival']['iata'] ?? null));
                    $departure_at   = $r['departure_at']        ?? ($seg0['departure']['at']       ?? ($seg0['departureDateTime'] ?? null));
                    $arrival_at     = $r['arrival_at']          ?? ($seg0['arrival']['at']         ?? ($seg0['arrivalDateTime'] ?? null));

                    // Validate critical fields
                    if (empty($departure_iata) || empty($arrival_iata) || empty($departure_at) || empty($arrival_at)) {
                        \Log::error('Flight Route: Missing critical route data (legacy)', [
                            'route' => $r,
                            'departure_iata' => $departure_iata,
                            'arrival_iata' => $arrival_iata,
                            'departure_at' => $departure_at,
                            'arrival_at' => $arrival_at
                        ]);
                        continue; // Skip invalid route
                    }

                    // Collect arrival times for end_date extraction
                    if (!empty($arrival_at)) {
                        $allArrivalTimes[] = $arrival_at;
                    }

                    // Extract carrier and flight number - try multiple variations
                    $carrier_code = $r['carrier_code'] ?? ($seg0['carrierCode'] ?? ($seg0['carrier']['code'] ?? ($seg0['marketingCarrier']['code'] ?? null)));
                    $flight_number = $r['flight_number'] ?? ($seg0['number'] ?? ($seg0['flightNumber'] ?? ($seg0['carrier']['flightNumber'] ?? null)));
                    $aircraft_code = $r['aircraft_code'] ?? ($seg0['aircraft']['code'] ?? ($seg0['equipment']['code'] ?? 'N/A'));
                    $duration = $r['duration'] ?? ($r['durationIso'] ?? ($seg0['duration'] ?? null));
                    // Extract booking class route-wise from original itineraries data
                    // Use segment data if available, otherwise use route data
                    $segmentForClass = !empty($seg0) ? $seg0 : $r;
                    $booking_class = $getSegmentClass($segmentForClass);

                    // Validate carrier code and flight number
                    if (empty($carrier_code) || empty($flight_number)) {
                        \Log::error('Flight Route: Missing carrier code or flight number (legacy)', [
                            'carrier_code' => $carrier_code,
                            'flight_number' => $flight_number,
                            'route' => $r
                        ]);
                        continue; // Skip invalid route
                    }

                    // Ensure string values (not arrays)
                    if (is_array($carrier_code)) {
                        $carrier_code = $carrier_code['code'] ?? $carrier_code['marketing'] ?? null;
                    }
                    if (is_array($flight_number)) {
                        $flight_number = $flight_number['number'] ?? $flight_number['marketingFlightNumber'] ?? null;
                    }
                    if (is_array($aircraft_code)) {
                        $aircraft_code = $aircraft_code['code'] ?? 'N/A';
                    }
                    if (is_array($duration)) {
                        $duration = null;
                    }

                    // Final validation
                    if (empty($carrier_code) || empty($flight_number)) {
                        \Log::error('Flight Route: Failed to extract carrier or flight number (legacy)', [
                            'carrier_code' => $carrier_code,
                            'flight_number' => $flight_number
                        ]);
                        continue;
                    }

                    // Store dates exactly as Sabre provides (no validation/fixing)
                    // Sabre dates may be in different timezones, so we preserve them as-is
                    // Only validate format, don't fix dates (timezone differences are normal)

                    $br = new BookingRoutes();
                    $br->fillByAttr([
                        'booking_id',
                        'departure_iata_code',
                        'departure_at',
                        'arrival_iata_code',
                        'arrival_at',
                        'carrier_code',
                        'aircraft_code',
                        'flight_number',
                        'duration',
                        'class'
                    ], [
                        'booking_id' => $booking->id,
                        'departure_iata_code' => $departure_iata, // Store exactly as Sabre provides
                        'departure_at'        => $departure_at, // Store exactly as Sabre provides (no modification)
                        'arrival_iata_code'   => $arrival_iata, // Store exactly as Sabre provides
                        'arrival_at'          => $arrival_at, // Store exactly as Sabre provides (no modification)
                        'carrier_code'        => $carrier_code, // Store exactly as Sabre provides
                        'aircraft_code'       => $aircraft_code, // Store exactly as Sabre provides
                        'duration'            => $duration, // Store exactly as Sabre provides (no str_replace)
                        'flight_number'       => $flight_number, // Store exactly as Sabre provides
                        'class'               => $booking_class, // Store booking class (Y/N/V/W etc) for PNR creation
                    ]);
                    $br->save();
                }
            }

            // Update end_date with the last collected arrival time if we found one
            // This ensures we use the exact same extraction logic as when saving routes
            if (!empty($allArrivalTimes)) {
                $lastArrivalTime = end($allArrivalTimes);
                if (!empty($lastArrivalTime)) {
                    try {
                        // Parse the arrival time using the same logic
                        $dateString = trim($lastArrivalTime);
                        if (preg_match('/^(\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}:\d{2})/', $dateString, $matches)) {
                            $dateString = str_replace('T', ' ', $matches[1]);
                        }
                        $parsedEnd = \Carbon\Carbon::parse($dateString);
                        $newEndDate = $parsedEnd->format('Y-m-d H:i:s');

                        // Only update if the new end_date is valid and after start_date
                        $currentStart = \Carbon\Carbon::parse($booking->start_date);
                        $newEnd = \Carbon\Carbon::parse($newEndDate);
                        if ($newEnd->gt($currentStart)) {
                            $booking->end_date = $newEndDate;
                            // Save the booking with updated end_date
                            $booking->save();
                        }
                    } catch (\Exception $e) {
                        // If parsing fails, keep the existing end_date
                    }
                }
            }
        }

        return $this->sendSuccess([
            'url'          => $booking->getCheckoutUrl(),
            'booking_code' => $booking->code,
        ]);
    }

    /**
     * Validate and fix route dates to ensure arrival is after departure
     * Preserves original Sabre date format unless dates need to be fixed
     *
     * @param string|null $departure_at
     * @param string|null $arrival_at
     * @return array ['departure' => string, 'arrival' => string]
     */
    private function validateRouteDates($departure_at, $arrival_at)
    {
        if (empty($departure_at) || empty($arrival_at)) {
            return [
                'departure' => $departure_at ?: '',
                'arrival' => $arrival_at ?: ''
            ];
        }

        // Store original format to preserve Sabre's format
        $originalDepFormat = $departure_at;
        $originalArrFormat = $arrival_at;

        try {
            $dep = \Carbon\Carbon::parse($departure_at);
            $arr = \Carbon\Carbon::parse($arrival_at);

            // If arrival is before or equal to departure, fix it
            if ($arr->lessThanOrEqualTo($dep)) {
                // If dates are same but times are backwards, assume arrival is next day
                if ($arr->isSameDay($dep)) {
                    $arr = $arr->copy()->addDay();
                    // Format fixed date to match original format if possible
                    $fixedArrival = $this->formatDateToMatchOriginal($arr, $originalArrFormat);
                    \Log::warning('Flight Route: Fixed backwards arrival time (same day)', [
                        'original_departure' => $departure_at,
                        'original_arrival' => $arrival_at,
                        'fixed_arrival' => $fixedArrival
                    ]);
                    return [
                        'departure' => $originalDepFormat, // Keep original format
                        'arrival' => $fixedArrival
                    ];
                } else {
                    // If arrival date is before departure date, swap them
                    \Log::error('Flight Route: Arrival date before departure date - swapping', [
                        'original_departure' => $departure_at,
                        'original_arrival' => $arrival_at
                    ]);
                    // Swap dates - format to match originals
                    $swappedDeparture = $this->formatDateToMatchOriginal($arr, $originalDepFormat);
                    $swappedArrival = $this->formatDateToMatchOriginal($dep, $originalArrFormat);
                    return [
                        'departure' => $swappedDeparture,
                        'arrival' => $swappedArrival
                    ];
                }
            }

            // Dates are valid - return original format unchanged
            return [
                'departure' => $originalDepFormat,
                'arrival' => $originalArrFormat
            ];
        } catch (\Exception $e) {
            \Log::error('Flight Route: Failed to parse dates', [
                'departure_at' => $departure_at,
                'arrival_at' => $arrival_at,
                'error' => $e->getMessage()
            ]);
            return [
                'departure' => $departure_at ?: '',
                'arrival' => $arrival_at ?: ''
            ];
        }
    }

    /**
     * Format a Carbon date to match the original format from Sabre
     *
     * @param \Carbon\Carbon $date
     * @param string $originalFormat
     * @return string
     */
    private function formatDateToMatchOriginal($date, $originalFormat)
    {
        // Detect original format
        if (strpos($originalFormat, 'T') !== false) {
            // ISO 8601 format with T separator (e.g., "2025-11-26T10:45:00" or "2025-11-26T10:45:00+00:00")
            if (preg_match('/[+-]\d{2}:\d{2}$/', $originalFormat)) {
                // Has timezone - preserve timezone if possible
                return $date->toIso8601String();
            } else {
                // No timezone - use T separator
                return $date->format('Y-m-d\TH:i:s');
            }
        } else {
            // Space-separated format (e.g., "2025-11-26 10:45:00")
            return $date->format('Y-m-d H:i:s');
        }
    }

    public function getPriceInRanges($start_date, $end_date)
    {
        $totalPrice = 0;
        $price = ($this->sale_price and $this->sale_price > 0 and $this->sale_price < $this->price) ? $this->sale_price : $this->price;

        $datesRaw = $this->FlightDateClass::getDatesInRanges($start_date, $end_date, $this->id);
        $dates = [];
        if (!empty($datesRaw)) {
            foreach ($datesRaw as $date) {
                $dates[date('Y-m-d', strtotime($date['start_date']))] = $date;
            }
        }

        if (strtotime($start_date) == strtotime($end_date)) {
            if (empty($dates[date('Y-m-d', strtotime($start_date))])) {
                $totalPrice += $price;
            } else {
                $totalPrice += $dates[date('Y-m-d', strtotime($start_date))]->price;
            }
            return $totalPrice;
        }
        if ($this->getBookingType() == 'by_day') {
            $period = periodDate($start_date, $end_date);
        }
        if ($this->getBookingType() == 'by_night') {
            $period = periodDate($start_date, $end_date, false);
        }
        foreach ($period as $dt) {
            $date = $dt->format('Y-m-d');
            if (empty($dates[$date])) {
                $totalPrice += $price;
            } else {
                $totalPrice += $dates[$date]->price;
            }
        }
        $this->tmp_dates = $dates;
        return $totalPrice;
    }

    public function addToCartValidate(Request $request)
    {
        $rules = [
            //'flight_seat.*.number' => 'required',
        ];
        $messages = [
            'flight_seat.*.number.required' => "Seat type number must be required"
        ];
        // Validation
        if (!empty($rules)) {
            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                return $this->sendError('', ['errors' => $validator->errors()]);
            }
        }
        return true;
    }


    public function getBookingData()
    {
        if (!empty($start = request()->input('start'))) {
            $start_html = display_date($start);
            $end_html = request()->input('end') ? display_date(request()->input('end')) : "";
            $date_html = $start_html . '<i class="fa fa-long-arrow-right" style="font-size: inherit"></i>' . $end_html;
        }
        $booking_data = [
            'id'                       => $this->id,
            'person_types'             => [],
            'max'                      => 0,
            'open_hours'               => [],
            'extra_price'              => [],
            'minDate'                  => date('m/d/Y'),
            'max_guests'               => $this->max_guests ?? 1,
            'buyer_fees'               => [],
            'start_date'               => request()->input('start') ?? "",
            'start_date_html'          => $date_html ?? __('Please select'),
            'end_date'                 => request()->input('end') ?? "",
            'deposit'                  => $this->isDepositEnable(),
            'deposit_type'             => $this->getDepositType(),
            'deposit_amount'           => $this->getDepositAmount(),
            'deposit_fomular'          => $this->getDepositFomular(),
            'is_form_enquiry_and_book' => $this->isFormEnquiryAndBook(),
            'enquiry_type'             => $this->getBookingEnquiryType(),
            'booking_type'             => $this->getBookingType(),
        ];
        if (!empty($adults = request()->input('adults'))) {
            $booking_data['adults'] = $adults;
        }
        if (!empty($children = request()->input('children'))) {
            $booking_data['children'] = $children;
        }
        $lang = app()->getLocale();
        if ($this->enable_extra_price) {
            $booking_data['extra_price'] = $this->extra_price;
            if (!empty($booking_data['extra_price'])) {
                foreach ($booking_data['extra_price'] as $k => &$type) {
                    if (!empty($lang) and !empty($type['name_' . $lang])) {
                        $type['name'] = $type['name_' . $lang];
                    }
                    $type['number'] = 0;
                    $type['enable'] = 0;
                    $type['price_html'] = format_money($type['price']);
                    $type['price_type'] = '';
                    switch ($type['type']) {
                        case "per_day":
                            $type['price_type'] .= '/' . __('day');
                            break;
                        case "per_hour":
                            $type['price_type'] .= '/' . __('hour');
                            break;
                    }
                    if (!empty($type['per_person'])) {
                        $type['price_type'] .= '/' . __('guest');
                    }
                }
            }

            $booking_data['extra_price'] = array_values((array) $booking_data['extra_price']);
        }

        $list_fees = setting_item_array('flight_booking_buyer_fees');
        if (!empty($list_fees)) {
            foreach ($list_fees as $item) {
                $item['type_name'] = $item['name_' . app()->getLocale()] ?? $item['name'] ?? '';
                $item['type_desc'] = $item['desc_' . app()->getLocale()] ?? $item['desc'] ?? '';
                $item['price_type'] = '';
                if (!empty($item['per_person']) and $item['per_person'] == 'on') {
                    $item['price_type'] .= '/' . __('guest');
                }
                $booking_data['buyer_fees'][] = $item;
            }
        }
        if (!empty($this->enable_service_fee) and !empty($service_fee = $this->service_fee)) {
            foreach ($service_fee as $item) {
                $item['type_name'] = $item['name_' . app()->getLocale()] ?? $item['name'] ?? '';
                $item['type_desc'] = $item['desc_' . app()->getLocale()] ?? $item['desc'] ?? '';
                $item['price_type'] = '';
                if (!empty($item['per_person']) and $item['per_person'] == 'on') {
                    $item['price_type'] .= '/' . __('guest');
                }
                $booking_data['buyer_fees'][] = $item;
            }
        }
        return $booking_data;
    }

    public static function searchForMenu($q = false)
    {
        $query = static::select('id', 'title as name');
        if (strlen($q)) {

            $query->where('title', 'like', "%" . $q . "%");
        }
        $a = $query->orderBy('id', 'desc')->limit(10)->get();
        return $a;
    }

    public static function getMinMaxPrice()
    {
        $min = FlightSeat::select(['price', 'flight_id'])->whereHas('flight', function (Builder $query) {
            $query->where('status', 'publish');
        })->min('price');
        $max = FlightSeat::select(['price', 'flight_id'])->whereHas('flight', function (Builder $query) {
            $query->where('status', 'publish');
        })->max('price');
        return [
            $min ?? 0,
            $max ?? 0
        ];
    }

    public function getReviewEnable()
    {
        return setting_item("flight_enable_review", 0);
    }

    public function getReviewApproved()
    {
        return setting_item("flight_review_approved", 0);
    }

    public function review_after_booking()
    {
        return setting_item("flight_enable_review_after_booking", 0);
    }

    public function count_remain_review()
    {
        $status_making_completed_booking = [];
        $options = setting_item("flight_allow_review_after_making_completed_booking", false);
        if (!empty($options)) {
            $status_making_completed_booking = json_decode($options);
        }
        $number_review = $this->reviewClass::countReviewByServiceID($this->id, Auth::id(), false, $this->type) ?? 0;
        $number_booking = $this->bookingClass::countBookingByServiceID($this->id, Auth::id(), $status_making_completed_booking) ?? 0;
        $number = $number_booking - $number_review;
        if ($number < 0) $number = 0;
        return $number;
    }

    public static function getReviewStats()
    {
        $reviewStats = [];
        if (!empty($list = setting_item("flight_review_stats", []))) {
            $list = json_decode($list, true);
            foreach ($list as $item) {
                $reviewStats[] = $item['title'];
            }
        }
        return $reviewStats;
    }

    public function getReviewDataAttribute()
    {
        $list_score = [
            'score_total'  => 0,
            'score_text'   => __("Not rated"),
            'total_review' => 0,
            'rate_score'   => [],
        ];
        $dataTotalReview = $this->reviewClass::selectRaw(" AVG(rate_number) as score_total , COUNT(id) as total_review ")->where('object_id', $this->id)->where('object_model', $this->type)->where("status", "approved")->first();
        if (!empty($dataTotalReview->score_total)) {
            $list_score['score_total'] = number_format($dataTotalReview->score_total, 1);
            $list_score['score_text'] = Review::getDisplayTextScoreByLever(round($list_score['score_total']));
        }
        if (!empty($dataTotalReview->total_review)) {
            $list_score['total_review'] = $dataTotalReview->total_review;
        }
        $list_data_rate = $this->reviewClass::selectRaw('COUNT( CASE WHEN rate_number = 5 THEN rate_number ELSE NULL END ) AS rate_5,
                                                            COUNT( CASE WHEN rate_number = 4 THEN rate_number ELSE NULL END ) AS rate_4,
                                                            COUNT( CASE WHEN rate_number = 3 THEN rate_number ELSE NULL END ) AS rate_3,
                                                            COUNT( CASE WHEN rate_number = 2 THEN rate_number ELSE NULL END ) AS rate_2,
                                                            COUNT( CASE WHEN rate_number = 1 THEN rate_number ELSE NULL END ) AS rate_1 ')->where('object_id', $this->id)->where('object_model', $this->type)->where("status", "approved")->first()->toArray();
        for ($rate = 5; $rate >= 1; $rate--) {
            if (!empty($number = $list_data_rate['rate_' . $rate])) {
                $percent = ($number / $list_score['total_review']) * 100;
            } else {
                $percent = 0;
            }
            $list_score['rate_score'][$rate] = [
                'title'   => $this->reviewClass::getDisplayTextScoreByLever($rate),
                'total'   => $number,
                'percent' => round($percent),
            ];
        }
        return $list_score;
    }

    /**
     * Get Score Review
     *
     * Using for loop Flight
     */
    public function getScoreReview()
    {
        $flight_id = $this->id;
        $list_score = Cache::rememberForever('review_' . $this->type . '_' . $flight_id, function () use ($flight_id) {
            $dataReview = $this->reviewClass::selectRaw(" AVG(rate_number) as score_total , COUNT(id) as total_review ")->where('object_id', $flight_id)->where('object_model', "Flight")->where("status", "approved")->first();
            $score_total = !empty($dataReview->score_total) ? number_format($dataReview->score_total, 1) : 0;
            return [
                'score_total'  => $score_total,
                'total_review' => !empty($dataReview->total_review) ? $dataReview->total_review : 0,
            ];
        });
        $list_score['review_text'] = $list_score['score_total'] ? Review::getDisplayTextScoreByLever(round($list_score['score_total'])) : __("Not rated");
        return $list_score;
    }

    public function getNumberReviewsInService($status = false)
    {
        return $this->reviewClass::countReviewByServiceID($this->id, false, $status, $this->type) ?? 0;
    }

    public function getReviewList()
    {
        return $this->reviewClass::select(['id', 'title', 'content', 'rate_number', 'author_ip', 'status', 'created_at', 'vendor_id', 'author_id'])->where('object_id', $this->id)->where('object_model', 'Flight')->where("status", "approved")->orderBy("id", "desc")->with('author')->paginate(setting_item('flight_review_number_per_page', 5));
    }

    public function getNumberServiceInLocation($location)
    {
        $number = 0;
        if (!empty($location)) {
            $number = parent::join('bravo_locations', function ($join) use ($location) {
                $join->on('bravo_locations.id', '=', $this->table . '.location_id')->where('bravo_locations._lft', '>=', $location->_lft)->where('bravo_locations._rgt', '<=', $location->_rgt);
            })->where($this->table . ".status", "publish")->with(['translation'])->count($this->table . ".id");
        }
        if (empty($number)) {
            return false;
        }
        if ($number > 1) {
            return __(":number Flights", ['number' => $number]);
        }
        return __(":number Flight", ['number' => $number]);
    }

    /**
     * @param $from
     * @param $to
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getBookingsInRange($from, $to)
    {

        $query = $this->bookingClass::query();
        $query->whereNotIn('status', ['draft']);
        $query->where('start_date', '<=', $to)->where('end_date', '>=', $from)->take(50);

        $query->where('object_id', $this->id);
        $query->where('object_model', $this->type);

        return $query->orderBy('id', 'asc')->get();
    }


    public function hasWishList()
    {
        return $this->hasOne($this->userWishListClass, 'object_id', 'id')->where('object_model', $this->type)->where('user_id', Auth::id() ?? 0);
    }

    public function isWishList()
    {
        if (Auth::check()) {
            if (!empty($this->hasWishList) and !empty($this->hasWishList->id)) {
                return 'active';
            }
        }
        return '';
    }

    public static function getServiceIconFeatured()
    {
        return "icofont-ui-flight";
    }


    public static function isEnable()
    {
        return setting_item('flight_disable') == false;
    }

    public function isDepositEnable()
    {
        return (setting_item('flight_deposit_enable') and setting_item('flight_deposit_amount'));
    }

    public function getDepositAmount()
    {
        return setting_item('flight_deposit_amount');
    }

    public function getDepositType()
    {
        return setting_item('flight_deposit_type');
    }

    public function getDepositFomular()
    {
        return setting_item('flight_deposit_fomular', 'default');
    }

    public function detailBookingEachDate($booking)
    {
        $startDate = $booking->start_date;
        $endDate = $booking->end_date;
        $rowDates = json_decode($booking->getMeta('tmp_dates'));
        $allDates = [];
        $service = $booking->service;

        if ($this->getBookingType() == 'by_day') {
            $period = periodDate($startDate, $endDate);
        }
        if ($this->getBookingType() == 'by_night') {
            $period = periodDate($startDate, $endDate, false);
        }

        foreach ($period as $dt) {
            $price = (!empty($service->sale_price) and $service->sale_price > 0 and $service->sale_price < $service->price) ? $service->sale_price : $service->price;

            $startDate = clone $dt;

            $endDate = $dt->modify('+1 day');

            $date['price'] = $price;
            $date['price_html'] = format_money($price);

            $date['from'] = $startDate->getTimestamp();
            $date['from_html'] = $startDate->format('d/m/Y');

            $date['to'] = $endDate->getTimestamp();
            $date['to_html'] = $endDate->format('d/m/Y');

            $allDates[$startDate->format('d/m/Y')] = $date;
        }

        if (!empty($rowDates)) {
            foreach ($rowDates as $item => $row) {
                $startDate = strtotime($item);
                $endDate = strtotime($item . " +1 day");
                $price = $row->price;
                $date['price'] = $price;
                $date['price_html'] = format_money($price);
                $date['from'] = $startDate;
                $date['from_html'] = date('d/m/Y', $startDate);
                $date['to'] = $endDate;
                $date['to_html'] = date('d/m/Y', ($endDate));
                $allDates[date('Y-m-d', $startDate)] = $date;
            }
        }
        return $allDates;
    }

    public static function isEnableEnquiry()
    {
        if (!empty(setting_item('booking_enquiry_for_Flight'))) {
            return true;
        }
        return false;
    }

    public static function isFormEnquiryAndBook()
    {
        $check = setting_item('booking_enquiry_for_Flight');
        if (!empty($check) and setting_item('booking_enquiry_type') == "booking_and_enquiry") {
            return true;
        }
        return false;
    }

    public static function getBookingEnquiryType()
    {
        $check = setting_item('booking_enquiry_for_Flight');
        if (!empty($check)) {
            if (setting_item('booking_enquiry_type') == "only_enquiry") {
                return "enquiry";
            }
        }
        return "book";
    }

    public function search($request)
    {
        //return $request;

        //echo '<pre>'; print_r($request); die();
        $request['start'] = isset($request['segments'][0]['departure']) ? $request['segments'][0]['departure'] : date('Y-m-d');


        $start = strtotime($request['start']) < time() ? time() : strtotime($request['start']);

        if (isset($request['price_range'])) {
            $price_max = explode(';', $request['price_range'])[1];
        } else {
            $price_max = 5000000;
        }
        if (isset($request['trip_type']) && $request['trip_type'] == 'round') {
            $request['end'] = $request['return_date'] ?? date('Y-m-d');
            $end = strtotime($request['end']) < time() ? time() : strtotime($request['end']);
            $return_query = '&returnDate=' . date('Y-m-d', $end);
        } else {
            $return_query = '';
        }

        // Set defaults for missing parameters
        $fromWhere = $request['from_where'] ?? '';
        $toWhere = $request['to_where'] ?? '';
        $adults = $request['adults'] ?? 1;
        $children = $request['children'] ?? 0;
        $infants = $request['infants'] ?? 0;
        $travelClass = $request['travel_class'] ?? 'ECONOMY';

        $amadeus_flight_search_api = 'https://test.api.amadeus.com/v2/shopping/flight-offers?originLocationCode=' . $fromWhere . '&destinationLocationCode=' . $toWhere . '&departureDate=' . date('Y-m-d', $start) . $return_query . '&adults=' . $adults . '&children=' . $children . '&infants=' . $infants . '&travelClass=' . $travelClass . '&nonStop=false&currencyCode=BDT&maxPrice=' . $price_max . '&max=250';
        //print_r($amadeus_flight_search_api); die();

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://test.api.amadeus.com/v1/security/oauth2/token',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'grant_type=client_credentials&client_id=reVtcYKdIprBcEOwESnvAy9eNyuntOYK&client_secret=nbg41aTDnt8A1ALg',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded'
            ),
        ));
        $amadeus_authentication_response = curl_exec($curl);
        $amadeus_access_token = json_decode($amadeus_authentication_response)->access_token;
        //die($amadeus_access_token);
        curl_close($curl);

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://test.api.amadeus.com/v2/shopping/flight-offers?originLocationCode=' . $fromWhere . '&destinationLocationCode=' . $toWhere . '&adults=' . $adults . '&children=' . $children . '&infants=' . $infants . '&max=250&departureDate=' . date('Y-m-d', $start) . $return_query . '&travelClass=' . $travelClass . '&currencyCode=BDT',
            //CURLOPT_URL => $amadeus_flight_search_api,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $amadeus_access_token
            ),
        ));

        $amadeus_flight_search_response = curl_exec($curl);
        $amadeus_flight_search_result =  json_decode($amadeus_flight_search_response);
        //echo '<pre>'; print_r($amadeus_flight_search_result); die('modules\Flight\Models\Flight.php');

        curl_close($curl);

        return $amadeus_flight_search_result;


        $seat_class = $request['seat_class'];
        $orderBy = $request["orderby"] ?? "";
        $query = self::query()->select(['bravo_flight.*'])->where('status', 'publish');

        //            if (!empty($request['start']) and !empty($request['end'])) {
        //                $start = strtotime($request['start']) < time() ? time() : strtotime($request['start']);
        //                $end = strtotime($request['end']) < time() ? time() : strtotime($request['end']);
        //                $query->where('departure_time', '>=', date('Y-m-d H:i:s ', $start));
        //                $query->Where('departure_time', '<=', date('Y-m-d H:i:s ', $end));
        //            }

        if (!empty($price_range = $request['price_range'] ?? "")) {
            $pri_from = Currency::convertPriceToMain(explode(";", $price_range)[0]);
            $pri_to =  Currency::convertPriceToMain(explode(";", $price_range)[1]);
            $query->where('min_price', '<=', $pri_to)->where('min_price', '>=', $pri_from);
        } else {
            $query->whereHas('flightSeat');
        }
        if (!empty($request['from_where'])) {
            // $query->whereHas('airportFrom', function (Builder $builder) use ($request) {
            //     $builder->where('location_id', $request['from_where']);
            // });
            $query->where('airport_from', $request['from_where']);
        }
        if (!empty($request['to_where'])) {
            // $query->whereHas('airportTo', function (Builder $builder) use ($request) {
            //     $builder->where('location_id', $request['to_where']);
            // });
            $query->where('airport_to', $request['to_where']);
        }
        if (!empty($request['start'])) {
            $start_date = explode('/', $request['start']);
            $start_date_formated = date('Y-m-d', strtotime($start_date[2] . '-' . $start_date[0] . '-' . $start_date[1]));
            $query->whereDate('departure_time', $start_date_formated);
        }

        if (!empty($request['seat_type'])) {
            $argv = array_filter($request['seat_type'], function ($v) {
                return $v != 0;
            });
            if (!empty($argv)) {
                $query->whereHas('flightSeat', function (Builder $builder) use ($argv, $seat_class) {
                    foreach ($argv as $item => $value) {
                        $builder->orWhere(function (Builder $query) use ($value, $item, $seat_class) {
                            $query->where('seat_type', $seat_class)->where('person', $item)->where('max_passengers', '>=', $value);
                        });
                    }
                });
            }
        }

        if (!empty($request['attrs'])) {
            $this->filterAttrs($query, $request['attrs'], 'bravo_flight_term');
        }

        if (!empty($request['is_featured'])) {
            $query->where($this->table . '.is_featured', 1);
        }
        if (!empty($request['custom_ids'])) {
            $query->whereIn($this->table . ".id", $request['custom_ids']);
        }

        if (!empty($request['custom_ids']) and !empty($ids = array_filter($request['custom_ids']))) {
            $query->whereIn($this->table . ".id", $ids);
            $query->orderByRaw('FIELD (id, ' . implode(', ', $ids) . ') ASC');
        }

        switch ($orderBy) {
            case "price_low_high":
                $query->orderBy($this->table . ".min_price", "asc");
                break;
            case "price_high_low":
                $query->orderBy($this->table . ".min_price", "desc");
                break;
            default:
                $query->orderBy($this->table . ".id", "desc");
        }

        return $query->with(['flightSeat', 'airportFrom', 'airportTo', 'airline']);
    }


    public static function searchCustom(Request $request)
    {
        $model_Flight = parent::query()->select("bravo_flight.*");
        $model_Flight->where("bravo_flight.status", "publish");
        if (!empty($location_id = $request->query('location_id'))) {
            $location = Location::query()->where('id', $location_id)->where("status", "publish")->first();
            if (!empty($location)) {
                $model_Flight->join('bravo_locations', function ($join) use ($location) {
                    $join->on('bravo_locations.id', '=', 'bravo_flight.location_id')
                        ->where('bravo_locations._lft', '>=', $location->_lft)
                        ->where('bravo_locations._rgt', '<=', $location->_rgt);
                });
            }
        }
        if (!empty($price_range = $request->query('price_range'))) {
            $pri_from = explode(";", $price_range)[0];
            $pri_to = explode(";", $price_range)[1];
            $raw_sql_min_max = "( (IFNULL(bravo_flight.sale_price,0) > 0 and bravo_flight.sale_price >= ? ) OR (IFNULL(bravo_flight.sale_price,0) <= 0 and bravo_flight.price >= ? ) )
                            AND ( (IFNULL(bravo_flight.sale_price,0) > 0 and bravo_flight.sale_price <= ? ) OR (IFNULL(bravo_flight.sale_price,0) <= 0 and bravo_flight.price <= ? ) )";
            $model_Flight->WhereRaw($raw_sql_min_max, [$pri_from, $pri_from, $pri_to, $pri_to]);
        }

        $terms = $request->query('terms', []);
        if ($term_id = $request->query('term_id')) {
            $terms[] = $term_id;
        }

        if (is_array($terms) && !empty($terms)) {
            $terms = Arr::where($terms, function ($value, $key) {
                return !is_null($value);
            });
            if (!empty($terms)) {
                $model_Flight->join('bravo_flight_term as tt', 'tt.target_id', "bravo_flight.id")->whereIn('tt.term_id', $terms);
            }
        }

        $review_scores = $request->query('review_score');
        if (is_array($review_scores) && !empty($review_scores)) {
            $where_review_score = [];
            $params = [];
            foreach ($review_scores as $number) {
                $where_review_score[] = " ( bravo_flight.review_score >= ? AND bravo_flight.review_score <= ? ) ";
                $params[] = $number;
                $params[] = $number . '.9';
            }
            $sql_where_review_score = " ( " . implode("OR", $where_review_score) . " )  ";
            $model_Flight->WhereRaw($sql_where_review_score, $params);
        }
        if (!empty($lat = $request->query('map_lat')) and !empty($lgn = $request->query('map_lgn'))) {
            $model_Flight->orderByRaw("POW((bravo_flight.map_lng-?),2) + POW((bravo_flight.map_lat-?),2)", [$lgn, $lat]);
        }
        $orderby = $request->input("orderby");
        switch ($orderby) {
            case "price_low_high":
                $raw_sql = "CASE WHEN IFNULL( bravo_flight.sale_price, 0 ) > 0 THEN bravo_flight.sale_price ELSE bravo_flight.price END AS tmp_min_price";
                $model_Flight->selectRaw($raw_sql);
                $model_Flight->orderBy("tmp_min_price", "asc");
                break;
            case "price_high_low":
                $raw_sql = "CASE WHEN IFNULL( bravo_flight.sale_price, 0 ) > 0 THEN bravo_flight.sale_price ELSE bravo_flight.price END AS tmp_min_price";
                $model_Flight->selectRaw($raw_sql);
                $model_Flight->orderBy("tmp_min_price", "desc");
                break;
            case "rate_high_low":
                $model_Flight->orderBy("review_score", "desc");
                break;
            default:
                $model_Flight->orderBy("id", "desc");
        }

        $model_Flight->groupBy("bravo_flight.id");

        $max_guests = (int) ($request->query('adults') + $request->query('children'));
        if ($max_guests) {
            $model_Flight->where('max_guests', '>=', $max_guests);
        }

        if (!empty($request->query('limit'))) {
            $limit = $request->query('limit');
        } else {
            $limit = !empty(setting_item("flight_page_limit_item")) ? setting_item("flight_page_limit_item") : 9;
        }
        return $model_Flight->with('flightSeat', 'airportFrom', 'airportTo', 'airline')->paginate($limit);
    }

    public function dataForApi($forSingle = false)
    {
        $airline = $this->airline;
        $airport_from = $this->airportFrom;
        $airport_to = $this->airportTo;
        $airline = $this->airline;
        $data = [
            'id'               => $this->id,
            'code'               => $this->code,
            'title'            => $this->title,
            'price'            => $this->price ?? $this->min_price,
            'sale_price'       => $this->sale_price,
            'discount_percent' => $this->discount_percent ?? null,
            'image'            => get_file_url($airline->image_id),
            'content'          => $this->content,
            'location'         => Location::selectRaw("id,name")->find($this->location_id) ?? null,
            'is_featured'      => $this->is_featured ?? null,
            'airport_form' => $airport_from->only('id', 'name') ?? null,
            'airport_to' => $airport_to->only('id', 'name') ?? null,
            'airline' => $airline->only('id', 'name') ?? null,
            'departure_time' => $this->departure_time,
            'arrival_time' => $this->arrival_time,
            'duration' => $this->duration,
            'terms' => Terms::getTermsByIdForAPI($this->terms->pluck('term_id'))
        ];
        return $data;
    }

    static public function getClassAvailability()
    {
        return "";
    }

    static public function getFiltersSearch()
    {
        $min_max_price = self::getMinMaxPrice();
        return [
            [
                "title"     => __("Filter Price"),
                "field"     => "price_range",
                "position"  => "1",
                "min_price" => floor(Currency::convertPrice($min_max_price[0])),
                "max_price" => ceil(Currency::convertPrice($min_max_price[1])),
            ],
            [
                "title"    => __("Attributes"),
                "field"    => "terms",
                "position" => "3",
                "data"     => Attributes::getAllAttributesForApi("flight")
            ]
        ];
    }

    static public function getFormSearch()
    {
        $search_fields = setting_item_array('flight_search_fields');
        $search_fields = array_values(\Illuminate\Support\Arr::sort($search_fields, function ($value) {
            return $value['position'] ?? 0;
        }));
        foreach ($search_fields as &$item) {
            if ($item['field'] == 'seat_type') {
                $item['seat_types'] = SeatType::selectRaw('id,name,code')->get()->toArray();
            }
            if ($item['field'] == 'to_where') {
                $item['rows'] = Location::selectRaw('id,name')->get()->toArray();
            }
        }
        return $search_fields;
    }

    public static function getBookingType()
    {
        return setting_item('flight_booking_type', 'by_day');
    }


    //    new module flight
    public function airportFrom()
    {
        return $this->hasOne(Airport::class, 'id', 'airport_from')->withDefault();
    }

    public function airportTo()
    {
        return $this->hasOne(Airport::class, 'id', 'airport_to')->withDefault();
    }

    public function airline()
    {
        return $this->hasOne(Airline::class, 'id', 'airline_id')->withDefault();
    }

    public function flightSeat()
    {
        return $this->hasMany(FlightSeat::class, 'flight_id')->orderBy('price');
    }

    public function bookingPassengers()
    {
        return $this->hasMany(BookingPassengers::class, 'flight_id')->whereHas('booking', function (Builder $query) {
            $query->whereNotIn('status', Booking::$notAcceptedStatus);
        });
    }

    public function booking()
    {
        return $this->hasMany(Booking::class, 'flight_id');
    }
    public function getDurationAttribute()
    {

        if (!empty($this->arrival_time) and !empty($this->departure_time)) {
            $interval = $this->arrival_time->diff($this->departure_time);
            return $interval->format('%h');
        } else {
            return 0;
        }
    }
    public function getCanBookAttribute()
    {
        $canBook = [];
        $bookingPassengers = $this->bookingPassengers->countBy('seat_type')->toArray();
        foreach ($this->flightSeat as &$value) {
            if (!empty($bookingPassengers[$value->seat_type])) {
                $canBook[$value->seat_type] = $value->max_passengers - $bookingPassengers[$value->seat_type];
            } else {
                $canBook[$value->seat_type] = $value->max_passengers;
            }
        }
        if (array_sum($canBook) > 0) {
            return true;
        } else {
            return false;
        }
        return true;
    }
}
