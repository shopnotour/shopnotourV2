<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>রক্ষণাবেক্ষণ চলছে - Shopnotour</title>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{min-height:100vh;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#0a1628 0%,#0f2347 50%,#0a1628 100%);font-family:'Sora',sans-serif;padding:2rem;position:relative;overflow:hidden}
        .bg-circle{position:absolute;border-radius:50%;background:rgba(55,138,221,0.06);animation:pulse 4s ease-in-out infinite}
        .c1{width:600px;height:600px;top:-150px;right:-150px}
        .c2{width:400px;height:400px;bottom:-100px;left:-100px;animation-delay:2s}
        @keyframes pulse{0%,100%{transform:scale(1);opacity:.5}50%{transform:scale(1.1);opacity:1}}
        .card{text-align:center;max-width:520px;z-index:1;position:relative}
        .icon-wrap{width:90px;height:90px;margin:0 auto 2rem;background:rgba(55,138,221,0.15);border:1px solid rgba(55,138,221,0.3);border-radius:50%;display:flex;align-items:center;justify-content:center;animation:float 3s ease-in-out infinite}
        @keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-10px)}}
        .icon-wrap svg{width:40px;height:40px;stroke:#378ADD;fill:none;stroke-width:1.5}
        .badge{display:inline-block;background:rgba(55,138,221,0.15);border:1px solid rgba(55,138,221,0.3);color:#85B7EB;font-size:11px;font-weight:600;letter-spacing:2px;text-transform:uppercase;padding:6px 18px;border-radius:20px;margin-bottom:1.5rem}
        h1{font-size:2.2rem;font-weight:600;color:#fff;margin:0 0 1rem;line-height:1.3}
        p{font-size:16px;color:#7a9cc4;line-height:1.9;margin:0 0 2rem}
        .dots{display:flex;gap:8px;justify-content:center;margin-bottom:2.5rem}
        .dot{width:9px;height:9px;border-radius:50%;background:#378ADD;animation:blink 1.4s ease-in-out infinite}
        .dot:nth-child(2){animation-delay:.2s}
        .dot:nth-child(3){animation-delay:.4s}
        @keyframes blink{0%,80%,100%{opacity:.2}40%{opacity:1}}
        .info{display:flex;gap:2rem;justify-content:center;flex-wrap:wrap}
        .info-item{display:flex;align-items:center;gap:8px;font-size:13px;color:#4a7aaa}
        .info-item svg{width:16px;height:16px;stroke:#378ADD;fill:none;stroke-width:1.5}
    </style>
</head>
<body>
    <div class="bg-circle c1"></div>
    <div class="bg-circle c2"></div>
    <div class="card">
        <div class="icon-wrap">
            <svg viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
        </div>
        <div class="badge">রক্ষণাবেক্ষণ চলছে</div>
        <h1>আমরা শীঘ্রই ফিরে আসছি!</h1>
        <p>আমাদের সাইটটি আরও ভালো করতে<br>কিছু আপডেট করা হচ্ছে।<br>অসুবিধার জন্য আন্তরিকভাবে ক্ষমাপ্রার্থী।</p>
        <div class="dots">
            <div class="dot"></div>
            <div class="dot"></div>
            <div class="dot"></div>
        </div>
        <div class="info">
            <div class="info-item">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                শীঘ্রই সম্পন্ন হবে
            </div>
            <div class="info-item">
                <svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81a19.79 19.79 0 01-3.07-8.63A2 2 0 012 .18h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 7.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
                যোগাযোগ করুন
            </div>
        </div>
    </div>
</body>
</html>
