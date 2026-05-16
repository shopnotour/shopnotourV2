@extends('admin.layouts.app')

@push('css')
    <style>
        .form-section {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px 24px;
            margin-bottom: 20px;
        }
        .form-section-title {
            font-size: .82rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #6b7280;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }
        .flag-preview {
            font-size: 2.5rem;
            line-height: 1;
            min-width: 48px;
            text-align: center;
        }
        .passport-hint-preview {
            background: #fffbeb;
            border: 1px solid #fcd34d;
            border-radius: 6px;
            padding: 8px 12px;
            font-size: .8rem;
            color: #92400e;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb20">
            <h1 class="title-bar">
                @if(isset($country))
                    <span class="mr-2">{{ $country->flag_emoji ?? '🏳' }}</span>
                    {{ __('Edit Country') }}: {{ $country->name }}
                @else
                    {{ __('Add Country') }}
                @endif
            </h1>
            <a href="{{ route('admin.countries.index') }}" class="btn btn-secondary btn-icon">
                <i class="fa fa-arrow-left"></i> {{ __('Back to List') }}
            </a>
        </div>

        @include('admin.message')

        <form method="POST"
              action="{{ isset($country) ? route('admin.countries.update', $country->id) : route('admin.countries.store') }}">
            @csrf
            @if(isset($country))
                @method('PUT')
            @endif

            <div class="row">
                <div class="col-md-8">

                    {{-- ── Basic Info ── --}}
                    <div class="form-section">
                        <div class="form-section-title"><i class="fa fa-globe"></i> Basic Information</div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('Country Name') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="name"
                                           class="form-control @error('name') is-invalid @enderror"
                                           value="{{ old('name', $country->name ?? '') }}"
                                           placeholder="e.g. Bangladesh" required>
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>{{ __('ISO Code (2)') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="code"
                                           class="form-control @error('code') is-invalid @enderror"
                                           value="{{ old('code', $country->code ?? '') }}"
                                           placeholder="BD" maxlength="2"
                                           style="text-transform:uppercase;font-weight:700;letter-spacing:.1em"
                                           required>
                                    @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <small class="text-muted">2-letter ISO code</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>{{ __('ISO Code (3)') }}</label>
                                    <input type="text" name="code3"
                                           class="form-control @error('code3') is-invalid @enderror"
                                           value="{{ old('code3', $country->code3 ?? '') }}"
                                           placeholder="BGD" maxlength="3"
                                           style="text-transform:uppercase;font-weight:700;letter-spacing:.1em">
                                    @error('code3') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <small class="text-muted">3-letter ISO code</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('Capital') }}</label>
                                    <input type="text" name="capital"
                                           class="form-control @error('capital') is-invalid @enderror"
                                           value="{{ old('capital', $country->capital ?? '') }}"
                                           placeholder="e.g. Dhaka">
                                    @error('capital') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>{{ __('Phone Code') }}</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">+</span>
                                        </div>
                                        <input type="text" name="phone_code"
                                               class="form-control @error('phone_code') is-invalid @enderror"
                                               value="{{ old('phone_code', $country->phone_code ?? '') }}"
                                               placeholder="880">
                                    </div>
                                    @error('phone_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>{{ __('Flag Emoji') }}</label>
                                    <div class="d-flex align-items-center" style="gap:8px">
                                        <input type="text" name="flag_emoji" id="flagEmojiInput"
                                               class="form-control @error('flag_emoji') is-invalid @enderror"
                                               value="{{ old('flag_emoji', $country->flag_emoji ?? '') }}"
                                               placeholder="🇧🇩" maxlength="10">
                                        <span id="flagPreview" class="flag-preview">
                                            {{ old('flag_emoji', $country->flag_emoji ?? '🏳') }}
                                        </span>
                                    </div>
                                    @error('flag_emoji') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <small class="text-muted">Copy-paste flag emoji</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ── Passport Rules ── --}}
                    <div class="form-section">
                        <div class="form-section-title"><i class="fa fa-id-card-o"></i> Passport Validation Rules</div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>{{ __('Min Length') }}</label>
                                    <input type="number" name="passport_min"
                                           class="form-control @error('passport_min') is-invalid @enderror"
                                           value="{{ old('passport_min', $country->passport_min ?? '') }}"
                                           min="1" max="30" placeholder="e.g. 7">
                                    @error('passport_min') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>{{ __('Max Length') }}</label>
                                    <input type="number" name="passport_max"
                                           class="form-control @error('passport_max') is-invalid @enderror"
                                           value="{{ old('passport_max', $country->passport_max ?? '') }}"
                                           min="1" max="30" placeholder="e.g. 9">
                                    @error('passport_max') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('Regex Pattern') }}</label>
                                    <input type="text" name="passport_pattern"
                                           class="form-control font-monospace @error('passport_pattern') is-invalid @enderror"
                                           value="{{ old('passport_pattern', $country->passport_pattern ?? '') }}"
                                           placeholder="e.g. ^[A-Z]{2}[0-9]{7}$"
                                           style="font-family:monospace;font-size:.82rem">
                                    @error('passport_pattern') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <small class="text-muted">JavaScript-compatible regex (without slashes)</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>{{ __('Passport Hint') }}</label>
                            <input type="text" name="passport_hint"
                                   class="form-control @error('passport_hint') is-invalid @enderror"
                                   value="{{ old('passport_hint', $country->passport_hint ?? '') }}"
                                   placeholder="e.g. Must start with 2 letters followed by 7 digits (AB1234567)"
                                   id="passportHintInput">
                            @error('passport_hint') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <small class="text-muted">Shown to users during booking — describe the expected format clearly</small>
                        </div>

                        {{-- Live Preview --}}
                        <div class="passport-hint-preview mt-2" id="hintPreview" style="{{ old('passport_hint', $country->passport_hint ?? '') ? '' : 'display:none' }}">
                            <i class="fa fa-info-circle"></i>
                            <strong>User will see:</strong>
                            <span id="hintPreviewText">{{ old('passport_hint', $country->passport_hint ?? '') }}</span>
                        </div>

                        {{-- Regex Tester --}}
                        <div class="mt-3 p-3" style="background:#f0fdf4;border:1px solid #86efac;border-radius:6px">
                            <label style="font-size:.8rem;font-weight:600;color:#166534">
                                <i class="fa fa-flask"></i> Live Regex Tester
                            </label>
                            <div class="d-flex" style="gap:8px">
                                <input type="text" id="regexTestInput" class="form-control form-control-sm"
                                       placeholder="Type a passport number to test..." style="font-family:monospace">
                                <span id="regexTestResult" class="d-flex align-items-center"
                                      style="min-width:90px;font-size:.8rem;font-weight:600"></span>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="col-md-4">

                    {{-- ── Status & Save ── --}}
                    <div class="form-section">
                        <div class="form-section-title"><i class="fa fa-toggle-on"></i> Status</div>
                        <div class="form-group mb-0">
                            <div class="d-flex align-items-center" style="gap:12px">
                                <label class="mb-0" style="font-weight:600">Active</label>
                                <div class="custom-control custom-switch">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" class="custom-control-input" id="isActive"
                                           name="is_active" value="1"
                                        {{ old('is_active', $country->is_active ?? 1) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="isActive"></label>
                                </div>
                            </div>
                            <small class="text-muted">Inactive countries won't appear in dropdowns</small>
                        </div>
                    </div>

                    {{-- ── Quick Reference ── --}}
                    <div class="form-section">
                        <div class="form-section-title"><i class="fa fa-lightbulb-o"></i> Passport Pattern Examples</div>
                        <table class="table table-sm table-bordered mb-0" style="font-size:.76rem">
                            <thead style="background:#f8fafc">
                            <tr><th>Country</th><th>Pattern</th><th>Example</th></tr>
                            </thead>
                            <tbody>
                            <tr><td>BD</td><td><code>^[A-Z]{2}[0-9]{7}$</code></td><td>AB1234567</td></tr>
                            <tr><td>US</td><td><code>^[0-9]{9}$</code></td><td>123456789</td></tr>
                            <tr><td>UK</td><td><code>^[0-9]{9}$</code></td><td>123456789</td></tr>
                            <tr><td>IN</td><td><code>^[A-Z]{1}[0-9]{7}[A-Z]{1}$</code></td><td>A1234567B</td></tr>
                            <tr><td>PK</td><td><code>^[A-Z]{2}[0-9]{7}$</code></td><td>AB1234567</td></tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- ── Save Buttons ── --}}
                    <div class="d-flex flex-column" style="gap:8px">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fa fa-save"></i>
                            {{ isset($country) ? __('Update Country') : __('Save Country') }}
                        </button>
                        <a href="{{ route('admin.countries.index') }}" class="btn btn-secondary btn-block">
                            <i class="fa fa-times"></i> {{ __('Cancel') }}
                        </a>
                    </div>

                </div>
            </div>
        </form>
    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function () {

            // Flag emoji live preview
            $('#flagEmojiInput').on('input', function () {
                $('#flagPreview').text($(this).val() || '🏳');
            });

            // Passport hint live preview
            $('#passportHintInput').on('input', function () {
                var val = $(this).val().trim();
                if (val) {
                    $('#hintPreviewText').text(val);
                    $('#hintPreview').show();
                } else {
                    $('#hintPreview').hide();
                }
            });

            // ISO code auto-uppercase
            $('input[name="code"], input[name="code3"]').on('input', function () {
                var pos = this.selectionStart;
                $(this).val($(this).val().toUpperCase());
                this.setSelectionRange(pos, pos);
            });

            // Live regex tester
            $('#regexTestInput').on('input', function () {
                var testVal = $(this).val();
                var pattern = $('input[name="passport_pattern"]').val().trim();
                var result  = $('#regexTestResult');

                if (!testVal || !pattern) { result.text('').css('color', ''); return; }

                try {
                    var regex = new RegExp(pattern);
                    if (regex.test(testVal)) {
                        result.html('<i class="fa fa-check-circle"></i> Valid').css('color', '#16a34a');
                    } else {
                        result.html('<i class="fa fa-times-circle"></i> Invalid').css('color', '#dc2626');
                    }
                } catch (e) {
                    result.html('<i class="fa fa-warning"></i> Bad regex').css('color', '#d97706');
                }
            });

            $('input[name="passport_pattern"]').on('input', function () {
                $('#regexTestInput').trigger('input');
            });
        });
    </script>
@endpush
