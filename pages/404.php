<?php
/**
 * Page: 404 Not Found
 * Path: pages/404.php
 */
$pageTitle = 'Halaman Tidak Ditemukan';
http_response_code(404);
?>
<div class="page-error">
  <div class="error-illustration">
    <span class="error-code">404</span>
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round"
        d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
    </svg>
  </div>
  <h1 class="error-title">Halaman Tidak Ditemukan</h1>
  <p class="error-desc">
    Halaman <code><?= htmlspecialchars($_GET['page'] ?? '') ?></code> tidak ada dalam sistem.<br>
    Pastikan URL yang Anda akses sudah benar.
  </p>
  <a href="?page=dashboard" class="btn btn-primary">
    ← Kembali ke Dashboard
  </a>
</div>