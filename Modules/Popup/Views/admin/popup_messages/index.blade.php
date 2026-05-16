{{--@extends('admin.layouts.app') --}}{{-- আপনার admin layout অনুযায়ী পরিবর্তন করুন --}}

{{--@push('css')--}}
{{--    <script src="https://cdn.tailwindcss.com"></script>--}}
{{--    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">--}}
{{--    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">--}}
{{--    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">--}}
{{--    <style>--}}
{{--        #popupTable thead th {--}}
{{--            background-color: #1e3a5f;--}}
{{--            color: #fff;--}}
{{--            font-weight: 600;--}}
{{--            padding: 10px 12px;--}}
{{--            font-size: 0.82rem;--}}
{{--            white-space: nowrap;--}}
{{--            border-bottom: none !important;--}}
{{--        }--}}
{{--        #popupTable tbody td {--}}
{{--            padding: 9px 12px;--}}
{{--            font-size: 0.83rem;--}}
{{--            vertical-align: middle;--}}
{{--            border-color: #f3f4f6;--}}
{{--            color: #374151;--}}
{{--        }--}}
{{--        #popupTable tbody tr:nth-child(even) td { background-color: #f8fafc; }--}}
{{--        #popupTable tbody tr:hover td           { background-color: #eff6ff !important; }--}}

{{--        #popupTable_filter input {--}}
{{--            border: 1px solid #d1d5db; border-radius: 6px;--}}
{{--            padding: 5px 10px; font-size: 0.82rem; outline: none;--}}
{{--        }--}}
{{--        #popupTable_filter input:focus {--}}
{{--            border-color: #3b82f6;--}}
{{--            box-shadow: 0 0 0 3px rgba(59,130,246,.15);--}}
{{--        }--}}
{{--        #popupTable_length select { border: 1px solid #d1d5db; border-radius: 6px; padding: 4px 8px; font-size: 0.82rem; }--}}
{{--        #popupTable_info, #popupTable_length label, #popupTable_filter label { font-size: 0.8rem; color: #6b7280; }--}}
{{--        #popupTable_paginate .paginate_button {--}}
{{--            border-radius: 5px !important; padding: 4px 9px !important;--}}
{{--            font-size: 0.78rem !important; border: 1px solid #e5e7eb !important;--}}
{{--            margin: 0 1px; color: #374151 !important;--}}
{{--        }--}}
{{--        #popupTable_paginate .paginate_button.current {--}}
{{--            background: #1e3a5f !important; color: #fff !important; border-color: #1e3a5f !important;--}}
{{--        }--}}
{{--        #popupTable_paginate .paginate_button:hover:not(.current):not(.disabled) {--}}
{{--            background: #eff6ff !important; border-color: #3b82f6 !important; color: #1d4ed8 !important;--}}
{{--        }--}}

{{--        .dt-buttons { display: flex; flex-wrap: wrap; gap: 5px; margin-bottom: 2px; }--}}
{{--        .dt-button {--}}
{{--            font-size: 0.75rem !important; padding: 5px 11px !important;--}}
{{--            border-radius: 5px !important; border: 1px solid #d1d5db !important;--}}
{{--            background: #fff !important; color: #374151 !important;--}}
{{--            cursor: pointer; transition: background .12s;--}}
{{--        }--}}
{{--        .dt-button:hover { background: #f3f4f6 !important; border-color: #9ca3af !important; }--}}

{{--        table.dataTable > tbody > tr.child ul.dtr-details { width: 100%; }--}}
{{--        table.dataTable > tbody > tr.child ul.dtr-details li {--}}
{{--            border-bottom: 1px solid #f3f4f6; padding: 6px 4px;--}}
{{--            font-size: 0.82rem; display: flex; gap: 8px; align-items: flex-start;--}}
{{--        }--}}
{{--        table.dataTable > tbody > tr.child ul.dtr-details li:last-child { border-bottom: none; }--}}
{{--        table.dtr-inline.collapsed > tbody > tr > td.dtr-control::before {--}}
{{--            background-color: #1e3a5f !important; border-color: #1e3a5f !important;--}}
{{--        }--}}

{{--        .r-badge {--}}
{{--            display: inline-flex; align-items: center;--}}
{{--            padding: 2px 10px; border-radius: 9999px;--}}
{{--            font-size: 0.71rem; font-weight: 600; white-space: nowrap;--}}
{{--        }--}}
{{--        .b-info      { background:#dbeafe; color:#1e40af; }--}}
{{--        .b-success   { background:#d1fae5; color:#065f46; }--}}
{{--        .b-warning   { background:#fef3c7; color:#92400e; }--}}
{{--        .b-danger    { background:#fee2e2; color:#991b1b; }--}}
{{--        .b-secondary { background:#f3f4f6; color:#374151; }--}}

{{--        /* Modal */--}}
{{--        #popupModal {--}}
{{--            display: none; position: fixed; inset: 0;--}}
{{--            background: rgba(0,0,0,0.5); z-index: 9999;--}}
{{--            align-items: center; justify-content: center;--}}
{{--        }--}}
{{--        #popupModal.open { display: flex; }--}}
{{--        .modal-box {--}}
{{--            background: #fff; border-radius: 12px;--}}
{{--            width: 100%; max-width: 560px;--}}
{{--            padding: 28px; margin: 16px;--}}
{{--            box-shadow: 0 20px 60px rgba(0,0,0,0.2);--}}
{{--            max-height: 90vh; overflow-y: auto;--}}
{{--        }--}}
{{--        .form-label { display: block; font-size: 0.82rem; font-weight: 600; color: #374151; margin-bottom: 4px; }--}}
{{--        .form-input {--}}
{{--            width: 100%; border: 1px solid #d1d5db; border-radius: 6px;--}}
{{--            padding: 8px 10px; font-size: 0.85rem; outline: none;--}}
{{--            transition: border .15s, box-shadow .15s;--}}
{{--        }--}}
{{--        .form-input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.15); }--}}

{{--        @media (max-width: 600px) {--}}
{{--            #popupTable_wrapper > div { display: flex; flex-direction: column; gap: 8px; }--}}
{{--            #popupTable_filter, #popupTable_length, #popupTable_info, #popupTable_paginate {--}}
{{--                width: 100%; text-align: left !important; float: none !important;--}}
{{--            }--}}
{{--            #popupTable thead th, #popupTable tbody td { padding: 7px 8px; font-size: 0.76rem; }--}}
{{--        }--}}
{{--    </style>--}}
{{--@endpush--}}

{{--@section('content')--}}
{{--    <div class="px-3 py-4 sm:px-5 md:px-6">--}}

{{--        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">--}}
{{--            <div>--}}
{{--                <h2 class="text-xl sm:text-2xl font-bold text-gray-800">{{ __('Popup Messages') }}</h2>--}}
{{--                <p class="text-sm text-gray-400">{{ __('Manage popup messages for each page') }}</p>--}}
{{--            </div>--}}
{{--            <button onclick="openModal()"--}}
{{--                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold text-white"--}}
{{--                    style="background:#1e3a5f">--}}
{{--                <i class="fa fa-plus"></i> {{ __('Add New') }}--}}
{{--            </button>--}}
{{--        </div>--}}

{{--        @if(session('success'))--}}
{{--            <div class="flex items-center gap-2 bg-green-50 border border-green-200 text-green-800 text-sm rounded-lg px-4 py-3 mb-4">--}}
{{--                <i class="fa fa-check-circle"></i> {{ session('success') }}--}}
{{--            </div>--}}
{{--        @endif--}}
{{--        @if(session('error'))--}}
{{--            <div class="flex items-center gap-2 bg-red-50 border border-red-200 text-red-800 text-sm rounded-lg px-4 py-3 mb-4">--}}
{{--                <i class="fa fa-exclamation-circle"></i> {{ session('error') }}--}}
{{--            </div>--}}
{{--        @endif--}}

{{--        <div class="bg-white rounded-xl shadow-sm border border-gray-100 w-full overflow-hidden">--}}
{{--            <div class="p-4 sm:p-5 w-full min-w-0">--}}

{{--                <table id="popupTable" style="width:100%">--}}
{{--                    <thead>--}}
{{--                    <tr>--}}
{{--                        <th>{{ __('Page') }}</th>--}}
{{--                        <th>{{ __('Title') }}</th>--}}
{{--                        <th>{{ __('Type') }}</th>--}}
{{--                        <th>{{ __('Show Once') }}</th>--}}
{{--                        <th>{{ __('Status') }}</th>--}}
{{--                        <th>{{ __('Created By') }}</th>--}}
{{--                        <th>{{ __('Updated By') }}</th>--}}
{{--                        <th>{{ __('Updated At') }}</th>--}}
{{--                        <th>{{ __('Action') }}</th>--}}
{{--                    </tr>--}}
{{--                    </thead>--}}
{{--                    <tbody>--}}
{{--                    @forelse($popups as $popup)--}}
{{--                        <tr>--}}
{{--                            --}}{{-- Page --}}
{{--                            <td class="font-semibold">--}}
{{--                                {{ $pageKeys[$popup->page_key] ?? $popup->page_key }}--}}
{{--                            </td>--}}

{{--                            --}}{{-- Title --}}
{{--                            <td>{{ $popup->title ?? '—' }}</td>--}}

{{--                            --}}{{-- Type --}}
{{--                            <td>--}}
{{--                                @php--}}
{{--                                    $typeCls = [--}}
{{--                                        'info'    => 'b-info',--}}
{{--                                        'success' => 'b-success',--}}
{{--                                        'warning' => 'b-warning',--}}
{{--                                        'danger'  => 'b-danger',--}}
{{--                                    ][$popup->type] ?? 'b-secondary';--}}
{{--                                @endphp--}}
{{--                                <span class="r-badge {{ $typeCls }}">{{ ucfirst($popup->type) }}</span>--}}
{{--                            </td>--}}

{{--                            --}}{{-- Show Once --}}
{{--                            <td>--}}
{{--                                <span class="r-badge {{ $popup->show_once ? 'b-warning' : 'b-secondary' }}">--}}
{{--                                    {{ $popup->show_once ? __('Once') : __('Always') }}--}}
{{--                                </span>--}}
{{--                            </td>--}}

{{--                            --}}{{-- Status --}}
{{--                            <td>--}}
{{--                                <form action="{{ route('popup.toggle', $popup->id) }}" method="POST" style="display:inline">--}}
{{--                                    @csrf @method('PATCH')--}}
{{--                                    <button type="submit"--}}
{{--                                            class="r-badge {{ $popup->is_active ? 'b-success' : 'b-secondary' }}"--}}
{{--                                            style="border:none;cursor:pointer"--}}
{{--                                            title="{{ $popup->is_active ? __('Click to deactivate') : __('Click to activate') }}">--}}
{{--                                        {{ $popup->is_active ? __('Active') : __('Inactive') }}--}}
{{--                                    </button>--}}
{{--                                </form>--}}
{{--                            </td>--}}

{{--                            --}}{{-- Created By --}}
{{--                            <td>{{ $popup->creator->name ?? '—' }}</td>--}}

{{--                            --}}{{-- Updated By --}}
{{--                            <td>{{ $popup->updater->name ?? '—' }}</td>--}}

{{--                            --}}{{-- Updated At --}}
{{--                            <td data-order="{{ $popup->updated_at->timestamp }}" style="white-space:nowrap">--}}
{{--                                {{ $popup->updated_at->format('d M Y, h:i A') }}--}}
{{--                            </td>--}}

{{--                            --}}{{-- Action --}}
{{--                            <td>--}}
{{--                                <div style="display:flex;gap:4px">--}}
{{--                                    <button onclick='openEditModal(@json($popup))'--}}
{{--                                            style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:5px;background:#1e3a5f;color:#fff;font-size:.75rem;font-weight:600;border:none;cursor:pointer">--}}
{{--                                        <i class="fa fa-edit"></i> Edit--}}
{{--                                    </button>--}}
{{--                                    <form action="{{ route('popup.destroy', $popup->id) }}" method="POST"--}}
{{--                                          onsubmit="return confirm('Delete this popup?')">--}}
{{--                                        @csrf @method('DELETE')--}}
{{--                                        <button type="submit"--}}
{{--                                                style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:5px;background:#dc2626;color:#fff;font-size:.75rem;font-weight:600;border:none;cursor:pointer">--}}
{{--                                            <i class="fa fa-trash"></i> Delete--}}
{{--                                        </button>--}}
{{--                                    </form>--}}
{{--                                </div>--}}
{{--                            </td>--}}
{{--                        </tr>--}}
{{--                    @empty--}}
{{--                        <tr>--}}
{{--                            <td colspan="9" style="text-align:center;padding:40px;color:#9ca3af">--}}
{{--                                <i class="fa fa-inbox" style="font-size:2rem;display:block;margin-bottom:8px"></i>--}}
{{--                                {{ __('No popup messages found.') }}--}}
{{--                            </td>--}}
{{--                        </tr>--}}
{{--                    @endforelse--}}
{{--                    </tbody>--}}
{{--                </table>--}}

{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}

{{--    --}}{{-- ═══════════════════ CREATE / EDIT MODAL ═══════════════════ --}}
{{--    <div id="popupModal">--}}
{{--        <div class="modal-box">--}}

{{--            <div class="flex items-center justify-between mb-5">--}}
{{--                <h3 id="modalTitle" class="text-base font-bold text-gray-800">Add Popup Message</h3>--}}
{{--                <button onclick="closeModal()" style="background:none;border:none;font-size:1.4rem;cursor:pointer;color:#9ca3af">&times;</button>--}}
{{--            </div>--}}

{{--            <form id="popupForm" method="POST">--}}
{{--                @csrf--}}
{{--                <span id="methodField"></span>--}}

{{--                <div style="display:flex;flex-direction:column;gap:14px">--}}

{{--                    --}}{{-- Page Key --}}
{{--                    <div>--}}
{{--                        <label class="form-label">{{ __('Page') }} <span style="color:red">*</span></label>--}}
{{--                        <select name="page_key" id="f_page_key" class="form-input" required>--}}
{{--                            <option value="">— Select page —</option>--}}
{{--                            @foreach($pageKeys as $key => $label)--}}
{{--                                <option value="{{ $key }}">{{ $label }}</option>--}}
{{--                            @endforeach--}}
{{--                        </select>--}}
{{--                    </div>--}}

{{--                    --}}{{-- Title --}}
{{--                    <div>--}}
{{--                        <label class="form-label">{{ __('Title') }}</label>--}}
{{--                        <input type="text" name="title" id="f_title" class="form-input"--}}
{{--                               placeholder="Optional title...">--}}
{{--                    </div>--}}

{{--                    --}}{{-- Message --}}
{{--                    <div>--}}
{{--                        <label class="form-label">{{ __('Message') }} <span style="color:red">*</span></label>--}}
{{--                        <textarea name="message" id="f_message" class="form-input" rows="4"--}}
{{--                                  placeholder="Type your popup message..." required--}}
{{--                                  style="resize:vertical"></textarea>--}}
{{--                    </div>--}}

{{--                    --}}{{-- Type --}}
{{--                    <div>--}}
{{--                        <label class="form-label">{{ __('Type') }} <span style="color:red">*</span></label>--}}
{{--                        <select name="type" id="f_type" class="form-input" required>--}}
{{--                            <option value="info">ℹ️ Info</option>--}}
{{--                            <option value="success">✅ Success</option>--}}
{{--                            <option value="warning">⚠️ Warning</option>--}}
{{--                            <option value="danger">🚨 Danger</option>--}}
{{--                        </select>--}}
{{--                    </div>--}}

{{--                    --}}{{-- Toggles --}}
{{--                    <div style="display:flex;gap:24px;flex-wrap:wrap">--}}
{{--                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:.85rem;font-weight:600;color:#374151">--}}
{{--                            <input type="checkbox" name="is_active" id="f_is_active" value="1"--}}
{{--                                   style="width:16px;height:16px;cursor:pointer">--}}
{{--                            {{ __('Active') }}--}}
{{--                        </label>--}}
{{--                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:.85rem;font-weight:600;color:#374151">--}}
{{--                            <input type="checkbox" name="show_once" id="f_show_once" value="1"--}}
{{--                                   style="width:16px;height:16px;cursor:pointer">--}}
{{--                            {{ __('Show once per user') }}--}}
{{--                        </label>--}}
{{--                    </div>--}}

{{--                </div>--}}

{{--                <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:20px">--}}
{{--                    <button type="button" onclick="closeModal()"--}}
{{--                            style="padding:8px 18px;border-radius:6px;border:1px solid #d1d5db;background:#fff;color:#374151;font-size:.85rem;font-weight:600;cursor:pointer">--}}
{{--                        Cancel--}}
{{--                    </button>--}}
{{--                    <button type="submit"--}}
{{--                            style="padding:8px 18px;border-radius:6px;border:none;background:#1e3a5f;color:#fff;font-size:.85rem;font-weight:600;cursor:pointer">--}}
{{--                        <i class="fa fa-save"></i> Save--}}
{{--                    </button>--}}
{{--                </div>--}}
{{--            </form>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--@endsection--}}

{{--@push('js')--}}
{{--    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>--}}
{{--    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>--}}
{{--    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>--}}
{{--    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>--}}
{{--    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>--}}
{{--    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>--}}
{{--    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>--}}
{{--    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>--}}

{{--    <script>--}}
{{--        // ── DataTable ─────────────────────────────────────────────--}}
{{--        $(document).ready(function () {--}}
{{--            $('#popupTable').DataTable({--}}
{{--                dom:--}}
{{--                    "<'flex flex-wrap items-center justify-between gap-y-2 mb-3'B<'ml-auto'f>>" +--}}
{{--                    "tr" +--}}
{{--                    "<'flex flex-wrap items-center justify-between gap-y-2 mt-3'i<'ml-auto'p>>",--}}
{{--                pageLength: 15,--}}
{{--                lengthMenu: [[15, 25, 50, -1], [15, 25, 50, 'All']],--}}
{{--                order: [[7, 'desc']],--}}
{{--                responsive: {--}}
{{--                    details: { type: 'inline', renderer: $.fn.dataTable.Responsive.renderer.listHiddenNodes() }--}}
{{--                },--}}
{{--                columnDefs: [--}}
{{--                    { responsivePriority: 1,  targets: 0 },                  // Page--}}
{{--                    { responsivePriority: 2,  targets: 4 },                  // Status--}}
{{--                    { responsivePriority: 3,  targets: 2 },                  // Type--}}
{{--                    { responsivePriority: 4,  targets: 8, orderable: false },// Action--}}
{{--                    { responsivePriority: 5,  targets: 7 },                  // Updated At--}}
{{--                    { responsivePriority: 6,  targets: 1 },                  // Title--}}
{{--                    { responsivePriority: 7,  targets: 3 },                  // Show Once--}}
{{--                    { responsivePriority: 8,  targets: 5 },                  // Created By--}}
{{--                    { responsivePriority: 9,  targets: 6 },                  // Updated By--}}
{{--                ],--}}
{{--                buttons: [--}}
{{--                    { extend: 'copy',  text: '<i class="fa fa-copy"></i> Copy',       exportOptions: { columns: ':not(:last-child)' } },--}}
{{--                    { extend: 'csv',   text: '<i class="fa fa-file-csv"></i> CSV',     exportOptions: { columns: ':not(:last-child)' } },--}}
{{--                    { extend: 'excel', text: '<i class="fa fa-file-excel"></i> Excel', exportOptions: { columns: ':not(:last-child)' } },--}}
{{--                    { extend: 'pdf',   text: '<i class="fa fa-file-pdf"></i> PDF',     exportOptions: { columns: ':not(:last-child)' } },--}}
{{--                    { extend: 'print', text: '<i class="fa fa-print"></i> Print',      exportOptions: { columns: ':not(:last-child)' } },--}}
{{--                ],--}}
{{--                language: {--}}
{{--                    search: '', searchPlaceholder: 'Search...',--}}
{{--                    lengthMenu: 'Show _MENU_',--}}
{{--                    info: 'Showing _START_–_END_ of _TOTAL_',--}}
{{--                    infoEmpty: 'No popups found',--}}
{{--                    emptyTable: 'No popup messages found.',--}}
{{--                    paginate: { first: '«', previous: '‹', next: '›', last: '»' },--}}
{{--                },--}}
{{--            });--}}
{{--        });--}}

{{--        // ── Modal helpers ─────────────────────────────────────────--}}
{{--        const storeUrl  = "{{ route('popup.store') }}";--}}

{{--        function openModal() {--}}
{{--            document.getElementById('modalTitle').textContent   = 'Add Popup Message';--}}
{{--            document.getElementById('popupForm').action         = storeUrl;--}}
{{--            document.getElementById('methodField').innerHTML    = '';--}}
{{--            document.getElementById('f_page_key').value         = '';--}}
{{--            document.getElementById('f_title').value            = '';--}}
{{--            document.getElementById('f_message').value          = '';--}}
{{--            document.getElementById('f_type').value             = 'info';--}}
{{--            document.getElementById('f_is_active').checked      = true;--}}
{{--            document.getElementById('f_show_once').checked      = false;--}}
{{--            document.getElementById('popupModal').classList.add('open');--}}
{{--        }--}}

{{--        function openEditModal(popup) {--}}
{{--            const updateUrl = "{{ url('admin/popup-messages') }}/" + popup.id;--}}
{{--            document.getElementById('modalTitle').textContent   = 'Edit Popup Message';--}}
{{--            document.getElementById('popupForm').action         = updateUrl;--}}
{{--            document.getElementById('methodField').innerHTML    = '<input type="hidden" name="_method" value="PUT">';--}}
{{--            document.getElementById('f_page_key').value         = popup.page_key;--}}
{{--            document.getElementById('f_title').value            = popup.title ?? '';--}}
{{--            document.getElementById('f_message').value          = popup.message;--}}
{{--            document.getElementById('f_type').value             = popup.type;--}}
{{--            document.getElementById('f_is_active').checked      = popup.is_active == 1;--}}
{{--            document.getElementById('f_show_once').checked      = popup.show_once == 1;--}}
{{--            document.getElementById('popupModal').classList.add('open');--}}
{{--        }--}}

{{--        function closeModal() {--}}
{{--            document.getElementById('popupModal').classList.remove('open');--}}
{{--        }--}}

{{--        // Backdrop click এ close--}}
{{--        document.getElementById('popupModal').addEventListener('click', function(e) {--}}
{{--            if (e.target === this) closeModal();--}}
{{--        });--}}

{{--        // ESC key এ close--}}
{{--        document.addEventListener('keydown', function(e) {--}}
{{--            if (e.key === 'Escape') closeModal();--}}
{{--        });--}}
{{--    </script>--}}
{{--@endpush--}}


@extends('admin.layouts.app')

@push('css')
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
    <style>
        #popupTable thead th {
            background-color: #1e3a5f;
            color: #fff;
            font-weight: 600;
            padding: 10px 12px;
            font-size: 0.82rem;
            white-space: nowrap;
            border-bottom: none !important;
        }
        #popupTable tbody td {
            padding: 9px 12px;
            font-size: 0.83rem;
            vertical-align: middle;
            border-color: #f3f4f6;
            color: #374151;
        }
        #popupTable tbody tr:nth-child(even) td { background-color: #f8fafc; }
        #popupTable tbody tr:hover td           { background-color: #eff6ff !important; }

        #popupTable_filter input {
            border: 1px solid #d1d5db; border-radius: 6px;
            padding: 5px 10px; font-size: 0.82rem; outline: none;
        }
        #popupTable_filter input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59,130,246,.15);
        }
        #popupTable_length select { border: 1px solid #d1d5db; border-radius: 6px; padding: 4px 8px; font-size: 0.82rem; }
        #popupTable_info, #popupTable_length label, #popupTable_filter label { font-size: 0.8rem; color: #6b7280; }
        #popupTable_paginate .paginate_button {
            border-radius: 5px !important; padding: 4px 9px !important;
            font-size: 0.78rem !important; border: 1px solid #e5e7eb !important;
            margin: 0 1px; color: #374151 !important;
        }
        #popupTable_paginate .paginate_button.current {
            background: #1e3a5f !important; color: #fff !important; border-color: #1e3a5f !important;
        }
        #popupTable_paginate .paginate_button:hover:not(.current):not(.disabled) {
            background: #eff6ff !important; border-color: #3b82f6 !important; color: #1d4ed8 !important;
        }

        .dt-buttons { display: flex; flex-wrap: wrap; gap: 5px; margin-bottom: 2px; }
        .dt-button {
            font-size: 0.75rem !important; padding: 5px 11px !important;
            border-radius: 5px !important; border: 1px solid #d1d5db !important;
            background: #fff !important; color: #374151 !important;
            cursor: pointer; transition: background .12s;
        }
        .dt-button:hover { background: #f3f4f6 !important; border-color: #9ca3af !important; }

        table.dataTable > tbody > tr.child ul.dtr-details { width: 100%; }
        table.dataTable > tbody > tr.child ul.dtr-details li {
            border-bottom: 1px solid #f3f4f6; padding: 6px 4px;
            font-size: 0.82rem; display: flex; gap: 8px; align-items: flex-start;
        }
        table.dataTable > tbody > tr.child ul.dtr-details li:last-child { border-bottom: none; }
        table.dtr-inline.collapsed > tbody > tr > td.dtr-control::before {
            background-color: #1e3a5f !important; border-color: #1e3a5f !important;
        }

        .r-badge {
            display: inline-flex; align-items: center;
            padding: 2px 10px; border-radius: 9999px;
            font-size: 0.71rem; font-weight: 600; white-space: nowrap;
        }
        .b-info      { background:#dbeafe; color:#1e40af; }
        .b-success   { background:#d1fae5; color:#065f46; }
        .b-warning   { background:#fef3c7; color:#92400e; }
        .b-danger    { background:#fee2e2; color:#991b1b; }
        .b-secondary { background:#f3f4f6; color:#374151; }

        /* Status toggle */
        .toggle-status-btn { border:none; cursor:pointer; transition: opacity .15s, transform .1s; }
        .toggle-status-btn:hover  { opacity: .8; }
        .toggle-status-btn:active { transform: scale(.95); }
        .toggle-status-btn.loading { opacity: .5; pointer-events: none; }

        /* Modal */
        #popupModal {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,0.5); z-index: 9999;
            align-items: center; justify-content: center;
        }
        #popupModal.open { display: flex; }
        .modal-box {
            background: #fff; border-radius: 12px;
            width: 100%; max-width: 560px;
            padding: 28px; margin: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
            max-height: 90vh; overflow-y: auto;
        }
        .form-label { display: block; font-size: 0.82rem; font-weight: 600; color: #374151; margin-bottom: 4px; }
        .form-input {
            width: 100%; border: 1px solid #d1d5db; border-radius: 6px;
            padding: 8px 10px; font-size: 0.85rem; outline: none;
            transition: border .15s, box-shadow .15s;
        }
        .form-input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.15); }
        .form-input.is-invalid { border-color: #dc2626 !important; }
        .invalid-feedback { font-size: .75rem; color: #dc2626; margin-top: 3px; display: none; }

        /* Toast */
        #toast {
            position: fixed; bottom: 24px; right: 24px; z-index: 99999;
            padding: 12px 18px; border-radius: 8px; font-size: .84rem; font-weight: 600;
            color: #fff; opacity: 0; transform: translateY(10px);
            transition: opacity .25s, transform .25s; pointer-events: none;
        }
        #toast.show { opacity: 1; transform: translateY(0); }
        #toast.t-success { background: #059669; }
        #toast.t-error   { background: #dc2626; }

        @media (max-width: 600px) {
            #popupTable_wrapper > div { display: flex; flex-direction: column; gap: 8px; }
            #popupTable_filter, #popupTable_length, #popupTable_info, #popupTable_paginate {
                width: 100%; text-align: left !important; float: none !important;
            }
            #popupTable thead th, #popupTable tbody td { padding: 7px 8px; font-size: 0.76rem; }
        }
    </style>
@endpush

@section('content')
    <div class="px-3 py-4 sm:px-5 md:px-6">

        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-gray-800">{{ __('Popup Messages') }}</h2>
                <p class="text-sm text-gray-400">{{ __('Manage popup messages for each page') }}</p>
            </div>
            <button onclick="openModal()"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold text-white"
                    style="background:#1e3a5f">
                <i class="fa fa-plus"></i> {{ __('Add New') }}
            </button>
        </div>

        @if(session('success'))
            <div class="flex items-center gap-2 bg-green-50 border border-green-200 text-green-800 text-sm rounded-lg px-4 py-3 mb-4">
                <i class="fa fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="flex items-center gap-2 bg-red-50 border border-red-200 text-red-800 text-sm rounded-lg px-4 py-3 mb-4">
                <i class="fa fa-exclamation-circle"></i> {{ session('error') }}
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 w-full overflow-hidden">
            <div class="p-4 sm:p-5 w-full min-w-0">
                <table id="popupTable" style="width:100%">
                    <thead>
                    <tr>
                        <th>{{ __('Page') }}</th>
                        <th>{{ __('Title') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Show Once') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Created By') }}</th>
                        <th>{{ __('Updated By') }}</th>
                        <th>{{ __('Updated At') }}</th>
                        <th>{{ __('Action') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($popups as $popup)
                        <tr id="row-{{ $popup->id }}">

                            <td class="font-semibold">{{ $pageKeys[$popup->page_key] ?? $popup->page_key }}</td>

                            <td>{{ $popup->title ?? '—' }}</td>

                            <td>
                                @php
                                    $typeCls = ['info'=>'b-info','success'=>'b-success','warning'=>'b-warning','danger'=>'b-danger'][$popup->type] ?? 'b-secondary';
                                @endphp
                                <span class="r-badge {{ $typeCls }}">{{ ucfirst($popup->type) }}</span>
                            </td>

                            <td>
                                <span class="r-badge {{ $popup->show_once ? 'b-warning' : 'b-secondary' }}">
                                    {{ $popup->show_once ? __('Once') : __('Always') }}
                                </span>
                            </td>

                            {{-- ✅ Status — AJAX toggle --}}
                            <td>
                                <button
                                    class="r-badge toggle-status-btn {{ $popup->is_active ? 'b-success' : 'b-secondary' }}"
                                    data-id="{{ $popup->id }}"
                                    data-url="{{ route('popup.toggle', $popup->id) }}"
                                    title="{{ $popup->is_active ? __('Click to deactivate') : __('Click to activate') }}"
                                    onclick="toggleStatus(this)">
                                    {{ $popup->is_active ? __('Active') : __('Inactive') }}
                                </button>
                            </td>

                            <td>{{ $popup->creator->name ?? '—' }}</td>
                            <td>{{ $popup->updater->name ?? '—' }}</td>

                            <td data-order="{{ $popup->updated_at->timestamp }}" style="white-space:nowrap">
                                {{ $popup->updated_at->format('d M Y, h:i A') }}
                            </td>

                            <td>
                                <div style="display:flex;gap:4px">
                                    <button onclick='openEditModal(@json($popup))'
                                            style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:5px;background:#1e3a5f;color:#fff;font-size:.75rem;font-weight:600;border:none;cursor:pointer">
                                        <i class="fa fa-edit"></i> Edit
                                    </button>
                                    <button onclick="deletePopup({{ $popup->id }}, '{{ route('popup.destroy', $popup->id) }}')"
                                            style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:5px;background:#dc2626;color:#fff;font-size:.75rem;font-weight:600;border:none;cursor:pointer">
                                        <i class="fa fa-trash"></i> Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align:center;padding:40px;color:#9ca3af">
                                <i class="fa fa-inbox" style="font-size:2rem;display:block;margin-bottom:8px"></i>
                                {{ __('No popup messages found.') }}
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="toast"></div>

    {{-- ═══════════════════ MODAL ═══════════════════ --}}
    <div id="popupModal">
        <div class="modal-box">
            <div class="flex items-center justify-between mb-5">
                <h3 id="modalTitle" class="text-base font-bold text-gray-800">Add Popup Message</h3>
                <button onclick="closeModal()" style="background:none;border:none;font-size:1.4rem;cursor:pointer;color:#9ca3af">&times;</button>
            </div>

            <form id="popupForm">
                <div style="display:flex;flex-direction:column;gap:14px">

                    <div>
                        <label class="form-label">{{ __('Page') }} <span style="color:red">*</span></label>
                        <select name="page_key" id="f_page_key" class="form-input" required>
                            <option value="">— Select page —</option>
                            @foreach($pageKeys as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="err_page_key"></div>
                    </div>

                    <div>
                        <label class="form-label">{{ __('Title') }}</label>
                        <input type="text" name="title" id="f_title" class="form-input" placeholder="Optional title...">
                        <div class="invalid-feedback" id="err_title"></div>
                    </div>

                    <div>
                        <label class="form-label">{{ __('Message') }} <span style="color:red">*</span></label>
                        <textarea name="message" id="f_message" class="form-input" rows="4"
                                  placeholder="Type your popup message..." style="resize:vertical"></textarea>
                        <div class="invalid-feedback" id="err_message"></div>
                    </div>

                    <div>
                        <label class="form-label">{{ __('Type') }} <span style="color:red">*</span></label>
                        <select name="type" id="f_type" class="form-input">
                            <option value="info">ℹ️ Info</option>
                            <option value="success">✅ Success</option>
                            <option value="warning">⚠️ Warning</option>
                            <option value="danger">🚨 Danger</option>
                        </select>
                        <div class="invalid-feedback" id="err_type"></div>
                    </div>

                    <div style="display:flex;gap:24px;flex-wrap:wrap">
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:.85rem;font-weight:600;color:#374151">
                            <input type="checkbox" id="f_is_active" style="width:16px;height:16px;cursor:pointer">
                            {{ __('Active') }}
                        </label>
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:.85rem;font-weight:600;color:#374151">
                            <input type="checkbox" id="f_show_once" style="width:16px;height:16px;cursor:pointer">
                            {{ __('Show once per user') }}
                        </label>
                    </div>

                </div>

                <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:20px">
                    <button type="button" onclick="closeModal()"
                            style="padding:8px 18px;border-radius:6px;border:1px solid #d1d5db;background:#fff;color:#374151;font-size:.85rem;font-weight:600;cursor:pointer">
                        Cancel
                    </button>
                    <button type="submit" id="saveBtn"
                            style="padding:8px 18px;border-radius:6px;border:none;background:#1e3a5f;color:#fff;font-size:.85rem;font-weight:600;cursor:pointer">
                        <i class="fa fa-save"></i> <span id="saveBtnText">Save</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

    <script>
        const CSRF  = "{{ csrf_token() }}";
        const STORE = "{{ route('popup.store') }}";

        // ── DataTable ─────────────────────────────────────────────
        let dt;
        $(document).ready(function () {
            dt = $('#popupTable').DataTable({
                dom:
                    "<'flex flex-wrap items-center justify-between gap-y-2 mb-3'B<'ml-auto'f>>" +
                    "tr" +
                    "<'flex flex-wrap items-center justify-between gap-y-2 mt-3'i<'ml-auto'p>>",
                pageLength: 15,
                lengthMenu: [[15, 25, 50, -1], [15, 25, 50, 'All']],
                order: [[7, 'desc']],
                responsive: {
                    details: { type: 'inline', renderer: $.fn.dataTable.Responsive.renderer.listHiddenNodes() }
                },
                columnDefs: [
                    { responsivePriority: 1, targets: 0 },
                    { responsivePriority: 2, targets: 4 },
                    { responsivePriority: 3, targets: 2 },
                    { responsivePriority: 4, targets: 8, orderable: false },
                    { responsivePriority: 5, targets: 7 },
                    { responsivePriority: 6, targets: 1 },
                    { responsivePriority: 7, targets: 3 },
                    { responsivePriority: 8, targets: 5 },
                    { responsivePriority: 9, targets: 6 },
                ],
                buttons: [
                    { extend: 'copy',  text: '<i class="fa fa-copy"></i> Copy',       exportOptions: { columns: ':not(:last-child)' } },
                    { extend: 'csv',   text: '<i class="fa fa-file-csv"></i> CSV',     exportOptions: { columns: ':not(:last-child)' } },
                    { extend: 'excel', text: '<i class="fa fa-file-excel"></i> Excel', exportOptions: { columns: ':not(:last-child)' } },
                    { extend: 'pdf',   text: '<i class="fa fa-file-pdf"></i> PDF',     exportOptions: { columns: ':not(:last-child)' } },
                    { extend: 'print', text: '<i class="fa fa-print"></i> Print',      exportOptions: { columns: ':not(:last-child)' } },
                ],
                language: {
                    search: '', searchPlaceholder: 'Search...',
                    lengthMenu: 'Show _MENU_',
                    info: 'Showing _START_–_END_ of _TOTAL_',
                    infoEmpty: 'No popups found',
                    emptyTable: 'No popup messages found.',
                    paginate: { first: '«', previous: '‹', next: '›', last: '»' },
                },
            });
        });

        // ── Toast ─────────────────────────────────────────────────
        let toastTimer;
        function showToast(msg, type = 'success') {
            const t = document.getElementById('toast');
            t.textContent = msg;
            t.className = `show t-${type}`;
            clearTimeout(toastTimer);
            toastTimer = setTimeout(() => { t.className = ''; }, 3000);
        }

        // ── AJAX helper ───────────────────────────────────────────
        function ajaxPost(url, body) {
            return fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': CSRF,
                    'Accept':       'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(body),
            }).then(r => r.json());
        }

        // ── Status Toggle ─────────────────────────────────────────
        function toggleStatus(btn) {
            btn.classList.add('loading');
            ajaxPost(btn.dataset.url, {})
                .then(data => {
                    if (data.success) {
                        const active = data.is_active;
                        btn.className = `r-badge toggle-status-btn ${active ? 'b-success' : 'b-secondary'}`;
                        btn.textContent = active ? 'Active' : 'Inactive';
                        btn.title = active ? 'Click to deactivate' : 'Click to activate';
                        showToast(data.message ?? 'Status updated.');
                    } else {
                        showToast(data.message ?? 'Something went wrong.', 'error');
                    }
                })
                .catch(() => showToast('Request failed.', 'error'))
                .finally(() => btn.classList.remove('loading'));
        }

        // ── Delete ────────────────────────────────────────────────
        function deletePopup(id, url) {
            if (!confirm('Delete this popup?')) return;
            ajaxPost(url, {})
                .then(data => {
                    if (data.success) {
                        const row = document.getElementById(`row-${id}`);
                        if (row) dt.row(row).remove().draw();
                        showToast(data.message ?? 'Deleted.');
                    } else {
                        showToast(data.message ?? 'Something went wrong.', 'error');
                    }
                })
                .catch(() => showToast('Request failed.', 'error'));
        }

        // ── Modal ─────────────────────────────────────────────────
        let editingId = null;

        function clearErrors() {
            document.querySelectorAll('.form-input').forEach(el => el.classList.remove('is-invalid'));
            document.querySelectorAll('.invalid-feedback').forEach(el => {
                el.style.display = 'none';
                el.textContent   = '';
            });
        }

        function showErrors(errors) {
            Object.entries(errors).forEach(([field, messages]) => {
                const input = document.querySelector(`[name="${field}"]`);
                const err   = document.getElementById(`err_${field}`);
                if (input) input.classList.add('is-invalid');
                if (err)   { err.textContent = messages[0]; err.style.display = 'block'; }
            });
        }

        function openModal() {
            editingId = null;
            clearErrors();
            document.getElementById('modalTitle').textContent  = 'Add Popup Message';
            document.getElementById('saveBtnText').textContent = 'Save';
            document.getElementById('f_page_key').value        = '';
            document.getElementById('f_title').value           = '';
            document.getElementById('f_message').value         = '';
            document.getElementById('f_type').value            = 'info';
            document.getElementById('f_is_active').checked     = true;
            document.getElementById('f_show_once').checked     = false;
            document.getElementById('popupModal').classList.add('open');
        }

        function openEditModal(popup) {
            editingId = popup.id;
            clearErrors();
            document.getElementById('modalTitle').textContent  = 'Edit Popup Message';
            document.getElementById('saveBtnText').textContent = 'Update';
            document.getElementById('f_page_key').value        = popup.page_key;
            document.getElementById('f_title').value           = popup.title ?? '';
            document.getElementById('f_message').value         = popup.message;
            document.getElementById('f_type').value            = popup.type;
            document.getElementById('f_is_active').checked     = popup.is_active == 1;
            document.getElementById('f_show_once').checked     = popup.show_once == 1;
            document.getElementById('popupModal').classList.add('open');
        }

        function closeModal() {
            document.getElementById('popupModal').classList.remove('open');
        }

        // ── Form Submit ───────────────────────────────────────────
        document.getElementById('popupForm').addEventListener('submit', function (e) {
            e.preventDefault();
            clearErrors();

            const isEdit = editingId !== null;
            // ✅ store = POST /popup-messages
            // ✅ update = POST /popup-messages/{id}/update
            const url = isEdit
                ? "{{ route('popup.update', ['popupMessage' => '__ID__']) }}".replace('__ID__', editingId)
                : STORE;

            const saveBtn = document.getElementById('saveBtn');
            saveBtn.disabled = true;
            document.getElementById('saveBtnText').textContent = 'Saving...';

            ajaxPost(url, {
                page_key:  document.getElementById('f_page_key').value,
                title:     document.getElementById('f_title').value,
                message:   document.getElementById('f_message').value,
                type:      document.getElementById('f_type').value,
                is_active: document.getElementById('f_is_active').checked ? 1 : 0,
                show_once: document.getElementById('f_show_once').checked  ? 1 : 0,
            })
                .then(data => {
                    if (data.errors) { showErrors(data.errors); return; }
                    if (data.success) {
                        closeModal();
                        showToast(data.message ?? (isEdit ? 'Updated.' : 'Created.'));
                        setTimeout(() => location.reload(), 900);
                    } else {
                        showToast(data.message ?? 'Something went wrong.', 'error');
                    }
                })
                .catch(() => showToast('Request failed.', 'error'))
                .finally(() => {
                    saveBtn.disabled = false;
                    document.getElementById('saveBtnText').textContent = isEdit ? 'Update' : 'Save';
                });
        });

        // ── Backdrop / ESC ────────────────────────────────────────
        document.getElementById('popupModal').addEventListener('click', function (e) {
            if (e.target === this) closeModal();
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeModal();
        });
    </script>
@endpush
