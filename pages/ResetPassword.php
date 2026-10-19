<?php
/**
 * reset_password.php
 * Halaman Reset Password — Standalone (di luar layout app.php)
 * Flow 3 tahap:
 *   Step 1 : Input email → kirim OTP/link
 *   Step 2 : Verifikasi kode OTP
 *   Step 3 : Set password baru
 *
 */
require_once __DIR__ . '/../config/appConfig.php';
require_once __DIR__ . '/../config/DBConfig.php';
require_once __DIR__ . '/../config/helper.php';

ini_set('session.name', SESSION_NAME);
session_start();

if (!empty($_SESSION['user'])) {
  header('Location: app.php');
  exit;
}

$step = (int) ($_SESSION['reset_step'] ?? 1);
$email = $_SESSION['reset_email'] ?? '';
$error = '';
$success = '';

if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (empty($_POST['_token']) || $_POST['_token'] !== $_SESSION['csrf_token']) {
    $error = 'Permintaan tidak valid. Silakan muat ulang halaman.';
  } else {
    $action = $_POST['action'] ?? '';

    if ($action === 'send_otp' && $step === 1) {
      $inputEmail = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
      if (!$inputEmail) {
        $error = 'Masukkan alamat email yang valid.';
      } else {
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $_SESSION['reset_email'] = $inputEmail;
        $_SESSION['reset_otp'] = password_hash($otp, PASSWORD_BCRYPT);
        $_SESSION['reset_otp_expiry'] = time() + 600;
        $_SESSION['reset_otp_tries'] = 0;
        $_SESSION['reset_step'] = 2;

        if (sendOTPEmail($inputEmail, $otp)) {
          $email = $inputEmail;
          $step = 2;
        } else {
          $error = 'Gagal mengirim email. Silakan coba lagi nanti.';
          foreach (['reset_email', 'reset_otp', 'reset_otp_expiry', 'reset_otp_tries', 'reset_step'] as $k) {
            unset($_SESSION[$k]);
          }
          $step = 1;
        }
      }
    } elseif ($action === 'verify_otp' && $step === 2) {
      $inputOtp = trim(implode('', $_POST['otp'] ?? []));
      $inputOtp = preg_replace('/\D/', '', $inputOtp);
      $tries = (int) ($_SESSION['reset_otp_tries'] ?? 0);
      $expiry = (int) ($_SESSION['reset_otp_expiry'] ?? 0);

      if ($tries >= 5) {
        $error = 'Terlalu banyak percobaan. Silakan minta kode baru.';
        $_SESSION['reset_step'] = 1;
        $step = 1;
      } elseif (time() > $expiry) {
        $error = 'Kode OTP sudah kedaluwarsa. Silakan minta kode baru.';
        $_SESSION['reset_step'] = 1;
        $step = 1;
      } elseif (!password_verify($inputOtp, $_SESSION['reset_otp'] ?? '')) {
        $_SESSION['reset_otp_tries']++;
        $remaining = 5 - $_SESSION['reset_otp_tries'];
        $error = "Kode OTP salah. Sisa percobaan: $remaining.";
      } else {
        $_SESSION['reset_step'] = 3;
        $_SESSION['reset_verified'] = true;
        $step = 3;
      }

    } elseif ($action === 'set_password' && $step === 3 && !empty($_SESSION['reset_verified'])) {
      $password = $_POST['password'] ?? '';
      $password2 = $_POST['password2'] ?? '';
      if (strlen($password) < 8) {
        $error = 'Password minimal 8 karakter.';
      } elseif (!preg_match('/[0-9]/', $password)) {
        $error = 'Password harus mengandung minimal 1 angka.';
      } elseif ($password !== $password2) {
        $error = 'Konfirmasi password tidak cocok.';
      } else {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $sql = "UPDATE users SET password = ?, updated_at = NOW() WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$hashedPassword, $_SESSION['reset_email']]);
        foreach (['reset_step', 'reset_email', 'reset_otp', 'reset_otp_expiry', 'reset_otp_tries', 'reset_verified'] as $k) {
          unset($_SESSION[$k]);
        }
        $_SESSION['reset_step'] = 4;
        $step = 4;
      }
    } elseif ($action === 'resend_otp') {
      $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
      $_SESSION['reset_otp'] = password_hash($otp, PASSWORD_BCRYPT);
      $_SESSION['reset_otp_expiry'] = time() + 600;
      $_SESSION['reset_otp_tries'] = 0;
      $_SESSION['reset_step'] = 2;
      if (sendOTPEmail($inputEmail, $otp)) {
        $step = 2;
        $success = 'Kode OTP baru telah dikirimkan.';
      } else {
        $error = 'Gagal mengirim email. Silakan coba lagi nanti.';
        foreach (['reset_email', 'reset_otp', 'reset_otp_expiry', 'reset_otp_tries', 'reset_step'] as $k) {
          unset($_SESSION[$k]);
        }
        $step = 1;
      }
    } elseif ($action === 'back_to_email') {
      foreach (['reset_step', 'reset_email', 'reset_otp', 'reset_otp_expiry', 'reset_otp_tries', 'reset_verified'] as $k) {
        unset($_SESSION[$k]);
      }
      $step = 1;
      $email = '';
    }
  }
}

$step = $step ?: (int) ($_SESSION['reset_step'] ?? 1);
$email = $email ?: ($_SESSION['reset_email'] ?? '');
$otpSecondsLeft = max(0, (int) ($_SESSION['reset_otp_expiry'] ?? 0) - time());
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset Password — <?= APP_NAME ?></title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&family=IBM+Plex+Mono:wght@400;500;600&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="../assets/reset-password.css">
</head>

<body>

  <div class="page-wrapper">

    <!-- ── Brand ── -->
    <div class="brand-header">
      <div class="brand-logo">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
          <path
            d="M18.375 2.25c-1.035 0-1.875.84-1.875 1.875v15.75c0 1.035.84 1.875 1.875 1.875h.75c1.035 0 1.875-.84 1.875-1.875V4.125c0-1.036-.84-1.875-1.875-1.875h-.75ZM9.75 8.625c0-1.036.84-1.875 1.875-1.875h.75c1.036 0 1.875.84 1.875 1.875v11.25c0 1.035-.84 1.875-1.875 1.875h-.75a1.875 1.875 0 0 1-1.875-1.875V8.625ZM3 13.125c0-1.036.84-1.875 1.875-1.875h.75c1.036 0 1.875.84 1.875 1.875v6.75c0 1.035-.84 1.875-1.875 1.875h-.75A1.875 1.875 0 0 1 3 19.875v-6.75Z" />
        </svg>
      </div>
      <span class="brand-name"><?= APP_NAME ?></span>
      <span class="brand-sep">/</span>
      <span class="brand-page">RESET_PASSWORD</span>
    </div>

    <!-- ── Card ── -->
    <div class="reset-card">

      <!-- Step Indicator -->
      <?php if ($step < 4): ?>
        <div class="step-indicator">
          <?php
          $steps = [['label' => 'Email'], ['label' => 'Verifikasi'], ['label' => 'Password']];
          foreach ($steps as $i => $s):
            $num = $i + 1;
            $status = $num < $step ? 'done' : ($num === $step ? 'active' : 'pending');
            if ($i > 0): ?>
              <div class="step-line step-line--<?= ($step > $num) ? 'done' : 'pending' ?>"></div>
            <?php endif; ?>
            <div class="step-node">
              <div class="step-dot step-dot--<?= $status ?>"><?= $status === 'done' ? '' : $num ?></div>
              <span class="step-label step-label--<?= $status ?>"><?= $s['label'] ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <!-- Alerts -->
      <?php if ($error): ?>
        <div class="alert alert-error">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
          </svg>
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="alert alert-success">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
          </svg>
          <?= htmlspecialchars($success) ?>
        </div>
      <?php endif; ?>


      <!-- ══ STEP 1 — Email ══ -->
      <?php if ($step === 1): ?>
        <div class="card-head">
          <h1 class="card-title">Lupa Password?</h1>
          <p class="card-subtitle">Masukkan email terdaftar Anda. Kami akan mengirimkan kode verifikasi 6 digit.</p>
        </div>

        <form method="POST" action="" novalidate id="formEmail">
          <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
          <input type="hidden" name="action" value="send_otp">
          <div class="form-group" style="margin-bottom: 1em;">
            <label class="form-label" for="email">Alamat Email</label>
            <div class="input-wrap">
              <input type="email" id="email" name="email" class="form-input"
                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="admin@spk.local" autocomplete="email"
                required autofocus>
              <span class="input-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M16.5 12a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0Zm0 0c0 1.657 1.007 3 2.25 3S21 13.657 21 12a9 9 0 1 0-2.636 6.364M16.5 12V8.25" />
                </svg>
              </span>
            </div>
          </div>
          <button type="submit" class="btn-submit" id="btnSendOtp">
            Kirim Kode Verifikasi
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
            </svg>
          </button>
        </form>

        <a href="login.php" class="back-link">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
          </svg>
          Kembali ke <span>halaman login</span>
        </a>
      <?php endif; ?>


      <!-- ══ STEP 2 — OTP ══ -->
      <?php if ($step === 2): ?>
        <div class="card-head">
          <h1 class="card-title">Masukkan Kode OTP</h1>
          <p class="card-subtitle">
            Kode 6 digit telah dikirim ke<br>
            <strong><?= htmlspecialchars(substr($email, 0, 3) . '***' . strstr($email, '@')) ?></strong>
          </p>
        </div>

        <form method="POST" action="" id="formOtp">
          <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
          <input type="hidden" name="action" value="verify_otp">

          <?php for ($i = 0; $i < 6; $i++): ?>
            <input type="hidden" name="otp[]" id="otpHidden<?= $i ?>" value="">
          <?php endfor; ?>

          <div class="form-group">
            <label class="form-label" style="justify-content:center">Kode Verifikasi</label>
            <div class="otp-wrapper" id="otpWrapper">
              <?php for ($i = 0; $i < 6; $i++): ?>
                <?php if ($i === 3): ?><span class="otp-separator">·</span><?php endif; ?>
                <input type="tel" maxlength="1" class="otp-digit" id="otpVis<?= $i ?>" data-index="<?= $i ?>"
                  pattern="[0-9]*" inputmode="numeric" autocomplete="one-time-code" <?= $i === 0 ? 'autofocus' : '' ?>>
              <?php endfor; ?>
            </div>
          </div>

          <!-- TIMER (visual only, bukan form) -->
          <div class="otp-timer">

            <div class="timer-badge" id="timerBadge">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
              </svg>
              Kedaluwarsa dalam
              <span class="timer-seconds" id="timerSeconds">
                <?= gmdate('i:s', $otpSecondsLeft) ?>
              </span>
            </div>

            <!-- tombol resend menggunakan atribut form -->
            <button type="submit" form="formResend" class="resend-btn" id="resendBtn" <?= $otpSecondsLeft > 0 ? 'disabled' : '' ?>>
              Kirim Ulang
            </button>

          </div>

          <div class="form-divider"></div>

          <button type="submit" class="btn-submit" id="btnVerify" disabled>
            Verifikasi Kode
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
          </button>
        </form>

        <!-- FORM RESEND DIPISAH (tidak merubah UI) -->
        <form method="POST" action="" id="formResend" style="display:none;">
          <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
          <input type="hidden" name="action" value="resend_otp">
        </form>

        <form method="POST" id="formBackEmail">
          <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
          <input type="hidden" name="action" value="back_to_email">
          <button type="submit" class="back-link">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            Ganti email atau <span>kembali</span>
          </button>
        </form>
      <?php endif; ?>


      <!-- ══ STEP 3 — New Password ══ -->
      <?php if ($step === 3): ?>
        <div class="card-head">
          <div class="card-icon card-icon--lock">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
            </svg>
          </div>
          <h1 class="card-title">Buat Password Baru</h1>
          <p class="card-subtitle">Password baru harus berbeda dari sebelumnya dan memenuhi persyaratan keamanan.</p>
        </div>

        <form method="POST" action="" id="formNewPassword" novalidate>
          <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
          <input type="hidden" name="action" value="set_password">

          <div class="form-group">
            <label class="form-label" for="password">Password Baru</label>
            <div class="input-wrap">
              <input type="password" id="password" name="password" class="form-input" placeholder="Min. 8 karakter"
                autocomplete="new-password" required autofocus>
              <button type="button" class="input-action" id="pwToggle1" aria-label="Toggle password">
                <svg id="eyeIcon1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
              </button>
            </div>

            <div class="pw-strength" id="pwStrength" style="display:none">
              <div class="pw-strength-bar">
                <div class="pw-bar-seg" id="seg0"></div>
                <div class="pw-bar-seg" id="seg1"></div>
              </div>
              <div class="pw-strength-text">
                <span class="pw-rule" id="ruleLen">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                  </svg>
                  8 karakter
                </span>
                <span class="pw-rule" id="ruleNum">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                  </svg>
                  Angka
                </span>
              </div>
            </div>
          </div>

          <div class="form-group" style="margin-bottom: 1em;">
            <label class="form-label" for="password2">Konfirmasi Password</label>
            <div class="input-wrap">
              <input type="password" id="password2" name="password2" class="form-input" placeholder="Ulangi password baru"
                autocomplete="new-password" required>
              <button type="button" class="input-action" id="pwToggle2" aria-label="Toggle confirm password">
                <svg id="eyeIcon2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
              </button>
            </div>
            <div id="matchHint"
              style="display:none;font-size:12px;margin-top:5px;font-weight:500;font-family:'IBM Plex Mono',monospace;">
            </div>
          </div>

          <button type="submit" class="btn-submit" id="btnSetPw">
            Simpan Password Baru
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
            </svg>
          </button>
        </form>
      <?php endif; ?>


      <!-- ══ STEP 4 — Success ══ -->
      <?php if ($step === 4): ?>
        <?php unset($_SESSION['reset_step']); ?>
        <div class="success-content">
          <div class="success-ring">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
          </div>
          <h1 class="success-title">Password Berhasil Diubah!</h1>
          <p class="success-desc">
            Password akun Anda telah berhasil diperbarui.<br>
            Silakan masuk menggunakan password baru Anda.
          </p>
          <a href="login.php" class="btn-login">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
            </svg>
            Masuk Sekarang
          </a>
        </div>
      <?php endif; ?>

    </div>
    <!-- /reset-card -->

  </div>
  <!-- /page-wrapper -->

  <script>
    (function () {
      'use strict';

      /* ── OTP INPUT ── */
      const digits = document.querySelectorAll('.otp-digit');
      const hiddens = document.querySelectorAll('[id^="otpHidden"]');
      const btnVerify = document.getElementById('btnVerify');

      function updateHiddens() {
        digits.forEach((d, i) => { if (hiddens[i]) hiddens[i].value = d.value; });
      }
      function isComplete() { return [...digits].every(d => d.value !== ''); }
      function updateVerifyBtn() { if (btnVerify) btnVerify.disabled = !isComplete(); }

      digits.forEach((digit, idx) => {
        digit.addEventListener('input', (e) => {
          const val = e.target.value.replace(/\D/, '');
          e.target.value = val.slice(-1);
          e.target.classList.toggle('filled', val !== '');
          updateHiddens();
          updateVerifyBtn();
          if (val && idx < digits.length - 1) digits[idx + 1].focus();
          if (isComplete()) setTimeout(() => { document.getElementById('formOtp')?.submit(); }, 300);
        });

        digit.addEventListener('keydown', (e) => {
          if (e.key === 'Backspace') {
            if (!e.target.value && idx > 0) {
              digits[idx - 1].value = '';
              digits[idx - 1].classList.remove('filled');
              digits[idx - 1].focus();
            }
            e.target.classList.remove('filled');
            updateHiddens(); updateVerifyBtn();
          }
          if (e.key === 'ArrowLeft' && idx > 0) digits[idx - 1].focus();
          if (e.key === 'ArrowRight' && idx < digits.length - 1) digits[idx + 1].focus();
        });

        digit.addEventListener('paste', (e) => {
          e.preventDefault();
          const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 6);
          pasted.split('').forEach((ch, i) => {
            if (digits[i]) { digits[i].value = ch; digits[i].classList.add('filled'); }
          });
          updateHiddens(); updateVerifyBtn();
          const nextEmpty = [...digits].findIndex(d => !d.value);
          (nextEmpty !== -1 ? digits[nextEmpty] : digits[digits.length - 1]).focus();
        });
      });

      /* ── OTP TIMER ── */
      const timerEl = document.getElementById('timerSeconds');
      const timerBadge = document.getElementById('timerBadge');
      const resendBtn = document.getElementById('resendBtn');
      let secondsLeft = <?= (int) $otpSecondsLeft ?>;

      if (timerEl && secondsLeft > 0) {
        const tick = setInterval(() => {
          secondsLeft--;
          const m = String(Math.floor(secondsLeft / 60)).padStart(2, '0');
          const s = String(secondsLeft % 60).padStart(2, '0');
          timerEl.textContent = `${m}:${s}`;
          if (secondsLeft <= 30) timerBadge?.classList.add('urgent');
          if (secondsLeft <= 0) {
            clearInterval(tick);
            timerEl.textContent = 'Kedaluwarsa';
            if (resendBtn) resendBtn.disabled = false;
          }
        }, 1000);
      }

      /* ── PASSWORD STRENGTH ── */
      const pwInput = document.getElementById('password');
      const pw2Input = document.getElementById('password2');
      const pwStrength = document.getElementById('pwStrength');
      const matchHint = document.getElementById('matchHint');
      const segments = [0, 1, 2, 3].map(i => document.getElementById('seg' + i));

      const checkIcon = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>`;
      const crossIcon = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>`;

      function setRuleEl(id, met, label) {
        const el = document.getElementById(id);
        if (!el) return;
        el.classList.toggle('met', met);
        el.innerHTML = (met ? checkIcon : crossIcon) + ' ' + label;
      }

      const levelClasses = ['weak', 'fair', 'good', 'strong'];

      if (pwInput) {
        pwInput.addEventListener('input', () => {
          const val = pwInput.value;
          if (pwStrength) pwStrength.style.display = val ? 'block' : 'none';
          const met = {
            len: val.length >= 8,
            num: /[0-9]/.test(val),
          };
          setRuleEl('ruleLen', met.len, '8+ karakter');
          setRuleEl('ruleNum', met.num, 'Angka');

          const score = Object.values(met).filter(Boolean).length;
          segments.forEach((seg, i) => {
            seg.className = 'pw-bar-seg';
            if (i < score) seg.classList.add(levelClasses[score - 1]);
          });
          checkMatch();
        });
      }

      function checkMatch() {
        if (!pw2Input || !matchHint) return;
        const v1 = pwInput?.value || '', v2 = pw2Input.value || '';
        if (!v2) { matchHint.style.display = 'none'; return; }
        matchHint.style.display = 'block';
        if (v1 === v2) {
          matchHint.style.color = 'var(--success)';
          matchHint.textContent = '✓ Password cocok';
          pw2Input.style.borderColor = '#a7f3d0';
        } else {
          matchHint.style.color = 'var(--danger)';
          matchHint.textContent = '✗ Password tidak cocok';
          pw2Input.style.borderColor = '#fecaca';
        }
      }
      pw2Input?.addEventListener('input', checkMatch);

      /* ── PASSWORD TOGGLE ── */
      function makeEyeIcon(crossed) {
        if (crossed) return `<path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/>`;
        return `<path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>`;
      }

      [['pwToggle1', 'password', 'eyeIcon1'], ['pwToggle2', 'password2', 'eyeIcon2']].forEach(([btnId, inputId, iconId]) => {
        const btn = document.getElementById(btnId);
        const inp = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        if (!btn || !inp) return;
        btn.addEventListener('click', () => {
          const show = inp.type === 'password';
          inp.type = show ? 'text' : 'password';
          if (icon) icon.innerHTML = makeEyeIcon(show);
        });
      });

      /* ── FORM LOADING STATE ── */
      const styleEl = document.createElement('style');
      styleEl.textContent = '@keyframes spin { to { transform: rotate(360deg); } }';
      document.head.appendChild(styleEl);

      ['formEmail', 'formOtp', 'formNewPassword'].forEach(id => {
        const form = document.getElementById(id);
        if (!form) return;
        form.addEventListener('submit', () => {
          const btn = form.querySelector('.btn-submit');
          if (!btn || btn.disabled) return;
          btn.disabled = true;
          btn.innerHTML = `
        <svg style="animation:spin .7s linear infinite" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/>
        </svg>
        Memproses...`;
        });
      });

    })();
  </script>
</body>

</html>