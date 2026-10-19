<?php
// Memanggil file koneksi
require 'config/DBConfig.php';

$errors = [];
$success = false;
$siswaData = ['id_siswa' => '', 'name' => '', 'asal_sekolah' => '', 'email' => '', 'jenis_kelamin' => '', 'tinggi_badan' => '', 'berat_badan' => ''];

// Deteksi mode: add atau edit
$action = $_GET['action'] ?? 'add';
$editId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$isEdit = ($action === 'edit' && $editId > 0);
$pageTitle = $isEdit ? 'Edit Siswa' : 'Tambah Siswa';

// ── Jika EDIT: ambil data siswa yang akan diedit ──
if ($conn && $isEdit) {
  try {
    $stmt = $conn->prepare("SELECT id_siswa, name, asal_sekolah, email, jenis_kelamin, tinggi_badan, berat_badan FROM siswa WHERE id_siswa = ?");
    $stmt->execute([$editId]);
    $row = $stmt->fetch();
    if ($row) {
      $siswaData = $row;
    } else {
      $errors[] = 'Siswa dengan ID ' . $editId . ' tidak ditemukan.';
      $isEdit = false;
    }
  } catch (\PDOException $e) {
    $errors[] = 'Gagal mengambil data siswa: ' . $e->getMessage();
  }
}

// ── Proses Form Submit ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $conn) {
  $name = trim($_POST['name'] ?? '');
  $asal_sekolah = trim($_POST['asal_sekolah'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $jenis_kelamin = $_POST['jenis_kelamin'] ?? '';
  $tinggi_badan = (int) ($_POST['tinggi_badan'] ?? 0);
  $berat_badan = (int) ($_POST['berat_badan'] ?? 0);
  $formAction = $_POST['form_action'] ?? 'add';
  $formId = (int) ($_POST['siswa_id'] ?? 0);

  // ── Validasi ──
  if (empty($name)) {
    $errors[] = 'Nama tidak boleh kosong.';
  } elseif (strlen($name) > 100) {
    $errors[] = 'Nama maksimal 100 karakter.';
  }

  if (empty($asal_sekolah)) {
    $errors[] = 'Asal sekolah tidak boleh kosong.';
  } elseif (strlen($asal_sekolah) > 100) {
    $errors[] = 'Asal sekolah maksimal 100 karakter.';
  }

  if (empty($email)) {
    $errors[] = 'Email tidak boleh kosong.';
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Format email tidak valid.';
  } elseif (strlen($email) > 150) {
    $errors[] = 'Email maksimal 150 karakter.';
  }

  if (!in_array($jenis_kelamin, ['L', 'P'])) {
    $errors[] = 'Jenis kelamin harus dipilih.';
  }

  if ($tinggi_badan <= 0) {
    $errors[] = 'Tinggi badan harus diisi dan lebih dari 0.';
  } elseif ($tinggi_badan > 300) {
    $errors[] = 'Tinggi badan tidak valid (max 300 cm).';
  }

  if ($berat_badan <= 0) {
    $errors[] = 'Berat badan harus diisi dan lebih dari 0.';
  } elseif ($berat_badan > 500) {
    $errors[] = 'Berat badan tidak valid (max 500 kg).';
  }

  // ── Jika tidak ada error, simpan ke database ──
  if (empty($errors)) {
    try {
      if ($formAction === 'edit' && $formId > 0) {
        // UPDATE
        $stmt = $conn->prepare(
          "UPDATE siswa SET name = ?, asal_sekolah = ?, email = ?, jenis_kelamin = ?, tinggi_badan = ?, berat_badan = ? WHERE id_siswa = ?"
        );
        $stmt->execute([$name, $asal_sekolah, $email, $jenis_kelamin, $tinggi_badan, $berat_badan, $formId]);
      } else {
        // INSERT
        $stmt = $conn->prepare(
          "INSERT INTO siswa (name, asal_sekolah, email, jenis_kelamin, tinggi_badan, berat_badan) VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$name, $asal_sekolah, $email, $jenis_kelamin, $tinggi_badan, $berat_badan]);
      }
      // Redirect setelah berhasil
      echo "<script>window.location.href='app.php?page=siswa&success=" . ($formAction === 'edit' ? 'edited' : 'added') . "';</script>";
      exit;
    } catch (\PDOException $e) {
      $errors[] = 'Gagal menyimpan data: ' . $e->getMessage();
    }
  }

  // Isi ulang field jika ada error
  $siswaData['name'] = $name;
  $siswaData['asal_sekolah'] = $asal_sekolah;
  $siswaData['email'] = $email;
  $siswaData['jenis_kelamin'] = $jenis_kelamin;
  $siswaData['tinggi_badan'] = $tinggi_badan;
  $siswaData['berat_badan'] = $berat_badan;
  $siswaData['id_siswa'] = $formId;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <link rel="stylesheet" href="assets/app.css">
</head>

<body>
  <!-- ── MAIN ── -->
  <div>
    <!-- Content -->
    <main class="app-content">
      <div class="page-view">

        <!-- Page Header -->
        <div class="page-header">
          <div>
            <h1 class="page-title"><?= htmlspecialchars($pageTitle) ?></h1>
            <p class="page-subtitle">
              <?= $isEdit
                ? 'Ubah informasi data siswa yang sudah terdaftar di sistem.'
                : 'Tambahkan data siswa baru ke dalam sistem.'
                ?>
            </p>
          </div>
          <a href="app.php?page=siswa" class="btn-secondary">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali
          </a>
        </div>

        <!-- Error list -->
        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger" style="max-width:680px;">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
            </svg>
            <div>
              <strong>Terjadi kesalahan, harap perbaiki berikut:</strong>
              <ul class="alert-list">
                <?php foreach ($errors as $e): ?>
                  <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          </div>
        <?php endif; ?>

        <!-- FORM CARD -->
        <div class="form-card" style="width: 100%; max-width: none;">
          <div class="form-card-header">
            <div class="form-card-icon">
              <?php if ($isEdit): ?>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
              <?php else: ?>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
              <?php endif; ?>
            </div>
            <div>
              <p class="form-card-title"><?= htmlspecialchars($pageTitle) ?></p>
              <p class="form-card-desc">
                <?= $isEdit
                  ? 'Perbarui data nama, asal sekolah, email, jenis kelamin, tinggi, dan berat badan siswa.'
                  : 'Lengkapi semua field yang diperlukan untuk mendaftarkan siswa baru.'
                  ?>
              </p>
            </div>
          </div>

          <form method="POST"
            action="app.php?page=siswa/<?= $isEdit ? 'edit&action=edit&id=' . $editId : 'add&action=add' ?>"
            id="siswaForm" novalidate>
            <input type="hidden" name="form_action" value="<?= $isEdit ? 'edit' : 'add' ?>">
            <?php if ($isEdit): ?>
              <input type="hidden" name="siswa_id" value="<?= (int) $siswaData['id_siswa'] ?>">
            <?php endif; ?>

            <div class="form-card-body">

              <!-- ── INFORMASI DASAR ── -->
              <p class="form-section-title">Informasi Siswa</p>

              <!-- Nama -->
              <div class="form-group">
                <label class="form-label" for="name">
                  Nama Lengkap <span class="required">*</span>
                </label>
                <input type="text" id="name" name="name" class="form-control" placeholder="Masukkan nama lengkap siswa"
                  value="<?= htmlspecialchars($siswaData['name']) ?>" maxlength="250" autocomplete="name" required />
                <span class="form-hint">Maksimal 250 karakter.</span>
              </div>

              <!-- Asal Sekolah -->
              <div class="form-group">
                <label class="form-label" for="asal_sekolah">
                  Asal Sekolah <span class="required">*</span>
                </label>
                <input type="text" id="asal_sekolah" name="asal_sekolah" class="form-control" placeholder="Masukkan asal sekolah siswa"
                  value="<?= htmlspecialchars($siswaData['asal_sekolah']) ?>" maxlength="100" required />
                <span class="form-hint">Maksimal 100 karakter.</span>
              </div>

              <!-- Email -->
              <div class="form-group">
                <label class="form-label" for="email">
                  Email <span class="required">*</span>
                </label>
                <input type="email" id="email" name="email" class="form-control" placeholder="Masukkan alamat email siswa"
                  value="<?= htmlspecialchars($siswaData['email']) ?>" maxlength="150" autocomplete="email" required />
                <span class="form-hint">Contoh: siswa@gmail.com</span>
              </div>

              <!-- Jenis Kelamin -->
              <div class="form-group">
                <label class="form-label" for="jenis_kelamin">
                  Jenis Kelamin <span class="required">*</span>
                </label>
                <div class="select-wrap">
                  <select id="jenis_kelamin" name="jenis_kelamin" class="form-control" required>
                    <option value="">— Pilih Jenis Kelamin —</option>
                    <option value="L" <?= $siswaData['jenis_kelamin'] === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                    <option value="P" <?= $siswaData['jenis_kelamin'] === 'P' ? 'selected' : '' ?>>Perempuan</option>
                  </select>
                </div>
              </div>

              <hr class="form-divider">

              <!-- ── DATA FISIK ── -->
              <p class="form-section-title">Data Fisik</p>

              <div class="form-row">
                <!-- Tinggi Badan -->
                <div class="form-group" style="margin-bottom:0;">
                  <label class="form-label" for="tinggi_badan">
                    Tinggi Badan <span class="required">*</span>
                  </label>
                  <div style="position:relative;">
                    <input type="number" id="tinggi_badan" name="tinggi_badan" class="form-control"
                      placeholder="Contoh: 165" value="<?= htmlspecialchars($siswaData['tinggi_badan']) ?>" min="1"
                      max="300" style="padding-right: 48px;" required />
                    <span style="position:absolute; right:13px; top:50%; transform:translateY(-50%);
                        font-size:12px; color:var(--text-muted); font-weight:600; pointer-events:none;">cm</span>
                  </div>
                  <span class="form-hint">Dalam satuan sentimeter (cm).</span>
                </div>

                <!-- Berat Badan -->
                <div class="form-group" style="margin-bottom:0;">
                  <label class="form-label" for="berat_badan">
                    Berat Badan <span class="required">*</span>
                  </label>
                  <div style="position:relative;">
                    <input type="number" id="berat_badan" name="berat_badan" class="form-control"
                      placeholder="Contoh: 55" value="<?= htmlspecialchars($siswaData['berat_badan']) ?>" min="1"
                      max="500" style="padding-right: 42px;" required />
                    <span style="position:absolute; right:13px; top:50%; transform:translateY(-50%);
                        font-size:12px; color:var(--text-muted); font-weight:600; pointer-events:none;">kg</span>
                  </div>
                  <span class="form-hint">Dalam satuan kilogram (kg).</span>
                </div>
              </div>

            </div><!-- /form-card-body -->

            <!-- Actions -->
            <div class="form-actions">
              <a href="app.php?page=siswa" class="btn-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
                Batal
              </a>
              <button type="submit" class="btn btn-primary" id="submitBtn">
                <?php if ($isEdit): ?>
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                  </svg>
                  Simpan Perubahan
                <?php else: ?>
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                  </svg>
                  Tambah Siswa
                <?php endif; ?>
              </button>
            </div>
          </form>
        </div><!-- /form-card -->

      </div>
    </main>
  </div>

  <script>
    // ── Client-side form validation sebelum submit ──
    document.getElementById('siswaForm').addEventListener('submit', function (e) {
      let hasError = false;

      const fields = [
        { id: 'name', check: v => v.trim() !== '' },
        { id: 'email', check: v => v.trim() !== '' && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v.trim()) },
        { id: 'jenis_kelamin', check: v => v !== '' },
        { id: 'tinggi_badan', check: v => parseInt(v) > 0 && parseInt(v) <= 300 },
        { id: 'berat_badan', check: v => parseInt(v) > 0 && parseInt(v) <= 500 },
      ];

      fields.forEach(({ id, check }) => {
        const el = document.getElementById(id);
        if (!el) return;
        if (!check(el.value)) {
          el.classList.add('is-invalid');
          hasError = true;
        }
      });

      if (hasError) {
        e.preventDefault();
        window.scrollTo({ top: 0, behavior: 'smooth' });
      }
    });

    // Clear invalid on focus
    document.querySelectorAll('.form-control').forEach(el => {
      el.addEventListener('focus', () => el.classList.remove('is-invalid'));
    });
  </script>
</body>

</html>