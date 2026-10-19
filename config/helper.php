<?php
require_once __DIR__ . './appConfig.php';
require_once __DIR__ . './Constant.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;

function logout(string $redirect = 'pages/Login.php'): void
{
  if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.name', SESSION_NAME);
    ini_set('session.gc_maxlifetime', (string) SESSION_LIFETIME);
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_strict_mode', '1');
    session_start();
  }

  // Regenerate ID SEBELUM destroy — mencegah session fixation attack.
  // Parameter true = hapus file session lama dari server sekaligus.
  session_regenerate_id(true);

  // Kosongkan semua data session di memori
  $_SESSION = [];

  if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
      SESSION_NAME,
      '',
      [
        'expires' => time() - 42000,
        'path' => $params['path'],
        'domain' => $params['domain'],
        'secure' => $params['secure'],
        'httponly' => true,   // pastikan selalu httponly
        'samesite' => 'Lax', // proteksi CSRF tambahan
      ]
    );
  }

  // Hancurkan session di server
  session_destroy();

  if (ob_get_level() > 0) {
    ob_end_clean();
  }

  header('Location: ../' . $redirect);
  exit;
}

function setAlert(
  string $type,
  string $title,
  string $message,
  ?string $onConfirmJs = null
): void {
  echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
      if (typeof setAlertOnJs === 'function') {
        setAlertOnJs('$type', '$title', '$message', " . ($onConfirmJs ? "'$onConfirmJs'" : 'null') . ");
      } else {
        console.error('Fungsi setAlertOnJs tidak ditemukan!');
      }
    });
  </script>";
}

function sendOTPEmail($email, $otp)
{
  $mail = new PHPMailer(true);

  $mail->isSMTP();
  $mail->Host = EMAIL_HOST;
  $mail->SMTPAuth = true;
  $mail->Username = EMAIL_USERNAME;
  $mail->Password = EMAIL_PASSWORD;
  $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
  $mail->Port = 587;

  $mail->setFrom(EMAIL_USERNAME, APP_NAME);
  $mail->addAddress($email);

  $mail->isHTML(true);
  $mail->Subject = 'Kode OTP Reset Password - ' . APP_NAME;
  $mail->Body = templateEmailResetPPassword($email, $otp);

  return $mail->send();
}

function getCount(PDO $conn, string $sql, string $field): int
{
  try {
    $stmt = $conn->query($sql);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return (int) ($result[$field] ?? 0);
  } catch (\PDOException $e) {
    global $errors;
    $errors[] = 'Database error: ' . $e->getMessage();
    return 0;
  }
}