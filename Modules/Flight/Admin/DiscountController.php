<?php

namespace Modules\Flight\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Modules\AdminController;
use Modules\Flight\Admin\Traits\BulkActionForms;  // ← IMPORT TRAIT
use Modules\Flight\Models\Flight;
use Modules\Flight\Models\Airline;
use Modules\Flight\Models\Airport;
use Modules\Flight\Models\FlightApi;
use Modules\Flight\Models\FlightDiscount;

class DiscountController extends AdminController
{
    use BulkActionForms;  // ← USE TRAIT

    public function __construct()
    {
        $this->setActiveMenu(route('flight.admin.index'));
    }

    public function callAction($method, $parameters)
    {
        if (!Flight::isEnable()) {
            return redirect('/');
        }
        return parent::callAction($method, $parameters);
    }

    /**
     * Display all discounts - DataTable version
     */
    public function index(Request $request)
    {
        $this->checkPermission('commission_view');

        // If AJAX request for DataTable
        if ($request->ajax()) {
            return $this->getDataTableData($request);
        }

        // Load GDS options for filters
        $gdsOptions = FlightApi::pluck('name', 'provider')->toArray();

        return view('Flight::admin.discount.index', compact('gdsOptions'));
    }

    /**
     * Get DataTable data (AJAX endpoint)
     */
    private function getDataTableData(Request $request)
    {
        $query = FlightDiscount::query();

        // Search across multiple columns
        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('code', 'LIKE', "%{$search}%")
                    ->orWhere('airline_code', 'LIKE', "%{$search}%")
                    ->orWhere('gds_type', 'LIKE', "%{$search}%");
            });
        }

        // Get total count before pagination
        $totalRecords = $query->count();

        // Sorting
        $orderColumn = $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc');

        // Map column numbers to database columns
        $columnMap = [
            0 => 'id',                  // checkbox
            1 => 'name',
            2 => 'airline_code',
            3 => 'gds_type',
            4 => 'departure_code',
            5 => 'arrival_code',
            6 => 'type',
            7 => 'value',
            8 => 'user_value',
            9 => 'b2b_user_value',
            10 => 'ait_charge',
            11 => 'service_charge',
            12 => 'segment_discount',
            13 => 'user_seg_discount',
            14 => 'valid_from',
            15 => 'status',
        ];

        if (isset($columnMap[$orderColumn])) {
            $query->orderBy($columnMap[$orderColumn], $orderDir);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Pagination
        $length = (int)$request->input('length', 10);
        $start = (int)$request->input('start', 0);
        $page = ($start / $length) + 1;

        $rows = $query->paginate($length, ['*'], 'page', $page);

        // Format data for DataTable
        $data = $rows->map(function ($item, $index) use ($start) {
            return [
                'id' => $item->id,
                'checkbox' => '<input type="checkbox" class="check-item" name="ids[]" value="' . $item->id . '">',
                'name' => '<strong>' . e($item->name) . '</strong>',
                'airline_code' => '<span class="badge badge-primary">' . e($item->airline_code) . '</span>',
                'gds_type' => $item->gds_type ? '<span class="badge badge-info">' . e($item->gds_type) . '</span>' : '-',
                'departure_code' => $item->departure_code ? '<span class="badge badge-success">' . e($item->departure_code) . '</span>' : '-',
                'arrival_code' => $item->arrival_code ? '<span class="badge badge-warning">' . e($item->arrival_code) . '</span>' : '-',
                'type' => ucfirst($item->type),
                'value' => $this->formatAmount($item->value, $item->type),
                'user_value' => $this->formatAmount($item->user_value, $item->type),
                'b2b_user_value' => $this->formatAmount($item->b2b_user_value, $item->type),
                'ait_charge' => $item->ait_charge ? number_format($item->ait_charge, 2) . '%' : '-',
                'service_charge' => $item->service_charge ? '৳' . number_format($item->service_charge, 0) : '-',
                'segment_discount' => $item->segment_discount ? '৳' . number_format($item->segment_discount, 0) : '-',
                'user_seg_discount' => $item->user_seg_discount ? '৳' . number_format($item->user_seg_discount, 0) : '-',
                'valid_period' => $this->formatValidPeriod($item->valid_from, $item->valid_to),
                'status' => $this->getStatusBadge($item->status),
                'action' => $this->getActionButtons($item),
            ];
        });

        return response()->json([
            'draw' => (int)$request->input('draw', 1),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $rows->total(),
            'data' => $data,
        ]);
    }

    /**
     * Format amount with type
     */
    private function formatAmount($value, $type)
    {
        $value = (float)($value ?? 0);

        if ($value <= 0) {
            return '-';
        }

        if ($type === 'percentage') {
            return '<strong class="text-success">' . number_format($value, 2) . '%</strong>';
        }

        return '<strong class="text-success">৳' . number_format($value, 0) . '</strong>';
    }

    /**
     * Format valid period
     */
    private function formatValidPeriod($validFrom, $validTo)
    {
        $from = $validFrom ? display_date($validFrom, 'M d') : '-';
        $to = $validTo ? display_date($validTo, 'M d') : '∞';

        return $from . ' to ' . $to;
    }

    /**
     * Get status badge HTML
     */
    private function getStatusBadge($status)
    {
        $badgeClass = match ($status) {
            'active' => 'badge-success',
            'inactive' => 'badge-secondary',
            default => 'badge-warning',
        };

        return '<span class="badge ' . $badgeClass . '">' . ucfirst($status) . '</span>';
    }

    /**
     * Get action buttons HTML with permission checks
     */
    private function getActionButtons($item)
    {
        $buttons = '<div class="btn-group" role="group">';

        // Edit Button (commission_edit)
        if (auth()->user()->hasPermission('commission_edit')) {
            $buttons .= '<a class="btn btn-sm btn-primary" href="' . route('flight.admin.discount.edit', $item->id) . '" title="Edit">
                            <i class="fa fa-edit"></i>
                        </a>';
        }

        // Status Toggle Button (commission_status)
        if (auth()->user()->hasPermission('commission_status')) {
            $buttons .= '<a class="btn btn-sm btn-warning status-toggle" href="' . route('flight.admin.discount.status', $item->id) . '" title="Toggle Status">
                            <i class="fa fa-toggle-' . ($item->status == 'active' ? 'on' : 'off') . '"></i>
                        </a>';
        }

        // Delete Button (commission_delete)
        if (auth()->user()->hasPermission('commission_delete')) {
            $buttons .= '<a class="btn btn-sm btn-danger" href="javascript:void(0)" onclick="deleteDiscount(' . $item->id . ')" title="Delete">
                            <i class="fa fa-trash"></i>
                        </a>';
        }

        $buttons .= '</div>';

        return $buttons;
    }

    public function create()
    {
        $this->checkPermission('commission_create');

        $gdsOptions = FlightApi::pluck('name', 'provider');
        $airlines = Airline::pluck('name', 'designator');
        $airports = Airport::pluck('name', 'code');

        return view('Flight::admin.discount.create', compact('gdsOptions', 'airlines', 'airports'));
    }

    public function store(Request $request)
    {
        $this->checkPermission('commission_create');

        $rules = $this->getValidationRules();
        $messages = $this->getValidationMessages();

        $this->validate($request, $rules, $messages);

        $row = new FlightDiscount();
        $row = $this->fillDiscountData($row, $request);
        $row->status = 'inactive';
        $row->usage_count = 0;
        $row->created_at = now();
        $row->updated_at = now();

        $row->save();

        return redirect()->route('flight.admin.discount.index')
            ->with('success', __('Discount created successfully'));
    }

    public function edit($id)
    {
        $this->checkPermission('commission_edit');

        $row = FlightDiscount::findOrFail($id);
        $gdsOptions = FlightApi::pluck('name', 'provider');
        $airlines = Airline::pluck('name', 'designator');
        $airports = Airport::pluck('name', 'code');

        return view('Flight::admin.discount.edit', compact('row', 'gdsOptions', 'airlines', 'airports'));
    }

    public function update(Request $request, $id)
    {
        $this->checkPermission('commission_edit');

        $row = FlightDiscount::findOrFail($id);

        $rules = $this->getValidationRules($id);
        $messages = $this->getValidationMessages();

        $this->validate($request, $rules, $messages);

        $row = $this->fillDiscountData($row, $request);

        if ($request->has('status')) {
            $row->status = $request->status;
        }

        $row->updated_at = now();
        $row->save();

        return redirect()->route('flight.admin.discount.index')
            ->with('success', __('Discount updated successfully'));
    }

    public function statusChange($id)
    {
        $this->checkPermission('commission_status');

        $item = FlightDiscount::findOrFail($id);
        $item->status = $item->status == 'active' ? 'inactive' : 'active';
        $item->save();

        return redirect()->back()
            ->with('success', __('Status updated successfully'));
    }

    public function destroy($id)
    {
        $this->checkPermission('commission_delete');

        $row = FlightDiscount::findOrFail($id);
        $row->delete();

        return redirect()->back()->with('success', __('Discount deleted successfully'));
    }

    /**
     * ============================================
     * BULK OPERATIONS METHODS
     * ============================================
     */

    /**
     * Execute bulk operations
     */
    public function bulkAction(Request $request)
    {
        $ids = $request->input('ids', []);
        $action = $request->input('action');

        if (empty($ids) || !is_array($ids)) {
            return back()->with('error', __('No items selected'));
        }

        switch ($action) {
            case 'delete':
                return $this->bulkDelete($ids);

            case 'copy':
                return $this->bulkCopy($request, $ids);

            case 'status':
                return $this->bulkStatus($request, $ids);

            case 'update-valid-dates':
                return $this->bulkUpdateValidDates($request, $ids);

            case 'change-source':
                return $this->bulkChangeSource($request, $ids);

            default:
                return back()->with('error', __('Invalid action'));
        }
    }

    /**
     * Bulk delete discounts
     */
    private function bulkDelete($ids)
    {
        $this->checkPermission('commission_bulk_delete');

        $deleted = FlightDiscount::whereIn('id', $ids)->delete();

        return redirect()->route('flight.admin.discount.index')
            ->with('success', __($deleted . ' discount(s) deleted successfully'));
    }

    /**
     * Bulk copy discounts
     */
    private function bulkCopy(Request $request, $ids)
    {
        $this->checkPermission('commission_bulk_copy');

        $sourceDiscounts = FlightDiscount::whereIn('id', $ids)->get();

        if ($sourceDiscounts->isEmpty()) {
            return back()->with('error', __('No discounts found'));
        }

        $gdsType = $request->input('gds_type');
        $copyCount = 0;

        foreach ($sourceDiscounts as $source) {
            // Generate unique code
            $newCode = $this->generateUniqueCode($source->code);

            // Create copy
            $copy = new FlightDiscount();
            $copy->fill($source->toArray());
            $copy->id = null;
            $copy->code = $newCode;
            $copy->status = 'inactive';
            $copy->usage_count = 0;
            $copy->created_at = now();
            $copy->updated_at = now();

            // Update GDS type if provided
            if ($gdsType && $gdsType !== 'keep') {
                $copy->gds_type = $gdsType;
            }

            $copy->save();
            $copyCount++;
        }

        return redirect()->route('flight.admin.discount.index')
            ->with('success', __($copyCount . ' discount(s) copied successfully'));
    }

    /**
     * Generate unique code for copied discounts
     */
    private function generateUniqueCode($originalCode)
    {
        if (empty($originalCode)) {
            return 'COPY-' . Str::random(8);
        }

        $baseCode = $originalCode;
        $newCode = $baseCode . '-COPY';
        $counter = 1;

        while (FlightDiscount::where('code', $newCode)->exists()) {
            $newCode = $baseCode . '-COPY-' . $counter;
            $counter++;
        }

        return $newCode;
    }

    /**
     * Bulk update status
     */
    private function bulkStatus(Request $request, $ids)
    {
        $this->checkPermission('commission_bulk_status');

        $status = $request->input('status');

        if (!in_array($status, ['active', 'inactive'])) {
            return back()->with('error', __('Invalid status'));
        }

        $updated = FlightDiscount::whereIn('id', $ids)->update(['status' => $status]);

        return redirect()->route('flight.admin.discount.index')
            ->with('success', __($updated . ' discount(s) status updated'));
    }

    /**
     * Bulk update valid dates
     */
    private function bulkUpdateValidDates(Request $request, $ids)
    {
        $this->checkPermission('commission_bulk_dates');

        $validFrom = $request->input('valid_from');
        $validTo = $request->input('valid_to');

        if ($validFrom && $validTo && strtotime($validFrom) > strtotime($validTo)) {
            return back()->with('error', __('Start date must be before end date'))
                ->withInput();
        }

        $updateData = [];
        if ($validFrom) {
            $updateData['valid_from'] = $validFrom;
        }
        if ($validTo) {
            $updateData['valid_to'] = $validTo;
        }

        if (empty($updateData)) {
            return back()->with('error', __('Please provide at least one date'));
        }

        $updated = FlightDiscount::whereIn('id', $ids)->update($updateData);

        return redirect()->route('flight.admin.discount.index')
            ->with('success', __($updated . ' discount(s) dates updated'));
    }

    /**
     * Bulk change GDS/source
     */
    private function bulkChangeSource(Request $request, $ids)
    {
        $this->checkPermission('commission_bulk_source');

        $gdsType = $request->input('gds_type');

        if (empty($gdsType)) {
            return back()->with('error', __('Please select a GDS type'));
        }

        $updated = FlightDiscount::whereIn('id', $ids)->update(['gds_type' => $gdsType]);

        return redirect()->route('flight.admin.discount.index')
            ->with('success', __($updated . ' discount(s) GDS type updated'));
    }

    /**
     * Show bulk action form with permission check
     */
    public function showBulkForm(Request $request)
    {
        $action = $request->input('action');
        $ids = explode(',', $request->input('ids', ''));
        $ids = array_filter($ids);

        if (empty($ids) || empty($action)) {
            return response()->json(['error' => __('Invalid request')], 400);
        }

        // Check permissions based on action
        switch($action) {
            case 'delete':
                if (!auth()->user()->hasPermission('commission_bulk_delete')) {
                    return response()->json(['error' => __('Permission denied')], 403);
                }
                break;
            case 'copy':
                if (!auth()->user()->hasPermission('commission_bulk_copy')) {
                    return response()->json(['error' => __('Permission denied')], 403);
                }
                break;
            case 'status':
                if (!auth()->user()->hasPermission('commission_bulk_status')) {
                    return response()->json(['error' => __('Permission denied')], 403);
                }
                break;
            case 'update-valid-dates':
                if (!auth()->user()->hasPermission('commission_bulk_dates')) {
                    return response()->json(['error' => __('Permission denied')], 403);
                }
                break;
            case 'change-source':
                if (!auth()->user()->hasPermission('commission_bulk_source')) {
                    return response()->json(['error' => __('Permission denied')], 403);
                }
                break;
        }

        $gdsOptions = FlightApi::pluck('name', 'provider')->toArray();
        $count = count($ids);

        // Generate form HTML using trait method  ← USE TRAIT METHOD HERE
        $formHtml = $this->generateFormHtml($action, $count, $gdsOptions);

        return response($formHtml);
    }

    /**
     * ============================================
     * HELPER METHODS
     * ============================================
     */

    private function getValidationRules($id = null)
    {
        $rules = [
            'name' => 'required|max:255',
            'code' => 'nullable|max:50',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'user_value' => 'nullable|numeric|min:0',
            'b2b_user_value' => 'nullable|numeric|min:0',
            'max_amount' => 'nullable|numeric|min:0',
            'min_purchase' => 'nullable|numeric|min:0',
            'airline_code' => 'nullable',
            'departure_code' => 'nullable',
            'arrival_code' => 'nullable',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
            'usage_limit' => 'nullable|integer|min:0',
            'per_user_limit' => 'nullable|integer|min:0',
            'priority' => 'nullable|integer',
            'description' => 'nullable|max:500',
            'conditions' => 'nullable',
            'ait_charge' => 'nullable|numeric|min:0|max:100',
            'service_charge' => 'nullable|numeric|min:0',
            'segment_discount' => 'nullable|numeric|min:0',
            'user_seg_discount' => 'nullable|numeric|min:0',
            'gds_type' => 'nullable',
        ];

        if ($id) {
            $rules['code'] = 'nullable|max:50|unique:flight_discounts,code,' . $id;
        } else {
            $rules['code'] = 'nullable|max:50|unique:flight_discounts,code';
        }

        if (request()->filled('type') && request()->type == 'percentage') {
            $rules['value'] = 'required|numeric|min:0|max:100';
            $rules['user_value'] = 'nullable|numeric|min:0|max:100';
            $rules['b2b_user_value'] = 'nullable|numeric|min:0|max:100';
        }

        return $rules;
    }

    private function getValidationMessages()
    {
        return [
            'name.required' => __('Name is required'),
            'type.required' => __('Discount type is required'),
            'value.required' => __('Discount value is required'),
            'value.max' => __('Percentage cannot be more than 100%'),
            'user_value.max' => __('User discount percentage cannot be more than 100%'),
            'valid_to.after_or_equal' => __('End date must be after or equal to start date'),
            'code.unique' => __('This discount code already exists'),
        ];
    }

    private function fillDiscountData(FlightDiscount $row, Request $request)
    {
        $row->name = $request->name;
        $row->code = $request->code;
        $row->type = $request->type;
        $row->value = $request->value ?? 0;
        $row->user_value = $request->user_value ?? 0;
        $row->b2b_user_value = $request->b2b_user_value ?? 0;
        $row->max_amount = $request->max_amount;
        $row->min_purchase = $request->min_purchase ?? 0;
        $row->valid_from = $request->valid_from;
        $row->valid_to = $request->valid_to;
        $row->usage_limit = $request->usage_limit;
        $row->per_user_limit = $request->per_user_limit;
        $row->priority = $request->priority ?? 0;
        $row->description = $request->description;
        $row->conditions = $request->conditions;
        $row->ait_charge = $request->ait_charge ?? 0;
        $row->service_charge = $request->service_charge ?? 0;
        $row->segment_discount = $request->segment_discount ?? 0;
        $row->user_seg_discount = $request->user_seg_discount ?? 0;
        $row->gds_type = $request->gds_type ?? null;

        // Process Airline - store designator directly
        if ($request->filled('airline_code') && $request->airline_code != '0') {
            $row->airline_code = $request->airline_code;
        } else {
            $row->airline_code = null;
        }

        // Process Departure Airport - store code directly
        if ($request->filled('departure_code') && $request->departure_code != '0') {
            $row->departure_code = $request->departure_code;
        } else {
            $row->departure_code = null;
        }

        // Process Arrival Airport - store code directly
        if ($request->filled('arrival_code') && $request->arrival_code != '0') {
            $row->arrival_code = $request->arrival_code;
        } else {
            $row->arrival_code = null;
        }

        return $row;
    }
}
