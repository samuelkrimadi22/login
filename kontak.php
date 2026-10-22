<?php
require_once __DIR__ . '/config/config.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontak | <?= APP_NAME ?></title>
    <meta name="description" content="Hubungi tim Clipora untuk bantuan, pertanyaan, atau kerja sama.">
    <link rel="icon" href="logo_clipora.png" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --bg: #0a1020;
            --card: #0f1729;
            --orange: #f59e0b;
            --orange-light: #fbbf24;
            --orange-dark: #d97706;
            --orange-soft: rgba(245, 158, 11, 0.1);
            --border: rgba(148, 163, 184, 0.12);
            --text: #e8ecf1;
            --text-muted: #94a3b8;
            --text-dim: #64748b;
            --green: #22c55e;
            --blue: #3b82f6;
        }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg);
            background-image: radial-gradient(circle at 20% 10%, rgba(245, 158, 11, 0.04) 0%, transparent 50%),
                              radial-gradient(circle at 80% 90%, rgba(245, 158, 11, 0.03) 0%, transparent 50%);
            color: var(--text);
            min-height: 100vh;
            line-height: 1.7;
            padding: 40px 20px;
        }
        .container {
            max-width: 960px;
            margin: 0 auto;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 48px 44px;
            box-shadow: 0 24px 60px rgba(0,0,0,0.4);
        }
        .top-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
            flex-wrap: wrap;
            gap: 12px;
        }
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.85rem;
            padding: 9px 16px;
            border-radius: 10px;
            transition: 0.2s;
            border: 1px solid var(--border);
            font-weight: 500;
        }
        .back-link:hover {
            color: var(--orange);
            border-color: rgba(245, 158, 11, 0.3);
            background: var(--orange-soft);
        }
        .doc-tag {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 6px 14px;
            background: var(--orange-soft);
            border: 1px solid rgba(245, 158, 11, 0.25);
            border-radius: 50px;
            color: var(--orange-light);
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .header-icon {
            width: 68px;
            height: 68px;
            border-radius: 18px;
            background: linear-gradient(135deg, var(--orange), var(--orange-dark));
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.7rem;
            margin-bottom: 20px;
            box-shadow: 0 12px 32px rgba(245, 158, 11, 0.35);
        }
        h1 {
            font-size: 2rem;
            font-weight: 800;
            color: #fff;
            margin-bottom: 8px;
            letter-spacing: -0.6px;
        }
        .subtitle {
            color: var(--text-muted);
            font-size: 0.92rem;
            margin-bottom: 36px;
            max-width: 620px;
        }
        /* Layout 2 kolom */
        .contact-grid {
            display: grid;
            grid-template-columns: 1fr 1.2fr;
            gap: 32px;
            margin-bottom: 40px;
        }
        @media (max-width: 768px) {
            .contact-grid { grid-template-columns: 1fr; gap: 24px; }
        }
        /* Info cards */
        .contact-info {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }
        .info-card {
            display: flex;
            gap: 14px;
            padding: 18px;
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--border);
            border-radius: 14px;
            transition: 0.25s;
            text-decoration: none;
            color: inherit;
        }
        .info-card:hover {
            border-color: rgba(245, 158, 11, 0.3);
            background: rgba(245, 158, 11, 0.03);
            transform: translateY(-2px);
        }
        .info-icon {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            color: #fff;
            flex-shrink: 0;
        }
        .info-icon.wa { background: linear-gradient(135deg, #25D366, #128C7E); }
        .info-icon.email { background: linear-gradient(135deg, var(--orange), var(--orange-dark)); }
        .info-icon.ig { background: linear-gradient(135deg, #833ab4, #fd1d1d, #fcb045); }
        .info-icon.tt { background: linear-gradient(135deg, #25f4ee, #fe2c55); }
        .info-icon.yt { background: linear-gradient(135deg, #ff0000, #cc0000); }
        .info-content { flex: 1; min-width: 0; }
        .info-label {
            font-size: 0.72rem;
            color: var(--text-dim);
            text-transform: uppercase;
            letter-spacing: 0.8px;
            font-weight: 700;
            margin-bottom: 3px;
        }
        .info-value {
            font-size: 0.92rem;
            font-weight: 600;
            color: var(--text);
            word-break: break-word;
        }
        .info-sub {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-top: 3px;
        }
        /* Form */
        .contact-form-wrap {
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 28px 26px;
        }
        .form-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .form-title i { color: var(--orange); }
        .form-subtitle {
            font-size: 0.82rem;
            color: var(--text-muted);
            margin-bottom: 22px;
        }
        .form-group {
            margin-bottom: 16px;
        }
        .form-group label {
            display: block;
            margin-bottom: 7px;
            font-size: 0.82rem;
            color: var(--text-muted);
            font-weight: 600;
        }
        .form-group label .req { color: var(--orange); }
        .form-control {
            width: 100%;
            padding: 13px 16px;
            background: rgba(0, 0, 0, 0.4);
            border: 1px solid var(--border);
            border-radius: 12px;
            color: #fff;
            font-size: 0.9rem;
            font-family: inherit;
            transition: 0.25s;
            resize: vertical;
        }
        .form-control::placeholder { color: var(--text-dim); }
        .form-control:focus {
            outline: none;
            border-color: var(--orange);
            background: rgba(0, 0, 0, 0.55);
            box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.1);
        }
        textarea.form-control {
            min-height: 120px;
            line-height: 1.6;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }
        @media (max-width: 600px) {
            .form-row { grid-template-columns: 1fr; }
        }
        .btn-submit {
            width: 100%;
            padding: 14px 20px;
            background: linear-gradient(135deg, var(--orange), var(--orange-dark));
            color: #fff;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            font-size: 0.92rem;
            font-family: inherit;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 6px 20px rgba(245, 158, 11, 0.35);
            transition: 0.25s;
            margin-top: 6px;
        }
        .btn-submit:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 10px 28px rgba(245, 158, 11, 0.5);
        }
        .btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        /* Response message */
        .form-response {
            margin-top: 14px;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 0.85rem;
            display: none;
            align-items: center;
            gap: 8px;
        }
        .form-response.success {
            display: flex;
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: #86efac;
        }
        .form-response.error {
            display: flex;
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
        }
        /* Quick links */
        .quick-links {
            margin-top: 36px;
            padding: 22px;
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.06), rgba(245, 158, 11, 0.02));
            border: 1px solid rgba(245, 158, 11, 0.2);
            border-radius: 16px;
        }
        .quick-links-title {
            font-size: 0.9rem;
            font-weight: 700;
            color: var(--orange-light);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .quick-links-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 10px;
        }
        .quick-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid var(--border);
            border-radius: 10px;
            text-decoration: none;
            color: var(--text);
            font-size: 0.85rem;
            font-weight: 600;
            transition: 0.2s;
        }
        .quick-link:hover {
            border-color: rgba(245, 158, 11, 0.3);
            background: var(--orange-soft);
            color: var(--orange-light);
        }
        .quick-link i {
            color: var(--orange);
            font-size: 0.95rem;
        }
        /* Footer */
        .footer-note {
            margin-top: 40px;
            padding-top: 24px;
            border-top: 1px solid var(--border);
            text-align: center;
            font-size: 0.78rem;
            color: var(--text-dim);
            line-height: 1.8;
        }
        .footer-note a {
            color: var(--orange);
            text-decoration: none;
            font-weight: 600;
        }
        .footer-note a:hover { text-decoration: underline; }
        @media (max-width: 600px) {
            .container { padding: 32px 22px; }
            h1 { font-size: 1.5rem; }
            .header-icon { width: 56px; height: 56px; font-size: 1.4rem; }
            .contact-form-wrap { padding: 22px 18px; }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="top-nav">
        <a href="index.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        <span class="doc-tag">
            <i class="fas fa-headset"></i> Hubungi Kami
        </span>
    </div>

    <div class="header-icon">
        <i class="fas fa-headset"></i>
    </div>

    <h1>Kontak Kami</h1>
    <p class="subtitle">Ada pertanyaan, saran, atau butuh bantuan? Tim Clipora siap membantu Anda. Pilih cara yang paling nyaman untuk Anda.</p>

    <div class="contact-grid">
        <!-- LEFT: Info kontak -->
        <div class="contact-info">
            <a href="https://wa.me/6282399116152?text=Halo%20Clipora,%20saya%20butuh%20bantuan" target="_blank" rel="noopener" class="info-card">
                <div class="info-icon wa"><i class="fab fa-whatsapp"></i></div>
                <div class="info-content">
                    <div class="info-label">WhatsApp</div>
                    <div class="info-value">+62 823-9911-6152</div>
                    <div class="info-sub">Respon cepat, jam 08:00 - 22:00 WIB</div>
                </div>
            </a>

            <a href="mailto:support@clipora.com" class="info-card">
                <div class="info-icon email"><i class="fas fa-envelope"></i></div>
                <div class="info-content">
                    <div class="info-label">Email Support</div>
                    <div class="info-value">support@clipora.com</div>
                    <div class="info-sub">Respon dalam 1×24 jam</div>
                </div>
            </a>

            <a href="https://instagram.com/clipora.id" target="_blank" rel="noopener" class="info-card">
                <div class="info-icon ig"><i class="fab fa-instagram"></i></div>
                <div class="info-content">
                    <div class="info-label">Instagram</div>
                    <div class="info-value">@clipora.id</div>
                    <div class="info-sub">Update & tips content creator</div>
                </div>
            </a>

            <a href="https://tiktok.com/@clipora.id" target="_blank" rel="noopener" class="info-card">
                <div class="info-icon tt"><i class="fab fa-tiktok"></i></div>
                <div class="info-content">
                    <div class="info-label">TikTok</div>
                    <div class="info-value">@clipora.id</div>
                    <div class="info-sub">Tutorial & showcase</div>
                </div>
            </a>

            <a href="https://youtube.com/@clipora" target="_blank" rel="noopener" class="info-card">
                <div class="info-icon yt"><i class="fab fa-youtube"></i></div>
                <div class="info-content">
                    <div class="info-label">YouTube</div>
                    <div class="info-value">@clipora</div>
                    <div class="info-sub">Video tutorial lengkap</div>
                </div>
            </a>
        </div>

        <!-- RIGHT: Form -->
        <div class="contact-form-wrap">
            <div class="form-title">
                <i class="fas fa-paper-plane"></i> Kirim Pesan
            </div>
            <div class="form-subtitle">Isi form di bawah, kami akan merespon lewat email Anda.</div>

            <form id="contactForm" autocomplete="on">
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Nama <span class="req">*</span></label>
                        <input type="text" id="name" name="name" class="form-control" placeholder="Nama lengkap" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email <span class="req">*</span></label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="nama@email.com" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="subject">Subjek <span class="req">*</span></label>
                    <input type="text" id="subject" name="subject" class="form-control" placeholder="Topik pesan Anda" required>
                </div>

                <div class="form-group">
                    <label for="message">Pesan <span class="req">*</span></label>
                    <textarea id="message" name="message" class="form-control" placeholder="Tulis pesan Anda di sini..." required></textarea>
                </div>

                <button type="submit" class="btn-submit" id="submitBtn">
                    <i class="fas fa-paper-plane"></i> Kirim Pesan
                </button>

                <div class="form-response" id="formResponse"></div>
            </form>
        </div>
    </div>

    <!-- Quick links -->
    <div class="quick-links">
        <div class="quick-links-title">
            <i class="fas fa-bolt"></i> Mungkin Anda mencari:
        </div>
        <div class="quick-links-grid">
            <a href="faq.php" class="quick-link">
                <i class="fas fa-circle-question"></i> FAQ / Bantuan
            </a>
            <a href="harga.php" class="quick-link">
                <i class="fas fa-tag"></i> Paket & Harga
            </a>
            <a href="syarat-ketentuan.php" class="quick-link">
                <i class="fas fa-file-contract"></i> Syarat & Ketentuan
            </a>
            <a href="kebijakan-privasi.php" class="quick-link">
                <i class="fas fa-shield-halved"></i> Kebijakan Privasi
            </a>
        </div>
    </div>

    <div class="footer-note">
        © <?= date('Y') ?> <?= APP_NAME ?> <?= APP_VERSION ?> · <a href="faq.php">FAQ</a> · <a href="syarat-ketentuan.php">Syarat & Ketentuan</a> · <a href="kebijakan-privasi.php">Kebijakan Privasi</a>
    </div>
</div>

<script>
(function() {
    'use strict';

    var form = document.getElementById('contactForm');
    var submitBtn = document.getElementById('submitBtn');
    var responseEl = document.getElementById('formResponse');

    function showResponse(type, message) {
        responseEl.className = 'form-response ' + type;
        responseEl.innerHTML = '<i class="fas fa-' + (type === 'success' ? 'check-circle' : 'exclamation-circle') + '"></i> ' + message;
        responseEl.style.display = 'flex';
    }

    function hideResponse() {
        responseEl.style.display = 'none';
    }

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        hideResponse();

        var name = document.getElementById('name').value.trim();
        var email = document.getElementById('email').value.trim();
        var subject = document.getElementById('subject').value.trim();
        var message = document.getElementById('message').value.trim();

        // Validation
        if (!name || !email || !subject || !message) {
            showResponse('error', 'Semua field wajib diisi.');
            return;
        }

        if (!/^[^s@]+@[^s@]+.[^s@]+$/.test(email)) {
            showResponse('error', 'Format email tidak valid.');
            return;
        }

        if (message.length < 10) {
            showResponse('error', 'Pesan terlalu pendek (min 10 karakter).');
            return;
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengirim...';

        try {
            // Send to backend API
            var apiUrl = (window.API_URL || 'http://localhost:3000/api') + '/contact';
            
            var response = await fetch(apiUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ name: name, email: email, subject: subject, message: message })
            });

            var data;
            try {
                data = await response.json();
            } catch (jsonErr) {
                // Kalau response bukan JSON (endpoint belum ada), fallback ke WhatsApp
                console.warn('[Contact] API endpoint not available, opening WhatsApp instead');
                
                var waText = encodeURIComponent(
                    '*Pesan dari Website Clipora*\n\n' +
                    '*Nama:* ' + name + '\n' +
                    '*Email:* ' + email + '\n' +
                    '*Subjek:* ' + subject + '\n\n' +
                    '*Pesan:*\n' + message
                );
                window.open('https://wa.me/6282399116152?text=' + waText, '_blank');
                
                showResponse('success', 'Pesan Anda akan dibuka di WhatsApp untuk dikirim.');
                form.reset();
                
                setTimeout(function() {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Kirim Pesan';
                }, 1500);
                return;
            }

            if (response.ok && data.success) {
                showResponse('success', data.message || 'Pesan berhasil dikirim! Kami akan merespon segera.');
                form.reset();
            } else {
                // Fallback ke WhatsApp kalau API error
                var waText2 = encodeURIComponent(
                    '*Pesan dari Website Clipora*\n\n' +
                    '*Nama:* ' + name + '\n' +
                    '*Email:* ' + email + '\n' +
                    '*Subjek:* ' + subject + '\n\n' +
                    '*Pesan:*\n' + message
                );
                window.open('https://wa.me/6282399116152?text=' + waText2, '_blank');
                showResponse('success', 'Pesan Anda akan dibuka di WhatsApp untuk dikirim.');
                form.reset();
            }
        } catch (err) {
            console.error('[Contact] Error:', err);
            // Fallback WhatsApp
            var waText3 = encodeURIComponent(
                '*Pesan dari Website Clipora*\n\n' +
                '*Nama:* ' + name + '\n' +
                '*Email:* ' + email + '\n' +
                '*Subjek:* ' + subject + '\n\n' +
                '*Pesan:*\n' + message
            );
            window.open('https://wa.me/6282399116152?text=' + waText3, '_blank');
            showResponse('success', 'Pesan Anda akan dibuka di WhatsApp untuk dikirim.');
            form.reset();
        }

        setTimeout(function() {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Kirim Pesan';
        }, 1500);
    });

    console.log('[Contact] V9.50.0 loaded');
})();
</script>

</body>
</html>
