@extends('admin.layouts.app')

@section('content')
    <div class="container-fluid">

        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-bullhorn text-primary"></i> Announcement Management
                </h1>
                <p class="text-muted mb-0">Manage scrolling announcements on homepage</p>
            </div>
            <a href="{{ route('announcements.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Announcement
            </a>
        </div>
        @include('admin.message')
        <!-- Success Message -->
{{--        @if(session('success'))--}}
{{--            <div class="alert alert-success alert-dismissible fade show" role="alert">--}}
{{--                <i class="fas fa-check-circle"></i> {{ session('success') }}--}}
{{--                <button type="button" class="close" data-dismiss="alert">--}}
{{--                    <span>&times;</span>--}}
{{--                </button>--}}
{{--            </div>--}}
{{--        @endif--}}

        <!-- Announcements Table -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">All Announcements</h6>
                <div>
                    <button type="button" class="btn btn-sm btn-danger" id="bulkDeleteBtn" style="display: none;">
                        <i class="fas fa-trash"></i> Delete Selected
                    </button>
                </div>
            </div>
            <div class="card-body">

                @if($announcements->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" width="100%">
                            <thead class="thead-light">
                            <tr>
                                <th width="30">
                                    <input type="checkbox" id="selectAll">
                                </th>
                                <th width="50">Order</th>
                                <th width="60">Icon</th>
                                <th>Content</th>
                                <th width="100">Color</th>
                                <th width="80">Speed</th>
                                <th width="100">Status</th>
                                <th width="150">Schedule</th>
                                <th width="150">Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($announcements as $announcement)
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" class="announcement-checkbox" value="{{ $announcement->id }}">
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-secondary">{{ $announcement->display_order }}</span>
                                    </td>
                                    <td class="text-center" style="font-size: 24px;">
                                        {{ $announcement->icon }}
                                    </td>
                                    <td>
                                        <div class="text-truncate" style="max-width: 400px;" title="{{ $announcement->content }}">
                                            {{ $announcement->content }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{
                                            $announcement->bg_color == 'blue' ? 'primary' :
                                            ($announcement->bg_color == 'green' ? 'success' :
                                            ($announcement->bg_color == 'purple' ? 'info' :
                                            ($announcement->bg_color == 'orange' ? 'warning' : 'dark')))
                                        }}">
                                            {{ ucfirst($announcement->bg_color) }}
                                        </span>
                                    </td>
                                    <td class="text-center">{{ $announcement->scroll_speed }}s</td>
                                    <td class="text-center">
                                        <form action="{{ route('admin.announcements.toggle', $announcement) }}" method="POST" style="display: inline;">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-{{ $announcement->is_active ? 'success' : 'secondary' }}">
                                                <i class="fas fa-{{ $announcement->is_active ? 'check' : 'times' }}"></i>
                                                {{ $announcement->is_active ? 'Active' : 'Inactive' }}
                                            </button>
                                        </form>
                                    </td>
                                    <td>
                                        @if($announcement->start_date || $announcement->end_date)
                                            <small>
                                                @if($announcement->start_date)
                                                    <strong>From:</strong> {{ $announcement->start_date->format('Y-m-d') }}<br>
                                                @endif
                                                @if($announcement->end_date)
                                                    <strong>To:</strong> {{ $announcement->end_date->format('Y-m-d') }}
                                                @endif
                                            </small>
                                        @else
                                            <span class="text-muted">Always</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('announcements.edit', $announcement) }}"
                                               class="btn btn-info" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-danger"
                                                    onclick="deleteAnnouncement({{ $announcement->id }})" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>

                                        <form id="delete-form-{{ $announcement->id }}"
                                              action="{{ route('announcements.destroy', $announcement) }}"
                                              method="POST" style="display: none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-3">
                        {{ $announcements->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-bullhorn fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No announcements found. Create your first announcement!</p>
                        <a href="{{ route('announcements.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Create Announcement
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Info Cards -->
        <div class="row">
            <div class="col-md-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active Announcements</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ \Modules\User\Models\Announcement::where('is_active', true)->count() }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Announcements</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ \Modules\User\Models\Announcement::count() }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-list fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Scheduled Announcements</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ \Modules\User\Models\Announcement::whereNotNull('start_date')->orWhereNotNull('end_date')->count() }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-calendar fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('js')
    <script>
        // Select All Checkbox
        document.getElementById('selectAll').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.announcement-checkbox');
            checkboxes.forEach(checkbox => checkbox.checked = this.checked);
            toggleBulkDeleteBtn();
        });

        // Individual checkbox change
        document.querySelectorAll('.announcement-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', toggleBulkDeleteBtn);
        });

        // Toggle bulk delete button
        function toggleBulkDeleteBtn() {
            const checkedBoxes = document.querySelectorAll('.announcement-checkbox:checked');
            const bulkBtn = document.getElementById('bulkDeleteBtn');
            bulkBtn.style.display = checkedBoxes.length > 0 ? 'inline-block' : 'none';
        }

        // Bulk Delete
        document.getElementById('bulkDeleteBtn')?.addEventListener('click', function() {
            const checkedBoxes = document.querySelectorAll('.announcement-checkbox:checked');
            const ids = Array.from(checkedBoxes).map(cb => cb.value);

            if (confirm(`Are you sure you want to delete ${ids.length} announcement(s)?`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("admin.announcements.bulk-delete") }}';

                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = '{{ csrf_token() }}';
                form.appendChild(csrfInput);

                const idsInput = document.createElement('input');
                idsInput.type = 'hidden';
                idsInput.name = 'ids';
                idsInput.value = JSON.stringify(ids);
                form.appendChild(idsInput);

                document.body.appendChild(form);
                form.submit();
            }
        });

        // Delete single announcement
        function deleteAnnouncement(id) {
            if (confirm('Are you sure you want to delete this announcement?')) {
                document.getElementById('delete-form-' + id).submit();
            }
        }
    </script>
@endpush
