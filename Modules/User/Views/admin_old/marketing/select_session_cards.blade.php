{{--
    ✅ partials/select-session-cards.blade.php
    Ajax request এ এই partial return হয়।
    #cardsContainer div এ inject হবে।
--}}

<div class="row">
    @forelse($users as $user)
        <div class="col-md-3 col-sm-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">

                    {{-- Avatar + Name --}}
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center mr-3"
                             style="width:42px;height:42px;background:#FAEEDA;flex-shrink:0;">
                            <span style="color:#854F0B;font-weight:500;font-size:15px;">
                                {{ strtoupper(substr($user->first_name, 0, 1)) }}{{ strtoupper(substr($user->last_name, 0, 1)) }}
                            </span>
                        </div>
                        <div style="overflow:hidden;">
                            <p class="mb-0 font-weight-bold" style="font-size:14px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                {{ $user->first_name }} {{ $user->last_name }}
                            </p>
                            <small class="text-muted" style="font-size:12px;">{{ $user->email }}</small>
                        </div>
                    </div>

                    {{-- Info --}}
                    <div class="mb-3">
                        @if($user->phone)
                            <div class="mb-1">
                                <small class="text-muted"><i class="fa fa-phone"></i> {{ $user->phone }}</small>
                            </div>
                        @endif
                        <div class="mb-1">
                            <small class="text-muted">
                                <i class="fa fa-clock-o"></i>
                                {{ \Carbon\Carbon::parse($user->last_select_at)->format('d M Y, h:i A') }}
                            </small>
                        </div>
                    </div>

                    {{-- Price Check Count + Action --}}
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted d-block">{{ __('Price Checks') }}</small>
                            <span class="badge badge-warning" style="font-size:13px;padding:4px 10px;">
                                {{ number_format($user->select_count) }}
                            </span>
                        </div>
                        <a href="{{ route('admin.marketing.select.session.detail', $user->user_id) }}"
                           class="btn btn-primary btn-sm">
                            <i class="fa fa-eye"></i> {{ __('Details') }}
                        </a>
                    </div>

                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-info">
                <i class="fa fa-info-circle"></i> {{ __('No price check sessions found') }}
            </div>
        </div>
    @endforelse
</div>

{{-- Pagination --}}
<div class="d-flex justify-content-end mt-2">
    {{ $users->links() }}
</div>
