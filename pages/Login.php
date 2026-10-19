<?php
/**
 * Login Page
 * File: login.php (di root, bukan di dalam app.php)
 * Standalone — tidak menggunakan layout app.php
 */

require_once __DIR__ . '/../config/appConfig.php';
require_once __DIR__ . '/../config/DBConfig.php';

// Konfigurasi session
ini_set('session.name', SESSION_NAME);
ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
session_start();

// Jika sudah login → redirect ke app
if (!empty($_SESSION['user'])) {
  header('Location: app.php');
  exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // CSRF check dasar
  if (empty($_POST['_token']) || $_POST['_token'] !== ($_SESSION['csrf_token'] ?? '')) {
    $error = 'Permintaan tidak valid. Silakan muat ulang halaman.';
  } else {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
      $error = 'Email dan password wajib diisi.';
    } else {
      $sql = "SELECT u.id, u.name, u.email, u.password, r.id_role, r.role 
      FROM users u 
      INNER JOIN role r ON u.role = r.id_role 
      WHERE u.email = ? LIMIT 1";

      $stmt = $conn->prepare($sql);
      $stmt->execute([$email]);
      $user = $stmt->fetch(PDO::FETCH_ASSOC);

      if ($user && password_verify($password, $user['password'])) {
        // Regenerate session ID untuk keamanan
        session_regenerate_id(true);

        $_SESSION['user'] = [
          'id' => $user['id'],
          'name' => $user['name'],
          'email' => $user['email'],
          'role' => $user['role'],
          'id_role' => $user['id_role'],
        ];
        $_SESSION['logged_in_at'] = time();

        // Redirect ke app
        $redirect = $_GET['redirect'] ?? 'app.php';
        $redirect = str_replace('pages/', '', $redirect);
        header('Location: ../' . $redirect);
        exit;
      } else {
        $error = 'Email atau password salah. Silakan coba lagi.';
      }
    }
  }
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login &mdash; <?= APP_NAME ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link
    href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap"
    rel="stylesheet">
  <style>
    *,
    *::before,
    *::after {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    :root {
      --primary: #4F46E5;
      --primary-dark: #3730A3;
      --primary-light: #EEF2FF;
      --surface: #FFFFFF;
      --bg: #F1F5F9;
      --text: #0F172A;
      --text-muted: #64748B;
      --border: #E2E8F0;
      --error-bg: #FEF2F2;
      --error-text: #991B1B;
      --error-border: #FECACA;
      --radius: 12px;
      --shadow: 0 20px 60px rgba(15, 23, 42, 0.12);
    }

    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      background: var(--bg);
      min-height: 100vh;
      display: grid;
      place-items: center;
      padding: 24px;
      background-image:
        radial-gradient(ellipse at 20% 50%, rgba(99, 102, 241, 0.06) 0%, transparent 60%),
        radial-gradient(ellipse at 80% 20%, rgba(139, 92, 246, 0.06) 0%, transparent 60%);
    }

    .login-wrapper {
      display: grid;
      grid-template-columns: 1fr 1fr;
      max-width: 920px;
      width: 100%;
      background: var(--surface);
      border-radius: 20px;
      box-shadow: var(--shadow);
      overflow: hidden;
      border: 1px solid var(--border);
    }

    /* ── LEFT PANEL ── */
    .login-left {
      background: linear-gradient(145deg, #4F46E5 0%, #7C3AED 100%);
      padding: 56px 48px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      color: #fff;
      position: relative;
      overflow: hidden;
    }

    .login-left::before {
      content: '';
      position: absolute;
      top: -80px;
      right: -80px;
      width: 260px;
      height: 260px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.06);
    }

    .login-left::after {
      content: '';
      position: absolute;
      bottom: -60px;
      left: -60px;
      width: 200px;
      height: 200px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.04);
    }

    .left-logo {
      display: flex;
      align-items: center;
      gap: 12px;
      position: relative;
      z-index: 1;
    }

    .left-logo-icon {
      width: 44px;
      height: 44px;
      border-radius: 12px;
      background: rgba(255, 255, 255, 0.2);
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .left-logo-icon svg {
      width: 26px;
      height: 26px;
    }

    .left-logo-name {
      font-size: 16px;
      font-weight: 700;
    }

    .left-copy {
      position: relative;
      z-index: 1;
    }

    .left-copy h2 {
      font-size: 28px;
      font-weight: 700;
      line-height: 1.3;
      margin-bottom: 12px;
    }

    .left-copy p {
      font-size: 14px;
      opacity: 0.8;
      line-height: 1.6;
    }

    .left-method {
      position: relative;
      z-index: 1;
      font-family: 'IBM Plex Mono', monospace;
      font-size: 12px;
      opacity: 0.6;
    }

    /* ── RIGHT PANEL ── */
    .login-right {
      padding: 56px 48px;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .login-right h1 {
      font-size: 24px;
      font-weight: 700;
      color: var(--text);
      margin-bottom: 6px;
    }

    .login-right .login-sub {
      font-size: 14px;
      color: var(--text-muted);
      margin-bottom: 32px;
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-label {
      display: block;
      font-size: 13px;
      font-weight: 600;
      color: var(--text);
      margin-bottom: 6px;
    }

    .form-control {
      width: 100%;
      padding: 11px 14px;
      border: 1.5px solid var(--border);
      border-radius: 10px;
      font-family: inherit;
      font-size: 14px;
      color: var(--text);
      background: #FAFAFA;
      outline: none;
      transition: border-color .2s, box-shadow .2s, background .2s;
    }

    .form-control:focus {
      border-color: var(--primary);
      background: #fff;
      box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12);
    }

    .form-control::placeholder {
      color: #CBD5E1;
    }

    .password-wrapper {
      position: relative;
    }

    .password-toggle {
      position: absolute;
      right: 12px;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      cursor: pointer;
      color: var(--text-muted);
      padding: 4px;
      display: flex;
      align-items: center;
    }

    .password-toggle svg {
      width: 18px;
      height: 18px;
    }

    .alert {
      padding: 12px 16px;
      border-radius: 10px;
      font-size: 13px;
      margin-bottom: 20px;
      display: flex;
      align-items: flex-start;
      gap: 10px;
    }

    .alert-error {
      background: var(--error-bg);
      color: var(--error-text);
      border: 1px solid var(--error-border);
    }

    .alert svg {
      flex-shrink: 0;
      width: 16px;
      height: 16px;
      margin-top: 1px;
    }

    .btn-login {
      width: 100%;
      padding: 13px;
      background: var(--primary);
      color: #fff;
      border: none;
      border-radius: 10px;
      font-size: 15px;
      font-weight: 600;
      font-family: inherit;
      cursor: pointer;
      transition: background .2s, transform .1s, box-shadow .2s;
      box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3);
    }

    .btn-login:hover {
      background: var(--primary-dark);
      box-shadow: 0 6px 20px rgba(79, 70, 229, 0.4);
    }

    .btn-login:active {
      transform: scale(0.98);
    }

    .login-demo {
      margin-top: 24px;
      padding-top: 20px;
      border-top: 1px solid var(--border);
      font-size: 12px;
      color: var(--text-muted);
      font-family: 'IBM Plex Mono', monospace;
    }

    .login-demo strong {
      color: var(--text);
      display: block;
      margin-bottom: 6px;
      font-size: 11px;
    }

    .demo-row {
      display: flex;
      gap: 8px;
      align-items: center;
      margin-top: 3px;
    }

    .demo-badge {
      background: var(--primary-light);
      color: var(--primary);
      padding: 2px 7px;
      border-radius: 5px;
      font-size: 10px;
      font-weight: 600;
    }

    /* Responsive */
    @media (max-width: 640px) {
      .login-wrapper {
        grid-template-columns: 1fr;
      }

      .login-left {
        display: none;
      }

      .login-right {
        padding: 40px 28px;
      }
    }

    .link {
      display: inline-block;
      margin-top: 8px;
      font-size: 12.5px;
      font-weight: 600;
      color: var(--primary);
      text-decoration: none;
      transition:
        color var(--transition),
        text-shadow var(--transition);
    }

    .link:hover {
      color: var(--primary-hover);
      text-shadow: 0 0 6px var(--primary-glow);
    }

    .link:active {
      transform: scale(0.97);
    }
  </style>
</head>

<body>
  <div class="login-wrapper">

    <!-- LEFT -->
    <div class="login-left">
      <div class="left-logo">
        <div class="left-logo-icon">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white">
            <path
              d="M18.375 2.25c-1.035 0-1.875.84-1.875 1.875v15.75c0 1.035.84 1.875 1.875 1.875h.75c1.035 0 1.875-.84 1.875-1.875V4.125c0-1.036-.84-1.875-1.875-1.875h-.75ZM9.75 8.625c0-1.036.84-1.875 1.875-1.875h.75c1.036 0 1.875.84 1.875 1.875v11.25c0 1.035-.84 1.875-1.875 1.875h-.75a1.875 1.875 0 0 1-1.875-1.875V8.625ZM3 13.125c0-1.036.84-1.875 1.875-1.875h.75c1.036 0 1.875.84 1.875 1.875v6.75c0 1.035-.84 1.875-1.875 1.875h-.75A1.875 1.875 0 0 1 3 19.875v-6.75Z" />
          </svg>
        </div>
        <span class="left-logo-name"><?= APP_NAME ?></span>
      </div>

      <div class="left-copy">
        <h2>Sistem Penunjang Keputusan Berbasis Logika Fuzzy</h2>
        <p>Gunakan metode Fuzzy Sugeno untuk pengambilan keputusan yang akurat, transparan, dan terstruktur.</p>
      </div>
    </div>

    <!-- RIGHT -->
    <div class="login-right">
      <h1>Masuk ke Sistem</h1>
      <p class="login-sub">Masukkan akun Anda untuk melanjutkan.</p>

      <?php if ($error): ?>
        <div class="alert alert-error">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
          </svg>
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="" novalidate>
        <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <div class="form-group">
          <label class="form-label" for="email">Alamat Email</label>
          <input type="email" id="email" name="email" class="form-control"
            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="admin@spk.local" autocomplete="email"
            required autofocus>
        </div>

        <div class="form-group">
          <label class="form-label" for="password">Password</label>
          <div class="password-wrapper">
            <input type="password" id="password" name="password" class="form-control" placeholder="••••••••"
              autocomplete="current-password" required>
            <button type="button" class="password-toggle" id="pwToggle" aria-label="Toggle password">
              <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
              </svg>
            </button>
          </div>
        </div>
        <!-- <div class="form-group">
          <a href="ResetPassword.php" class="link">Lupa Password?</a>
        </div> -->
        <button type="submit" class="btn-login">Masuk</button>
      </form>
    </div>
  </div>

  <script>
    // Toggle password visibility
    const pwToggle = document.getElementById('pwToggle');
    const pwInput = document.getElementById('password');
    pwToggle.addEventListener('click', () => {
      const isPassword = pwInput.type === 'password';
      pwInput.type = isPassword ? 'text' : 'password';
      document.getElementById('eyeIcon').innerHTML = isPassword
        ? '<path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/>'
        : '<path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>';
    });
  </script>
</body>

</html>