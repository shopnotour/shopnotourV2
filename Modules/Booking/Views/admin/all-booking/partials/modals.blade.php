{{-- Booking Detail Modal --}}
<div class="modal fade" id="modal_booking_detail" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white py-2">
                <h6 class="modal-title"><i class="fa fa-plane"></i> Booking Details — <span class="booking-id-display"></span></h6>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" id="booking-detail-content">
                <div class="text-center py-5"><div class="spinner-border text-primary"></div></div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary btn-sm" onclick="printBookingDetail()">
                    <i class="fa fa-print"></i> Print
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Duplicate Confirm Modal --}}
<div class="modal fade" id="modal_duplicate" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-primary">
            <div class="modal-header bg-primary text-white py-2">
                <h6 class="modal-title"><i class="fa fa-copy"></i> Duplicate Booking — <span id="dup-code"></span></h6>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="dup_booking_id">
                <div class="alert alert-info py-2 mb-3">
                    <i class="fa fa-info-circle"></i>
                    A new booking will be created with <strong>all passengers and routes copied</strong>.
                    The new booking will have status <strong>booked</strong>.
                </div>
                <div class="alert alert-warning py-2 mb-0">
                    <i class="fa fa-exclamation-triangle"></i>
                    You can edit prices, passengers, and routes after duplication.
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" id="btn-confirm-duplicate">
                    <i class="fa fa-copy"></i> Confirm Duplicate
                </button>
            </div>
        </div>
    </div>
</div>

{{-- PNR Edit Modal --}}
<div class="modal fade" id="modal_pnr_edit" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white py-2">
                <h6 class="modal-title"><i class="fa fa-edit"></i> Edit PNR / Source / Status — <span id="pnr-edit-code"></span></h6>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="pnr_edit_booking_id">
                <div class="form-group">
                    <label class="small fw-semibold">PNR / GDS Reference <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="pnr_edit_input"
                           style="text-transform:uppercase;letter-spacing:2px;font-weight:bold"
                           maxlength="20" placeholder="e.g. ABC123">
                </div>
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label class="small fw-semibold">Source / GDS <span class="text-danger">*</span></label>
                            <select class="form-control" id="pnr_edit_source">
                                <option value="sabre">Sabre</option>
                                <option value="travelport">Travelport</option>
                                <option value="galileo">Galileo</option>
                                <option value="amadeus">Amadeus</option>
                                <option value="manual">Manual</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label class="small fw-semibold">Status <span class="text-danger">*</span></label>
                            <select class="form-control" id="pnr_edit_status">
                                <option value="issue_request">Issue Request</option>
                                <option value="pending">Pending</option>
                                <option value="paid">Paid</option>
                                <option value="booked">Booked</option>
                                <option value="issued">Issued</option>
                                <option value="ticketed">Ticketed</option>
                                <option value="completed">Completed</option>
                                <option value="failed">Failed</option>
                                <option value="refunded">Refunded</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-info btn-sm text-white" id="btn-save-pnr">
                    <i class="fa fa-save"></i> Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Booking Cancel Modal --}}
<div class="modal fade" id="modal_booking_cancel" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-warning">
            <div class="modal-header bg-warning py-2">
                <h6 class="modal-title"><i class="fa fa-ban"></i> Booking Cancel — <span id="booking-cancel-code"></span></h6>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="booking_cancel_url">
                <div class="alert alert-warning py-2 mb-0">
                    <i class="fa fa-exclamation-triangle"></i>
                    This will cancel the booking in GDS. This action <strong>cannot be undone</strong>.
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">No, Go Back</button>
                <a href="#" id="btn-confirm-booking-cancel" class="btn btn-warning btn-sm">
                    <i class="fa fa-ban"></i> Yes, Cancel Booking
                </a>
            </div>
        </div>
    </div>
</div>