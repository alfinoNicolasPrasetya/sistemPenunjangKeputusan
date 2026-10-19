<?php
/**
 * Application Configuration
 */

// Session config (harus sebelum session_start)
define('SESSION_NAME', 'spk_session');
define('SESSION_LIFETIME', 7200); // 2 jam
define('APP_NAME', 'SPK Fuzzy Sugeno');
define('APP_VERSION', '1.0.0');

// Email Config
define('EMAIL_HOST', 'smtp.gmail.com');
define('EMAIL_PORT', 587);
define('EMAIL_USERNAME', 'frystra12@gmail.com');
define('EMAIL_PASSWORD', 'nryf jeeg wnpk etnx');

// Sidebar menu definition
define('SIDEBAR_MENU', [
  [
    'group' => 'Utama',
    'items' => [
      ['label' => 'Dashboard', 'page' => 'dashboard', 'icon' => 'home', 'id_role' => [1,3]],
      ['label' => 'Hasil Pengumuman', 'page' => 'pengumuman', 'icon' => 'chart-pie', 'id_role' => [2]],
      // ['label' => 'Panduan', 'page' => 'panduan', 'icon' => 'book-open', 'id_role' => [1]],
    ],
  ],
  [
    'group' => 'Data Master',
    'items' => [
      ['label' => 'Siswa', 'page' => 'siswa', 'icon' => 'users', 'id_role' => [1,3]],
      ['label' => 'Kriteria', 'page' => 'kriteria', 'icon' => 'folder', 'id_role' => [1,3]],
    ],
  ],
  [
    'group' => 'Fuzzy Sugeno',
    'items' => [
      ['label' => 'Himpunan Fuzzy', 'page' => 'himpunan', 'icon' => 'chart-bar', 'id_role' => [1,3]],
      ['label' => 'Aturan Fuzzy', 'page' => 'aturan', 'icon' => 'adjustments', 'id_role' => [1,3]],
    ],
  ],
  [
    'group' => 'Proses',
    'items' => [
      ['label' => 'Penilaian', 'page' => 'penilaian', 'icon' => 'clipboard-list', 'id_role' => [3]],
      ['label' => 'Perhitungan', 'page' => 'perhitungan', 'icon' => 'calculator', 'id_role' => [3]],
      ['label' => 'Hasil', 'page' => 'hasil', 'icon' => 'chart-pie', 'id_role' => [1,3]], // user bisa lihat
    ],
  ],
  [
    'group' => 'Sistem',
    'items' => [
      ['label' => 'Manajemen User', 'page' => 'users', 'icon' => 'user-group', 'id_role' => [1,3]],
      // ['label' => 'Manajemen Role', 'page' => 'roles', 'icon' => 'key', 'id_role' => [1]],
      ['label' => 'Profil Saya', 'page' => 'profile', 'icon' => 'user-circle', 'id_role' => [1, 2,3]],
    ],
  ],
]);