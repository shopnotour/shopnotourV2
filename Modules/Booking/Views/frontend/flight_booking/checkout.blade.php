@extends('layouts.app')

@push('css')
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link href="{{ asset('module/booking/css/checkout.css?_ver='.config('app.asset_version')) }}" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --blue:    #1d4ed8;
            --blue2:   #1e3a8a;
            --blue-lt: #eff6ff;
            --green:   #059669;
            --red:     #dc2626;
            --amber:   #d97706;
            --border:  #e2e8f0;
            --bg:      #f0f4f8;
            --card:    #ffffff;
            --text:    #0f172a;
            --muted:   #64748b;
            --radius:  14px;
        }
        body { font-family: 'DM Sans', sans-serif; background: var(--bg); color: var(--text); }

        .co-wrap   { min-height: 100vh; padding: 90px 0 60px; }
        .co-inner  { max-width: 1180px; margin: 0 auto; padding: 0 16px; }
        .co-grid   { display: grid; grid-template-columns: 1fr 360px; gap: 20px; align-items: start; }

        .co-header {
            background: linear-gradient(135deg, var(--blue2), var(--blue));
            border-radius: 18px;
            padding: 18px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 20px;
            box-shadow: 0 8px 24px rgba(29,78,216,.25);
        }
        .co-hd-left  { display: flex; align-items: center; gap: 14px; }
        .co-back-btn {
            width: 38px; height: 38px; border-radius: 10px; display: flex;
            align-items: center; justify-content: center; color: #fff;
            text-decoration: none; font-size: 16px;
            background: rgba(255,255,255,.15); border: 1px solid rgba(255,255,255,.25);
            transition: background .2s;
        }
        .co-back-btn:hover { background: rgba(255,255,255,.25); }
        .co-hd-title { font-size: 18px; font-weight: 800; color: #fff; }
        .co-hd-sub   { font-size: 11px; color: rgba(255,255,255,.7); margin-top: 2px; }

        .co-timer-wrap  { display: flex; align-items: center; gap: 12px; }
        .co-timer-icon  { color: rgba(255,255,255,.75); font-size: 20px; }
        .co-timer-val   { font-size: 28px; font-weight: 800; color: #fff; font-variant-numeric: tabular-nums; line-height: 1; min-width: 80px; }
        .co-timer-label { font-size: 9px; text-transform: uppercase; letter-spacing: .1em; color: rgba(255,255,255,.55); margin-top: 2px; }
        .co-timer-warn  {
            display: none; background: rgba(255,255,255,.15);
            border: 1px solid rgba(255,255,255,.3); border-radius: 8px;
            padding: 8px 12px; font-size: 11px; font-weight: 600; color: #fff; text-align: center;
        }
        @keyframes pulse-red { 0%,100% { opacity:1; transform:scale(1); } 50% { opacity:.85; transform:scale(1.01); } }
        .timer-critical { animation: pulse-red 1s infinite; }

        .co-card {
            background: var(--card);
            border-radius: var(--radius);
            border: 1px solid var(--border);
            box-shadow: 0 1px 6px rgba(0,0,0,.05);
            margin-bottom: 14px;
        }
        .co-card-head {
            display: flex; align-items: center; gap: 12px;
            padding: 18px 20px 14px;
            border-bottom: 1px solid var(--border);
        }
        .co-card-icon {
            width: 38px; height: 38px; border-radius: 10px;
            background: var(--blue-lt); color: var(--blue);
            display: flex; align-items: center; justify-content: center;
            font-size: 15px; flex-shrink: 0;
        }
        .co-card-title { font-size: 15px; font-weight: 700; color: var(--text); }
        .co-card-sub   { font-size: 11px; color: var(--muted); margin-top: 1px; }
        .co-card-body  { padding: 18px 20px; }

        .co-alert {
            display: flex; align-items: flex-start; gap: 10px;
            padding: 12px 16px; border-radius: 10px;
            font-size: 13px; margin-bottom: 14px;
        }
        .co-alert i  { flex-shrink: 0; margin-top: 1px; }
        .co-alert-err  { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }
        .co-alert-ok   { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; }
        .co-alert-warn { background: #fffbeb; border: 1px solid #fde68a; color: #92400e; }
        .co-alert-info { background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; }

        .f-group  { display: flex; flex-direction: column; gap: 5px; }
        .f-label  {
            font-size: 10.5px; font-weight: 700; color: var(--muted);
            text-transform: uppercase; letter-spacing: .06em;
        }
        .f-label .req { color: var(--red); margin-left: 2px; }
        .f-hint   { font-size: 10px; color: #94a3b8; margin-top: 3px; }

        .f-input {
            width: 100%; padding: 10px 14px;
            border: 1.5px solid var(--border); border-radius: 10px;
            font-size: 14px; font-family: 'DM Sans', sans-serif;
            color: var(--text); background: #f8fafc;
            outline: none; transition: border-color .2s, box-shadow .2s, background .2s;
            -webkit-appearance: none;
        }
        .f-input::placeholder { color: #94a3b8; font-weight: 400; }
        .f-input:focus {
            border-color: var(--blue);
            background: #fff;
            box-shadow: 0 0 0 3px rgba(29,78,216,.08);
        }
        .f-input.valid   { border-color: var(--green); background: #f0fdf4; }
        .f-input.invalid { border-color: var(--red);   background: #fef2f2; }

        .upper { text-transform: uppercase; }
        .upper::placeholder { text-transform: none; }

        .f-err { font-size: 10.5px; color: var(--red); margin-top: 3px; display: none; }
        .f-err.show { display: block; }
        .f-ok  { font-size: 10.5px; color: var(--green); margin-top: 3px; display: none; }
        .f-ok.show  { display: block; }

        .f-icon-wrap { position: relative; }
        .f-icon-wrap .f-input { padding-right: 38px; }
        .f-icon-wrap .f-icon  {
            position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
            color: #94a3b8; font-size: 13px; pointer-events: none;
        }

        .f-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px 14px; }
        .f-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px 14px; }
        .f-col-2  { grid-column: span 2; }

        .pax-hd { display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; border-bottom: 1px solid var(--border); }
        .pax-hd-left { display: flex; align-items: center; gap: 12px; }
        .pax-num {
            width: 40px; height: 40px; border-radius: 11px;
            display: flex; align-items: center; justify-content: center;
            font-size: 16px; font-weight: 800; color: #fff; flex-shrink: 0;
        }
        .pax-name  { font-size: 15px; font-weight: 700; color: var(--text); }
        .pax-age   { font-size: 11px; color: var(--muted); margin-top: 1px; }
        .pax-tag   { font-size: 10px; font-weight: 700; padding: 3px 10px; border-radius: 999px; }

        /* ── Custom Country Select ── */
        .cs-wrap   { position: relative; }
        .cs-input-row {
            display: flex; align-items: center; gap: 10px;
            padding: 9px 14px;
            border: 1.5px solid var(--border); border-radius: 10px;
            background: #f8fafc; cursor: pointer;
            transition: border-color .2s, box-shadow .2s, background .2s;
            min-height: 44px;
        }
        .cs-input-row:focus-within,
        .cs-input-row.open {
            border-color: var(--blue);
            background: #fff;
            box-shadow: 0 0 0 3px rgba(29,78,216,.08);
        }
        .cs-flag   { font-size: 20px; line-height: 1; flex-shrink: 0; width: 26px; text-align: center; }
        .cs-text   { flex: 1; font-size: 14px; color: var(--text); background: transparent; border: none; outline: none; font-family: 'DM Sans', sans-serif; }
        .cs-text::placeholder { color: #94a3b8; }
        .cs-arrow  { color: #94a3b8; font-size: 11px; flex-shrink: 0; transition: transform .2s; }
        .cs-input-row.open .cs-arrow { transform: rotate(180deg); }

        .cs-drop {
            position: absolute; top: calc(100% + 5px); left: 0; right: 0;
            background: #fff; border: 1.5px solid var(--border); border-radius: 12px;
            box-shadow: 0 8px 28px rgba(0,0,0,.12);
            z-index: 100002; display: none; overflow: hidden;
        }
        .cs-drop.open { display: block; }

        .cs-search-wrap {
            padding: 10px; border-bottom: 1px solid #f1f5f9;
            position: relative;
        }
        .cs-search {
            width: 100%; padding: 8px 12px 8px 34px;
            border: 1.5px solid var(--border); border-radius: 8px;
            font-size: 13px; font-family: 'DM Sans', sans-serif;
            outline: none; background: #f8fafc;
        }
        .cs-search:focus { border-color: var(--blue); background: #fff; }
        .cs-search-icon {
            position: absolute; left: 20px; top: 50%; transform: translateY(-50%);
            color: #94a3b8; font-size: 12px;
        }

        .cs-list {
            max-height: 220px; overflow-y: auto;
            padding: 4px 0;
            scrollbar-width: thin; scrollbar-color: #cbd5e1 transparent;
        }
        .cs-list::-webkit-scrollbar { width: 4px; }
        .cs-list::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 2px; }

        .cs-opt {
            display: flex; align-items: center; gap: 10px;
            padding: 9px 14px; cursor: pointer;
            font-size: 13px; color: var(--text);
            transition: background .1s;
        }
        .cs-opt:hover   { background: var(--blue-lt); }
        .cs-opt.active  { background: var(--blue-lt); color: var(--blue); font-weight: 600; }
        .cs-opt.hidden  { display: none; }
        .cs-opt-flag    { font-size: 18px; width: 24px; text-align: center; }
        .cs-opt-name    { flex: 1; }
        .cs-opt-code    { font-size: 11px; color: var(--muted); font-family: 'DM Mono', monospace; }
        .cs-empty { padding: 18px; text-align: center; color: var(--muted); font-size: 13px; display: none; }
        .cs-empty.show { display: block; }
        .cs-real { display: none !important; }

        /* ── Date picker ── */
        .dp-wrap    { position: relative; }
        .dp-display {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 14px;
            border: 1.5px solid var(--border); border-radius: 10px;
            background: #f8fafc; cursor: pointer; min-height: 44px;
            transition: border-color .2s, box-shadow .2s, background .2s;
            user-select: none;
        }
        .dp-display:hover { border-color: #a5b4fc; }
        .dp-display.open, .dp-display.filled {
            border-color: var(--blue); background: #fff;
            box-shadow: 0 0 0 3px rgba(29,78,216,.08);
        }
        .dp-display.valid  { border-color: var(--green); background: #f0fdf4; box-shadow: none; }
        .dp-display.invalid { border-color: var(--red); background: #fef2f2; box-shadow: none; }
        .dp-icon    { color: #94a3b8; font-size: 13px; }
        .dp-val     { flex: 1; font-size: 14px; color: var(--text); }
        .dp-ph      { flex: 1; font-size: 14px; color: #94a3b8; }
        .dp-clear   { color: #94a3b8; font-size: 11px; padding: 2px 4px; display: none; }
        .dp-display.filled .dp-clear { display: block; }

        .dp-cal {
            position: fixed;
            z-index: 999999;
            background: #fff;
            border: 1.5px solid var(--border);
            border-radius: 14px;
            box-shadow: 0 8px 28px rgba(0,0,0,.13);
            width: 300px;
            display: none;
            overflow: hidden;
        }
        .dp-cal.open { display: block; }

        .dp-nav {
            display: flex; align-items: center; gap: 8px;
            padding: 12px 14px 8px; background: #f8fafc;
            border-bottom: 1px solid var(--border);
        }
        .dp-nav-btn {
            width: 30px; height: 30px; border-radius: 8px;
            border: 1.5px solid var(--border); background: #fff;
            color: var(--muted); font-size: 11px; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: all .15s; flex-shrink: 0;
        }
        .dp-nav-btn:hover { border-color: var(--blue); color: var(--blue); background: var(--blue-lt); }
        .dp-nav-sel { display: flex; gap: 6px; flex: 1; justify-content: center; }
        .dp-nav-sel select {
            border: 1.5px solid var(--border); border-radius: 8px;
            padding: 5px 8px; font-size: 12px; font-weight: 700;
            font-family: 'DM Sans', sans-serif; color: var(--text);
            background: #fff; cursor: pointer; outline: none;
            -webkit-appearance: none; text-align: center;
        }
        .dp-nav-sel select:focus { border-color: var(--blue); }

        .dp-wdays {
            display: grid; grid-template-columns: repeat(7, 1fr);
            padding: 6px 10px 2px;
        }
        .dp-wdays span { text-align: center; font-size: 10px; font-weight: 700; color: var(--muted); padding: 3px 0; }

        .dp-days {
            display: grid; grid-template-columns: repeat(7, 1fr);
            gap: 1px; padding: 0 10px 10px;
        }
        .dp-day {
            aspect-ratio: 1; display: flex; align-items: center; justify-content: center;
            font-size: 12.5px; border: none; background: transparent;
            border-radius: 7px; cursor: pointer; color: var(--text); font-family: 'DM Sans', sans-serif;
            transition: all .1s; -webkit-tap-highlight-color: transparent;
        }
        .dp-day:hover:not(:disabled):not(.sel):not(.blank) { background: var(--blue-lt); color: var(--blue); }
        .dp-day.today  { font-weight: 700; color: var(--blue); border: 1.5px solid var(--blue); }
        .dp-day.sel    { background: var(--blue) !important; color: #fff !important; font-weight: 700; }
        .dp-day.blank  { cursor: default; }
        .dp-day:disabled { color: #cbd5e1; cursor: not-allowed; background: transparent !important; }

        .dp-foot {
            display: flex; gap: 6px; padding: 8px 10px 10px;
            border-top: 1px solid var(--border); background: #f8fafc;
        }
        .dp-today-btn {
            flex: 1; padding: 7px; border: 1.5px solid var(--border);
            border-radius: 8px; background: #fff; font-size: 12px;
            font-weight: 600; font-family: 'DM Sans', sans-serif;
            color: var(--text); cursor: pointer; transition: all .15s;
        }
        .dp-today-btn:hover { background: var(--blue-lt); border-color: var(--blue); color: var(--blue); }
        .dp-done-btn {
            flex: 2; padding: 7px; border: none; border-radius: 8px;
            background: var(--blue); color: #fff; font-size: 12px;
            font-weight: 700; font-family: 'DM Sans', sans-serif; cursor: pointer;
        }
        @media (max-width: 600px) {
            .dp-cal {
                position: fixed; bottom: 0; left: 0; right: 0; top: auto;
                width: 100%; border-radius: 20px 20px 0 0;
                box-shadow: 0 -6px 32px rgba(0,0,0,.2);
                z-index: 100001;
            }
        }
        .dp-backdrop {
            display: none;
            position: fixed; inset: 0;
            background: rgba(0,0,0,.45);
            z-index: 99998;
        }
        @media (max-width: 600px) { .dp-backdrop.show { display: block; } }

        /* ── Visa required badge ── */
        .visa-required-badge {
            display: inline-flex; align-items: center; gap: 5px;
            background: #fef2f2; border: 1px solid #fecaca;
            color: #991b1b; font-size: 10.5px; font-weight: 700;
            padding: 4px 10px; border-radius: 999px; margin-bottom: 10px;
        }

        /* ── Sidebar ── */
        .sid-flight {
            background: #fff; border-radius: var(--radius);
            border: 1px solid var(--border);
            box-shadow: 0 1px 6px rgba(0,0,0,.05);
            overflow: hidden; margin-bottom: 12px;
        }
        .sid-flight-head {
            padding: 16px 18px;
            background: linear-gradient(135deg, var(--blue2), var(--blue));
            color: #fff;
        }
        .sid-fl-top  { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; }
        .sid-fl-air  { display: flex; align-items: center; gap: 8px; }
        .sid-fl-logo { width: 28px; height: 28px; object-fit: contain; border-radius: 6px; padding: 2px; background: rgba(255,255,255,.15); }
        .sid-fl-name { font-size: 13px; font-weight: 600; color: rgba(255,255,255,.9); }
        .sid-fl-badge { font-size: 10px; font-weight: 700; padding: 3px 10px; border-radius: 999px; }
        .sid-fl-route { display: flex; align-items: center; gap: 8px; }
        .sid-iata { font-size: 26px; font-weight: 800; line-height: 1; }
        .sid-iata-sub { font-size: 11px; color: rgba(255,255,255,.65); margin-top: 2px; }
        .sid-mid  { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 4px; }
        .sid-dur  { font-size: 11px; font-weight: 600; color: rgba(255,255,255,.8); }
        .sid-line { width: 100%; height: 1.5px; background: rgba(255,255,255,.35); position: relative; }
        .sid-line::after {
            content: '✈'; position: absolute; left: 50%; top: 50%;
            transform: translate(-50%,-50%);
            background: var(--blue); padding: 0 5px; font-size: 13px;
        }
        .sid-stop { font-size: 10px; color: rgba(255,255,255,.6); margin-top: 2px; }

        .sid-price { background: #fff; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: 0 1px 6px rgba(0,0,0,.05); overflow: hidden; margin-bottom: 12px; }
        .sid-price-head { padding: 14px 18px; border-bottom: 1px solid var(--border); font-size: 13px; font-weight: 700; color: var(--text); display: flex; align-items: center; gap: 8px; }

        .pr-row { display: flex; justify-content: space-between; align-items: center; padding: 8px 18px; border-bottom: 1px dashed #f1f5f9; font-size: 13px; }
        .pr-row:last-child { border-bottom: none; }
        .pr-lbl   { color: var(--muted); }
        .pr-val   { font-weight: 600; color: var(--text); }
        .pr-sub   { background: #f8fafc; font-weight: 700; border-top: 1px solid var(--border); border-bottom: 1px solid var(--border); }
        .pr-sub .pr-lbl, .pr-sub .pr-val { color: var(--text); }
        .pr-disc .pr-lbl, .pr-disc .pr-val { color: var(--green); }
        .pr-total { border-top: 2px solid var(--border); padding-top: 12px; border-bottom: none; }
        .pr-total .pr-lbl { font-size: 14px; font-weight: 700; color: var(--text); }
        .pr-total .pr-val { font-size: 22px; font-weight: 800; color: var(--blue); }

        .sid-meta { padding: 10px 18px; display: flex; flex-direction: column; gap: 6px; }
        .sid-meta-row { display: flex; align-items: center; gap: 7px; font-size: 11.5px; color: var(--muted); }

        .co-submit {
            width: 100%; padding: 15px; border: none; border-radius: 12px;
            background: linear-gradient(135deg, var(--blue2), var(--blue));
            color: #fff; font-size: 15px; font-weight: 700;
            font-family: 'DM Sans', sans-serif;
            cursor: pointer; transition: all .2s;
            display: flex; align-items: center; justify-content: center; gap: 10px;
            box-shadow: 0 4px 16px rgba(29,78,216,.3);
        }
        .co-submit:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(29,78,216,.4); }
        .co-submit:disabled { opacity: .7; transform: none; cursor: not-allowed; }
        .co-ssl { text-align: center; margin-top: 10px; font-size: 11px; color: var(--muted); display: flex; align-items: center; justify-content: center; gap: 5px; }

        .f-file {
            width: 100%; padding: 9px 14px; border: 1.5px dashed var(--border);
            border-radius: 10px; font-size: 13px; font-family: 'DM Sans', sans-serif;
            background: #f8fafc; cursor: pointer; color: var(--muted);
            transition: border-color .2s;
        }
        .f-file:hover { border-color: var(--blue); }
        .f-file.required-doc { border-color: #fca5a5; background: #fef2f2; }

        .pax-section-title {
            font-size: 11px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .08em; color: var(--muted);
            padding: 14px 20px 8px;
            display: flex; align-items: center; gap: 8px;
        }
        .pax-section-title::after { content: ''; flex: 1; height: 1px; background: var(--border); }
        .pax-section-title::before { content: ''; flex: 1; height: 1px; background: var(--border); display: none; }

        /* meal preference */
        .meal-opts { display: flex; flex-wrap: wrap; gap: 6px; }
        .meal-opt  {
            display: flex; align-items: center; gap: 6px;
            padding: 6px 12px; border: 1.5px solid var(--border);
            border-radius: 8px; cursor: pointer; font-size: 12px; font-weight: 500;
            color: var(--muted); background: #f8fafc; transition: all .15s;
            user-select: none;
        }
        .meal-opt input { display: none; }
        .meal-opt:hover { border-color: var(--blue); color: var(--blue); background: var(--blue-lt); }
        .meal-opt.selected { border-color: var(--blue); color: var(--blue); background: var(--blue-lt); font-weight: 700; }

        /* special assistance checkbox */
        .assist-row {
            display: flex; align-items: center; gap: 8px;
            padding: 10px 14px; border: 1.5px solid var(--border);
            border-radius: 10px; cursor: pointer; background: #f8fafc;
            transition: all .15s;
        }
        .assist-row:hover { border-color: var(--blue); background: var(--blue-lt); }
        .assist-row input[type=checkbox] { width: 16px; height: 16px; accent-color: var(--blue); cursor: pointer; }
        .assist-row-label { font-size: 13px; color: var(--text); }
        .assist-row-sub   { font-size: 10.5px; color: var(--muted); margin-top: 1px; }

        @media (max-width: 1024px) {
            .co-grid { grid-template-columns: 1fr; }
            .co-sidebar { order: -1; }
            .sid-flight-group { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        }
        @media (max-width: 768px) {
            .co-inner  { padding: 0 12px; }
            .co-header { padding: 14px 16px; border-radius: 14px; gap: 12px; }
            .co-hd-title { font-size: 16px; }
            .co-timer-val { font-size: 24px; }
            .f-grid-2 { grid-template-columns: 1fr; gap: 10px; }
            .f-grid-3 { grid-template-columns: 1fr 1fr; gap: 10px; }
            .f-col-2  { grid-column: span 1; }
            .co-card-body { padding: 14px 14px; }
            .pax-section-title { padding: 12px 14px 6px; }
            .pr-total .pr-val { font-size: 20px; }
        }
        @media (max-width: 480px) {
            .f-grid-3 { grid-template-columns: 1fr; }
            .co-header { flex-direction: column; align-items: flex-start; }
            .co-timer-wrap { align-self: flex-end; }
            .sid-flight-group { grid-template-columns: 1fr; }
        }
    </style>
@endpush

@section('content')
    @php
        $adults         = $searchParams['adults']   ?? 1;
        $children       = $searchParams['children'] ?? 0;
        $infants        = $searchParams['infants']  ?? 0;
        $passengerIndex = 1;

        // ── International / Domestic detect ──
        $isLocalFlight = collect($selectedFlight['legs'] ?? [])
            ->every(fn($l) =>
                ($l['departure']['country'] ?? '') === 'BD' &&
                ($l['arrival']['country']   ?? '') === 'BD'
            );

        // One-way international → visa mandatory
        $tripType       = $searchParams['trip_type'] ?? 'oneway';
        $isOneWay       = $tripType === 'oneway';
        $visaMandatory  = !$isLocalFlight && $isOneWay;

        $totalPax      = $adults + $children + $infants;
        $today         = now()->format('Y-m-d');
        $minExpiry     = now()->addMonths(6)->format('Y-m-d');
        $departureDate = $selectedFlight['legs'][0]['departure']['date'] ?? $today;

        // Price breakdown
        $baseFare  = $selectedFlight['price']['api_base_fare'] ?? 0;
        $tax       = $selectedFlight['price']['api_tax'] ?? 0;
        $grossFare = $baseFare + $tax;
        $ait       = $selectedFlight['charges_details']['ait_amount'] ?? 0;
        $svc       = $selectedFlight['charges_details']['service_charge'] ?? 0;
        $subtotal  = $grossFare + $ait + $svc;
        $segDisc   = $selectedFlight['charges_details']['segment_discount_total'] ?? 0;
        $fltDisc   = $selectedFlight['flight_discount_details']['flight_discount_amount'] ?? 0;
        $total     = $selectedFlight['price']['total'] ?? 0;

        // ── Session draft: pre-fill passenger data ──
        // Priority: old() (validation fail) → session draft → empty
        $draft = session('passenger_draft', []);
        $contactEmail = old('contact_email', $draft['contact_email'] ?? auth()->user()->email ?? '');
        $contactPhone = old('contact_phone', $draft['contact_phone'] ?? auth()->user()->phone ?? '');
        $emergencyName  = old('emergency_contact_name',  $draft['emergency_contact_name']  ?? '');
        $emergencyPhone = old('emergency_contact_phone', $draft['emergency_contact_phone'] ?? '');

        $paxTypes = [
            ['type'=>'adult',  'count'=>$adults,    'start'=>0,                 'label'=>'Adult',  'age'=>'12+ years',  'bg'=>'linear-gradient(135deg,#1e40af,#3b82f6)', 'tag_bg'=>'#eff6ff', 'tag_color'=>'#1e40af', 'titles'=>['Mr','Mrs','Ms']],
            ['type'=>'child',  'count'=>$children,  'start'=>$adults,           'label'=>'Child',  'age'=>'2–11 years', 'bg'=>'linear-gradient(135deg,#059669,#10b981)', 'tag_bg'=>'#f0fdf4', 'tag_color'=>'#059669', 'titles'=>['Master','Miss']],
            ['type'=>'infant', 'count'=>$infants,   'start'=>$adults+$children, 'label'=>'Infant', 'age'=>'Under 2',    'bg'=>'linear-gradient(135deg,#7c3aed,#a78bfa)', 'tag_bg'=>'#f5f3ff', 'tag_color'=>'#7c3aed', 'titles'=>['Master','Miss']],
        ];

        // Helper: get passenger draft value
        // Priority: old() → session draft → default
        $paxVal = fn(int $idx, string $field, $default = '') =>
            old("passengers.$idx.$field", $draft['passengers'][$idx][$field] ?? $default);
    @endphp

    <div class="co-wrap">
        <div class="co-inner">

            {{-- ── Header ── --}}
            <div id="co-header" class="co-header">
                <div class="co-hd-left">
                    <a href="javascript:history.back()" class="co-back-btn"><i class="fa fa-arrow-left"></i></a>
                    <div>
                        <div class="co-hd-title">Complete Your Booking</div>
                        <div class="co-hd-sub">{{ $totalPax }} passenger{{ $totalPax > 1 ? 's' : '' }} · {{ count($selectedFlight['legs'] ?? []) }} leg{{ count($selectedFlight['legs'] ?? []) > 1 ? 's' : '' }}</div>
                    </div>
                </div>
                <div class="co-timer-wrap">
                    <i class="fa fa-clock co-timer-icon"></i>
                    <div>
                        <div id="timer-val" class="co-timer-val">30:00</div>
                        <div class="co-timer-label">Session Remaining</div>
                    </div>
                    <div id="timer-warn" class="co-timer-warn">
                        <i class="fa fa-exclamation-triangle"></i> Hurry up!<br>
                        <span style="font-weight:400;opacity:.8;font-size:10.5px">Book before timeout</span>
                    </div>
                </div>
            </div>

            {{-- ── Main Grid ── --}}
            <div class="co-grid">

                {{-- ════ LEFT: Forms ════ --}}
                <div>

                    {{-- Alerts --}}
                    @if(session('error'))
                        <div class="co-alert co-alert-err">
                            <i class="fa fa-exclamation-circle"></i>
                            <div><strong>Error:</strong> {{ session('error') }}</div>
                        </div>
                    @endif
                    @if(session('success'))
                        <div class="co-alert co-alert-ok"><i class="fa fa-check-circle"></i><div><strong>Success:</strong> {{ session('success') }}@if(session('pnr'))<br><strong>PNR: {{ session('pnr') }}</strong>@endif</div></div>
                    @endif
                    @if(session('warning'))
                        <div class="co-alert co-alert-warn"><i class="fa fa-exclamation-triangle"></i><div>{{ session('warning') }}</div></div>
                    @endif
                    @if($errors->any())
                        <div class="co-alert co-alert-err"><i class="fa fa-times-circle"></i><div><strong>Please fix:</strong><ul style="margin:6px 0 0;padding-left:16px">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div></div>
                    @endif

                    {{-- Visa mandatory notice --}}
                    @if($visaMandatory)
                        <div class="co-alert co-alert-warn">
                            <i class="fa fa-exclamation-triangle"></i>
                            <div>
                                <strong>One-way International Flight — Visa Copy Required</strong><br>
                                <span style="font-size:12px">Please upload a valid visa copy for each passenger. This is mandatory for one-way international travel.</span>
                            </div>
                        </div>
                    @endif

                    <form id="booking-form" method="POST" action="{{ route('booking.doCheckout') }}" autocomplete="off" enctype="multipart/form-data">
                        @csrf
                        @if(!empty($token = request()->input('token')))
                            <input type="hidden" name="token" value="{{ $token }}">
                        @endif
                        <input type="hidden" name="payment_method" value="without_payment">

                        {{-- ── Contact Info ── --}}
                        <div class="co-card">
                            <div class="co-card-head">
                                <div class="co-card-icon"><i class="fa fa-envelope"></i></div>
                                <div>
                                    <div class="co-card-title">Contact Information</div>
                                    <div class="co-card-sub">Booking confirmation will be sent here</div>
                                </div>
                            </div>
                            <div class="co-card-body">
                                <div class="f-grid-2" style="margin-bottom:12px">
                                    <div class="f-group">
                                        <label class="f-label">Email Address <span class="req">*</span></label>
                                        <div class="f-icon-wrap">
                                            <input type="email" name="contact_email" required autocomplete="email"
                                                   class="f-input" placeholder="your@email.com"
                                                   value="{{ $contactEmail }}">
                                            <i class="fa fa-envelope f-icon"></i>
                                        </div>
                                    </div>
                                    <div class="f-group">
                                        <label class="f-label">Phone Number <span class="req">*</span></label>
                                        <div class="f-icon-wrap">
                                            <input type="tel" name="contact_phone" required autocomplete="tel"
                                                   class="f-input" placeholder="+880 1XXX-XXXXXX"
                                                   value="{{ $contactPhone }}">
                                            <i class="fa fa-phone f-icon"></i>
                                        </div>
                                    </div>
                                </div>

                                {{-- Emergency Contact --}}
{{--                                <div class="pax-section-title">Emergency Contact</div>--}}
{{--                                <div class="f-grid-2">--}}
{{--                                    <div class="f-group">--}}
{{--                                        <label class="f-label">Emergency Contact Name <span class="req">*</span></label>--}}
{{--                                        <div class="f-icon-wrap">--}}
{{--                                            <input type="text" name="emergency_contact_name" required--}}
{{--                                                   class="f-input" placeholder="Full name"--}}
{{--                                                   value="{{ $emergencyName }}">--}}
{{--                                            <i class="fa fa-user f-icon"></i>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                    <div class="f-group">--}}
{{--                                        <label class="f-label">Emergency Contact Phone <span class="req">*</span></label>--}}
{{--                                        <div class="f-icon-wrap">--}}
{{--                                            <input type="tel" name="emergency_contact_phone" required--}}
{{--                                                   class="f-input" placeholder="+880 1XXX-XXXXXX"--}}
{{--                                                   value="{{ $emergencyPhone }}">--}}
{{--                                            <i class="fa fa-phone f-icon"></i>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
                            </div>
                        </div>

                        {{-- ── Passenger Cards ── --}}
                        @foreach($paxTypes as $paxType)
                            @for($i = 1; $i <= $paxType['count']; $i++)
                                @php $idx = $paxType['start'] + $i - 1; $paxNum = $passengerIndex++; @endphp

                                <div class="co-card pax-card" id="pax-{{ $idx }}" data-type="{{ $paxType['type'] }}" data-idx="{{ $idx }}">

                                    {{-- Header --}}
                                    <div class="pax-hd">
                                        <div class="pax-hd-left">
                                            <div class="pax-num" style="background:{{ $paxType['bg'] }}">{{ $paxNum }}</div>
                                            <div>
                                                <div class="pax-name">{{ $paxType['label'] }} Passenger {{ $i }}</div>
                                                <div class="pax-age">{{ $paxType['age'] }}</div>
                                            </div>
                                        </div>
                                        <span class="pax-tag" style="background:{{ $paxType['tag_bg'] }};color:{{ $paxType['tag_color'] }}">{{ strtoupper($paxType['type']) }}</span>
                                    </div>

                                    <div class="co-card-body">

                                        {{-- Row 1: Title + Gender --}}
                                        <div class="f-grid-2" style="margin-bottom:12px">
                                            <div class="f-group">
                                                <label class="f-label">Title <span class="req">*</span></label>
                                                <select name="passengers[{{ $idx }}][title]" required class="f-input">
                                                    <option value="">Select title</option>
                                                    @foreach($paxType['titles'] as $t)
                                                        <option value="{{ $t }}" {{ $paxVal($idx,'title') == $t ? 'selected' : '' }}>{{ $t }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="f-group">
                                                <label class="f-label">Gender <span class="req">*</span></label>
                                                <select name="passengers[{{ $idx }}][gender]" required class="f-input">
                                                    <option value="">Select gender</option>
                                                    <option value="male"   {{ $paxVal($idx,'gender') == 'male'   ? 'selected' : '' }}>Male</option>
                                                    <option value="female" {{ $paxVal($idx,'gender') == 'female' ? 'selected' : '' }}>Female</option>
                                                </select>
                                            </div>
                                        </div>

                                        {{-- Row 2: First + Last Name --}}
                                        <div class="f-grid-2" style="margin-bottom:12px">
                                            <div class="f-group">
                                                <label class="f-label">First Name <span class="req">*</span></label>
                                                <input type="text" name="passengers[{{ $idx }}][first_name]" required
                                                       autocomplete="off" class="f-input upper"
                                                       placeholder="As per passport"
                                                       value="{{ $paxVal($idx,'first_name') }}">
                                            </div>
                                            <div class="f-group">
                                                <label class="f-label">Last Name <span class="req">*</span></label>
                                                <input type="text" name="passengers[{{ $idx }}][last_name]" required
                                                       autocomplete="off" class="f-input upper"
                                                       placeholder="As per passport"
                                                       value="{{ $paxVal($idx,'last_name') }}">
                                            </div>
                                        </div>

                                        {{-- Row 3: DOB + Nationality --}}
                                        <div class="f-grid-2" style="margin-bottom:12px">
                                            <div class="f-group">
                                                <label class="f-label">Date of Birth <span class="req">*</span></label>
                                                <div class="dp-wrap" data-pax-type="{{ $paxType['type'] }}" data-idx="{{ $idx }}">
                                                    <input type="hidden" name="passengers[{{ $idx }}][dob]"
                                                           class="dp-hidden" value="{{ $paxVal($idx,'dob') }}">
                                                    <div class="dp-display" tabindex="0" role="button">
                                                        <i class="fa fa-calendar dp-icon"></i>
                                                        <span class="dp-val" style="display:none"></span>
                                                        <span class="dp-ph">Select date</span>
                                                        <i class="fa fa-times dp-clear" title="Clear"></i>
                                                    </div>
                                                    <div class="dp-cal">
                                                        <div class="dp-nav">
                                                            <button type="button" class="dp-nav-btn dp-prev"><i class="fa fa-chevron-left"></i></button>
                                                            <div class="dp-nav-sel">
                                                                <select class="dp-month-sel"></select>
                                                                <select class="dp-year-sel"></select>
                                                            </div>
                                                            <button type="button" class="dp-nav-btn dp-next"><i class="fa fa-chevron-right"></i></button>
                                                        </div>
                                                        <div class="dp-wdays">
                                                            <span>Su</span><span>Mo</span><span>Tu</span>
                                                            <span>We</span><span>Th</span><span>Fr</span><span>Sa</span>
                                                        </div>
                                                        <div class="dp-days"></div>
                                                        <div class="dp-foot">
                                                            <button type="button" class="dp-today-btn">Today</button>
                                                            <button type="button" class="dp-done-btn">Done ✓</button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="f-hint dob-hint"></div>
                                                <div class="f-err dob-err"></div>
                                            </div>

                                            <div class="f-group">
                                                <label class="f-label">Nationality <span class="req">*</span></label>
                                                <div class="cs-wrap" data-pax="{{ $idx }}">
                                                    <select name="passengers[{{ $idx }}][nationality]" required class="cs-real">
                                                        <option value="">Select Country</option>
                                                        @foreach($countries as $country)
                                                            <option value="{{ $country->code }}"
                                                                    data-code="{{ $country->code }}"
                                                                    data-flag="{{ $country->flag_emoji ?? '' }}"
                                                                {{ $paxVal($idx,'nationality','BD') == $country->code ? 'selected' : '' }}>
                                                                {{ $country->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <div class="cs-input-row" tabindex="0" role="button">
                                                        <span class="cs-flag">🌐</span>
                                                        <input class="cs-text" placeholder="Search country…" readonly>
                                                        <i class="fa fa-chevron-down cs-arrow"></i>
                                                    </div>
                                                    <div class="cs-drop">
                                                        <div class="cs-search-wrap">
                                                            <i class="fa fa-search cs-search-icon"></i>
                                                            <input type="text" class="cs-search" placeholder="Type to search…" autocomplete="off">
                                                        </div>
                                                        <div class="cs-list">
                                                            @foreach($countries as $country)
                                                                <div class="cs-opt {{ $paxVal($idx,'nationality','BD') == $country->code ? 'active' : '' }}"
                                                                     data-value="{{ $country->code }}"
                                                                     data-name="{{ $country->name }}"
                                                                     data-flag="{{ $country->flag_emoji ?? '' }}"
                                                                     data-code="{{ $country->code }}">
                                                                    <span class="cs-opt-flag">{{ $country->flag_emoji ?? '🌐' }}</span>
                                                                    <span class="cs-opt-name">{{ $country->name }}</span>
                                                                    <span class="cs-opt-code">{{ $country->code }}</span>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                        <div class="cs-empty">No country found</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- ── NID (domestic only) ── --}}
                                        @if($isLocalFlight && $paxType['type'] === 'adult')
                                            <div class="f-grid-2" style="margin-bottom:12px">
                                                <div class="f-group">
                                                    <label class="f-label">NID / Birth Certificate No</label>
                                                    <div class="f-icon-wrap">
                                                        <input type="text" name="passengers[{{ $idx }}][nid_number]"
                                                               autocomplete="off" class="f-input"
                                                               placeholder="National ID or Birth Cert. No"
                                                               value="{{ $paxVal($idx,'nid_number') }}">
                                                        <i class="fa fa-id-card f-icon"></i>
                                                    </div>
                                                    <div class="f-hint">Required for domestic travel if no passport</div>
                                                </div>
                                                <div class="f-group">
                                                    <label class="f-label">NID / Birth Certificate Image</label>
                                                    <input type="file" name="passengers[{{ $idx }}][nid_image]"
                                                           accept="image/*,.pdf" class="f-file">
                                                    <div class="f-hint">JPG, PNG or PDF · Max 5MB</div>
                                                </div>
                                            </div>
                                        @endif

                                        {{-- ── Passport Section (always shown for international) ── --}}
                                        @if(!$isLocalFlight)
                                            <div class="pax-section-title">Passport & Travel Documents</div>

                                            {{-- Passport No + Expiry --}}
                                            <div class="f-grid-2" style="margin-bottom:12px">
                                                <div class="f-group">
                                                    <label class="f-label">Passport Number <span class="req">*</span></label>
                                                    <div class="f-icon-wrap">
                                                        <input type="text"
                                                               name="passengers[{{ $idx }}][passport_number]"
                                                               id="passport_{{ $idx }}"
                                                               required
                                                               autocomplete="off"
                                                               class="f-input upper passport-inp"
                                                               data-pax="{{ $idx }}"
                                                               placeholder="e.g. AB1234567"
                                                               value="{{ $paxVal($idx,'passport_number') }}">
                                                        <i class="fa fa-passport f-icon"></i>
                                                    </div>
                                                    <div id="pp-msg-{{ $idx }}" class="f-err"></div>
                                                </div>

                                                <div class="f-group">
                                                    <label class="f-label">Passport Expiry <span class="req">*</span></label>
                                                    <div class="dp-wrap dp-expiry" data-min="{{ $minExpiry }}">
                                                        <input type="hidden" name="passengers[{{ $idx }}][passport_expiry]"
                                                               required
                                                               class="dp-hidden" value="{{ $paxVal($idx,'passport_expiry') }}">
                                                        <div class="dp-display" tabindex="0" role="button">
                                                            <i class="fa fa-calendar-check dp-icon"></i>
                                                            <span class="dp-val" style="display:none"></span>
                                                            <span class="dp-ph">Select expiry date</span>
                                                            <i class="fa fa-times dp-clear" title="Clear"></i>
                                                        </div>
                                                        <div class="dp-cal">
                                                            <div class="dp-nav">
                                                                <button type="button" class="dp-nav-btn dp-prev"><i class="fa fa-chevron-left"></i></button>
                                                                <div class="dp-nav-sel">
                                                                    <select class="dp-month-sel"></select>
                                                                    <select class="dp-year-sel"></select>
                                                                </div>
                                                                <button type="button" class="dp-nav-btn dp-next"><i class="fa fa-chevron-right"></i></button>
                                                            </div>
                                                            <div class="dp-wdays">
                                                                <span>Su</span><span>Mo</span><span>Tu</span>
                                                                <span>We</span><span>Th</span><span>Fr</span><span>Sa</span>
                                                            </div>
                                                            <div class="dp-days"></div>
                                                            <div class="dp-foot">
                                                                <button type="button" class="dp-done-btn" style="flex:1">Done ✓</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="f-hint">Must be valid 6+ months from today</div>
                                                </div>
                                            </div>

                                            {{-- Passport Issue Date (visible) --}}
                                            <div class="f-grid-2" style="margin-bottom:12px">
                                                <div class="f-group">
                                                    <label class="f-label">Passport Issue Date</label>
                                                    <div class="dp-wrap dp-issue" data-max="{{ $today }}">
                                                        <input type="hidden" name="passengers[{{ $idx }}][passport_issue_date]"
                                                               class="dp-hidden" value="{{ $paxVal($idx,'passport_issue_date') }}">
                                                        <div class="dp-display" tabindex="0" role="button">
                                                            <i class="fa fa-calendar-plus dp-icon"></i>
                                                            <span class="dp-val" style="display:none"></span>
                                                            <span class="dp-ph">Select issue date</span>
                                                            <i class="fa fa-times dp-clear" title="Clear"></i>
                                                        </div>
                                                        <div class="dp-cal">
                                                            <div class="dp-nav">
                                                                <button type="button" class="dp-nav-btn dp-prev"><i class="fa fa-chevron-left"></i></button>
                                                                <div class="dp-nav-sel">
                                                                    <select class="dp-month-sel"></select>
                                                                    <select class="dp-year-sel"></select>
                                                                </div>
                                                                <button type="button" class="dp-nav-btn dp-next"><i class="fa fa-chevron-right"></i></button>
                                                            </div>
                                                            <div class="dp-wdays">
                                                                <span>Su</span><span>Mo</span><span>Tu</span>
                                                                <span>We</span><span>Th</span><span>Fr</span><span>Sa</span>
                                                            </div>
                                                            <div class="dp-days"></div>
                                                            <div class="dp-foot">
                                                                <button type="button" class="dp-today-btn">Today</button>
                                                                <button type="button" class="dp-done-btn">Done ✓</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="f-hint">Date passport was issued</div>
                                                </div>

                                                {{--
                                                    ── FUTURE USE: Passport Issue Country ──
                                                    <div class="f-group">
                                                        <label class="f-label">Passport Issue Country</label>
                                                        <div class="cs-wrap" data-pax="{{ $idx }}-issue-country">
                                                            <select name="passengers[{{ $idx }}][passport_issue_country]" class="cs-real">
                                                                <option value="">Select Country</option>
                                                                @foreach($countries as $country)
                                                                    <option value="{{ $country->code }}"
                                                                        {{ $paxVal($idx,'passport_issue_country','BD') == $country->code ? 'selected' : '' }}>
                                                                        {{ $country->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            <div class="cs-input-row" tabindex="0" role="button">
                                                                <span class="cs-flag">🌐</span>
                                                                <input class="cs-text" placeholder="Search country…" readonly>
                                                                <i class="fa fa-chevron-down cs-arrow"></i>
                                                            </div>
                                                            ... (same cs-drop structure as nationality)
                                                        </div>
                                                    </div>

                                                    ── FUTURE USE: Frequent Flyer Number ──
                                                    <div class="f-group">
                                                        <label class="f-label">Frequent Flyer No <span style="font-weight:400">(optional)</span></label>
                                                        <div class="f-icon-wrap">
                                                            <input type="text"
                                                                   name="passengers[{{ $idx }}][frequent_flyer_number]"
                                                                   autocomplete="off" class="f-input upper"
                                                                   placeholder="e.g. BS123456789"
                                                                   value="{{ $paxVal($idx,'frequent_flyer_number') }}">
                                                            <i class="fa fa-star f-icon"></i>
                                                        </div>
                                                        <div class="f-hint">Earn miles on this booking</div>
                                                    </div>
                                                --}}
                                            </div>
                                        @else
                                            {{-- Domestic: optional passport --}}
                                            <div class="pax-section-title">Passport <span style="font-weight:400;text-transform:none;font-size:10px;color:#94a3b8">(optional for domestic)</span></div>
                                            <div class="f-grid-2" style="margin-bottom:12px">
                                                <div class="f-group">
                                                    <label class="f-label">Passport Number</label>
                                                    <div class="f-icon-wrap">
                                                        <input type="text"
                                                               name="passengers[{{ $idx }}][passport_number]"
                                                               id="passport_{{ $idx }}"
                                                               autocomplete="off"
                                                               class="f-input upper passport-inp"
                                                               data-pax="{{ $idx }}"
                                                               placeholder="e.g. AB1234567"
                                                               value="{{ $paxVal($idx,'passport_number') }}">
                                                        <i class="fa fa-passport f-icon"></i>
                                                    </div>
                                                    <div id="pp-msg-{{ $idx }}" class="f-err"></div>
                                                </div>
                                                <div class="f-group">
                                                    <label class="f-label">Passport Expiry</label>
                                                    <div class="dp-wrap dp-expiry" data-min="{{ $minExpiry }}">
                                                        <input type="hidden" name="passengers[{{ $idx }}][passport_expiry]"
                                                               class="dp-hidden" value="{{ $paxVal($idx,'passport_expiry') }}">
                                                        <div class="dp-display" tabindex="0" role="button">
                                                            <i class="fa fa-calendar-check dp-icon"></i>
                                                            <span class="dp-val" style="display:none"></span>
                                                            <span class="dp-ph">Select expiry date</span>
                                                            <i class="fa fa-times dp-clear" title="Clear"></i>
                                                        </div>
                                                        <div class="dp-cal">
                                                            <div class="dp-nav">
                                                                <button type="button" class="dp-nav-btn dp-prev"><i class="fa fa-chevron-left"></i></button>
                                                                <div class="dp-nav-sel">
                                                                    <select class="dp-month-sel"></select>
                                                                    <select class="dp-year-sel"></select>
                                                                </div>
                                                                <button type="button" class="dp-nav-btn dp-next"><i class="fa fa-chevron-right"></i></button>
                                                            </div>
                                                            <div class="dp-wdays">
                                                                <span>Su</span><span>Mo</span><span>Tu</span>
                                                                <span>We</span><span>Th</span><span>Fr</span><span>Sa</span>
                                                            </div>
                                                            <div class="dp-days"></div>
                                                            <div class="dp-foot">
                                                                <button type="button" class="dp-done-btn" style="flex:1">Done ✓</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        {{-- ── City + Address ── --}}
                                        <div class="f-grid-2" style="margin-bottom:12px">
                                            <div class="f-group">
                                                <label class="f-label">City</label>
                                                <input type="text" name="passengers[{{ $idx }}][city]"
                                                       autocomplete="off" class="f-input"
                                                       placeholder="e.g. Dhaka"
                                                       value="{{ $paxVal($idx,'city') }}">
                                            </div>
                                            <div class="f-group">
                                                <label class="f-label">Address</label>
                                                <input type="text" name="passengers[{{ $idx }}][address]"
                                                       autocomplete="off" class="f-input"
                                                       placeholder="Full address"
                                                       value="{{ $paxVal($idx,'address') }}">
                                            </div>
                                        </div>

                                        {{-- ── Meal Preference ── --}}
{{--                                        <div class="pax-section-title">Meal Preference <span style="font-weight:400;text-transform:none;font-size:10px;color:#94a3b8">(optional)</span></div>--}}
{{--                                        <div style="margin-bottom:12px">--}}
{{--                                            <div class="meal-opts" id="meal-opts-{{ $idx }}">--}}
{{--                                                @php--}}
{{--                                                    $mealOpts = [--}}
{{--                                                        ['value'=>'RVML','icon'=>'🥦','label'=>'Vegetarian'],--}}
{{--                                                        ['value'=>'MOML','icon'=>'🥩','label'=>'Non-Veg'],--}}
{{--                                                        ['value'=>'VGML','icon'=>'🌱','label'=>'Vegan'],--}}
{{--                                                        ['value'=>'HNML','icon'=>'🍖','label'=>'Halal'],--}}
{{--                                                        ['value'=>'KSML','icon'=>'✡️', 'label'=>'Kosher'],--}}
{{--                                                        ['value'=>'DBML','icon'=>'🩺','label'=>'Diabetic'],--}}
{{--                                                        ['value'=>'LCML','icon'=>'🥗','label'=>'Low Cal'],--}}
{{--                                                        ['value'=>'NLML','icon'=>'🚫','label'=>'No Meal'],--}}
{{--                                                    ];--}}
{{--                                                    $selectedMeal = $paxVal($idx, 'meal_preference', '');--}}
{{--                                                @endphp--}}
{{--                                                @foreach($mealOpts as $meal)--}}
{{--                                                    <label class="meal-opt {{ $selectedMeal == $meal['value'] ? 'selected' : '' }}"--}}
{{--                                                           data-val="{{ $meal['value'] }}"--}}
{{--                                                           data-idx="{{ $idx }}">--}}
{{--                                                        <input type="radio" name="passengers[{{ $idx }}][meal_preference]"--}}
{{--                                                               value="{{ $meal['value'] }}"--}}
{{--                                                            {{ $selectedMeal == $meal['value'] ? 'checked' : '' }}>--}}
{{--                                                        <span>{{ $meal['icon'] }}</span>--}}
{{--                                                        <span>{{ $meal['label'] }}</span>--}}
{{--                                                    </label>--}}
{{--                                                @endforeach--}}
{{--                                            </div>--}}
{{--                                        </div>--}}

                                        {{--
                                            ── FUTURE USE: Special Assistance / Wheelchair ──
                                            <div class="pax-section-title">Special Assistance <span style="...optional..."></span></div>
                                            <label class="assist-row">
                                                <input type="checkbox" name="passengers[IDX][wheelchair]" value="1">
                                                <div>
                                                    <div class="assist-row-label">♿ Wheelchair Assistance</div>
                                                    <div class="assist-row-sub">Request wheelchair support at airport</div>
                                                </div>
                                            </label>
                                        --}}

                                        {{-- ── Documents ── --}}
                                        <div class="pax-section-title">
                                            Documents
                                            @if($visaMandatory)
                                                <span class="visa-required-badge"><i class="fa fa-exclamation-circle"></i> Visa required</span>
                                            @else
                                                <span style="font-weight:400;text-transform:none;font-size:10px;color:#94a3b8">(optional)</span>
                                            @endif
                                        </div>
                                        <div class="f-grid-2">
                                            @if(!$isLocalFlight)
                                                <div class="f-group">
                                                    <label class="f-label">
                                                        Passport Image
                                                    </label>
                                                    <input type="file" name="passengers[{{ $idx }}][passport_image]"
                                                           accept="image/*,.pdf" class="f-file">
                                                    <div class="f-hint">JPG, PNG or PDF · Max 5MB</div>
                                                </div>
                                                <div class="f-group">
                                                    <label class="f-label">
                                                        Visa Image
                                                        @if($visaMandatory) <span class="req">*</span> @endif
                                                    </label>
                                                    <input type="file"
                                                           name="passengers[{{ $idx }}][visa_image]"
                                                           accept="image/*,.pdf"
                                                           {{ $visaMandatory ? 'required' : '' }}
                                                           class="f-file {{ $visaMandatory ? 'required-doc' : '' }}">
                                                    <div class="f-hint">
                                                        @if($visaMandatory)
                                                            <span style="color:var(--red);font-weight:600">Required for one-way international travel</span>
                                                        @else
                                                            JPG, PNG or PDF · Max 5MB
                                                        @endif
                                                    </div>
                                                </div>
                                            @else
                                                <div class="f-group">
                                                    <label class="f-label">NID / Passport Image</label>
                                                    <input type="file" name="passengers[{{ $idx }}][passport_image]"
                                                           accept="image/*,.pdf" class="f-file">
                                                    <div class="f-hint">JPG, PNG or PDF · Max 5MB</div>
                                                </div>
                                            @endif
                                        </div>

                                    </div>
                                    <input type="hidden" name="passengers[{{ $idx }}][type]" value="{{ $paxType['type'] }}">
                                </div>
                            @endfor
                        @endforeach

                    </form>
                </div>

                {{-- ════ RIGHT: Sidebar ════ --}}
                <div class="co-sidebar" style="position:sticky;top:24px">

                    <div class="sid-flight-group">
                        @foreach($selectedFlight['legs'] as $leg)
                            <div class="sid-flight">
                                <div class="sid-flight-head">
                                    <div class="sid-fl-top">
                                        <div class="sid-fl-air">
                                            @if(!empty($leg['segments'][0]['carrier_images']['thumb']))
                                                <img src="{{ $leg['segments'][0]['carrier_images']['thumb'] }}" class="sid-fl-logo">
                                            @endif
                                            <span class="sid-fl-name">{{ $leg['segments'][0]['carrier_name'] ?? 'Airline' }}</span>
                                        </div>
                                        @if(count($selectedFlight['legs']) > 1)
                                            <span class="sid-fl-badge"
                                                  style="background:{{ $leg['leg_type']==='outbound' ? 'rgba(255,255,255,.2)' : 'rgba(253,224,71,.25)' }};color:{{ $leg['leg_type']==='outbound' ? '#fff' : '#fde047' }}">
                                                {{ $leg['leg_type']==='outbound' ? '→ OUTBOUND' : '← RETURN' }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="sid-fl-route">
                                        <div>
                                            <div class="sid-iata">{{ $leg['departure']['airport_code'] }}</div>
                                            <div class="sid-iata-sub">{{ $leg['departure']['time_12h'] ?? '' }}</div>
                                        </div>
                                        <div class="sid-mid">
                                            <div class="sid-dur">{{ $leg['duration_formatted'] }}</div>
                                            <div class="sid-line"></div>
                                            <div class="sid-stop">{{ $leg['is_direct'] ? 'Non-stop' : $leg['stops'].' Stop' }}</div>
                                        </div>
                                        <div style="text-align:right">
                                            <div class="sid-iata">{{ $leg['arrival']['airport_code'] }}</div>
                                            <div class="sid-iata-sub">{{ $leg['arrival']['time_12h'] ?? '' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="sid-price">
                        <div class="sid-price-head"><i class="fa fa-receipt" style="color:var(--blue)"></i> Price Summary</div>
                        @if($baseFare > 0)
                            <div class="pr-row"><span class="pr-lbl">Base Fare</span><span class="pr-val">৳{{ number_format($baseFare) }}</span></div>
                        @endif
                        @if($tax > 0)
                            <div class="pr-row"><span class="pr-lbl">+ Tax</span><span class="pr-val">৳{{ number_format($tax) }}</span></div>
                        @endif
                        <div class="pr-row pr-sub"><span class="pr-lbl">= Gross Fare</span><span class="pr-val">৳{{ number_format($grossFare) }}</span></div>
                        @if($ait > 0)
                            <div class="pr-row"><span class="pr-lbl">+ AIT</span><span class="pr-val">৳{{ number_format($ait, 2) }}</span></div>
                        @endif
                        @if($svc > 0)
                            <div class="pr-row"><span class="pr-lbl">+ Service Charge</span><span class="pr-val">৳{{ number_format($svc) }}</span></div>
                        @endif
                        @if($ait > 0 || $svc > 0)
                            <div class="pr-row pr-sub"><span class="pr-lbl">= Subtotal</span><span class="pr-val">৳{{ number_format($subtotal, 2) }}</span></div>
                        @endif
                        @if($segDisc > 0)
                            <div class="pr-row pr-disc"><span class="pr-lbl"><i class="fa fa-tag"></i> Segment Disc.</span><span class="pr-val">-৳{{ number_format($segDisc) }}</span></div>
                        @endif
                        @if($fltDisc > 0)
                            <div class="pr-row pr-disc"><span class="pr-lbl"><i class="fa fa-tag"></i> Discount</span><span class="pr-val">-৳{{ number_format($fltDisc, 2) }}</span></div>
                        @endif
                        <div class="pr-row pr-total" style="padding:12px 18px"><span class="pr-lbl">You Pay</span><span class="pr-val">৳{{ number_format($total, 2) }}</span></div>
                        <div class="sid-meta">
                            <div class="sid-meta-row">
                                <i class="fa fa-{{ !empty($selectedFlight['refundable']) ? 'check-circle' : 'times-circle' }}"
                                   style="color:{{ !empty($selectedFlight['refundable']) ? 'var(--green)' : 'var(--red)' }}"></i>
                                {{ !empty($selectedFlight['refundable']) ? 'Refundable fare' : 'Non-refundable fare' }}
                            </div>
                            @if(!empty($selectedFlight['last_ticket_date']))
                                <div class="sid-meta-row">
                                    <i class="fa fa-clock" style="color:var(--amber)"></i>
                                    Ticket by {{ \Carbon\Carbon::parse($selectedFlight['last_ticket_date'])->format('d M Y') }}
                                    @if(!empty($selectedFlight['last_ticket_time'])) · {{ $selectedFlight['last_ticket_time'] }}@endif
                                </div>
                            @endif
                        </div>
                    </div>

                    <button type="submit" form="booking-form" id="submit-btn" class="co-submit">
                        <i class="fa fa-paper-plane"></i> Confirm Booking
                    </button>
                    <div class="co-ssl">
                        <i class="fa fa-shield-alt" style="color:var(--green)"></i> Secured with SSL encryption
                    </div>
                </div>

            </div>
        </div>
    </div>
    <div id="dp-backdrop" class="dp-backdrop"></div>
@endsection

@push('js')
    <script>
        /* ═══════════════════════════════════════════════════
           CUSTOM DATE PICKER
        ═══════════════════════════════════════════════════ */
        const MONTHS = ['January','February','March','April','May','June','July','August','September','October','November','December'];
        const MONTHS_SHORT = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

        function fmtDate(d) { if(!d) return null; return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`; }
        function fmtDisplay(d) { if(!d) return ''; return `${d.getDate()} ${MONTHS_SHORT[d.getMonth()]} ${d.getFullYear()}`; }
        function parseDate(s) { if(!s) return null; const [y,m,d]=s.split('-').map(Number); return new Date(y,m-1,d); }

        function initDatePicker(wrap) {
            const hidden   = wrap.querySelector('.dp-hidden');
            const display  = wrap.querySelector('.dp-display');
            const cal      = wrap.querySelector('.dp-cal');
            const valEl    = display.querySelector('.dp-val');
            const phEl     = display.querySelector('.dp-ph');
            const clearEl  = display.querySelector('.dp-clear');
            const prevBtn  = wrap.querySelector('.dp-prev');
            const nextBtn  = wrap.querySelector('.dp-next');
            const monthSel = wrap.querySelector('.dp-month-sel');
            const yearSel  = wrap.querySelector('.dp-year-sel');
            const daysEl   = wrap.querySelector('.dp-days');
            const todayBtn = wrap.querySelector('.dp-today-btn');
            const doneBtn  = wrap.querySelector('.dp-done-btn');

            const paxType  = wrap.dataset.paxType;
            const isExpiry = wrap.classList.contains('dp-expiry');
            const isIssue  = wrap.classList.contains('dp-issue');
            const depDate  = new Date('{{ $departureDate }}');

            let minDate = null, maxDate = null;
            if (isExpiry) {
                minDate = new Date('{{ $minExpiry }}');
                maxDate = new Date(); maxDate.setFullYear(maxDate.getFullYear()+20);
            } else if (isIssue) {
                minDate = new Date(1924,0,1);
                maxDate = new Date();
            } else if (paxType === 'adult') {
                maxDate = new Date(depDate); maxDate.setFullYear(maxDate.getFullYear()-12);
                minDate = new Date(depDate); minDate.setFullYear(minDate.getFullYear()-100);
            } else if (paxType === 'child') {
                maxDate = new Date(depDate); maxDate.setFullYear(maxDate.getFullYear()-2);
                minDate = new Date(depDate); minDate.setFullYear(minDate.getFullYear()-12); minDate.setDate(minDate.getDate()+1);
            } else if (paxType === 'infant') {
                maxDate = new Date(depDate);
                minDate = new Date(depDate); minDate.setFullYear(minDate.getFullYear()-2); minDate.setDate(minDate.getDate()+1);
            }

            let selected  = parseDate(hidden.value);
            let viewYear  = selected ? selected.getFullYear() : (isExpiry ? new Date().getFullYear()+4 : (maxDate ? maxDate.getFullYear() : new Date().getFullYear()));
            let viewMonth = selected ? selected.getMonth()    : (maxDate ? maxDate.getMonth() : new Date().getMonth());

            function buildYearSel() {
                yearSel.innerHTML='';
                const startY = minDate ? minDate.getFullYear() : 1924;
                const endY   = maxDate ? maxDate.getFullYear() : new Date().getFullYear()+20;
                for(let y=endY;y>=startY;y--){const o=document.createElement('option');o.value=y;o.textContent=y;if(y===viewYear)o.selected=true;yearSel.appendChild(o);}
            }
            function buildMonthSel() {
                monthSel.innerHTML='';
                MONTHS.forEach((m,mi)=>{const o=document.createElement('option');o.value=mi;o.textContent=m;if(mi===viewMonth)o.selected=true;monthSel.appendChild(o);});
            }

            function renderCal() {
                buildMonthSel(); buildYearSel();
                const firstDay=new Date(viewYear,viewMonth,1).getDay();
                const daysInMonth=new Date(viewYear,viewMonth+1,0).getDate();
                const today=new Date(); today.setHours(0,0,0,0);
                const selStr=selected?fmtDate(selected):null;
                daysEl.innerHTML='';
                const frag=document.createDocumentFragment();
                for(let b=0;b<firstDay;b++){const s=document.createElement('span');s.className='dp-day blank';frag.appendChild(s);}
                for(let d=1;d<=daysInMonth;d++){
                    const dt=new Date(viewYear,viewMonth,d);
                    const btn=document.createElement('button');
                    btn.type='button'; btn.className='dp-day'; btn.textContent=d;
                    if(dt.getTime()===today.getTime()) btn.classList.add('today');
                    if(selStr&&fmtDate(dt)===selStr) btn.classList.add('sel');
                    if((minDate&&dt<minDate)||(maxDate&&dt>maxDate)) btn.disabled=true;
                    btn.addEventListener('click',()=>selectDay(dt));
                    frag.appendChild(btn);
                }
                daysEl.appendChild(frag);
            }

            function selectDay(dt) {
                selected=dt; hidden.value=fmtDate(dt);
                valEl.textContent=fmtDisplay(dt); valEl.style.display='block'; phEl.style.display='none';
                display.classList.add('filled');
                daysEl.querySelectorAll('.dp-day.sel').forEach(b=>b.classList.remove('sel'));
                validateDob(); closeCal();
            }

            const backdrop=document.getElementById('dp-backdrop');
            function openCal() {
                document.querySelectorAll('.dp-cal.open').forEach(c=>{if(c!==cal){c.classList.remove('open');c.closest('.dp-wrap')?.querySelector('.dp-display')?.classList.remove('open');}});
                const rect=display.getBoundingClientRect();
                const spaceBelow=window.innerHeight-rect.bottom;
                const calHeight=340;
                cal.style.left=rect.left+'px';
                cal.style.width=Math.max(rect.width,300)+'px';
                if(spaceBelow<calHeight&&rect.top>calHeight){cal.style.top='';cal.style.bottom=(window.innerHeight-rect.top+4)+'px';}
                else{cal.style.top=(rect.bottom+4)+'px';cal.style.bottom='';}
                cal.classList.add('open'); display.classList.add('open');
                if(backdrop&&window.innerWidth<=600) backdrop.classList.add('show');
                renderCal();
            }
            function closeCal() {
                cal.classList.remove('open'); display.classList.remove('open');
                if(backdrop) backdrop.classList.remove('show');
            }

            function validateDob() {
                if(isExpiry||isIssue||!paxType) return;
                const errEl=wrap.closest('.f-group')?.querySelector('.dob-err');
                const hintEl=wrap.closest('.f-group')?.querySelector('.dob-hint');
                const msgs={adult:'Must be 12+ years old on departure',child:'Must be 2–11 years old on departure',infant:'Must be under 2 years old on departure'};
                if(hintEl){hintEl.textContent=msgs[paxType]||'';}
                if(!selected||!errEl) return;
                const ok=(!minDate||selected>=minDate)&&(!maxDate||selected<=maxDate);
                if(ok){errEl.textContent='';errEl.classList.remove('show');display.classList.add('valid');display.classList.remove('invalid');}
                else{selected=null;hidden.value='';valEl.style.display='none';phEl.style.display='block';display.classList.remove('filled');renderCal();}
            }

            if(selected){valEl.textContent=fmtDisplay(selected);valEl.style.display='block';phEl.style.display='none';display.classList.add('filled');}

            display.addEventListener('click',(e)=>{
                if(e.target===clearEl||clearEl.contains(e.target)){selected=null;hidden.value='';valEl.style.display='none';phEl.style.display='block';display.classList.remove('filled','valid','invalid');renderCal();return;}
                cal.classList.contains('open')?closeCal():openCal();
            });

            window.addEventListener('scroll',()=>{if(cal.classList.contains('open')){const rect=display.getBoundingClientRect();cal.style.left=rect.left+'px';cal.style.top=(rect.bottom+4)+'px';}},{passive:true});
            prevBtn?.addEventListener('click',()=>{viewMonth--;if(viewMonth<0){viewMonth=11;viewYear--;}renderCal();});
            nextBtn?.addEventListener('click',()=>{viewMonth++;if(viewMonth>11){viewMonth=0;viewYear++;}renderCal();});
            monthSel?.addEventListener('change',()=>{viewMonth=+monthSel.value;renderCal();});
            yearSel?.addEventListener('change', ()=>{viewYear=+yearSel.value;renderCal();});
            todayBtn?.addEventListener('click', ()=>{const t=new Date();viewYear=t.getFullYear();viewMonth=t.getMonth();renderCal();});
            doneBtn?.addEventListener('click',  ()=>closeCal());

            wrap._closeCal=closeCal; wrap._cal=cal;
            renderCal();
        }

        document.querySelectorAll('.dp-wrap').forEach(initDatePicker);

        const dpBackdrop=document.getElementById('dp-backdrop');
        document.addEventListener('mousedown',(e)=>{
            if(e.target===dpBackdrop) return;
            document.querySelectorAll('.dp-wrap').forEach(w=>{if(w._cal?.classList.contains('open')&&!w.contains(e.target))w._closeCal?.();});
        },true);
        if(dpBackdrop){
            dpBackdrop.addEventListener('click',   ()=>document.querySelectorAll('.dp-wrap').forEach(w=>w._closeCal?.()));
            dpBackdrop.addEventListener('touchend',()=>document.querySelectorAll('.dp-wrap').forEach(w=>w._closeCal?.()));
        }

        document.addEventListener('mousedown',(e)=>{
            document.querySelectorAll('.cs-wrap').forEach(w=>{
                if(!w.contains(e.target)){w.querySelector('.cs-drop')?.classList.remove('open');w.querySelector('.cs-input-row')?.classList.remove('open');}
            });
        },true);


        /* ═══════════════════════════════════════════════════
           CUSTOM COUNTRY SELECT
        ═══════════════════════════════════════════════════ */
        document.querySelectorAll('.cs-wrap').forEach(wrap=>{
            const realSel=wrap.querySelector('.cs-real');
            const inputRow=wrap.querySelector('.cs-input-row');
            const csText=wrap.querySelector('.cs-text');
            const csFlag=wrap.querySelector('.cs-flag');
            const drop=wrap.querySelector('.cs-drop');
            const search=wrap.querySelector('.cs-search');
            const opts=wrap.querySelectorAll('.cs-opt');
            const emptyEl=wrap.querySelector('.cs-empty');

            csText.readOnly=false; csText.style.pointerEvents='none'; csText.style.userSelect='none'; csText.tabIndex=-1;

            function setDisplay(name,flag){csFlag.textContent=flag||'🌐';csText.value=name||'';csText.placeholder=name?'':'Search country…';}
            function openDrop(){drop.classList.add('open');inputRow.classList.add('open');search.value='';filterOpts('');setTimeout(()=>search.focus(),50);}
            function closeDrop(){drop.classList.remove('open');inputRow.classList.remove('open');}
            function filterOpts(q){q=q.toLowerCase().trim();let v=0;opts.forEach(o=>{const m=!q||o.dataset.name.toLowerCase().includes(q)||o.dataset.code.toLowerCase().includes(q);o.classList.toggle('hidden',!m);if(m)v++;});emptyEl.classList.toggle('show',v===0);}
            function selectOpt(o){
                opts.forEach(x=>x.classList.remove('active'));o.classList.add('active');
                realSel.value=o.dataset.value;realSel.dispatchEvent(new Event('change'));
                setDisplay(o.dataset.name,o.dataset.flag);closeDrop();
                const pax=wrap.dataset.pax;const rule=getRule(o.dataset.code);
                const inp=document.getElementById(`passport_${pax}`);const msg=document.getElementById(`pp-msg-${pax}`);
                if(inp){inp.maxLength=rule.max;inp.placeholder=`e.g. ${rule.hint}`;inp.value='';inp.classList.remove('valid','invalid');}
                if(msg){msg.textContent=`Format: ${rule.hint}`;msg.style.color='#94a3b8';msg.classList.add('show');}
            }

            const activeOpt=wrap.querySelector('.cs-opt.active');
            if(activeOpt){setDisplay(activeOpt.dataset.name,activeOpt.dataset.flag);}
            else{const selVal=realSel.value;if(selVal){const match=wrap.querySelector(`.cs-opt[data-value="${selVal}"]`);if(match){match.classList.add('active');setDisplay(match.dataset.name,match.dataset.flag);}}}

            inputRow.addEventListener('mousedown',(e)=>{e.preventDefault();drop.classList.contains('open')?closeDrop():openDrop();});
            search.addEventListener('input',()=>filterOpts(search.value));
            opts.forEach(o=>o.addEventListener('mousedown',e=>{e.preventDefault();selectOpt(o);}));
        });


        /* ═══════════════════════════════════════════════════
           PASSPORT RULES
        ═══════════════════════════════════════════════════ */
        const ppRules={!! \App\Models\Country::getPassportRulesJson() !!};
        const ppDefault={min:6,max:12,pattern:/^[A-Z0-9]{6,12}$/,hint:'6–12 alphanumeric'};
        function getRule(code){const r=ppRules[code];if(!r)return ppDefault;return{min:r.min,max:r.max,pattern:new RegExp(r.pattern),hint:r.hint};}

        document.querySelectorAll('.passport-inp').forEach(inp=>{
            inp.addEventListener('input',function(){
                const pax=this.dataset.pax;const wrap=this.closest('.co-card');const natSel=wrap?.querySelector('.cs-real');
                const code=natSel?natSel.value:'';const rule=getRule(code);
                this.value=this.value.toUpperCase().slice(0,rule.max);
                const msg=document.getElementById(`pp-msg-${pax}`);
                if(!this.value){this.classList.remove('valid','invalid');if(msg)msg.classList.remove('show');return;}
                if(rule.pattern.test(this.value)){this.classList.add('valid');this.classList.remove('invalid');if(msg){msg.textContent='✓ Valid';msg.style.color='var(--green)';msg.classList.add('show');}}
                else{this.classList.add('invalid');this.classList.remove('valid');if(msg){msg.textContent=`✗ ${rule.hint} (${this.value.length}/${rule.max})`;msg.style.color='var(--red)';msg.classList.add('show');}}
            });
        });


        /* ═══════════════════════════════════════════════════
           NAME UPPERCASE
        ═══════════════════════════════════════════════════ */
        document.querySelectorAll('.upper').forEach(inp=>{
            inp.addEventListener('input',function(){const p=this.selectionStart;this.value=this.value.toUpperCase();this.setSelectionRange(p,p);});
        });


        /* ═══════════════════════════════════════════════════
           MEAL OPTION TOGGLE
        ═══════════════════════════════════════════════════ */
        document.querySelectorAll('.meal-opt').forEach(opt=>{
            opt.addEventListener('click',function(){
                const idx=this.dataset.idx;
                document.querySelectorAll(`#meal-opts-${idx} .meal-opt`).forEach(o=>o.classList.remove('selected'));
                this.classList.add('selected');
                this.querySelector('input').checked=true;
            });
        });


        /* ═══════════════════════════════════════════════════
           SESSION DRAFT SAVE (auto-save on input)
        ═══════════════════════════════════════════════════ */
        let draftTimer = null;
        function collectDraft() {
            const fd = new FormData(document.getElementById('booking-form'));
            const data = {};
            // contact
            data.contact_email = fd.get('contact_email') || '';
            data.contact_phone = fd.get('contact_phone') || '';
            data.emergency_contact_name  = fd.get('emergency_contact_name')  || '';
            data.emergency_contact_phone = fd.get('emergency_contact_phone') || '';
            // passengers — collect all text/select fields (not files)
            data.passengers = {};
            fd.forEach((val, key) => {
                const m = key.match(/^passengers\[(\d+)\]\[(.+)\]$/);
                if (!m) return;
                const idx = m[1], field = m[2];
                if (!data.passengers[idx]) data.passengers[idx] = {};
                data.passengers[idx][field] = val;
            });
            return data;
        }

        function saveDraft() {
            clearTimeout(draftTimer);
            draftTimer = setTimeout(() => {
                fetch('{{ route("booking.saveDraft") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(collectDraft())
                }).catch(() => {}); // silent fail
            }, 800);
        }

        // listen on all non-file inputs
        document.getElementById('booking-form').addEventListener('input',  saveDraft);
        document.getElementById('booking-form').addEventListener('change', saveDraft);


        /* ═══════════════════════════════════════════════════
           SESSION TIMER
        ═══════════════════════════════════════════════════ */
        let t={{ $remainingSeconds }};
        const hdr=document.getElementById('co-header');
        const tVal=document.getElementById('timer-val');
        const tWrn=document.getElementById('timer-warn');

        const tick=setInterval(()=>{
            const m=Math.floor(t/60),s=t%60;
            tVal.textContent=`${m}:${String(s).padStart(2,'0')}`;
            if(t<=300&&t>60){hdr.style.background='linear-gradient(135deg,#d97706,#f59e0b)';tWrn.style.display='block';}
            if(t<=60){hdr.style.background='linear-gradient(135deg,#dc2626,#ef4444)';hdr.classList.add('timer-critical');}
            if(t<=0){
                clearInterval(tick);
                const btn=document.getElementById('submit-btn');
                if(btn){btn.disabled=true;btn.innerHTML='<i class="fa fa-lock"></i> Session Expired';}
                alert('Your session has expired.');
                window.location.href='{{ url("/") }}';
            }
            t--;
        },1000);


        /* ═══════════════════════════════════════════════════
           FORM SUBMIT
        ═══════════════════════════════════════════════════ */
        document.getElementById('booking-form').addEventListener('submit',function(e){
            let err=false;
            document.querySelectorAll('.dp-wrap[data-pax-type]').forEach(wrap=>{
                const h=wrap.querySelector('.dp-hidden');
                if(!h.value) return;
                if(wrap.querySelector('.dp-display.invalid')){err=true;}
            });
            if(err){e.preventDefault();alert('Please check passenger dates of birth — age doesn\'t match the passenger type.');return;}
            const btn=document.getElementById('submit-btn');
            btn.disabled=true; btn.innerHTML='<i class="fa fa-spinner fa-spin"></i> Processing…';
        });
    </script>
@endpush
