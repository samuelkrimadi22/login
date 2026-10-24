<?php

require_once __DIR__ . '/config/config.php';



// Redirect kalau sudah login

if ($isLoggedIn) {

    if (!empty($isAdmin)) {

        header('Location: admin/index.php');

    } else {

        header('Location: dashboard.php');

    }

    exit;

}



$error = '';

$success = '';



// Handle logout message

if (isset($_GET['logout']) && $_GET['logout'] === 'true') {

    $success = 'Anda telah berhasil logout.';

}



// V8.9.0: Handle expired session dari auto-redirect interceptor

if (isset($_GET['expired']) && $_GET['expired'] === '1') {

    $error = '- Sesi Anda sudah berakhir. Silakan login ulang untuk melanjutkan.';

}



if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = $_POST['email'] ?? '';

    $password = $_POST['password'] ?? '';



    $ch = curl_init(API_URL . '/auth/login');

    curl_setopt_array($ch, [

        CURLOPT_POST => true,

        CURLOPT_RETURNTRANSFER => true,

        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],

        CURLOPT_POSTFIELDS => json_encode(['email' => $email, 'password' => $password]),

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

        if ($code === 200 && !empty($data['success'])) {

            // Simpan token + user info ke cookie (via helper di config.php)

            

            // - V9.7.0: Cek apakah butuh 2FA

            if (!empty($data['requires_2fa'])) {

                // Simpan temp_token di session untuk verifikasi

                $_SESSION['temp_2fa_token'] = $data['temp_token'];

                $_SESSION['temp_2fa_email'] = $email;

                $_SESSION['temp_2fa_time'] = time();

                

                // Redirect ke halaman verify 2FA

                header('Location: login-2fa.php');

                exit;

            }

            setAuthToken($data['token'], $data['user']);



            // Simpan juga ke session untuk admin panel & API fetch

            $_SESSION['user_id']       = $data['user']['id'];

            $_SESSION['user_name']     = $data['user']['username'];

            $_SESSION['user_email']    = $data['user']['email'];

            $_SESSION['is_admin']      = !empty($data['user']['is_admin']) ? 1 : 0;

            $_SESSION['is_subscribed'] = !empty($data['user']['is_subscribed']);

            $_SESSION['auth_token']    = $data['token'];



            // Redirect sesuai role

            if (!empty($data['user']['is_admin'])) {

                header('Location: admin/index.php');

            } else {

                header('Location: dashboard.php');

            }

            exit;

        }

        $error = $data['error'] ?? 'Email atau password salah.';

    }

}



// V8.9.0: Deteksi jenis pesan (expired vs error biasa)

$isExpiredMsg = (isset($_GET['expired']) && $_GET['expired'] === '1');

?>

<!DOCTYPE html>

<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login | <?= APP_NAME ?></title>

    <meta name="description" content="Login ke akun Clipora Anda.">

    <link rel="icon" href="logo_clipora.png" type="image/png">

    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <link rel="stylesheet" href="assets/css/style.css">

    <style>

        /* ============================================================

           AUTH PAGE - LOGIN

           ============================================================ */

        body {

            background-image: 

                radial-gradient(circle at 20% 20%, rgba(245,158,11,0.08) 0%, transparent 45%),

                radial-gradient(circle at 80% 80%, rgba(139,92,246,0.06) 0%, transparent 45%),

                radial-gradient(circle at 50% 0%, rgba(245,158,11,0.05) 0%, transparent 50%);

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

            background: rgba(245,158,11,0.08);

            border: 1px solid rgba(245,158,11,0.2);

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

        .auth-brand > p {

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

            background: rgba(245,158,11,0.1);

            border: 1px solid rgba(245,158,11,0.2);

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

            background: rgba(255,255,255,0.03);

            border: 1px solid var(--border);

            border-radius: 24px;

            padding: 40px;

            max-width: 440px;

            width: 100%;

            box-shadow: 0 30px 80px rgba(0,0,0,0.4);

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

            box-shadow: 0 10px 30px rgba(245,158,11,0.3);

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

            margin-bottom: 18px;

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

            background: rgba(0,0,0,0.35);

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

            background: rgba(0,0,0,0.5);

            box-shadow: 0 0 0 4px rgba(245,158,11,0.08);

        }

        .auth-input-wrap input:focus + i.input-icon,

        .auth-input-wrap input:not(:placeholder-shown) + i.input-icon {

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

        .password-toggle:hover { color: var(--primary); }



        .auth-extras {

            display: flex;

            justify-content: space-between;

            align-items: center;

            margin-bottom: 20px;

            font-size: 0.8rem;

        }

        .auth-extras label {

            display: flex;

            align-items: center;

            gap: 8px;

            color: var(--text-muted);

            cursor: pointer;

            margin: 0;

        }

        .auth-extras input[type="checkbox"] {

            width: 16px;

            height: 16px;

            accent-color: var(--primary);

            cursor: pointer;

        }

        .auth-extras a {

            color: var(--primary);

            text-decoration: none;

            font-weight: 500;

        }

        .auth-extras a:hover { text-decoration: underline; }



        .btn-login-submit {

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

            box-shadow: 0 4px 20px rgba(245,158,11,0.25);

            font-family: inherit;

        }

        .btn-login-submit:hover:not(:disabled) {

            transform: translateY(-2px);

            box-shadow: 0 8px 32px rgba(245,158,11,0.4);

        }

        .btn-login-submit:disabled {

            opacity: 0.6;

            cursor: not-allowed;

        }



        .auth-divider {

            display: flex;

            align-items: center;

            gap: 12px;

            margin: 24px 0;

            color: var(--text-muted);

            font-size: 0.75rem;

        }

        .auth-divider::before,

        .auth-divider::after {

            content: '';

            flex: 1;

            height: 1px;

            background: var(--border);

        }



        .auth-switch {

            text-align: center;

            font-size: 0.85rem;

            color: var(--text-muted);

        }

        .auth-switch a {

            color: var(--primary);

            text-decoration: none;

            font-weight: 600;

        }

        .auth-switch a:hover { text-decoration: underline; }



        .auth-footer-note {

            text-align: center;

            margin-top: 20px;

            font-size: 0.75rem;

            color: var(--text-muted);

            line-height: 1.6;

        }

        .auth-footer-note a {

            color: var(--primary);

            text-decoration: none;

        }



        /* ============================================================ */

        /* V8.9.0: ALERT WARNING (untuk expired session)                */

        /* ============================================================ */

        .alert-warning {

            display: flex;

            align-items: center;

            gap: 10px;

            padding: 14px 18px;

            background: linear-gradient(135deg, rgba(245,158,11,0.12), rgba(245,158,11,0.05));

            border: 1px solid rgba(245,158,11,0.35);

            border-radius: 12px;

            color: #f59e0b;

            font-size: 0.85rem;

            font-weight: 500;

            line-height: 1.5;

            margin-bottom: 20px;

            animation: alertSlideIn 0.4s ease;

        }

        .alert-warning i {

            font-size: 1.1rem;

            flex-shrink: 0;

        }

        @keyframes alertSlideIn {

            from { opacity: 0; transform: translateY(-6px); }

            to { opacity: 1; transform: translateY(0); }

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

            .auth-brand > p { margin-left: auto; margin-right: auto; }

            .auth-brand h1 { font-size: 1.8rem; }

            .auth-features {

                display: none;

            }

            .auth-card { padding: 32px 24px; }

        }

        @media (max-width: 480px) {

            .auth-brand h1 { font-size: 1.5rem; }

            .auth-brand-logo-text { font-size: 1.4rem; }

            .auth-brand-logo-img { width: 44px; height: 44px; }

            .auth-card { padding: 28px 20px; }

            .auth-card h2 { font-size: 1.3rem; }

        }

    </style>

</head>

<body>



<main>
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



        <h1>Selamat Datang <span>Kembali</span></h1>

        <p>Login untuk melanjutkan dan nikmati fitur AI Auto-Cut, subtitle otomatis, dan pemrosesan video tanpa batas.</p>



        <div class="auth-features">

            <div class="auth-feature">

                <div class="auth-feature-icon"><i class="fas fa-bolt"></i></div>

                <div class="auth-feature-text">

                    <strong>Proses Cepat</strong>

                    <span>AI Auto-Cut dalam hitungan detik</span>

                </div>

            </div>

            <div class="auth-feature">

                <div class="auth-feature-icon"><i class="fas fa-shield-alt"></i></div>

                <div class="auth-feature-text">

                    <strong>Aman & Privat</strong>

                    <span>Video terhapus otomatis setelah 1 jam</span>

                </div>

            </div>

            <div class="auth-feature">

                <div class="auth-feature-icon"><i class="fas fa-gem"></i></div>

                <div class="auth-feature-text">

                    <strong>Upgrade Kapan Saja</strong>

                    <span>Tanpa batas upload dengan paket premium</span>

                </div>

            </div>

        </div>

    </div>



    <!-- ============ RIGHT: FORM ============ -->

    <div class="auth-form-wrap">

        <div class="auth-card">

            <div class="auth-card-header">

                <div class="auth-card-icon">

                    <i class="fas fa-sign-in-alt"></i>

                </div>

                <h2>Login Akun</h2>

                <p class="auth-card-subtitle">Masuk untuk melanjutkan ke dashboard</p>

            </div>



            <?php if ($error): ?>

                <div class="<?= $isExpiredMsg ? 'alert-warning' : 'alert-error' ?>">

                    <i class="fas <?= $isExpiredMsg ? 'fa-clock' : 'fa-exclamation-circle' ?>"></i>

                    <?= htmlspecialchars($error) ?>

                </div>

            <?php endif; ?>



            <?php if ($success): ?>

                <div class="alert-success">

                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>

                </div>

            <?php endif; ?>



            <form method="POST" id="loginForm" autocomplete="on">

                <div class="form-group">

                    <label for="email">Email</label>

                    <div class="auth-input-wrap">

                        <input type="email" id="email" name="email" placeholder="nama@email.com" required autofocus autocomplete="email">

                        <i class="fas fa-envelope input-icon"></i>

                    </div>

                </div>



                <div class="form-group">

                    <label for="password">Password</label>

                    <div class="auth-input-wrap">

                        <input type="password" id="password" name="password" placeholder="Masukkan password" required autocomplete="current-password">

                        <i class="fas fa-lock input-icon"></i>

                        <button type="button" class="password-toggle" id="togglePassword" aria-label="Tampilkan password">

                            <i class="fas fa-eye"></i>

                        </button>

                    </div>

                </div>



                <div class="auth-extras">

                    <label>

                        <input type="checkbox" name="remember" checked>

                        Ingat saya

                    </label>

                    <a href="forgot.php">Lupa password?</a>

                </div>



                <button type="submit" class="btn-login-submit" id="submitBtn">

                    <i class="fas fa-sign-in-alt"></i> <span>Login Sekarang</span>

                </button>

            </form>



            <div class="auth-divider">ATAU</div>

            <!-- ============================================================ -->
            <!-- GOOGLE LOGIN BUTTON                                          -->
            <!-- ============================================================ -->
           <a href="http://38.103.170.165:3000/api/auth/google"
               class="btn-google-login"
               id="googleLoginBtn"
               style="display: flex; align-items: center; justify-content: center; gap: 12px;
                      background: #ffffff; color: #1f2937; border: 2px solid #e5e7eb;
                      padding: 14px 20px; border-radius: 12px; text-decoration: none;
                      font-weight: 600; font-size: 15px; margin-top: 16px;
                      transition: all 0.2s ease; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                <svg width="22" height="22" viewBox="0 0 48 48" style="flex-shrink: 0;">
                    <path fill="#FFC107" d="M43.611,20.083H42V20H24v8h11.303c-1.649,4.657-6.08,8-11.303,8c-6.627,0-12-5.373-12-12c0-6.627,5.373-12,12-12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C12.955,4,4,12.955,4,24c0,11.045,8.955,20,20,20c11.045,0,20-8.955,20-20C44,22.659,43.862,21.35,43.611,20.083z"/>
                    <path fill="#FF3D00" d="M6.306,14.691l6.571,4.819C14.655,15.108,18.961,12,24,12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C16.318,4,9.656,8.337,6.306,14.691z"/>
                    <path fill="#4CAF50" d="M24,44c5.166,0,9.86-1.977,13.409-5.192l-6.19-5.238C29.211,35.091,26.715,36,24,36c-5.202,0-9.619-3.317-11.283-7.946l-6.522,5.025C9.505,39.556,16.227,44,24,44z"/>
                    <path fill="#1976D2" d="M43.611,20.083H42V20H24v8h11.303c-0.792,2.237-2.231,4.166-4.087,5.571c0.001-0.001,0.002-0.001,0.003-0.002l6.19,5.238C36.971,39.205,44,34,44,24C44,22.659,43.862,21.35,43.611,20.083z"/>
                </svg>
                <span>Lanjutkan dengan Google</span>
            </a>

            <style>
            .btn-google-login:hover {
                background: #f9fafb !important;
                border-color: #8b5cf6 !important;
                box-shadow: 0 4px 16px rgba(139, 92, 246, 0.2) !important;
                transform: translateY(-1px);
            }
            .btn-google-login:active {
                transform: translateY(0);
            }
            </style>




            <div class="auth-switch">

                Belum punya akun? <a href="register.php">Daftar Gratis</a>

            </div>



            <div class="auth-footer-note">

                Dengan login, Anda menyetujui <a href="#">Syarat & Ketentuan</a> dan <a href="#">Kebijakan Privasi</a> kami.

            </div>

        </div>

    </div>



</main>



<script>

    // ===== TOGGLE PASSWORD =====

    const toggleBtn = document.getElementById('togglePassword');

    const passwordInput = document.getElementById('password');

    if (toggleBtn && passwordInput) {

        toggleBtn.addEventListener('click', () => {

            const isPassword = passwordInput.type === 'password';

            passwordInput.type = isPassword ? 'text' : 'password';

            toggleBtn.innerHTML = isPassword

                ? '<i class="fas fa-eye-slash"></i>'

                : '<i class="fas fa-eye"></i>';

        });

    }



    // ===== SUBMIT STATE =====

    const loginForm = document.getElementById('loginForm');

    const submitBtn = document.getElementById('submitBtn');

    if (loginForm && submitBtn) {

        loginForm.addEventListener('submit', () => {

            submitBtn.disabled = true;

            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Memproses...</span>';

        });

    }

</script>



</body>

</html>

