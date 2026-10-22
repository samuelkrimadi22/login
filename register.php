<?php
require_once __DIR__ . '/config/config.php';
if ($isLoggedIn) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $fullname = $_POST['full_name'] ?? '';

    $ch = curl_init(API_URL . '/auth/register');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode([
            'email' => $email,
            'username' => $username,
            'password' => $password,
            'full_name' => $fullname
        ]),
        CURLOPT_TIMEOUT => 15
    ]);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        $error = 'Tidak dapat terhubung ke server. Pastikan server berjalan.';
    } else {
        $data = json_decode($resp, true);

        if ($code === 201 && !empty($data['success'])) {
            setAuthToken($data['token']);
            // V9.6.0 SECURITY: Set session + cookie dengan flags aman
            $_SESSION['is_admin'] = !empty($data['user']['is_admin']);
            $_SESSION['user_plan'] = $data['user']['plan'] ?? 'free';
            $_SESSION['user_username'] = $data['user']['username'] ?? '';

            setcookie('clipora_username', $data['user']['username'], [
                'expires' => time() + 7 * 24 * 3600,
                'path' => '/',
                'httponly' => false,
                'samesite' => 'Lax'
            ]);

            setcookie('clipora_plan', $data['user']['plan'], [
                'expires' => time() + 7 * 24 * 3600,
                'path' => '/',
                'httponly' => false,
                'samesite' => 'Lax'
            ]);

            setcookie('clipora_is_admin', !empty($data['user']['is_admin']) ? '1' : '0', [
                'expires' => time() + 7 * 24 * 3600,
                'path' => '/',
                'httponly' => true,        // ⭐ JS tidak bisa baca/tulis
                'samesite' => 'Strict'
            ]);
            header('Location: dashboard.php');
            exit;
        }

        if (!empty($data['errors'])) {
            $error = implode(', ', array_map(fn($e) => $e['msg'], $data['errors']));
        } else {
            $error = $data['error'] ?? 'Registrasi gagal. Silakan coba lagi.';
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
    <title>Daftar | <?= APP_NAME ?></title>
    <meta name="description" content="Daftar akun Clipora gratis dan mulai edit video otomatis dengan AI.">
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* ============================================================
           AUTH PAGE — REGISTER
           ============================================================ */
        body {
            background-image:
                radial-gradient(circle at 20% 20%, rgba(139, 92, 246, 0.08) 0%, transparent 45%),
                radial-gradient(circle at 80% 80%, rgba(245, 158, 11, 0.06) 0%, transparent 45%),
                radial-gradient(circle at 50% 0%, rgba(245, 158, 11, 0.05) 0%, transparent 50%);
            background-attachment: fixed;
        }

        .auth-layout {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1fr 1fr;
            align-items: center;
            padding: 20px;
            gap: 40px;
            max-width: 1200px;
            margin: 0 auto;
        }

        /* LEFT SIDE - BRANDING */
        .auth-brand {
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 40px;
        }

        .auth-brand-logo {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 32px;
            text-decoration: none;
        }

        .auth-brand-logo-img {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            overflow: hidden;
            background: rgba(245, 158, 11, 0.08);
            border: 1px solid rgba(245, 158, 11, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .auth-brand-logo-img img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 4px;
        }

        .auth-brand-logo-text {
            font-size: 1.8rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary-light), var(--primary), var(--primary-dark));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.5px;
        }

        .auth-brand-logo-slogan {
            display: block;
            font-size: 0.7rem;
            color: var(--text-muted);
            letter-spacing: 1.5px;
            text-transform: uppercase;
            -webkit-text-fill-color: var(--text-muted);
            margin-top: 2px;
        }

        .auth-brand h1 {
            font-size: 2.5rem;
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 16px;
        }

        .auth-brand h1 span {
            background: linear-gradient(135deg, var(--primary-light), var(--primary), var(--primary-dark));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .auth-brand>p {
            font-size: 1.05rem;
            color: var(--text-muted);
            line-height: 1.7;
            max-width: 440px;
            margin-bottom: 32px;
        }

        .auth-features {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .auth-feature {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .auth-feature-icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: rgba(245, 158, 11, 0.1);
            border: 1px solid rgba(245, 158, 11, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 1rem;
            flex-shrink: 0;
        }

        .auth-feature-text strong {
            display: block;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text);
        }

        .auth-feature-text span {
            font-size: 0.8rem;
            color: var(--text-muted);
        }

        /* RIGHT SIDE - FORM */
        .auth-form-wrap {
            display: flex;
            justify-content: center;
        }

        .auth-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 40px;
            max-width: 460px;
            width: 100%;
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(20px);
        }

        .auth-card-header {
            text-align: center;
            margin-bottom: 28px;
        }

        .auth-card-icon {
            width: 60px;
            height: 60px;
            border-radius: 18px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            color: white;
            font-size: 1.4rem;
            box-shadow: 0 10px 30px rgba(245, 158, 11, 0.3);
        }

        .auth-card h2 {
            font-size: 1.6rem;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .auth-card-subtitle {
            color: var(--text-muted);
            font-size: 0.85rem;
        }

        .auth-card .form-group {
            margin-bottom: 16px;
        }

        .auth-card label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.85rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        .auth-input-wrap {
            position: relative;
        }

        .auth-input-wrap i.input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 0.9rem;
            pointer-events: none;
            transition: 0.3s;
        }

        .auth-input-wrap input {
            width: 100%;
            padding: 14px 16px 14px 44px;
            background: rgba(0, 0, 0, 0.35);
            border: 1px solid var(--border);
            border-radius: 14px;
            color: white;
            font-size: 0.95rem;
            font-family: inherit;
            transition: 0.3s;
        }

        .auth-input-wrap input:focus {
            outline: none;
            border-color: var(--primary);
            background: rgba(0, 0, 0, 0.5);
            box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.08);
        }

        .auth-input-wrap input:focus+i.input-icon,
        .auth-input-wrap input:not(:placeholder-shown)+i.input-icon {
            color: var(--primary);
        }

        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            font-size: 0.9rem;
            padding: 4px;
            transition: 0.3s;
        }

        .password-toggle:hover {
            color: var(--primary);
        }

        /* Password strength */
        .password-strength {
            margin-top: 8px;
            display: flex;
            gap: 4px;
            height: 4px;
        }

        .password-strength-bar {
            flex: 1;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 10px;
            transition: 0.3s;
        }

        .password-strength-text {
            font-size: 0.7rem;
            color: var(--text-muted);
            margin-top: 6px;
        }

        .password-strength.weak .password-strength-bar:nth-child(1) {
            background: #ef4444;
        }

        .password-strength.fair .password-strength-bar:nth-child(-n+2) {
            background: #f59e0b;
        }

        .password-strength.good .password-strength-bar:nth-child(-n+3) {
            background: #22c55e;
        }

        .password-strength.strong .password-strength-bar {
            background: #22c55e;
        }

        /* Username availability */
        .input-hint {
            font-size: 0.7rem;
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .input-hint.valid {
            color: var(--success);
        }

        .input-hint.invalid {
            color: var(--secondary);
        }

        .auth-terms {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin: 20px 0;
            font-size: 0.8rem;
            color: var(--text-muted);
            line-height: 1.5;
        }

        .auth-terms input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: var(--primary);
            cursor: pointer;
            margin-top: 2px;
            flex-shrink: 0;
        }

        .auth-terms a {
            color: var(--primary);
            text-decoration: none;
        }

        .auth-terms a:hover {
            text-decoration: underline;
        }

        .btn-register-submit {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border: none;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 4px 20px rgba(245, 158, 11, 0.25);
            font-family: inherit;
        }

        .btn-register-submit:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 32px rgba(245, 158, 11, 0.4);
        }

        .btn-register-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .auth-switch {
            text-align: center;
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-top: 20px;
        }

        .auth-switch a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .auth-switch a:hover {
            text-decoration: underline;
        }

        .auth-benefits {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .auth-benefit {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.7rem;
            color: var(--text-muted);
        }

        .auth-benefit i {
            color: var(--success);
            font-size: 0.75rem;
        }

        /* Responsive */
        @media (max-width: 900px) {
            .auth-layout {
                grid-template-columns: 1fr;
                max-width: 480px;
                gap: 20px;
            }

            .auth-brand {
                padding: 20px 0;
                text-align: center;
                align-items: center;
            }

            .auth-brand>p {
                margin-left: auto;
                margin-right: auto;
            }

            .auth-brand h1 {
                font-size: 1.8rem;
            }

            .auth-features {
                display: none;
            }

            .auth-card {
                padding: 32px 24px;
            }
        }

        @media (max-width: 480px) {
            .auth-brand h1 {
                font-size: 1.5rem;
            }

            .auth-brand-logo-text {
                font-size: 1.4rem;
            }

            .auth-brand-logo-img {
                width: 44px;
                height: 44px;
            }

            .auth-card {
                padding: 28px 20px;
            }

            .auth-card h2 {
                font-size: 1.3rem;
            }

            .auth-benefits {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>

<body>

    <div class="auth-layout">

        <!-- ============ LEFT: BRANDING ============ -->
        <div class="auth-brand">
            <a href="index.php" class="auth-brand-logo">
                <div class="auth-brand-logo-img">
                    <img src="logo_clipora.png" alt="<?= APP_NAME ?> Logo">
                </div>
                <div>
                    <span class="auth-brand-logo-text"><?= APP_NAME ?></span>
                    <span class="auth-brand-logo-slogan"><?= APP_TAGLINE ?></span>
                </div>
            </a>

            <h1>Mulai <span>Gratis</span> Sekarang</h1>
            <p>Daftar akun Clipora dan dapatkan video gratis setiap hari. Tidak perlu kartu kredit!</p>

            <div class="auth-features">
                <div class="auth-feature">
                    <div class="auth-feature-icon"><i class="fas fa-gift"></i></div>
                    <div class="auth-feature-text">
                        <strong>Video Gratis / Hari</strong>
                        <span>Tanpa biaya, tanpa kartu kredit</span>
                    </div>
                </div>
                <div class="auth-feature">
                    <div class="auth-feature-icon"><i class="fas fa-robot"></i></div>
                    <div class="auth-feature-text">
                        <strong>AI Auto-Cut</strong>
                        <span>Potong jeda, kata dobol, subtitle otomatis</span>
                    </div>
                </div>
                <div class="auth-feature">
                    <div class="auth-feature-icon"><i class="fas fa-chart-line"></i></div>
                    <div class="auth-feature-text">
                        <strong>Dashboard & Analitik</strong>
                        <span>Pantau riwayat dan statistik video Anda</span>
                    </div>
                </div>
                <div class="auth-feature">
                    <div class="auth-feature-icon"><i class="fas fa-key"></i></div>
                    <div class="auth-feature-text">
                        <strong>API Keys</strong>
                        <span>Integrasi dengan aplikasi Anda sendiri</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============ RIGHT: FORM ============ -->
        <div class="auth-form-wrap">
            <div class="auth-card">
                <div class="auth-card-header">
                    <div class="auth-card-icon">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <h2>Buat Akun Baru</h2>
                    <p class="auth-card-subtitle">Gratis selamanya. Tidak perlu kartu kredit.</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" id="registerForm" autocomplete="on">
                    <div class="form-group">
                        <label for="full_name">Nama Lengkap <span style="color:var(--text-muted);font-weight:400;">(opsional)</span></label>
                        <div class="auth-input-wrap">
                            <input type="text" id="full_name" name="full_name" placeholder="John Doe" autocomplete="name">
                            <i class="fas fa-user input-icon"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="username">Username</label>
                        <div class="auth-input-wrap">
                            <input type="text" id="username" name="username" placeholder="john_doe"
                                required minlength="3" maxlength="30"
                                pattern="[a-zA-Z0-9_]+"
                                autocomplete="username">
                            <i class="fas fa-at input-icon"></i>
                        </div>
                        <div class="input-hint" id="usernameHint" style="display:none;">
                            <i class="fas fa-info-circle"></i>
                            <span>3-30 karakter, hanya huruf, angka, dan underscore</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <div class="auth-input-wrap">
                            <input type="email" id="email" name="email" placeholder="nama@email.com" required autocomplete="email">
                            <i class="fas fa-envelope input-icon"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="auth-input-wrap">
                            <input type="password" id="password" name="password" placeholder="Min 6 karakter"
                                required minlength="6" autocomplete="new-password">
                            <i class="fas fa-lock input-icon"></i>
                            <button type="button" class="password-toggle" id="togglePassword" aria-label="Tampilkan password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="password-strength" id="passwordStrength">
                            <div class="password-strength-bar"></div>
                            <div class="password-strength-bar"></div>
                            <div class="password-strength-bar"></div>
                            <div class="password-strength-bar"></div>
                        </div>
                        <div class="password-strength-text" id="passwordStrengthText">Masukkan password</div>
                    </div>

                    <div class="auth-terms">
                        <input type="checkbox" id="agreeTerms" name="agree_terms" required>
                        <label for="agreeTerms" style="margin:0;color:var(--text-muted);font-weight:400;font-size:0.8rem;line-height:1.5;cursor:pointer;">
                            Saya menyetujui <a href="#" target="_blank">Syarat & Ketentuan</a> dan <a href="#" target="_blank">Kebijakan Privasi</a> Clipora
                        </label>
                    </div>

                    <button type="submit" class="btn-register-submit" id="submitBtn">
                        <i class="fas fa-rocket"></i> <span>Daftar Gratis Sekarang</span>
                    </button>
                </form>

                <div class="auth-switch">
                    Sudah punya akun? <a href="login.php">Login di sini</a>
                </div>

                <div class="auth-benefits">
                    <div class="auth-benefit">
                        <i class="fas fa-check-circle"></i> Gratis selamanya
                    </div>
                    <div class="auth-benefit">
                        <i class="fas fa-check-circle"></i> Tanpa kartu kredit
                    </div>
                    <div class="auth-benefit">
                        <i class="fas fa-check-circle"></i> Siap dalam 30 detik
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        // ===== TOGGLE PASSWORD =====
        const toggleBtn = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        if (toggleBtn && passwordInput) {
            toggleBtn.addEventListener('click', () => {
                const isPassword = passwordInput.type === 'password';
                passwordInput.type = isPassword ? 'text' : 'password';
                toggleBtn.innerHTML = isPassword ?
                    '<i class="fas fa-eye-slash"></i>' :
                    '<i class="fas fa-eye"></i>';
            });
        }

        // ===== PASSWORD STRENGTH =====
        const strengthBar = document.getElementById('passwordStrength');
        const strengthText = document.getElementById('passwordStrengthText');

        if (passwordInput && strengthBar && strengthText) {
            passwordInput.addEventListener('input', () => {
                const val = passwordInput.value;
                let score = 0;

                if (val.length >= 6) score++;
                if (val.length >= 10) score++;
                if (/[A-Z]/.test(val) && /[a-z]/.test(val)) score++;
                if (/[0-9]/.test(val)) score++;
                if (/[^A-Za-z0-9]/.test(val)) score++;

                strengthBar.className = 'password-strength';
                if (val.length === 0) {
                    strengthText.textContent = 'Masukkan password';
                } else if (score <= 1) {
                    strengthBar.classList.add('weak');
                    strengthText.textContent = '🔴 Password lemah';
                } else if (score === 2) {
                    strengthBar.classList.add('fair');
                    strengthText.textContent = '🟡 Password sedang';
                } else if (score === 3) {
                    strengthBar.classList.add('good');
                    strengthText.textContent = '🟢 Password bagus';
                } else {
                    strengthBar.classList.add('strong');
                    strengthText.textContent = '✅ Password kuat';
                }
            });
        }

        // ===== USERNAME HINT =====
        const usernameInput = document.getElementById('username');
        const usernameHint = document.getElementById('usernameHint');
        if (usernameInput && usernameHint) {
            usernameInput.addEventListener('focus', () => {
                usernameHint.style.display = 'flex';
                usernameHint.className = 'input-hint';
            });
            usernameInput.addEventListener('blur', () => {
                if (!usernameInput.value) {
                    usernameHint.style.display = 'none';
                }
            });
            usernameInput.addEventListener('input', () => {
                const val = usernameInput.value;
                const valid = /^[a-zA-Z0-9_]{3,30}$/.test(val);
                if (val.length === 0) {
                    usernameHint.className = 'input-hint';
                    usernameHint.querySelector('span').textContent = '3-30 karakter, hanya huruf, angka, dan underscore';
                } else if (valid) {
                    usernameHint.className = 'input-hint valid';
                    usernameHint.querySelector('span').textContent = '✓ Username valid';
                } else {
                    usernameHint.className = 'input-hint invalid';
                    usernameHint.querySelector('span').textContent = '✗ Gunakan 3-30 karakter alfanumerik atau underscore';
                }
            });
        }

        // ===== SUBMIT STATE =====
        const registerForm = document.getElementById('registerForm');
        const submitBtn = document.getElementById('submitBtn');
        if (registerForm && submitBtn) {
            registerForm.addEventListener('submit', (e) => {
                const agree = document.getElementById('agreeTerms');
                if (agree && !agree.checked) {
                    e.preventDefault();
                    alert('Anda harus menyetujui Syarat & Ketentuan terlebih dahulu.');
                    return;
                }

                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Membuat akun...</span>';
            });
        }
    </script>

</body>

</html>