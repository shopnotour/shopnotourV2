@extends('layouts.app')
@push('css')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
    /* ─── Design Tokens ─── */
    :root {
        --ink:        #0f0f13;
        --ink-soft:   #3b3b4f;
        --ink-muted:  #7a7a95;
        --surface:    #fff;
        --card-bg:    #ffffff;
        --accent:     #1d4ed8;
        --accent-2:   #7696f0;
        --accent-pale:#F3F6FF;
        --line:       #dee7ff;
        --shadow-sm:  0 2px 8px rgba(15,15,19,.06);
        --shadow-md:  0 8px 32px rgba(44, 44, 61, 0.1);
        --shadow-lg:  0 20px 60px rgba(15,15,19,.14);
        --r:          14px;
        --r-sm:       8px;
        --transition: .28s cubic-bezier(.4,0,.2,1);
    }

    /* ─── Reset / Base ─── */
    .bravo-contact-block *, .bravo-contact-block *::before, .bravo-contact-block *::after {
        box-sizing: border-box; margin: 0; padding: 0;
    }
    .bravo-contact-block {
        font-family: 'DM Sans', sans-serif;
        background: var(--surface);
        color: var(--ink);
        min-height: 100vh;
        padding: 0 0 80px;
    }

    /* ─── Hero Header ─── */
    .cb-hero {
        position: relative;
        background: var(--ink);
        padding: 72px 48px 64px;
        overflow: hidden;
    }
    .cb-hero::before {
        content: '';
        position: absolute; inset: 0;
        background:
            radial-gradient(ellipse 60% 80% at 80% 50%, rgba(200,80,42,.22) 0%, transparent 70%),
            radial-gradient(ellipse 40% 60% at 10% 80%, rgba(232,168,124,.12) 0%, transparent 70%);
    }
    .cb-hero-inner {
        position: relative;
        max-width: 1260px;
        margin: 0 auto;
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 32px;
        flex-wrap: wrap;
    }
    .cb-hero-label {
        font-family: 'DM Sans', sans-serif;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: .18em;
        text-transform: uppercase;
        color: var(--accent-2);
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .cb-hero-label::before {
        content: '';
        display: inline-block;
        width: 28px; height: 2px;
        background: var(--accent);
        border-radius: 2px;
    }
    .cb-hero h1 {
        font-family: 'DM Serif Display', serif;
        font-size: clamp(36px, 5vw, 64px);
        font-weight: 400;
        line-height: 1.08;
        color: #fff;
    }
    .cb-hero h1 em {
        font-style: italic;
        color: var(--accent-2);
    }
    .cb-hero-meta {
        font-size: 13px;
        color: rgba(255,255,255,.45);
        font-weight: 300;
        white-space: nowrap;
    }
    .cb-hero-meta strong {
        color: rgba(255,255,255,.85);
        font-weight: 500;
    }

    /* ─── Filter Bar ─── */
    .cb-filter-wrap {
        position: sticky;
        top: 0;
        z-index: 50;
        background: rgba(245,244,240,.92);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border-bottom: 1px solid var(--line);
        padding: 0 48px;
    }
    .cb-filter-inner {
        max-width: 1260px;
        margin: 0 auto;
        display: flex;
        align-items: center;
        gap: 0;
        overflow-x: auto;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
    .cb-filter-inner::-webkit-scrollbar { display: none; }
    .cb-filter-btn {
        flex-shrink: 0;
        background: none;
        border: none;
        cursor: pointer;
        font-family: 'DM Sans', sans-serif;
        font-size: 13px;
        font-weight: 500;
        color: var(--ink-muted);
        padding: 18px 20px 16px;
        border-bottom: 2.5px solid transparent;
        transition: color var(--transition), border-color var(--transition);
        letter-spacing: .01em;
        position: relative;
        white-space: nowrap;
    }
    .cb-filter-btn:hover { color: var(--ink); }
    .cb-filter-btn.active {
        color: var(--accent);
        border-bottom-color: var(--accent);
        font-weight: 600;
    }
    .cb-filter-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 18px; height: 18px;
        border-radius: 50%;
        background: var(--accent-pale);
        color: var(--accent);
        font-size: 10px;
        font-weight: 700;
        margin-left: 6px;
        vertical-align: middle;
    }
    .cb-filter-btn.active .cb-filter-count {
        background: var(--accent);
        color: #fff;
    }

    /* ─── Content ─── */
    .cb-content {
        max-width: 1260px;
        margin: 0 auto;
        padding: 0 48px;
    }

    /* ─── Department Section ─── */
    .cb-dept-section {
        margin-top: 56px;
        transition: opacity .3s ease, transform .3s ease;
    }
    .cb-dept-section.hidden {
        display: none;
    }
    .cb-dept-header {
        display: flex;
        align-items: center;
        gap: 20px;
        margin-bottom: 32px;
        padding-bottom: 20px;
        border-bottom: 1px solid var(--line);
    }
    .cb-dept-icon {
        width: 48px; height: 48px;
        border-radius: var(--r-sm);
        background: var(--accent-pale);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        color: var(--accent);
        flex-shrink: 0;
    }
    .cb-dept-name {
        font-family: 'DM Serif Display', serif;
        font-size: 26px;
        font-weight: 400;
        color: var(--ink);
        line-height: 1;
    }
    .cb-dept-subtitle {
        font-size: 12px;
        color: var(--ink-muted);
        font-weight: 400;
        margin-top: 4px;
        letter-spacing: .02em;
    }
    .cb-dept-badge {
        margin-left: auto;
        background: var(--ink);
        color: rgba(255,255,255,.8);
        font-size: 11px;
        font-weight: 600;
        padding: 5px 14px;
        border-radius: 100px;
        letter-spacing: .04em;
        flex-shrink: 0;
    }

    /* ─── LIST STYLES (replaces card grid) ─── */
    .cb-list {
        display: flex;
        flex-direction: column;
        gap: 8px;
        width: 100%;
    }

    /* Simple list item row — name + number only */
    .cb-list-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: var(--card-bg);
        border: 1px solid var(--line);
        border-radius: var(--r-sm);
        padding: 14px 24px;
        transition: all var(--transition);
        animation: fadeUp .3s ease both;
    }
    .cb-list-item:hover {
        background: var(--accent-pale);
        border-color: var(--accent-2);
        transform: translateX(4px);
    }
    .cb-list-name {
        font-family: 'DM Serif Display', serif;
        font-size: 17px;
        font-weight: 500;
        color: var(--ink);
        letter-spacing: -0.2px;
    }
    .cb-list-phone {
        display: flex;
        align-items: center;
        gap: 10px;
        font-family: 'DM Sans', monospace;
        font-size: 14px;
        font-weight: 500;
        color: var(--ink-soft);
        text-decoration: none;
        transition: color var(--transition);
        background: var(--surface);
        padding: 6px 12px;
        border-radius: 40px;
    }
    .cb-list-phone i {
        font-size: 13px;
        color: var(--accent);
        opacity: 0.8;
    }
    .cb-list-phone:hover {
        color: var(--accent);
        background: white;
        box-shadow: var(--shadow-sm);
    }

    /* Empty state */
    .cb-empty {
        display: none;
        text-align: center;
        padding: 80px 20px;
    }
    .cb-empty.visible { display: block; }
    .cb-empty-icon {
        font-size: 40px;
        color: var(--line);
        margin-bottom: 16px;
    }
    .cb-empty p {
        font-size: 15px;
        color: var(--ink-muted);
    }

    /* ─── Responsive ─── */
    @media (max-width: 768px) {
        .cb-hero { padding: 48px 24px 40px; }
        .cb-filter-wrap { padding: 0 16px; }
        .cb-content { padding: 0 20px; }
        .cb-list-item {
            padding: 12px 18px;
            flex-wrap: wrap;
            gap: 10px;
        }
        .cb-list-phone {
            font-size: 12px;
            padding: 4px 10px;
        }
    }

    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(12px); }
        to   { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush

@section('content')
<div class="bravo-contact-block">

    {{-- ── Hero ── --}}
    <div class="cb-hero">
        <div class="cb-hero-inner">
            <div>
                <div class="cb-hero-label">Our People</div>
                <h1>Contact <em>List</em></h1>
            </div>
            <div class="cb-hero-meta">
                <strong>18</strong> team members across <strong>6</strong> departments
            </div>
        </div>
    </div>

    {{-- ── Department Filter Tabs ── --}}
    <div class="cb-filter-wrap">
        <div class="cb-filter-inner" role="tablist" aria-label="Filter by department">
            <button class="cb-filter-btn active" data-dept="all" role="tab" aria-selected="true">
                All Teams <span class="cb-filter-count">18</span>
            </button>
            <button class="cb-filter-btn" data-dept="engineering" role="tab" aria-selected="false">
                Engineering <span class="cb-filter-count">4</span>
            </button>
            <button class="cb-filter-btn" data-dept="design" role="tab" aria-selected="false">
                Design <span class="cb-filter-count">3</span>
            </button>
            <button class="cb-filter-btn" data-dept="marketing" role="tab" aria-selected="false">
                Marketing <span class="cb-filter-count">3</span>
            </button>
            <button class="cb-filter-btn" data-dept="sales" role="tab" aria-selected="false">
                Sales <span class="cb-filter-count">3</span>
            </button>
            <button class="cb-filter-btn" data-dept="hr" role="tab" aria-selected="false">
                Human Resources <span class="cb-filter-count">2</span>
            </button>
            <button class="cb-filter-btn" data-dept="finance" role="tab" aria-selected="false">
                Finance <span class="cb-filter-count">3</span>
            </button>
        </div>
    </div>

    {{-- ── Contact Content ── --}}
    <div class="cb-content">

        {{-- ═══════════════════════════════ ENGINEERING (LIST) ═══════════════════════════════ --}}
        <div class="cb-dept-section" data-dept="engineering">
            <div class="cb-dept-header">
                <div class="cb-dept-icon"><i class="fa-solid fa-microchip"></i></div>
                <div>
                    <div class="cb-dept-name">Engineering</div>
                    <div class="cb-dept-subtitle">Building the product, one commit at a time</div>
                </div>
                <div class="cb-dept-badge">4 Members</div>
            </div>
            <div class="cb-list">
                {{-- Row 1 --}}
                <div class="cb-list-item" style="animation-delay:0.00s">
                    <span class="cb-list-name">Arman Hossain</span>
                    <a class="cb-list-phone" href="tel:+8801712345678"><i class="fa-solid fa-phone"></i>+880 171 234 5678</a>
                </div>
                {{-- Row 2 --}}
                <div class="cb-list-item" style="animation-delay:0.03s">
                    <span class="cb-list-name">Riya Chakraborty</span>
                    <a class="cb-list-phone" href="tel:+8801812345678"><i class="fa-solid fa-phone"></i>+880 181 234 5678</a>
                </div>
                {{-- Row 3 --}}
                <div class="cb-list-item" style="animation-delay:0.06s">
                    <span class="cb-list-name">Sifat Nawaz</span>
                    <a class="cb-list-phone" href="tel:+8801912345678"><i class="fa-solid fa-phone"></i>+880 191 234 5678</a>
                </div>
                {{-- Row 4 --}}
                <div class="cb-list-item" style="animation-delay:0.09s">
                    <span class="cb-list-name">Tanvir Ahsan</span>
                    <a class="cb-list-phone" href="tel:+8801612345678"><i class="fa-solid fa-phone"></i>+880 161 234 5678</a>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════ DESIGN (LIST) ═══════════════════════════════ --}}
        <div class="cb-dept-section" data-dept="design">
            <div class="cb-dept-header">
                <div class="cb-dept-icon"><i class="fa-solid fa-pen-nib"></i></div>
                <div>
                    <div class="cb-dept-name">Design</div>
                    <div class="cb-dept-subtitle">Crafting experiences that delight and inspire</div>
                </div>
                <div class="cb-dept-badge">3 Members</div>
            </div>
            <div class="cb-list">
                <div class="cb-list-item"><span class="cb-list-name">Nadia Rahman</span><a class="cb-list-phone" href="tel:+8801712300001"><i class="fa-solid fa-phone"></i>+880 171 230 0001</a></div>
                <div class="cb-list-item"><span class="cb-list-name">Karim Uddin</span><a class="cb-list-phone" href="tel:+8801812300002"><i class="fa-solid fa-phone"></i>+880 181 230 0002</a></div>
                <div class="cb-list-item"><span class="cb-list-name">Farhan Jalil</span><a class="cb-list-phone" href="tel:+8801912300003"><i class="fa-solid fa-phone"></i>+880 191 230 0003</a></div>
            </div>
        </div>

        {{-- ═══════════════════════════════ MARKETING (LIST) ═══════════════════════════════ --}}
        <div class="cb-dept-section" data-dept="marketing">
            <div class="cb-dept-header">
                <div class="cb-dept-icon"><i class="fa-solid fa-bullhorn"></i></div>
                <div>
                    <div class="cb-dept-name">Marketing</div>
                    <div class="cb-dept-subtitle">Growing our brand and community</div>
                </div>
                <div class="cb-dept-badge">3 Members</div>
            </div>
            <div class="cb-list">
                <div class="cb-list-item"><span class="cb-list-name">Sumaiya Islam</span><a class="cb-list-phone" href="tel:+8801712400001"><i class="fa-solid fa-phone"></i>+880 171 240 0001</a></div>
                <div class="cb-list-item"><span class="cb-list-name">Mehedi Hasan</span><a class="cb-list-phone" href="tel:+8801812400002"><i class="fa-solid fa-phone"></i>+880 181 240 0002</a></div>
                <div class="cb-list-item"><span class="cb-list-name">Zara Ahmed</span><a class="cb-list-phone" href="tel:+8801912400003"><i class="fa-solid fa-phone"></i>+880 191 240 0003</a></div>
            </div>
        </div>

        {{-- ═══════════════════════════════ SALES (LIST) ═══════════════════════════════ --}}
        <div class="cb-dept-section" data-dept="sales">
            <div class="cb-dept-header">
                <div class="cb-dept-icon"><i class="fa-solid fa-chart-line"></i></div>
                <div>
                    <div class="cb-dept-name">Sales</div>
                    <div class="cb-dept-subtitle">Converting relationships into revenue</div>
                </div>
                <div class="cb-dept-badge">3 Members</div>
            </div>
            <div class="cb-list">
                <div class="cb-list-item"><span class="cb-list-name">Rajib Das</span><a class="cb-list-phone" href="tel:+8801712500001"><i class="fa-solid fa-phone"></i>+880 171 250 0001</a></div>
                <div class="cb-list-item"><span class="cb-list-name">Priya Sen</span><a class="cb-list-phone" href="tel:+8801812500002"><i class="fa-solid fa-phone"></i>+880 181 250 0002</a></div>
                <div class="cb-list-item"><span class="cb-list-name">Mostak Khan</span><a class="cb-list-phone" href="tel:+8801912500003"><i class="fa-solid fa-phone"></i>+880 191 250 0003</a></div>
            </div>
        </div>

        {{-- ═══════════════════════════════ HR (LIST) ═══════════════════════════════ --}}
        <div class="cb-dept-section" data-dept="hr">
            <div class="cb-dept-header">
                <div class="cb-dept-icon"><i class="fa-solid fa-people-group"></i></div>
                <div>
                    <div class="cb-dept-name">Human Resources</div>
                    <div class="cb-dept-subtitle">Empowering our team to grow and thrive</div>
                </div>
                <div class="cb-dept-badge">2 Members</div>
            </div>
            <div class="cb-list">
                <div class="cb-list-item"><span class="cb-list-name">Sabrina Haque</span><a class="cb-list-phone" href="tel:+8801712600001"><i class="fa-solid fa-phone"></i>+880 171 260 0001</a></div>
                <div class="cb-list-item"><span class="cb-list-name">Imran Chowdhury</span><a class="cb-list-phone" href="tel:+8801812600002"><i class="fa-solid fa-phone"></i>+880 181 260 0002</a></div>
            </div>
        </div>

        {{-- ═══════════════════════════════ FINANCE (LIST) ═══════════════════════════════ --}}
        <div class="cb-dept-section" data-dept="finance">
            <div class="cb-dept-header">
                <div class="cb-dept-icon"><i class="fa-solid fa-coins"></i></div>
                <div>
                    <div class="cb-dept-name">Finance</div>
                    <div class="cb-dept-subtitle">Keeping our numbers clean and our goals clear</div>
                </div>
                <div class="cb-dept-badge">3 Members</div>
            </div>
            <div class="cb-list">
                <div class="cb-list-item"><span class="cb-list-name">Khalid Mahmud</span><a class="cb-list-phone" href="tel:+8801712700001"><i class="fa-solid fa-phone"></i>+880 171 270 0001</a></div>
                <div class="cb-list-item"><span class="cb-list-name">Rubel Akter</span><a class="cb-list-phone" href="tel:+8801812700002"><i class="fa-solid fa-phone"></i>+880 181 270 0002</a></div>
                <div class="cb-list-item"><span class="cb-list-name">Tania Begum</span><a class="cb-list-phone" href="tel:+8801912700003"><i class="fa-solid fa-phone"></i>+880 191 270 0003</a></div>
            </div>
        </div>

        {{-- Empty state --}}
        <div class="cb-empty" id="cb-empty-state">
            <div class="cb-empty-icon"><i class="fa-regular fa-face-frown-open"></i></div>
            <p>No team members found in this department.</p>
        </div>

    </div>{{-- /cb-content --}}
</div>{{-- /bravo-contact-block --}}

@push('js')
<script>
(function () {
    const filterBtns = document.querySelectorAll('.cb-filter-btn');
    const sections   = document.querySelectorAll('.cb-dept-section');
    const emptyState = document.getElementById('cb-empty-state');

    function applyFilter(dept) {
        let anyVisible = false;

        sections.forEach(function (sec) {
            if (dept === 'all' || sec.dataset.dept === dept) {
                sec.classList.remove('hidden');
                anyVisible = true;
                // Replay list item animation
                sec.querySelectorAll('.cb-list-item').forEach(function (item) {
                    item.style.animation = 'none';
                    item.offsetHeight; // reflow
                    item.style.animation = '';
                });
            } else {
                sec.classList.add('hidden');
            }
        });

        emptyState.classList.toggle('visible', !anyVisible);

        filterBtns.forEach(function (btn) {
            const isActive = btn.dataset.dept === dept;
            btn.classList.toggle('active', isActive);
            btn.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });
    }

    filterBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            applyFilter(btn.dataset.dept);
        });
    });

    // Keyboard navigation (arrow keys across tabs)
    filterBtns.forEach(function (btn, idx) {
        btn.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowRight') {
                const next = filterBtns[idx + 1] || filterBtns[0];
                next.focus(); next.click();
            } else if (e.key === 'ArrowLeft') {
                const prev = filterBtns[idx - 1] || filterBtns[filterBtns.length - 1];
                prev.focus(); prev.click();
            }
        });
    });
})();
</script>
@endpush
@endsection