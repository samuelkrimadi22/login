<?php
require_once __DIR__ . '/config/config.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <!-- V9.46.0: Favicon force refresh -->
    <link rel="icon" type="image/x-icon" href="favicon-v2.ico">
    <link rel="shortcut icon" type="image/x-icon" href="favicon-v2.ico">
    <link rel="apple-touch-icon" href="favicon-v2.ico">
    <!-- V9.44.0: Favicon multi-format -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kebijakan Privasi | <?= APP_NAME ?></title>
    
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
        }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg);
            background-image: radial-gradient(circle at 20% 10%, rgba(245, 158, 11, 0.04) 0%, transparent 50%),
                              radial-gradient(circle at 80% 90%, rgba(245, 158, 11, 0.03) 0%, transparent 50%);
            color: var(--text);
            min-height: 100vh;
            line-height: 1.75;
            padding: 40px 20px;
        }
        .container {
            max-width: 820px;
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
            font-size: 0.85rem;
            margin-bottom: 30px;
        }
        h2 {
            font-size: 1.08rem;
            font-weight: 700;
            color: var(--orange-light);
            margin-top: 34px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        h2 i { font-size: 0.95rem; color: var(--orange); flex-shrink: 0; }
        p {
            color: var(--text-muted);
            font-size: 0.91rem;
            margin-bottom: 12px;
        }
        ul {
            margin: 10px 0 16px 22px;
            color: var(--text-muted);
            font-size: 0.89rem;
        }
        li { margin-bottom: 8px; }
        strong { color: var(--text); font-weight: 600; }
        .highlight {
            padding: 16px 20px;
            background: var(--orange-soft);
            border-left: 3px solid var(--orange);
            border-radius: 12px;
            margin: 20px 0 26px;
            font-size: 0.88rem;
            color: var(--text);
            display: flex;
            gap: 10px;
            align-items: flex-start;
        }
        .highlight i { color: var(--orange); margin-top: 2px; flex-shrink: 0; }
        .footer-note {
            margin-top: 44px;
            padding-top: 26px;
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
        }
    </style>
</head>
<body>
<div class="container">
    <div class="top-nav">
        <a href="login.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Kembali ke Login
        </a>
        <span class="doc-tag">
            <i class="fas fa-file-shield"></i> Dokumen Legal
        </span>
    </div>

    <div class="header-icon">
        <i class="fas fa-user-shield"></i>
    </div>

    <h1>Kebijakan Privasi</h1>
    <p class="subtitle">Terakhir diperbarui: <?= date('d F Y') ?></p>


    <div class="highlight">
        <i class="fas fa-info-circle"></i>
        <div><strong>Ringkas:</strong> Kami berkomitmen melindungi data pribadi Anda. Kebijakan ini menjelaskan bagaimana kami mengumpulkan, menggunakan, dan melindungi informasi Anda.</div>
    </div>

    <h2><i class="fas fa-database"></i> 1. Informasi yang Kami Kumpulkan</h2>
    <ul>
        <li><strong>Data Akun</strong> — nama, email, kata sandi terenkripsi, tanggal lahir.</li>
        <li><strong>Data Video</strong> — video yang Anda unggah untuk diproses (dihapus otomatis setelah 1 jam).</li>
        <li><strong>Data Penggunaan</strong> — aktivitas, preferensi, dan interaksi dengan Layanan.</li>
        <li><strong>Data Teknis</strong> — alamat IP, browser, perangkat, dan sistem operasi.</li>
        <li><strong>Cookie</strong> — untuk sesi login dan analitik anonim.</li>
    </ul>

    <h2><i class="fas fa-bullseye"></i> 2. Cara Kami Menggunakan Data</h2>
    <ul>
        <li>Menyediakan, memelihara, dan meningkatkan Layanan.</li>
        <li>Memproses transaksi dan mengirim notifikasi penting.</li>
        <li>Menganalisis pola penggunaan untuk pengembangan fitur.</li>
        <li>Mendeteksi dan mencegah aktivitas ilegal atau penyalahgunaan.</li>
        <li>Mengirim marketing (dengan persetujuan, dapat dibatalkan kapan saja).</li>
    </ul>

    <h2><i class="fas fa-share-nodes"></i> 3. Berbagi Data dengan Pihak Ketiga</h2>
    <p>Kami <strong>TIDAK</strong> menjual data pribadi Anda. Kami hanya berbagi data dengan:</p>
    <ul>
        <li><strong>Penyedia Layanan</strong> — Midtrans (pembayaran), OpenAI (AI processing).</li>
        <li><strong>Kewajiban Hukum</strong> — jika diminta oleh otoritas berwenang.</li>
        <li><strong>Perlindungan Hak</strong> — untuk melindungi hak dan keamanan pengguna.</li>
    </ul>

    <h2><i class="fas fa-lock"></i> 4. Keamanan Data</h2>
    <ul>
        <li>Enkripsi <strong>SSL/TLS</strong> untuk semua transmisi data.</li>
        <li>Kata sandi disimpan menggunakan <strong>bcrypt hashing</strong>.</li>
        <li>Video dihapus otomatis <strong>setelah 1 jam</strong> pemrosesan.</li>
        <li>Akses internal dibatasi hanya untuk staf yang berwenang.</li>
        <li>Backup rutin dengan enkripsi.</li>
    </ul>

    <h2><i class="fas fa-clock"></i> 5. Retensi Data</h2>
    <ul>
        <li><strong>Video</strong> — dihapus otomatis setelah 1 jam pemrosesan.</li>
        <li><strong>File hasil render</strong> — tersimpan 7 hari, lalu dihapus.</li>
        <li><strong>Data akun</strong> — selama akun aktif, atau hingga Anda menghapus akun.</li>
        <li><strong>Log aktivitas</strong> — maksimal 90 hari untuk keamanan.</li>
    </ul>

    <h2><i class="fas fa-user-shield"></i> 6. Hak Anda</h2>
    <p>Sesuai UU Perlindungan Data Pribadi Indonesia, Anda berhak untuk:</p>
    <ul>
        <li><strong>Akses</strong> — melihat data pribadi yang kami simpan.</li>
        <li><strong>Koreksi</strong> — memperbaiki data yang tidak akurat.</li>
        <li><strong>Hapus</strong> — menghapus akun dan semua data terkait.</li>
        <li><strong>Portabilitas</strong> — meminta salinan data dalam format umum.</li>
        <li><strong>Penarikan Persetujuan</strong> — membatalkan izin marketing kapan saja.</li>
    </ul>

    <h2><i class="fas fa-cookie-bite"></i> 7. Cookie dan Tracking</h2>
    <p>Kami menggunakan cookie untuk:</p>
    <ul>
        <li><strong>Cookie Esensial</strong> — sesi login, tidak bisa dinonaktifkan.</li>
        <li><strong>Cookie Analitik</strong> — mengukur performa situs (anonim).</li>
        <li><strong>Cookie Fungsional</strong> — mengingat preferensi Anda.</li>
    </ul>
    <p>Anda dapat mengelola cookie melalui pengaturan browser Anda.</p>

    <h2><i class="fas fa-child"></i> 8. Anak di Bawah Umur</h2>
    <p>Layanan ini tidak ditujukan untuk anak di bawah 13 tahun. Jika kami mengetahui adanya data anak di bawah umur, kami akan menghapusnya segera.</p>

    <h2><i class="fas fa-sync-alt"></i> 9. Perubahan Kebijakan</h2>
    <p>Kami akan memberitahukan perubahan penting melalui email minimal 14 hari sebelum berlaku. Kami sarankan Anda memeriksa halaman ini secara berkala.</p>

    <h2><i class="fas fa-envelope"></i> 10. Kontak</h2>
    <p>Untuk pertanyaan tentang privasi atau permintaan data, hubungi:</p>
    <ul>
        <li>Email: <strong>privacy@clipora.com</strong></li>
        <li>WhatsApp: <strong>+62 823-9810-6023</strong></li>
    </ul>


    <div class="footer-note">
        © <?= date('Y') ?> <?= APP_NAME ?> · <a href="syarat-ketentuan.php">Syarat &amp; Ketentuan</a> · <a href="kebijakan-privasi.php">Kebijakan Privasi</a> · <a href="login.php">Login</a>
    </div>
</div>
</body>
</html>
