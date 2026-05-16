@extends('admin.layouts.app')

@section('content')
    <div class="container-fluid">

        <div class="d-flex justify-content-between mb20">
            <h1 class="title-bar">
                <i class="fa fa-check-square"></i> {{ __('Price Check Sessions') }}
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
                                   placeholder="{{ __('Search by name, email, phone...') }}"
                                   value="{{ request('s') }}">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="button" id="searchBtn">
                                    <i class="fa fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ✅ Cards Container — ajax inject হবে এখানে --}}
        <div id="cardsContainer">
            @include('User::admin.marketing.select_session_cards', compact('users'))
        </div>

        {{-- Loading spinner --}}
        <div id="loadingSpinner" class="text-center py-4" style="display:none;">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">{{ __('Loading...') }}</span>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function () {
            let searchTimeout;

            function performSearch() {
                const query = $('#cardSearch').val();

                $('#loadingSpinner').show();
                $('#cardsContainer').css('opacity', '0.5');

                $.ajax({
                    url: window.location.pathname,
                    method: 'GET',
                    data: { s: query },
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    success: function (html) {
                        $('#cardsContainer').html(html);

                        const newUrl = query
                            ? `${window.location.pathname}?s=${encodeURIComponent(query)}`
                            : window.location.pathname;
                        window.history.pushState({}, '', newUrl);
                    },
                    error: function () {
                        alert('{{ __("Search failed. Please try again.") }}');
                    },
                    complete: function () {
                        $('#loadingSpinner').hide();
                        $('#cardsContainer').css('opacity', '1');
                    }
                });
            }

            $('#searchBtn').on('click', function () {
                performSearch();
            });

            $('#cardSearch').on('keyup', function (e) {
                if (e.keyCode === 13) {
                    performSearch();
                } else {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(function () {
                        performSearch();
                    }, 500);
                }
            });
        });
    </script>
@endpush
