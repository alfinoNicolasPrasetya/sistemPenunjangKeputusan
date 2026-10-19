<?php
/**
 * Router Configuration
 * Mapping URL path → file di folder pages/
 * Tambah route baru di sini jika ada halaman baru.
 */

return [
  // Key   : path segment (setelah ?page= atau dari PATH_INFO)
  // Value : path file relatif dari folder pages/

  'dashboard' => 'Dashboard.php',

  // User management (admin only)
  'users' => 'users/Users.php',
  'users/add' => 'users/FormUsers.php',
  'users/edit' => 'users/FormUsers.php',

  // Role management (admin only)
  'roles' => 'role/Role.php',
  'roles/add' => 'role/FormRole.php',
  'roles/edit' => 'role/FormRole.php',

  // Profile
  'profile' => 'Profil.php',
  'reset-password' => 'ResetPassword.php',

  // Default fallback
  'not-found' => '404.php',

  // Siswa
  'siswa' => 'siswa/Siswa.php',
  'siswa/add' => 'siswa/FormSiswa.php',
  'siswa/edit' => 'siswa/FormSiswa.php',

  // Kriteria
  'kriteria' => 'kriteria/Kriteria.php',
  'kriteria/add' => 'kriteria/FormKriteria.php',
  'kriteria/edit' => 'kriteria/FormKriteria.php',

  // Himpunan Fuzzy
  'himpunan' => 'himpunan/Himpunan.php',
  'himpunan/add' => 'himpunan/FormHimpunan.php',
  'himpunan/edit' => 'himpunan/FormHimpunan.php',

  // Aturan Fuzzy
  'aturan' => 'aturan/Aturan.php',
  'aturan/add' => 'aturan/FormAturan.php',
  'aturan/edit' => 'aturan/FormAturan.php',

  // Penilaian
  'penilaian' => 'penilaian/Penilaian.php',
  'penilaian/add' => 'penilaian/FormPenilaian.php',
  'penilaian/edit' => 'penilaian/FormPenilaian.php',

  // Perhitungan
  'perhitungan' => 'perhitungan/Perhitungan.php',

  // Panduan
  'panduan' => 'Panduan.php',

  // Hasil Perhitungan
  'hasil' => 'hasil/Hasil.php',
  'pengumuman' => 'Pengumuman.php',
];
