<?php
require_once __DIR__ . '/config/config.php';

if ($isLoggedIn) {
    header('Location: index.php');
    exit;
}

$token = $_GET['token'] ?? '';
$error = '';
$success = '';

if (empty($token)) {
    $error = 'Link reset tidak valid. Silakan minta link baru.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($token)) {
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (empty($password) || strlen($password) < 6) {
        $error = 'Password minimal 6 karakter';
    } elseif ($password !== $confirm) {
        $error = 'Konfirmasi password tidak cocok';
    } else {
        $ch = curl_init(API_URL . '/auth/reset-password');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode(['token' => $token, 'password' => $password]),
            CURLOPT_TIMEOUT => 15
        ]);
        $resp = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            $error = 'Tidak dapat terhubung ke server';
        } else {
            $data = json_decode($resp, true);
            if ($httpCode === 200 && !empty($data['success'])) {
                $success = 'Password berhasil direset! Silakan login dengan password baru Anda.';
                $token = '';
            } else {
                $error = $data['error'] ?? 'Gagal reset password';
            }
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
    <title>Reset Password | <?= APP_NAME ?></title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background: #0a0a0f;
            background-image:
                radial-gradient(circle at 20% 20%, rgba(139,92,246,0.08) 0%, transparent 45%),
                radial-gradient(circle at 80% 80%, rgba(6,182,212,0.06) 0%, transparent 45%);
            color: #e8e8ef;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .topbar {
            max-width: 1200px;
            width: 100%;
            margin: 0 auto;
            padding: 22px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo { display: flex; align-items: center; gap: 12px; text-decoration: none; }
        .logo img { width: 40px; height: 40px; border-radius: 12px; }
        .logo span {
            font-size: 1.05rem; font-weight: 800;
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }
        .back-btn {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 9px 16px;
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 50px;
            color: #94a3b8; font-size: 0.82rem; font-weight: 600;
            text-decoration: none; transition: 0.25s;
        }
        .back-btn:hover { background: rgba(139,92,246,0.08); border-color: rgba(139,92,246,0.3); color: #a78bfa; }
        .wrap { flex: 1; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .card {
            width: 100%; max-width: 440px;
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 24px;
            padding: 40px 32px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            backdrop-filter: blur(20px);
            position: relative; overflow: hidden;
        }
        .card::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
            background: linear-gradient(90deg, #8b5cf6, #06b6d4);
        }
        .icon-circle {
            width: 72px; height: 72px; border-radius: 50%;
            background: rgba(139,92,246,0.12);
            border: 1px solid rgba(139,92,246,0.25);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px;
            font-size: 2rem; color: #a78bfa;
        }
        h1 { text-align: center; font-size: 1.6rem; font-weight: 700; margin-bottom: 8px; }
        .sub { text-align: center; color: #94a3b8; font-size: 0.9rem; margin-bottom: 28px; line-height: 1.5; }
        .form-group { margin-bottom: 18px; }
        label {
            display: block; font-size: 0.75rem; font-weight: 600;
            color: #94a3b8; margin-bottom: 8px;
            text-transform: uppercase; letter-spacing: 0.7px;
        }
        .input-wrap { position: relative; }
        .input-wrap i {
            position: absolute; left: 16px; top: 50%; transform: translateY(-50%);
            color: #6b7280; font-size: 0.9rem;
        }
        input {
            width: 100%; padding: 14px 16px 14px 44px;
            background: rgba(0,0,0,0.35);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 12px; color: #e8e8ef;
            font-size: 0.95rem; font-family: inherit;
            transition: 0.25s; outline: none;
        }
        input:focus {
            border-color: #8b5cf6;
            background: rgba(139,92,246,0.05);
            box-shadow: 0 0 0 4px rgba(139,92,246,0.1);
        }
        .btn {
            width: 100%; padding: 14px 24px;
            background: linear-gradient(135deg, #8b5cf6, #06b6d4);
            border: none; border-radius: 50px;
            color: white; font-weight: 700; font-size: 0.95rem;
            cursor: pointer; font-family: inherit;
            display: flex; align-items: center; justify-content: center; gap: 10px;
            box-shadow: 0 4px 20px rgba(139,92,246,0.25);
            transition: 0.3s; margin-top: 6px;
            text-decoration: none;
        }
        .btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(139,92,246,0.4);
        }
        .btn:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
        .alert {
            padding: 14px 18px; border-radius: 12px;
            font-size: 0.88rem; margin-bottom: 20px;
            display: flex; align-items: flex-start; gap: 10px;
            line-height: 1.5;
        }
        .alert i { margin-top: 3px; flex-shrink: 0; }
        .alert-error { background: rgba(239,68,68,0.12); border: 1px solid rgba(239,68,68,0.3); color: #f87171; }
        .alert-success { background: rgba(34,197,94,0.12); border: 1px solid rgba(34,197,94,0.3); color: #4ade80; }
        .back-link { text-align: center; margin-top: 24px; font-size: 0.85rem; color: #94a3b8; }
        .back-link a { color: #f59e0b; text-decoration: none; font-weight: 600; }
        .back-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<header class="topbar">
    <a href="index.php" class="logo">
        <img src="logo_clipora.png" alt="<?= APP_NAME ?>">
        <span><?= APP_NAME ?></span>
    </a>
    <a href="login.php" class="back-btn">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
</header>

<main class="wrap">
    <div class="card">
        <div class="icon-circle">
            <i class="fas fa-key"></i>
        </div>

        <?php if ($success): ?>
            <h1>✅ Berhasil!</h1>
            <p class="sub">Password Anda sudah direset.</p>

            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span><?= htmlspecialchars($success) ?></span>
            </div>

            <a href="login.php" class="btn">
                <i class="fas fa-sign-in-alt"></i> Login Sekarang
            </a>

        <?php elseif ($error && empty($token)): ?>
            <h1>❌ Link Tidak Valid</h1>
            <p class="sub">Link reset password tidak valid atau sudah kadaluarsa.</p>

            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>

            <a href="forgot.php" class="btn">
                <i class="fas fa-redo"></i> Minta Link Baru
            </a>

        <?php else: ?>
            <h1>Reset Password</h1>
            <p class="sub">Masukkan password baru untuk akun Anda.</p>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" id="resetForm">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                <div class="form-group">
                    <label for="password">Password Baru</label>
                    <div class="input-wrap">
                        <input type="password" id="password" name="password" placeholder="Min 6 karakter" required minlength="6">
                        <i class="fas fa-lock"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Konfirmasi Password</label>
                    <div class="input-wrap">
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Ulangi password baru" required minlength="6">
                        <i class="fas fa-lock"></i>
                    </div>
                </div>

                <button type="submit" class="btn" id="submitBtn">
                    <i class="fas fa-save"></i> Simpan Password Baru
                </button>
            </form>

            <div class="back-link">
                Ingat password? <a href="login.php">Kembali ke Login</a>
            </div>
        <?php endif; ?>
    </div>
</main>

<script>
    document.getElementById('resetForm')?.addEventListener('submit', function(e) {
        const btn = document.getElementById('submitBtn');
        const pass = document.getElementById('password').value;
        const confirm = document.getElementById('confirm_password').value;

        if (pass !== confirm) {
            e.preventDefault();
            alert('Konfirmasi password tidak cocok');
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
    });
</script>

</body>
</html>