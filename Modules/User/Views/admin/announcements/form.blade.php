@extends('admin.layouts.app')

@section('content')
    <div class="container-fluid">

        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-{{ isset($announcement) ? 'edit' : 'plus' }}"></i>
                    {{ isset($announcement) ? 'Edit' : 'Create' }} Announcement
                </h1>
            </div>
            <a href="{{ route('announcements.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>

        @include('admin.message')
        <!-- Form Card -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Announcement Details</h6>
                    </div>
                    <div class="card-body">

                        <form action="{{ isset($announcement) ? route('announcements.update', $announcement) : route('announcements.store') }}"
                              method="POST">
                            @csrf
                            @if(isset($announcement))
                                @method('PUT')
                            @endif

                            <!-- Content -->
                            <div class="form-group">
                                <label for="content">Announcement Content <span class="text-danger">*</span></label>
                                <textarea name="content" id="content" rows="4"
                                          class="form-control @error('content') is-invalid @enderror"
                                          placeholder="Enter announcement text..."
                                          maxlength="500" required>{{ old('content', $announcement->content ?? '') }}</textarea>
                                <small class="form-text text-muted">
                                    <span id="charCount">0</span>/500 characters
                                </small>
                                @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Icon -->
                            <div class="form-group">
                                <label for="icon">Icon (Emoji)</label>
                                <input type="text" name="icon" id="icon"
                                       class="form-control @error('icon') is-invalid @enderror"
                                       value="{{ old('icon', $announcement->icon ?? '🌟') }}"
                                       placeholder="🌟" maxlength="10">
                                <small class="form-text text-muted">Add an emoji icon (e.g., 🌟 ✈️ 🎊)</small>
                                @error('icon')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <!-- Background Color -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="bg_color">Background Color <span class="text-danger">*</span></label>
                                        <select name="bg_color" id="bg_color"
                                                class="form-control @error('bg_color') is-invalid @enderror" required>
                                            <option value="blue" {{ old('bg_color', $announcement->bg_color ?? 'blue') == 'blue' ? 'selected' : '' }}>Blue (Default)</option>
                                            <option value="green" {{ old('bg_color', $announcement->bg_color ?? '') == 'green' ? 'selected' : '' }}>Green</option>
                                            <option value="purple" {{ old('bg_color', $announcement->bg_color ?? '') == 'purple' ? 'selected' : '' }}>Purple</option>
                                            <option value="orange" {{ old('bg_color', $announcement->bg_color ?? '') == 'orange' ? 'selected' : '' }}>Orange</option>
                                            <option value="dark" {{ old('bg_color', $announcement->bg_color ?? '') == 'dark' ? 'selected' : '' }}>Dark</option>
                                        </select>
                                        @error('bg_color')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Scroll Speed -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="scroll_speed">Scroll Speed (seconds) <span class="text-danger">*</span></label>
                                        <input type="number" name="scroll_speed" id="scroll_speed"
                                               class="form-control @error('scroll_speed') is-invalid @enderror"
                                               value="{{ old('scroll_speed', $announcement->scroll_speed ?? 40) }}"
                                               min="10" max="100" required>
                                        <small class="form-text text-muted">Lower = Faster (10-100 seconds)</small>
                                        @error('scroll_speed')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Display Order -->
                            <div class="form-group">
                                <label for="display_order">Display Order <span class="text-danger">*</span></label>
                                <input type="number" name="display_order" id="display_order"
                                       class="form-control @error('display_order') is-invalid @enderror"
                                       value="{{ old('display_order', $announcement->display_order ?? 0) }}"
                                       min="0" required>
                                <small class="form-text text-muted">Lower numbers appear first (0 = highest priority)</small>
                                @error('display_order')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <!-- Start Date -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="start_date">Start Date (Optional)</label>
                                        <input type="datetime-local" name="start_date" id="start_date"
                                               class="form-control @error('start_date') is-invalid @enderror"
                                               value="{{ old('start_date', isset($announcement) && $announcement->start_date ? $announcement->start_date->format('Y-m-d\TH:i') : '') }}">
                                        <small class="form-text text-muted">Leave empty to show immediately</small>
                                        @error('start_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- End Date -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="end_date">End Date (Optional)</label>
                                        <input type="datetime-local" name="end_date" id="end_date"
                                               class="form-control @error('end_date') is-invalid @enderror"
                                               value="{{ old('end_date', isset($announcement) && $announcement->end_date ? $announcement->end_date->format('Y-m-d\TH:i') : '') }}">
                                        <small class="form-text text-muted">Leave empty to show indefinitely</small>
                                        @error('end_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Active Status -->
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="is_active" name="is_active"
                                        {{ old('is_active', $announcement->is_active ?? true) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_active">
                                        <strong>Active</strong> (Display this announcement)
                                    </label>
                                </div>
                            </div>

                            <!-- Preview -->
                            <div class="form-group">
                                <label>Preview:</label>
                                <div id="preview" class="p-3 text-white text-center"
                                     style="background: linear-gradient(90deg, #1e3c72 0%, #2a5298 50%, #1e3c72 100%); border-radius: 8px;">
                                    <span id="previewIcon">🌟</span>
                                    <span id="previewContent">Your announcement will appear here...</span>
                                </div>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> {{ isset($announcement) ? 'Update' : 'Create' }} Announcement
                                </button>
                                <a href="{{ route('announcements.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Help Card -->
            <div class="col-lg-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-info">
                            <i class="fas fa-info-circle"></i> Help & Tips
                        </h6>
                    </div>
                    <div class="card-body">
                        <h6>Content Guidelines:</h6>
                        <ul class="small">
                            <li>Keep it short and engaging (max 500 chars)</li>
                            <li>Use emojis to attract attention</li>
                            <li>Include call-to-action if needed</li>
                            <li>Test different scroll speeds</li>
                        </ul>

                        <h6 class="mt-3">Color Schemes:</h6>
                        <div class="small">
                            <span class="badge badge-primary">Blue</span> Professional<br>
                            <span class="badge badge-success">Green</span> Eco/Nature<br>
                            <span class="badge badge-info">Purple</span> Premium<br>
                            <span class="badge badge-warning">Orange</span> Energetic<br>
                            <span class="badge badge-dark">Dark</span> Elegant
                        </div>

                        <h6 class="mt-3">Common Emojis:</h6>
                        <div class="small">
                            🌟 ✈️ 🎊 💼 📞 ✉️ 🎉 🔥 ⚡ 🚀 🏆 💯 ⭐ 🌍 🎯
                        </div>
                        <small class="text-muted">Click to copy and paste</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('js')
    <script>
        // Character counter
        const contentField = document.getElementById('content');
        const charCount = document.getElementById('charCount');

        function updateCharCount() {
            charCount.textContent = contentField.value.length;
        }

        contentField.addEventListener('input', updateCharCount);
        updateCharCount();

        // Live Preview
        const iconField = document.getElementById('icon');
        const bgColorField = document.getElementById('bg_color');
        const previewIcon = document.getElementById('previewIcon');
        const previewContent = document.getElementById('previewContent');
        const previewDiv = document.getElementById('preview');

        const gradients = {
            blue: 'linear-gradient(90deg, #1e3c72 0%, #2a5298 50%, #1e3c72 100%)',
            green: 'linear-gradient(90deg, #134e5e 0%, #71b280 50%, #134e5e 100%)',
            purple: 'linear-gradient(90deg, #667eea 0%, #764ba2 50%, #667eea 100%)',
            orange: 'linear-gradient(90deg, #f12711 0%, #f5af19 50%, #f12711 100%)',
            dark: 'linear-gradient(90deg, #232526 0%, #414345 50%, #232526 100%)'
        };

        function updatePreview() {
            previewIcon.textContent = iconField.value || '🌟';
            previewContent.textContent = contentField.value || 'Your announcement will appear here...';
            previewDiv.style.background = gradients[bgColorField.value] || gradients.blue;
        }

        contentField.addEventListener('input', updatePreview);
        iconField.addEventListener('input', updatePreview);
        bgColorField.addEventListener('change', updatePreview);

        // Initialize preview
        updatePreview();
    </script>
@endpush
