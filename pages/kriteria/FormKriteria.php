<?php
// Memanggil file koneksi
require 'config/DBConfig.php';

$errors = [];
$kriteriaData = [
  'id_kriteria' => '',
  'nama_kriteria' => '',
  'satuan' => '',
  'nilai_min' => 0,
  'nilai_max' => 100,
  'keterangan' => '',
  'urutan' => 0,
];

// Deteksi mode: add atau edit
$action = $_GET['action'] ?? 'add';
$editId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$isEdit = ($action === 'edit' && $editId > 0);
$pageTitle = $isEdit ? 'Edit Kriteria' : 'Tambah Kriteria';

// ── Jika EDIT: ambil data kriteria ──
if ($conn && $isEdit) {
  try {
    $stmt = $conn->prepare("
      SELECT id_kriteria, nama_kriteria, satuan, nilai_min, nilai_max, keterangan, urutan
      FROM kriteria
      WHERE id_kriteria = ?
    ");
    $stmt->execute([$editId]);
    $row = $stmt->fetch();
    if ($row) {
      $kriteriaData = $row;
    } else {
      $errors[] = 'Kriteria dengan ID ' . $editId . ' tidak ditemukan.';
      $isEdit = false;
    }
  } catch (\PDOException $e) {
    $errors[] = 'Gagal mengambil data kriteria: ' . $e->getMessage();
  }
}

// ── Proses Form Submit ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $conn) {
  $nama_kriteria = trim($_POST['nama_kriteria'] ?? '');
  $satuan = trim($_POST['satuan'] ?? '');
  $nilai_min = $_POST['nilai_min'] ?? '';
  $nilai_max = $_POST['nilai_max'] ?? '';
  $keterangan = trim($_POST['keterangan'] ?? '');
  $urutan = $_POST['urutan'] ?? 0;
  $formAction = $_POST['form_action'] ?? 'add';
  $formId = (int) ($_POST['kriteria_id'] ?? 0);

  // ── Validasi ──
  if (empty($nama_kriteria)) {
    $errors[] = 'Nama kriteria tidak boleh kosong.';
  } elseif (strlen($nama_kriteria) > 200) {
    $errors[] = 'Nama kriteria maksimal 200 karakter.';
  }

  if ($nilai_min === '' || !is_numeric($nilai_min)) {
    $errors[] = 'Nilai minimum harus berupa angka.';
  }

  if ($nilai_max === '' || !is_numeric($nilai_max)) {
    $errors[] = 'Nilai maksimum harus berupa angka.';
  }

  if (is_numeric($nilai_min) && is_numeric($nilai_max) && (float) $nilai_min >= (float) $nilai_max) {
    $errors[] = 'Nilai minimum harus lebih kecil dari nilai maksimum.';
  }

  if ($urutan !== '' && !ctype_digit((string) $urutan)) {
    $errors[] = 'Urutan harus berupa angka bulat positif.';
  }

  // ── Simpan ke database ──
  if (empty($errors)) {
    try {
      $params = [
        $nama_kriteria,
        $satuan ?: null,
        (float) $nilai_min,
        (float) $nilai_max,
        $keterangan ?: null,
        (int) $urutan,
      ];

      if ($formAction === 'edit' && $formId > 0) {
        $stmt = $conn->prepare("
          UPDATE kriteria
          SET nama_kriteria = ?,
              satuan        = ?,
              nilai_min     = ?,
              nilai_max     = ?,
              keterangan    = ?,
              urutan        = ?
          WHERE id_kriteria = ?
        ");
        $params[] = $formId;
        $stmt->execute($params);
      } else {
        $stmt = $conn->prepare("
          INSERT INTO kriteria (nama_kriteria, satuan, nilai_min, nilai_max, keterangan, urutan)
          VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute($params);
      }

      echo "<script>window.location.href='app.php?page=kriteria&success=" . ($formAction === 'edit' ? 'edited' : 'added') . "';</script>";
      exit;
    } catch (\PDOException $e) {
      $errors[] = 'Gagal menyimpan data: ' . $e->getMessage();
    }
  }

  // Isi ulang field jika ada error
  $kriteriaData['nama_kriteria'] = $nama_kriteria;
  $kriteriaData['satuan'] = $satuan;
  $kriteriaData['nilai_min'] = $nilai_min;
  $kriteriaData['nilai_max'] = $nilai_max;
  $kriteriaData['keterangan'] = $keterangan;
  $kriteriaData['urutan'] = $urutan;
  $kriteriaData['id_kriteria'] = $formId;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <link rel="stylesheet" href="assets/app.css">
</head>

<body>
  <div>
    <main class="app-content">
      <div class="page-view">

        <!-- Page Header -->
        <div class="page-header">
          <div>
            <h1 class="page-title"><?= htmlspecialchars($pageTitle) ?></h1>
            <p class="page-subtitle">
              <?= $isEdit
                ? 'Ubah informasi kriteria yang sudah terdaftar di sistem.'
                : 'Tambahkan kriteria penilaian baru ke dalam sistem SPK.'
                ?>
            </p>
          </div>
          <a href="app.php?page=kriteria" class="btn-secondary">
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
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
              <?php endif; ?>
            </div>
            <div>
              <p class="form-card-title"><?= htmlspecialchars($pageTitle) ?></p>
              <p class="form-card-desc">
                <?= $isEdit
                  ? 'Perbarui informasi dan domain nilai kriteria.'
                  : 'Lengkapi semua field untuk menambah kriteria baru.'
                  ?>
              </p>
            </div>
          </div>

          <form method="POST"
            action="app.php?page=kriteria/<?= $isEdit ? 'edit&action=edit&id=' . $editId : 'add&action=add' ?>"
            id="kriteriaForm" novalidate>

            <input type="hidden" name="form_action" value="<?= $isEdit ? 'edit' : 'add' ?>">
            <?php if ($isEdit): ?>
              <input type="hidden" name="kriteria_id" value="<?= (int) $kriteriaData['id_kriteria'] ?>">
            <?php endif; ?>

            <div class="form-card-body">

              <!-- ── SECTION 1: Informasi Dasar ── -->
              <p class="form-section-title">Informasi Kriteria</p>

              <!-- Nama Kriteria -->
              <div class="form-group">
                <label class="form-label" for="nama_kriteria">
                  Nama Kriteria <span class="required">*</span>
                </label>
                <input type="text" id="nama_kriteria" name="nama_kriteria" class="form-control"
                  placeholder="Contoh: Tinggi Badan, Berat Badan"
                  value="<?= htmlspecialchars($kriteriaData['nama_kriteria']) ?>" maxlength="200" required />
                <span class="form-hint">Maksimal 200 karakter.</span>
              </div>

              <!-- Satuan & Urutan (2 kolom) -->
              <div class="form-row">
                <div class="form-group">
                  <label class="form-label" for="satuan">Satuan</label>
                  <input type="text" id="satuan" name="satuan" class="form-control" placeholder="Contoh: cm, kg, nilai"
                    value="<?= htmlspecialchars($kriteriaData['satuan'] ?? '') ?>" maxlength="50" />
                  <span class="form-hint">Satuan tampil di form penilaian.</span>
                </div>
                <div class="form-group">
                  <label class="form-label" for="urutan">Urutan Tampil</label>
                  <input type="number" id="urutan" name="urutan" class="form-control" placeholder="Contoh: 1, 2, 3"
                    value="<?= htmlspecialchars($kriteriaData['urutan'] ?? 0) ?>" min="0" />
                  <span class="form-hint">Urutan pada form penilaian.</span>
                </div>
              </div>

              <!-- Keterangan -->
              <div class="form-group">
                <label class="form-label" for="keterangan">Keterangan</label>
                <textarea id="keterangan" name="keterangan" class="form-control"
                  placeholder="Deskripsi singkat tentang kriteria ini..." rows="3"
                  style="resize:vertical;"><?= htmlspecialchars($kriteriaData['keterangan'] ?? '') ?></textarea>
                <span class="form-hint">Opsional. Tampil sebagai panduan di halaman admin.</span>
              </div>

              <hr class="form-divider">

              <!-- ── SECTION 2: Domain Nilai (untuk himpunan fuzzy & slider) ── -->
              <p class="form-section-title">Domain Nilai</p>
              <div class="alert alert-info" style="margin-bottom:20px;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                </svg>
                <span>Nilai minimum dan maksimum digunakan sebagai <strong>domain sumbu X</strong> pada visualisasi
                  himpunan fuzzy dan batas slider input penilaian.</span>
              </div>

              <div class="form-row">
                <!-- Nilai Min -->
                <div class="form-group">
                  <label class="form-label" for="nilai_min">
                    Nilai Minimum <span class="required">*</span>
                  </label>
                  <div style="position:relative;">
                    <input type="number" id="nilai_min" name="nilai_min" class="form-control" placeholder="0"
                      value="<?= htmlspecialchars($kriteriaData['nilai_min'] ?? 0) ?>" step="0.01" required
                      style="padding-right:<?= !empty($kriteriaData['satuan']) ? '52px' : '13px' ?>;"
                      oninput="updateSatuanSuffix()" />
                    <span id="suffixMin" style="
                      position:absolute;right:13px;top:50%;transform:translateY(-50%);
                      font-size:12px;font-family:'IBM Plex Mono',monospace;
                      color:var(--text-muted);pointer-events:none;
                      display:<?= !empty($kriteriaData['satuan']) ? 'block' : 'none' ?>;">
                      <?= htmlspecialchars($kriteriaData['satuan'] ?? '') ?>
                    </span>
                  </div>
                  <span class="form-hint">Batas bawah domain kriteria.</span>
                </div>

                <!-- Nilai Max -->
                <div class="form-group">
                  <label class="form-label" for="nilai_max">
                    Nilai Maksimum <span class="required">*</span>
                  </label>
                  <div style="position:relative;">
                    <input type="number" id="nilai_max" name="nilai_max" class="form-control" placeholder="100"
                      value="<?= htmlspecialchars($kriteriaData['nilai_max'] ?? 100) ?>" step="0.01" required
                      style="padding-right:<?= !empty($kriteriaData['satuan']) ? '52px' : '13px' ?>;"
                      oninput="updateSatuanSuffix()" />
                    <span id="suffixMax" style="
                      position:absolute;right:13px;top:50%;transform:translateY(-50%);
                      font-size:12px;font-family:'IBM Plex Mono',monospace;
                      color:var(--text-muted);pointer-events:none;
                      display:<?= !empty($kriteriaData['satuan']) ? 'block' : 'none' ?>;">
                      <?= htmlspecialchars($kriteriaData['satuan'] ?? '') ?>
                    </span>
                  </div>
                  <span class="form-hint">Batas atas domain kriteria.</span>
                </div>
              </div>

              <!-- Preview domain -->
              <div id="domainPreview" style="
                display:flex;align-items:center;gap:12px;
                padding:12px 16px;
                background:var(--surface-2);
                border:1.5px solid var(--border);
                border-radius:9px;
                margin-top:-8px;
              ">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                  stroke="currentColor" style="width:16px;height:16px;color:var(--text-muted);flex-shrink:0;">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3" />
                </svg>
                <span style="font-size:12.5px;color:var(--text-muted);">
                  Preview slider:
                </span>
                <div style="flex:1;position:relative;height:6px;background:var(--border);border-radius:3px;">
                  <div id="slider"
                    style="position:absolute;inset:0;background:linear-gradient(90deg,var(--primary),#7c3aed);border-radius:3px;width:0%;">
                  </div>
                </div>
                <span id="previewLabel"
                  style="font-size:12px;font-family:'IBM Plex Mono',monospace;color:var(--text-muted);white-space:nowrap;">
                  <?= htmlspecialchars($kriteriaData['nilai_min'] ?? 0) ?> –
                  <?= htmlspecialchars($kriteriaData['nilai_max'] ?? 100) ?>
                  <?= htmlspecialchars($kriteriaData['satuan'] ?? '') ?>
                </span>
              </div>

            </div>

            <!-- Actions -->
            <div class="form-actions">
              <a href="app.php?page=kriteria" class="btn-secondary">
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
                  Tambah Kriteria
                <?php endif; ?>
              </button>
            </div>

          </form>
        </div><!-- /form-card -->

      </div>
    </main>
  </div>

  <script>
    // ── Update suffix satuan di input min/max ──
    function updateSatuanSuffix() {
      const satuan = document.getElementById('satuan').value.trim();
      const min = document.getElementById('nilai_min').value;
      const max = document.getElementById('nilai_max').value;
      const slider = document.getElementById('slider');
      const showSuf = satuan !== '';

      ['suffixMin', 'suffixMax'].forEach(id => {
        const el = document.getElementById(id);
        if (!el) return;
        el.textContent = satuan;
        el.style.display = showSuf ? 'block' : 'none';
      });

      // Update slider width
      if (slider && min !== '0' && max !== '') {
        const minVal = parseFloat(min);
        const maxVal = parseFloat(max);
        const width = Math.max(0, Math.min(100, ((minVal - 0) / (maxVal - 0)) * 100));
        slider.style.width = `${width}%`;
      } else {
        slider.style.width = '0%';
      }

      // Update padding input agar tidak tertimpa suffix
      ['nilai_min', 'nilai_max'].forEach(id => {
        document.getElementById(id).style.paddingRight = showSuf ? '52px' : '13px';
      });

      // Update preview label
      const preview = document.getElementById('previewLabel');
      if (preview) {
        preview.textContent = `${min || 0} – ${max || 100}${satuan ? ' ' + satuan : ''}`;
      }
    }

    // Bind satuan input
    document.getElementById('satuan').addEventListener('input', updateSatuanSuffix);
    document.getElementById('nilai_min').addEventListener('input', updateSatuanSuffix);
    document.getElementById('nilai_max').addEventListener('input', updateSatuanSuffix);

    // ── Validasi client-side ──
    document.getElementById('kriteriaForm').addEventListener('submit', function (e) {
      let hasError = false;

      const namaEl = document.getElementById('nama_kriteria');
      const minEl = document.getElementById('nilai_min');
      const maxEl = document.getElementById('nilai_max');
      const urutanEl = document.getElementById('urutan');

      // Reset
      [namaEl, minEl, maxEl, urutanEl].forEach(el => el.classList.remove('is-invalid'));

      if (!namaEl.value.trim()) {
        namaEl.classList.add('is-invalid');
        hasError = true;
      }

      const minVal = parseFloat(minEl.value);
      const maxVal = parseFloat(maxEl.value);

      if (minEl.value === '' || isNaN(minVal)) {
        minEl.classList.add('is-invalid');
        hasError = true;
      }

      if (maxEl.value === '' || isNaN(maxVal)) {
        maxEl.classList.add('is-invalid');
        hasError = true;
      }

      if (!isNaN(minVal) && !isNaN(maxVal) && minVal >= maxVal) {
        minEl.classList.add('is-invalid');
        maxEl.classList.add('is-invalid');
        hasError = true;
      }

      if (urutanEl.value !== '' && (isNaN(parseInt(urutanEl.value)) || parseInt(urutanEl.value) < 0)) {
        urutanEl.classList.add('is-invalid');
        hasError = true;
      }

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