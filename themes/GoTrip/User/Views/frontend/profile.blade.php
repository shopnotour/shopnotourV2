@extends('layouts.user')

@section('content')
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        * { font-family: 'Outfit', sans-serif; box-sizing: border-box; }

        /* Tab system — card style */
        .sn-tab-btn {
            transition: all .2s; cursor: pointer; white-space: nowrap;
            display: flex; flex-direction: column; align-items: center; gap: 6px;
            padding: 14px 20px;
            border-radius: 14px;
            border: 2px solid transparent;
            background: #f9fafb;
            color: #6b7280;
            font-size: 13px; font-weight: 600;
            text-decoration: none;
        }
        .sn-tab-btn:hover { background: #eff6ff; color: #2563eb; border-color: #bfdbfe; }
        .sn-tab-btn.active {
            background: #eff6ff; color: #2563eb;
            border-color: #2563eb;
            box-shadow: 0 2px 8px rgba(37,99,235,.15);
        }
        .sn-tab-btn .tab-icon {
            width: 36px; height: 36px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            background: #e5e7eb; transition: background .2s;
        }
        .sn-tab-btn.active .tab-icon,
        .sn-tab-btn:hover .tab-icon { background: #dbeafe; }
        .sn-tab-btn .tab-icon svg { stroke: #9ca3af; }
        .sn-tab-btn.active .tab-icon svg,
        .sn-tab-btn:hover .tab-icon svg { stroke: #2563eb; }
        .sn-tab-pane { display: none; }
        .sn-tab-pane.active { display: block; }

        /* Floating label inputs */
        .sn-field { position: relative; }
        .sn-field input,
        .sn-field textarea,
        .sn-field select {
            width: 100%;
            padding: 22px 14px 8px;
            background: #f9fafb;
            border: 1.5px solid #e5e7eb;
            border-radius: 12px;
            font-size: 14px;
            font-family: 'Outfit', sans-serif;
            color: #111827;
            outline: none;
            transition: border-color .15s, background .15s, box-shadow .15s;
            appearance: none;
        }
        .sn-field textarea { padding-top: 26px; resize: vertical; min-height: 100px; }
        .sn-field input:focus,
        .sn-field textarea:focus,
        .sn-field select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37,99,235,.1);
            background: #fff;
        }
        .sn-field label {
            position: absolute;
            left: 14px; top: 8px;
            font-size: 10px; font-weight: 700;
            color: #6b7280;
            text-transform: uppercase; letter-spacing: .06em;
            pointer-events: none;
        }
        .sn-field select { padding-top: 22px; }

        /* File upload */
        .sn-file-box {
            border: 2px dashed #d1d5db;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            transition: border-color .2s, background .2s;
            cursor: pointer;
            position: relative;
        }
        .sn-file-box:hover { border-color: #2563eb; background: #eff6ff; }
        .sn-file-box input[type=file] {
            position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%;
        }
        .sn-file-box.has-file { border-color: #22c55e; background: #f0fdf4; }

        /* Save button */
        .sn-save-btn {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 0 28px; height: 48px;
            background: #2563eb; color: #fff;
            font-size: 14px; font-weight: 700;
            border: none; border-radius: 12px; cursor: pointer;
            transition: background .15s, box-shadow .15s;
            box-shadow: 0 2px 8px rgba(37,99,235,.3);
        }
        .sn-save-btn:hover { background: #1d4ed8; box-shadow: 0 4px 16px rgba(37,99,235,.4); }

        /* Avatar */
        .sn-avatar-wrap { position: relative; width: 96px; height: 96px; flex-shrink: 0; }
        .sn-avatar-wrap img { width: 96px; height: 96px; border-radius: 50%; object-fit: cover; border: 3px solid #e5e7eb; }
        .sn-avatar-overlay {
            position: absolute; inset: 0; border-radius: 50%;
            background: rgba(0,0,0,.45);
            display: flex; align-items: center; justify-content: center;
            opacity: 0; transition: opacity .2s; cursor: pointer;
        }
        .sn-avatar-wrap:hover .sn-avatar-overlay { opacity: 1; }
        .sn-avatar-wrap input[type=file] { position: absolute; inset: 0; opacity: 0; cursor: pointer; border-radius: 50%; }

        /* Badge */
        .sn-badge {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 3px 10px; border-radius: 99px;
            font-size: 11px; font-weight: 600;
        }
        .sn-badge-blue { background: #dbeafe; color: #1d4ed8; }
        .sn-badge-green { background: #dcfce7; color: #15803d; }
        .sn-badge-yellow { background: #fef9c3; color: #a16207; }

        @keyframes snFadeUp {
            from { opacity:0; transform:translateY(10px); }
            to   { opacity:1; transform:translateY(0); }
        }
        .sn-fadein { animation: snFadeUp .35s ease both; }

        /* Mobile */
        @media (max-width: 640px) {
            .sn-tabs-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; }
            .sn-tabs-scroll::-webkit-scrollbar { display: none; }
        }
    </style>

    {{-- Page header --}}
    <div class="mb-8">
        <h1 class="text-2xl font-extrabold text-gray-900">{{ __('Settings') }}</h1>
        <p class="text-sm text-gray-500 mt-1">{{ auth()->user()->name }} &mdash; {{ __('Profile') }}</p>
    </div>

    @include('admin.message')

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden sn-fadein">

        {{-- Tab navigation --}}
        <div class="p-4 md:p-6 border-b border-gray-100 bg-gray-50">
            <div class="flex flex-wrap gap-3">
                <button class="sn-tab-btn active" onclick="snTab('personal', this)">
                    <div class="tab-icon">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </div>
                    {{ __('Personal') }}
                </button>
                <button class="sn-tab-btn" onclick="snTab('location', this)">
                    <div class="tab-icon">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    </div>
                    {{ __('Location') }}
                </button>
                @if(setting_item('vendor_enable'))
                    <button class="sn-tab-btn" onclick="snTab('agency', this)">
                        <div class="tab-icon">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/></svg>
                        </div>
                        <span class="flex items-center gap-1.5">
                    {{ __('Agency') }}
                            @if(auth()->user()->verify_submit_status == 'approved')
                                <span class="sn-badge sn-badge-green">✓</span>
                            @elseif(auth()->user()->verify_submit_status == 'pending')
                                <span class="sn-badge sn-badge-yellow">{{ __('Pending') }}</span>
                            @endif
                </span>
                    </button>
                @endif
                <a href="{{ route('user.change_password') }}" class="sn-tab-btn">
                    <div class="tab-icon">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                    </div>
                    {{ __('Password') }}
                </a>
            </div>
        </div>

        <form action="{{ route('user.profile.update') }}" method="post" enctype="multipart/form-data" class="input-has-icon">
            @csrf

            {{-- ── PERSONAL TAB ── --}}
            <div class="sn-tab-pane active p-6 md:p-8" id="sn-tab-personal">

                {{-- Avatar --}}
                <div class="flex items-center gap-5 mb-8 pb-8 border-b border-gray-100">
                    <div class="sn-avatar-wrap">
                        <img src="{{ get_file_url(old('avatar_id', $dataUser->avatar_id)) ?? $dataUser->getAvatarUrl() ?? '' }}"
                             id="sn-avatar-preview"
                             onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=2563eb&color=fff&size=96'">
                        <div class="sn-avatar-overlay">
                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M23 19a2 2 0 01-2 2H3a2 2 0 01-2-2V8a2 2 0 012-2h4l2-3h6l2 3h4a2 2 0 012 2z"/><circle cx="12" cy="13" r="4"/></svg>
                        </div>
                        <input type="file" name="avatar_file" accept="image/*" onchange="snPreviewAvatar(this)" style="position:absolute;inset:0;opacity:0;cursor:pointer;border-radius:50%;">
                    </div>
                    <div>
                        <div class="font-700 text-gray-900">{{ auth()->user()->name }}</div>
                        <div class="text-sm text-gray-500 mt-0.5">{{ auth()->user()->email }}</div>
                        <div class="text-xs text-gray-400 mt-2">{{ __('Click on avatar to change photo') }}</div>
                        <input type="hidden" name="avatar_id" value="{{ old('avatar_id', $dataUser->avatar_id) ?? '' }}">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    @if($is_vendor_access)
                        <div class="md:col-span-2 sn-field">
                            <input type="text" value="{{ old('business_name', $dataUser->business_name) }}" name="business_name">
                            <label>{{ __('Business Name') }}</label>
                        </div>
                    @endif

                    <div class="md:col-span-2 sn-field">
                        <input type="text" minlength="4" name="user_name" value="{{ old('user_name', $dataUser->user_name) }}">
                        <label>{{ __('Username') }}</label>
                    </div>

                    <div class="sn-field">
                        <input type="text" value="{{ old('first_name', $dataUser->first_name) }}" name="first_name">
                        <label>{{ __('First Name') }}</label>
                    </div>

                    <div class="sn-field">
                        <input type="text" value="{{ old('last_name', $dataUser->last_name) }}" name="last_name">
                        <label>{{ __('Last Name') }}</label>
                    </div>

                    <div class="sn-field">
                        <input type="text" name="email" value="{{ old('email', $dataUser->email) }}"
                               id="sn-email-input" readonly
                               style="background:#f3f4f6; color:#6b7280; cursor:not-allowed;">
                        <label>{{ __('Email Address') }}</label>
                        <button type="button" onclick="snEnableEmail()"
                                id="sn-email-edit-btn"
                                style="position:absolute; right:12px; top:50%; transform:translateY(-50%);
                                   background:none; border:none; cursor:pointer; font-size:11px;
                                   color:#2563eb; font-weight:600; font-family:'Outfit',sans-serif;">
                            {{ __('Change') }}
                        </button>
                    </div>
                    {{-- Email change warning --}}
                    <div id="sn-email-warning" style="display:none;" class="md:col-span-2">
                        <div class="flex items-start gap-2 bg-yellow-50 border border-yellow-200 rounded-xl px-4 py-3 text-sm text-yellow-700">
                            <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            {{ __('Changing your email will require re-verification. You will need to verify your new email to continue using your account.') }}
                        </div>
                    </div>

                    <div class="sn-field">
                        <input type="text" value="{{ old('phone', $dataUser->phone) }}" name="phone">
                        <label>{{ __('Phone Number') }}</label>
                    </div>

                    <div class="sn-field">
                        <input type="text" class="date-picker has-value"
                               value="{{ old('birthday', $dataUser->birthday ? display_date($dataUser->birthday) : '') }}"
                               name="birthday">
                        <label>{{ __('Birthday') }}</label>
                    </div>

                    <div class="md:col-span-2 sn-field">
                        <textarea rows="4" name="bio">{{ old('bio', $dataUser->bio) }}</textarea>
                        <label>{{ __('About Yourself') }}</label>
                    </div>
                </div>

                <div class="mt-6">
                    <button type="submit" class="sn-save-btn">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        {{ __('Save Changes') }}
                    </button>
                </div>
            </div>

            {{-- ── LOCATION TAB ── --}}
            <div class="sn-tab-pane p-6 md:p-8" id="sn-tab-location">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    <div class="md:col-span-2 sn-field">
                        <input type="text" value="{{ old('address', $dataUser->address) }}" name="address">
                        <label>{{ __('Address Line 1') }}</label>
                    </div>

                    <div class="md:col-span-2 sn-field">
                        <input type="text" value="{{ old('address2', $dataUser->address2) }}" name="address2">
                        <label>{{ __('Address Line 2') }}</label>
                    </div>

                    <div class="sn-field">
                        <input type="text" value="{{ old('city', $dataUser->city) }}" name="city">
                        <label>{{ __('City') }}</label>
                    </div>

                    <div class="sn-field">
                        <input type="text" value="{{ old('state', $dataUser->state) }}" name="state">
                        <label>{{ __('State / Province') }}</label>
                    </div>

                    <div class="sn-field">
                        <select name="country">
                            <option value="">{{ __('-- Select Country --') }}</option>
                            @foreach(get_country_lists() as $id => $name)
                                <option @if(old('country', $dataUser->country ?? '') == $id) selected @endif value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                        <label>{{ __('Country') }}</label>
                    </div>

                    <div class="sn-field">
                        <input type="text" value="{{ old('zip_code', $dataUser->zip_code) }}" name="zip_code">
                        <label>{{ __('ZIP / Postal Code') }}</label>
                    </div>
                </div>

                <div class="mt-6">
                    <button type="submit" class="sn-save-btn">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        {{ __('Save Changes') }}
                    </button>
                </div>
            </div>

            {{-- ── AGENCY TAB ── --}}
            @if(setting_item('vendor_enable'))
                <div class="sn-tab-pane p-6 md:p-8" id="sn-tab-agency">

                    {{-- Status banner --}}
                    @if(auth()->user()->verify_submit_status == 'approved')
                        <div class="flex items-center gap-3 bg-green-50 border border-green-200 rounded-xl px-5 py-4 mb-6">
                            <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                            <div>
                                <div class="font-700 text-green-800 text-sm">{{ __('Agency Verified') }}</div>
                                <div class="text-xs text-green-600">{{ __('Your agency has been verified and approved.') }}</div>
                            </div>
                        </div>
                    @elseif(auth()->user()->verify_submit_status == 'pending')
                        <div class="flex items-center gap-3 bg-yellow-50 border border-yellow-200 rounded-xl px-5 py-4 mb-6">
                            <svg class="w-5 h-5 text-yellow-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            <div>
                                <div class="font-700 text-yellow-800 text-sm">{{ __('Verification Pending') }}</div>
                                <div class="text-xs text-yellow-600">{{ __('Your documents are under review. We will notify you shortly.') }}</div>
                            </div>
                        </div>
                    @else
                        <div class="flex items-center gap-3 bg-blue-50 border border-blue-200 rounded-xl px-5 py-4 mb-6">
                            <svg class="w-5 h-5 text-blue-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            <div>
                                <div class="font-700 text-blue-800 text-sm">{{ __('Submit Agency Documents') }}</div>
                                <div class="text-xs text-blue-600">{{ __('Please fill in your agency details and upload required documents for verification.') }}</div>
                            </div>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                        <div class="md:col-span-2 sn-field">
                            <input type="text" value="{{ old('business_name', $dataUser->business_name) }}" name="business_name">
                            <label>{{ __('Agency / Business Name') }}</label>
                        </div>

                        <div class="sn-field">
                            <input type="text" value="{{ old('iata_number', $dataUser->iata_number) }}" name="iata_number">
                            <label>{{ __('IATA Number') }}</label>
                        </div>

                        <div class="sn-field">
                            <input type="text" value="{{ old('civil_aviation_number', $dataUser->civil_aviation_number) }}" name="civil_aviation_number">
                            <label>{{ __('Civil Aviation Number') }}</label>
                        </div>

                        <div class="md:col-span-2 sn-field">
                            <input type="text" value="{{ old('trade_license_number', $dataUser->trade_license_number) }}" name="trade_license_number">
                            <label>{{ __('Trade License Number') }}</label>
                        </div>
                    </div>

                    {{-- Document uploads --}}
                    <div class="mt-6">
                        <div class="text-sm font-700 text-gray-700 mb-4 flex items-center gap-2">
                            <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            {{ __('Required Documents') }}
                            <span class="text-xs text-gray-400 font-400">(PDF, JPG, PNG — max 5MB)</span>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                            @php
                                $agencyFiles = [
                                    ['label' => __('Trade License'),    'name' => 'trade_license_file',    'id_field' => 'trade_license_file_id'],
                                    ['label' => __('IATA Certificate'), 'name' => 'iata_file',              'id_field' => 'iata_file_id'],
                                    ['label' => __('Civil Aviation'),   'name' => 'civil_aviation_file',   'id_field' => 'civil_aviation_file_id'],
                                ];
                            @endphp

                            @foreach($agencyFiles as $af)
                                @php
                                    $fileId  = $dataUser->{$af['id_field']};
                                    $fileUrl = $fileId ? get_file_url($fileId) : null;
                                    $fileObj = $fileId ? \Modules\Media\Models\MediaFile::find($fileId) : null;
                                    $isImage = $fileObj && in_array(strtolower($fileObj->file_extension ?? ''), ['jpg','jpeg','png','gif','webp']);
                                @endphp
                                <div>
                                    <div class="text-xs font-600 text-gray-500 mb-2 uppercase tracking-wider">{{ $af['label'] }}</div>
                                    <label style="display:block; cursor:pointer; position:relative; border-radius:12px; overflow:hidden; border:2px dashed #d1d5db; transition:border-color .2s;"
                                           onmouseover="this.style.borderColor='#2563eb'"
                                           onmouseout="this.style.borderColor=this.querySelector('input').dataset.hasfile ? '#22c55e' : '#d1d5db'">

                                        {{-- Preview area --}}
                                        <div class="sn-af-preview-{{ $loop->index }}" style="min-height:120px; display:flex; flex-direction:column; align-items:center; justify-content:center; padding:16px; background:{{ $fileId ? '#f0fdf4' : '#f9fafb' }};">
                                            @if($fileId && $fileUrl)
                                                @if($isImage)
                                                    <img src="{{ $fileUrl }}"
                                                         id="sn-af-img-{{ $loop->index }}"
                                                         style="max-height:120px; max-width:100%; object-fit:contain; border-radius:8px; margin-bottom:8px;">
                                                @else
                                                    <div id="sn-af-img-{{ $loop->index }}"
                                                         style="width:48px; height:48px; background:#fee2e2; border-radius:12px; display:flex; align-items:center; justify-content:center; margin-bottom:8px;">
                                                        <svg style="width:24px;height:24px;stroke:#ef4444;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                                    </div>
                                                @endif
                                                <div style="font-size:11px; color:#16a34a; font-weight:600; margin-bottom:2px;">✓ {{ __('Uploaded') }}</div>
                                                <a href="{{ $fileUrl }}" target="_blank" onclick="event.stopPropagation()"
                                                   style="font-size:11px; color:#2563eb; text-decoration:none;">{{ __('View file') }}</a>
                                                <div style="font-size:10px; color:#9ca3af; margin-top:4px;">{{ __('Click to replace') }}</div>
                                            @else
                                                <div id="sn-af-img-{{ $loop->index }}">
                                                    <svg style="width:32px;height:32px;stroke:#9ca3af;fill:none;stroke-width:1.5;display:block;margin:0 auto 8px;" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                                </div>
                                                <div style="font-size:12px; color:#6b7280; margin-bottom:2px;">{{ __('Click to upload') }}</div>
                                                <div style="font-size:10px; color:#9ca3af;">PDF, JPG, PNG — max 5MB</div>
                                            @endif
                                        </div>

                                        <input type="file" name="{{ $af['name'] }}" accept=".pdf,.jpg,.jpeg,.png"
                                               data-hasfile="{{ $fileId ? '1' : '' }}"
                                               data-index="{{ $loop->index }}"
                                               style="position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%;"
                                               onchange="snAgencyFile(this)">
                                    </label>
                                </div>
                            @endforeach

                        </div>
                    </div>

                    <div class="mt-6">
                        <button type="submit" class="sn-save-btn">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                            {{ __('Save & Submit') }}
                        </button>
                    </div>
                </div>
            @endif

        </form>
    </div>

    {{-- Delete account --}}
    @if(!empty(setting_item('user_enable_permanently_delete')) and !is_admin())
        <div class="mt-6 bg-white rounded-2xl border border-red-100 p-6">
            <h4 class="font-700 text-red-600 mb-2 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
                {{ __('Delete Account') }}
            </h4>
            <div class="text-sm text-gray-500 mb-4">
                {!! clean(setting_item_with_lang('user_permanently_delete_content','',__('Your account will be permanently deleted. Once you delete your account, there is no going back. Please be certain.'))) !!}
            </div>
            <button onclick="document.getElementById('sn-delete-modal').classList.remove('hidden')"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-600 rounded-xl transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/></svg>
                {{ __('Delete your account') }}
            </button>
        </div>

        {{-- Delete modal --}}
        <div id="sn-delete-modal" class="hidden fixed inset-0 flex items-center justify-center z-50 px-4"
             style="background:rgba(0,0,0,.5);">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
                <h5 class="font-700 text-gray-900 text-lg mb-3">{{ __('Confirm Delete Account') }}</h5>
                <div class="text-sm text-gray-500 mb-6">
                    {!! clean(setting_item_with_lang('user_permanently_delete_content_confirm')) !!}
                </div>
                <div class="flex gap-3">
                    <button onclick="document.getElementById('sn-delete-modal').classList.add('hidden')"
                            class="flex-1 py-2.5 border border-gray-200 rounded-xl text-sm font-600 text-gray-600 hover:bg-gray-50 transition-all">
                        {{ __('Cancel') }}
                    </button>
                    <a href="{{ route('user.permanently.delete') }}"
                       class="flex-1 py-2.5 bg-red-600 hover:bg-red-700 text-white text-center rounded-xl text-sm font-600 transition-all no-underline">
                        {{ __('Yes, Delete') }}
                    </a>
                </div>
            </div>
        </div>
    @endif

    <script>
        /* Email change enable */
        function snEnableEmail() {
            var inp  = document.getElementById('sn-email-input');
            var btn  = document.getElementById('sn-email-edit-btn');
            var warn = document.getElementById('sn-email-warning');
            inp.readOnly = false;
            inp.style.background = '';
            inp.style.color = '';
            inp.style.cursor = '';
            inp.focus();
            btn.style.display = 'none';
            if(warn) warn.style.display = 'block';
        }

        /* Tab switching */
        function snTab(name, btn) {
            document.querySelectorAll('.sn-tab-btn').forEach(function(b){ b.classList.remove('active'); });
            document.querySelectorAll('.sn-tab-pane').forEach(function(p){ p.classList.remove('active'); });
            btn.classList.add('active');
            var pane = document.getElementById('sn-tab-' + name);
            if(pane) pane.classList.add('active');
        }

        /* Avatar preview */
        function snPreviewAvatar(input) {
            if(input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('sn-avatar-preview').src = e.target.result;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        /* File select feedback */
        function snFileSelect(input, boxId, nameId) {
            var box  = document.getElementById(boxId);
            var name = document.getElementById(nameId);
            if(input.files && input.files[0]) {
                name.textContent = input.files[0].name;
                box.classList.add('has-file');
            }
        }

        /* Agency file select — instant preview */
        function snAgencyFile(input) {
            if (!input.files || !input.files[0]) return;
            var file  = input.files[0];
            var index = input.dataset.index;
            var area  = document.querySelector('.sn-af-preview-' + index);
            var isImg = file.type.startsWith('image/');

            input.dataset.hasfile = '1';

            if (isImg) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    area.style.background = '#f0fdf4';
                    area.innerHTML =
                        '<img src="' + e.target.result + '" style="max-height:120px;max-width:100%;object-fit:contain;border-radius:8px;margin-bottom:8px;">' +
                        '<div style="font-size:11px;color:#16a34a;font-weight:600;">✓ ' + file.name + '</div>' +
                        '<div style="font-size:10px;color:#9ca3af;margin-top:2px;">{{ __("Save to upload") }}</div>';
                };
                reader.readAsDataURL(file);
            } else {
                area.style.background = '#f0fdf4';
                area.innerHTML =
                    '<div style="width:48px;height:48px;background:#fee2e2;border-radius:12px;display:flex;align-items:center;justify-content:center;margin-bottom:8px;">' +
                    '<svg style="width:24px;height:24px;stroke:#ef4444;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></div>' +
                    '<div style="font-size:11px;color:#16a34a;font-weight:600;">✓ ' + file.name + '</div>' +
                    '<div style="font-size:10px;color:#9ca3af;margin-top:2px;">{{ __("Save to upload") }}</div>';
            }
        }
    </script>
@endsection
