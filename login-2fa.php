<?php
require_once __DIR__ . '/config/config.php';

// Cek apakah ada temp_token di session
if (empty($_SESSION['temp_2fa_token'])) {
    header('Location: login.php');
    exit;
}

// Cek timeout (max 15 menit)
if (time() - ($_SESSION['temp_2fa_time'] ?? 0) > 900) {
    unset($_SESSION['temp_2fa_token'], $_SESSION['temp_2fa_email'], $_SESSION['temp_2fa_time']);
    header('Location: login.php?expired=1');
    exit;
}

// Kalau sudah login, redirect
if ($isLoggedIn) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$email = $_SESSION['temp_2fa_email'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code'] ?? '');
    
    if (empty($code)) {
        $error = 'Kode wajib diisi';
    } else {
        // Kirim ke backend
        $ch = curl_init(API_URL . '/auth/verify-2fa');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode([
                'temp_token' => $_SESSION['temp_2fa_token'],
                'code' => $code,
            ]),
            CURLOPT_TIMEOUT => 15
        ]);
        $resp = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            $error = 'Tidak dapat terhubung ke server.';
        } else {
            $data = json_decode($resp, true);
            
            if ($httpCode === 200 && !empty($data['success'])) {
                // Login sukses
                setAuthToken($data['token'], $data['user']);
                
                $_SESSION['user_id']       = $data['user']['id'];
                $_SESSION['user_name']     = $data['user']['username'];
                $_SESSION['user_email']    = $data['user']['email'];
                $_SESSION['is_admin']      = !empty($data['user']['is_admin']) ? 1 : 0;
                $_SESSION['is_subscribed'] = !empty($data['user']['is_subscribed']);
                $_SESSION['auth_token']    = $data['token'];
                
                // Cleanup temp session
                unset($_SESSION['temp_2fa_token'], $_SESSION['temp_2fa_email'], $_SESSION['temp_2fa_time']);
                
                // Redirect
                if (!empty($data['user']['is_admin'])) {
                    header('Location: admin/index.php');
                } else {
                    header('Location: dashboard.php');
                }
                exit;
            }
            
            $error = $data['error'] ?? 'Kode salah. Coba lagi.';
        }
    }
}
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
    <title>Verifikasi 2FA | <?= APP_NAME ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --bg-main: #0a1020;
            --bg-card: #0f1729;
            --bg-input: rgba(0, 0, 0, 0.3);
            --orange: #f59e0b;
            --orange-light: #fbbf24;
            --orange-dark: #d97706;
            --orange-soft: rgba(245, 158, 11, 0.1);
            --orange-border: rgba(245, 158, 11, 0.3);
            --border: rgba(148, 163, 184, 0.12);
            --text: #e8ecf1;
            --text-muted: #94a3b8;
            --text-dim: #64748b;
            --red: #ef4444;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg-main);
            background-image: radial-gradient(circle at 20% 30%, rgba(245, 158, 11, 0.04) 0%, transparent 50%),
                              radial-gradient(circle at 80% 70%, rgba(245, 158, 11, 0.03) 0%, transparent 50%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: var(--text);
        }

        /* Layout split: branding kiri + card kanan (sama kayak login) */
        .layout {
            display: grid;
            grid-template-columns: 1fr 480px;
            gap: 60px;
            max-width: 1100px;
            width: 100%;
            align-items: center;
        }

        @media (max-width: 900px) {
            .layout {
                grid-template-columns: 1fr;
                gap: 32px;
                max-width: 460px;
            }
            .branding { display: none; }
        }

        /* Left: Branding section */
        .branding {
            padding: 20px 0;
        }
        .brand-logo {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 40px;
        }
        .brand-logo-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--orange), var(--orange-dark));
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.4rem;
            box-shadow: 0 8px 24px rgba(245, 158, 11, 0.3);
        }
        .brand-logo-text {
            font-size: 1.6rem;
            font-weight: 800;
            color: #fff;
            letter-spacing: -0.5px;
        }

        .branding h1 {
            font-size: 2.8rem;
            font-weight: 800;
            line-height: 1.15;
            margin-bottom: 18px;
            letter-spacing: -1px;
            color: #fff;
        }
        .branding h1 .accent {
            color: var(--orange);
        }

        .branding p {
            color: var(--text-muted);
            font-size: 1.05rem;
            line-height: 1.6;
            margin-bottom: 36px;
            max-width: 420px;
        }

        .feature-list {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }
        .feature-item {
            display: flex;
            gap: 14px;
            align-items: flex-start;
        }
        .feature-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: var(--orange-soft);
            border: 1px solid var(--orange-border);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--orange);
            font-size: 1rem;
            flex-shrink: 0;
        }
        .feature-content strong {
            display: block;
            font-size: 0.92rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 3px;
        }
        .feature-content small {
            font-size: 0.82rem;
            color: var(--text-muted);
            line-height: 1.5;
        }

        /* Right: Card */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 40px 36px 32px;
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.5),
                        inset 0 1px 0 rgba(255, 255, 255, 0.03);
            animation: cardIn 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes cardIn {
            from { transform: translateY(12px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        /* Icon top (rounded square orange, sama kayak login) */
        .card-icon {
            width: 62px;
            height: 62px;
            margin: 0 auto 22px;
            border-radius: 18px;
            background: linear-gradient(135deg, var(--orange), var(--orange-dark));
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.6rem;
            box-shadow: 0 12px 32px rgba(245, 158, 11, 0.35);
            position: relative;
        }
        .card-icon::before {
            content: '';
            position: absolute;
            inset: -6px;
            border-radius: 22px;
            border: 1px solid var(--orange-border);
            opacity: 0.5;
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: 800;
            color: #fff;
            text-align: center;
            margin-bottom: 6px;
            letter-spacing: -0.4px;
        }

        .card-subtitle {
            font-size: 0.88rem;
            color: var(--text-muted);
            text-align: center;
            margin-bottom: 8px;
            line-height: 1.6;
        }

        .email-display {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: var(--orange-soft);
            border: 1px solid var(--orange-border);
            border-radius: 10px;
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--orange-light);
            margin: 12px auto 26px;
            word-break: break-all;
            max-width: 100%;
            justify-content: center;
        }

        .card-body {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* Alert error */
        .alert-error {
            width: 100%;
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 0.84rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: shakeIn 0.5s ease;
        }
        .alert-error i { color: var(--red); font-size: 1rem; }

        @keyframes shakeIn {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-6px); }
            50% { transform: translateX(6px); }
            75% { transform: translateX(-4px); }
        }

        /* OTP boxes (orange theme) */
        .otp-container {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 22px;
            width: 100%;
        }

        .otp-input {
            flex: 1;
            max-width: 62px;
            height: 68px;
            background: var(--bg-input);
            border: 2px solid var(--border);
            border-radius: 14px;
            color: #fff;
            font-size: 1.8rem;
            font-weight: 700;
            text-align: center;
            font-family: 'Inter', sans-serif;
            outline: none;
            transition: all 0.2s ease;
            caret-color: var(--orange);
        }

        .otp-input:hover {
            border-color: rgba(245, 158, 11, 0.25);
        }

        .otp-input:focus {
            border-color: var(--orange);
            background: rgba(245, 158, 11, 0.05);
            box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.12);
            transform: translateY(-2px);
        }

        .otp-input:not(:placeholder-shown) {
            border-color: var(--orange-border);
            background: rgba(245, 158, 11, 0.03);
        }

        /* Backup input */
        .backup-input-wrap {
            display: none;
            width: 100%;
            margin-bottom: 18px;
        }
        .backup-input-wrap.active { display: block; }

        .code-input-backup {
            width: 100%;
            padding: 16px 18px;
            background: var(--bg-input);
            border: 2px solid var(--border);
            border-radius: 14px;
            color: #fff;
            font-size: 1.3rem;
            letter-spacing: 0.3em;
            text-align: center;
            font-family: 'Courier New', monospace;
            font-weight: 700;
            outline: none;
            transition: 0.3s;
        }
        .code-input-backup:focus {
            border-color: var(--orange);
            box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.12);
        }
        .code-input-backup::placeholder {
            color: var(--text-dim);
            letter-spacing: 0.2em;
        }

        /* Submit button (orange gradient seperti login) */
        .btn-verify {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, var(--orange), var(--orange-dark));
            color: #fff;
            border: none;
            border-radius: 14px;
            font-weight: 700;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: inherit;
            box-shadow: 0 8px 24px rgba(245, 158, 11, 0.35);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            position: relative;
            overflow: hidden;
            letter-spacing: 0.2px;
        }
        .btn-verify::before {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        .btn-verify:hover:not(:disabled)::before { left: 100%; }
        .btn-verify:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(245, 158, 11, 0.5);
        }
        .btn-verify:active:not(:disabled) { transform: translateY(0); }
        .btn-verify:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Countdown */
        .countdown {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            margin-top: 14px;
            padding: 10px 14px;
            background: rgba(245, 158, 11, 0.06);
            border: 1px solid rgba(245, 158, 11, 0.15);
            border-radius: 10px;
            font-size: 0.78rem;
            color: var(--orange-light);
            font-weight: 600;
        }
        .countdown i { font-size: 0.72rem; }
        .countdown.warning {
            background: rgba(239, 68, 68, 0.08);
            border-color: rgba(239, 68, 68, 0.25);
            color: #fca5a5;
            animation: blink 1s infinite;
        }
        @keyframes blink {
            50% { opacity: 0.6; }
        }

        /* Divider */
        .divider {
            position: relative;
            width: 100%;
            margin: 22px 0 16px;
            text-align: center;
        }
        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: var(--border);
        }
        .divider span {
            position: relative;
            background: var(--bg-card);
            padding: 0 12px;
            font-size: 0.7rem;
            color: var(--text-dim);
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-weight: 600;
        }

        /* Backup toggle */
        .backup-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--text-muted);
            font-size: 0.84rem;
            font-weight: 600;
            text-decoration: none;
            padding: 10px 16px;
            border-radius: 10px;
            transition: 0.2s;
            cursor: pointer;
            border: 1px solid var(--border);
            background: transparent;
            font-family: inherit;
            width: 100%;
            justify-content: center;
        }
        .backup-link:hover {
            color: var(--orange);
            border-color: var(--orange-border);
            background: var(--orange-soft);
        }
        .backup-link i { color: var(--orange); font-size: 0.85rem; }

        /* Back to login */
        .logout-link {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            margin-top: 14px;
            color: var(--text-muted);
            font-size: 0.82rem;
            text-decoration: none;
            transition: 0.2s;
            padding: 10px;
            border-radius: 10px;
            font-weight: 500;
        }
        .logout-link:hover {
            color: var(--red);
            background: rgba(239, 68, 68, 0.06);
        }

        /* Footer card */
        .card-footer {
            margin-top: 22px;
            padding-top: 18px;
            border-top: 1px solid var(--border);
            font-size: 0.74rem;
            color: var(--text-dim);
            text-align: center;
            line-height: 1.6;
        }
        .card-footer a {
            color: var(--orange);
            text-decoration: none;
            font-weight: 600;
        }
        .card-footer a:hover { text-decoration: underline; }

        /* Shake */
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-8px); }
            20%, 40%, 60%, 80% { transform: translateX(8px); }
        }
        .shake { animation: shake 0.5s; }

        /* Mobile */
        @media (max-width: 480px) {
            .card {
                padding: 30px 22px 24px;
                border-radius: 20px;
            }
            .otp-container { gap: 6px; }
            .otp-input {
                height: 58px;
                font-size: 1.5rem;
                border-radius: 12px;
                max-width: none;
            }
            .card-title { font-size: 1.3rem; }
            .card-icon { width: 54px; height: 54px; font-size: 1.4rem; }
        }
    </style>
</head>
<body>

<div class="layout">

    <!-- LEFT: Branding (mirror login.php) -->
    <div class="branding">
        <div class="brand-logo">
            <div class="brand-logo-icon">
                <i class="fas fa-play"></i>
            </div>
            <div class="brand-logo-text">Clipora</div>
        </div>

        <h1>Verifikasi <span class="accent">Keamanan</span></h1>
        <p>Kami perlu memverifikasi identitas Anda untuk melindungi akun dari akses tidak sah.</p>

        <div class="feature-list">
            <div class="feature-item">
                <div class="feature-icon"><i class="fas fa-shield-halved"></i></div>
                <div class="feature-content">
                    <strong>Keamanan Ganda</strong>
                    <small>Lapisan proteksi ekstra untuk akun Anda</small>
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-icon"><i class="fas fa-mobile-screen-button"></i></div>
                <div class="feature-content">
                    <strong>Google Authenticator</strong>
                    <small>Kode 6-digit berganti setiap 30 detik</small>
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-icon"><i class="fas fa-key"></i></div>
                <div class="feature-content">
                    <strong>Backup Code</strong>
                    <small>Akses darurat jika perangkat hilang</small>
                </div>
            </div>
        </div>
    </div>

    <!-- RIGHT: 2FA Card -->
    <div class="card" id="twofaCard">
        <div class="card-icon">
            <i class="fas fa-shield-halved"></i>
        </div>

        <h2 class="card-title">Verifikasi 2FA</h2>
        <p class="card-subtitle">Masukkan kode dari Google Authenticator</p>

        <div class="card-body">
            <div class="email-display">
                <i class="fas fa-user-circle"></i>
                <?= htmlspecialchars($email) ?>
            </div>

            <?php if ($error): ?>
                <div class="alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" id="verifyForm" style="width: 100%;">
                <!-- OTP boxes -->
                <div class="otp-container" id="otpContainer">
                    <input type="text" class="otp-input" maxlength="1" inputmode="numeric" data-index="0" placeholder=" " autofocus>
                    <input type="text" class="otp-input" maxlength="1" inputmode="numeric" data-index="1" placeholder=" ">
                    <input type="text" class="otp-input" maxlength="1" inputmode="numeric" data-index="2" placeholder=" ">
                    <input type="text" class="otp-input" maxlength="1" inputmode="numeric" data-index="3" placeholder=" ">
                    <input type="text" class="otp-input" maxlength="1" inputmode="numeric" data-index="4" placeholder=" ">
                    <input type="text" class="otp-input" maxlength="1" inputmode="numeric" data-index="5" placeholder=" ">
                </div>

                <!-- Backup input -->
                <div class="backup-input-wrap" id="backupInputWrap">
                    <input
                        type="text"
                        id="codeBackup"
                        class="code-input-backup"
                        placeholder="XXXX-XXXX"
                        maxlength="9"
                        inputmode="text"
                        autocomplete="off"
                    >
                </div>

                <input type="hidden" name="code" id="hiddenCode">

                <button type="submit" class="btn-verify" id="submitBtn">
                    <i class="fas fa-check-circle"></i> Verifikasi Sekarang
                </button>

                <div class="countdown" id="countdown">
                    <i class="fas fa-clock"></i>
                    <span>Berlaku <strong id="timer">15:00</strong></span>
                </div>
            </form>

            <div class="divider"><span>atau</span></div>

            <button type="button" class="backup-link" id="toggleBackup">
                <i class="fas fa-key"></i> Pakai backup code
            </button>

            <a href="logout.php" class="logout-link">
                <i class="fas fa-arrow-left"></i> Batal & kembali ke login
            </a>

            <div class="card-footer">
                © <?= date('Y') ?> Clipora — <a href="#">Butuh bantuan?</a>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';

    const otpInputs = document.querySelectorAll('.otp-input');
    const otpContainer = document.getElementById('otpContainer');
    const backupWrap = document.getElementById('backupInputWrap');
    const backupInput = document.getElementById('codeBackup');
    const hiddenCode = document.getElementById('hiddenCode');
    const form = document.getElementById('verifyForm');
    const submitBtn = document.getElementById('submitBtn');
    const toggleBackup = document.getElementById('toggleBackup');
    const twofaCard = document.getElementById('twofaCard');

    // === FIX: Deklarasikan timerInterval di atas sebelum digunakan ===
    let timerInterval = null;

    let isBackupMode = false;

    otpInputs.forEach((input, index) => {
        input.addEventListener('input', (e) => {
            const val = e.target.value.replace(/\D/g, '');
            e.target.value = val.slice(0, 1);

            if (val && index < otpInputs.length - 1) {
                otpInputs[index + 1].focus();
            }

            checkComplete();
        });

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && !e.target.value && index > 0) {
                otpInputs[index - 1].focus();
                otpInputs[index - 1].value = '';
            }
            if (e.key === 'ArrowLeft' && index > 0) otpInputs[index - 1].focus();
            if (e.key === 'ArrowRight' && index < otpInputs.length - 1) otpInputs[index + 1].focus();
        });

        input.addEventListener('paste', (e) => {
            e.preventDefault();
            const pasted = (e.clipboardData || window.clipboardData).getData('text');
            const digits = pasted.replace(/\D/g, '').slice(0, 6);
            if (digits.length === 0) return;

            digits.split('').forEach((digit, i) => {
                if (otpInputs[i]) otpInputs[i].value = digit;
            });

            const nextIdx = Math.min(digits.length, otpInputs.length - 1);
            otpInputs[nextIdx].focus();
            checkComplete();
        });

        input.addEventListener('focus', () => {
            setTimeout(() => input.select(), 10);
        });
    });

    function checkComplete() {
        const code = Array.from(otpInputs).map(i => i.value).join('');
        if (code.length === 6) {
            setTimeout(() => submitForm(code), 300);
        }
    }

    toggleBackup.addEventListener('click', () => {
        isBackupMode = !isBackupMode;
        if (isBackupMode) {
            otpContainer.style.display = 'none';
            backupWrap.classList.add('active');
            backupInput.focus();
            toggleBackup.innerHTML = '<i class="fas fa-arrow-left"></i> Kembali ke kode 6-digit';
        } else {
            otpContainer.style.display = 'flex';
            backupWrap.classList.remove('active');
            otpInputs[0].focus();
            toggleBackup.innerHTML = '<i class="fas fa-key"></i> Pakai backup code';
        }
    });

    backupInput.addEventListener('input', (e) => {
        let val = e.target.value.replace(/[^0-9A-Fa-f]/g, '').toUpperCase();
        if (val.length > 4) {
            val = val.slice(0, 4) + '-' + val.slice(4, 8);
        }
        e.target.value = val;

        if (/^[A-F0-9]{4}-[A-F0-9]{4}$/.test(val)) {
            setTimeout(() => submitForm(val), 300);
        }
    });

    function submitForm(code) {
        hiddenCode.value = code;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memverifikasi...';
        form.submit();
    }

    form.addEventListener('submit', (e) => {
        if (isBackupMode) {
            hiddenCode.value = backupInput.value;
        } else {
            const code = Array.from(otpInputs).map(i => i.value).join('');
            if (code.length !== 6) {
                e.preventDefault();
                twofaCard.classList.remove('shake');
                setTimeout(() => twofaCard.classList.add('shake'), 10);
                return;
            }
            hiddenCode.value = code;
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memverifikasi...';
    });

    // Countdown 15 menit
    const TIMEOUT_MINUTES = 15;
    let remainingSeconds = TIMEOUT_MINUTES * 60;
    const timerEl = document.getElementById('timer');
    const countdownEl = document.getElementById('countdown');

    // Selalu mulai dari awal setiap halaman dibuka
let startTime = Date.now();
sessionStorage.setItem('2fa_start_time', startTime);
    if (!startTime) {
        startTime = Date.now();
        sessionStorage.setItem('2fa_start_time', startTime);
    } else {
        const elapsed = Math.floor((Date.now() - parseInt(startTime)) / 1000);
        remainingSeconds = Math.max(0, remainingSeconds - elapsed);
    }

    function updateTimer() {
        if (remainingSeconds <= 0) {
            timerEl.textContent = '00:00';
            countdownEl.classList.add('warning');
            countdownEl.innerHTML = '<i class="fas fa-clock"></i> <span>Kode kadaluarsa — <a href="logout.php" style="color:inherit;text-decoration:underline;">login ulang</a></span>';
            if (timerInterval) clearInterval(timerInterval);
            return;
        }

        const min = Math.floor(remainingSeconds / 60);
        const sec = remainingSeconds % 60;
        timerEl.textContent = String(min).padStart(2, '0') + ':' + String(sec).padStart(2, '0');

        if (remainingSeconds < 120) {
            countdownEl.classList.add('warning');
        }

        remainingSeconds--;
    }

    updateTimer();
    timerInterval = setInterval(updateTimer, 1000);

    form.addEventListener('submit', () => {
        if (timerInterval) clearInterval(timerInterval);
        sessionStorage.removeItem('2fa_start_time');
    });

    console.log('[2FA] Ready — Clipora theme');
})();
</script>

</body>
</html>
