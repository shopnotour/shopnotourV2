@extends('admin.layouts.app')

@section('content')
    <div class="container-fluid">

        <div class="d-flex justify-content-between mb20">
            <h1 class="title-bar">
                <i class="fa fa-search"></i> {{ __('Search Sessions') }}
            </h1>
            <a href="{{ route('admin.marketing.dashboard') }}" class="btn btn-secondary">
                <i class="fa fa-arrow-left"></i> {{ __('Back') }}
            </a>
        </div>

        @include('admin.message')

        {{-- ✅ Search Box --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="input-group">
                            <input type="text"
                                   id="cardSearch"
                                   class="form-control"
                                   placeholder="{{ __('Search by name, email, phone...') }}">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="button">
                                    <i class="fa fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ✅ User Cards — 4 columns --}}
        <div class="row" id="userCardsContainer">
            @forelse($users as $user)
                <div class="col-md-3 col-sm-6 mb-4 user-card-item"
                     data-name="{{ strtolower($user->first_name . ' ' . $user->last_name) }}"
                     data-email="{{ strtolower($user->email) }}"
                     data-phone="{{ $user->phone }}">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">

                            {{-- Avatar + Name --}}
                            <div class="d-flex align-items-center mb-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center mr-3"
                                     style="width:42px;height:42px;background:#EEEDFE;flex-shrink:0;">
                                    <span style="color:#534AB7;font-weight:500;font-size:15px;">
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
                                        {{ \Carbon\Carbon::parse($user->last_search_at)->format('d M Y, h:i A') }}
                                    </small>
                                </div>
                            </div>

                            {{-- Search Count + Action --}}
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted d-block">{{ __('Total Searches') }}</small>
                                    <span class="badge badge-primary" style="font-size:13px;padding:4px 10px;">
                                        {{ number_format($user->search_count) }}
                                    </span>
                                </div>
                                <a href="{{ route('admin.marketing.search.session.detail', $user->user_id) }}"
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
                        <i class="fa fa-info-circle"></i> {{ __('No search sessions found') }}
                    </div>
                </div>
            @endforelse
        </div>

        {{-- No results message --}}
        <div id="noResults" class="text-center py-4" style="display:none;">
            <div class="alert alert-info">
                <i class="fa fa-info-circle"></i> {{ __('No matching users found') }}
            </div>
        </div>

        {{-- Pagination --}}
        <div class="d-flex justify-content-end mt-2">
            {{ $users->links() }}
        </div>

    </div>
@endsection

@push('js')
    <script>
        // ✅ Client-side card search
        $('#cardSearch').on('keyup', function () {
            const query = $(this).val().toLowerCase().trim();
            let visibleCount = 0;

            $('.user-card-item').each(function () {
                const name  = $(this).data('name');
                const email = $(this).data('email');
                const phone = $(this).data('phone') || '';

                if (name.includes(query) || email.includes(query) || phone.includes(query)) {
                    $(this).show();
                    visibleCount++;
                } else {
                    $(this).hide();
                }
            });

            $('#noResults').toggle(visibleCount === 0);
        });
    </script>
@endpush
