<?php
/**
 * FormHimpunan.php
 * Halaman form Add / Edit himpunan fuzzy
 * Kriteria diambil dinamis dari tabel kriteria
 */
require 'config/DBConfig.php';

$errors    = [];
$action    = $_GET['action'] ?? 'add';
$editId    = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$isEdit    = ($action === 'edit' && $editId > 0);
$pageTitle = $isEdit ? 'Edit Himpunan Fuzzy' : 'Tambah Himpunan Fuzzy';

// ── Ambil daftar kriteria dari DB ────────────────────────────
$kriteriaList = [];
if ($conn) {
  try {
    $stmt = $conn->query("
      SELECT id_kriteria, nama_kriteria, satuan, nilai_min, nilai_max
      FROM kriteria
      ORDER BY urutan ASC, id_kriteria ASC
    ");
    $kriteriaList = $stmt->fetchAll();
  } catch (\PDOException $e) {
    $errors[] = 'Gagal mengambil data kriteria: ' . $e->getMessage();
  }
}

// ── Default form data ────────────────────────────────────────
$formData = [
  'id_kriteria'     => '',
  'nama_himpunan'   => '',
  'tipe'            => 'trimf',
  'nilai_a'         => '',
  'nilai_b'         => '',
  'nilai_c'         => '',
  'nilai_d'         => '',
];

// ── Jika EDIT: ambil data himpunan existing ──────────────────
if ($conn && $isEdit) {
  try {
    $stmt = $conn->prepare("
      SELECT id_himpunan, id_kriteria, nama_himpunan, tipe_fungsi as tipe, nilai_a, nilai_b, nilai_c, nilai_d
      FROM himpunan_fuzzy
      WHERE id_himpunan = ?
    ");
    $stmt->execute([$editId]);
    $row = $stmt->fetch();
    if ($row) {
      $formData = $row;
    } else {
      $errors[] = 'Himpunan fuzzy dengan ID ' . $editId . ' tidak ditemukan.';
      $isEdit   = false;
    }
  } catch (\PDOException $e) {
    $errors[] = 'Gagal mengambil data himpunan: ' . $e->getMessage();
  }
}

// ── Helper: cari kriteria by id ──────────────────────────────
function getKriteriaById(array $list, int $id): ?array {
  foreach ($list as $k) {
    if ((int)$k['id_kriteria'] === $id) return $k;
  }
  return null;
}

// ── Proses Form Submit ────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $conn) {
  $id_kriteria   = (int)   ($_POST['id_kriteria']   ?? 0);
  $nama_himpunan = trim(   $_POST['nama_himpunan']  ?? '');
  $tipe          = trim(   $_POST['tipe']            ?? 'trimf');
  $nilai_a       =         $_POST['nilai_a']         ?? '';
  $nilai_b       =         $_POST['nilai_b']         ?? '';
  $nilai_c       =         $_POST['nilai_c']         ?? '';
  $nilai_d       =         $_POST['nilai_d']         ?? '';
  $formAction    =         $_POST['form_action']     ?? 'add';
  $formId        = (int)   ($_POST['himpunan_id']   ?? 0);

  // ── Validasi dasar ──
  if (!$id_kriteria) {
    $errors[] = 'Kriteria harus dipilih.';
  }

  if (empty($nama_himpunan)) {
    $errors[] = 'Nama himpunan tidak boleh kosong.';
  } elseif (strlen($nama_himpunan) > 100) {
    $errors[] = 'Nama himpunan maksimal 100 karakter.';
  }

  if (!in_array($tipe, ['trimf', 'trapmf'], true)) {
    $errors[] = 'Tipe fungsi tidak valid.';
  }

  foreach (['a' => 'a', 'b' => 'b', 'c' => 'c'] as $key => $label) {
    $val = $_POST['nilai_' . $key] ?? '';
    if ($val === '' || !is_numeric($val)) {
      $errors[] = "Nilai <strong>$label</strong> harus berupa angka.";
    }
  }

  if ($tipe === 'trapmf') {
    if ($nilai_d === '' || !is_numeric($nilai_d)) {
      $errors[] = 'Nilai <strong>d</strong> wajib diisi untuk fungsi Trapesium (trapmf).';
    }
  }

  // ── Validasi urutan parameter ──
  if (empty($errors) && is_numeric($nilai_a) && is_numeric($nilai_b) && is_numeric($nilai_c)) {
    if ((float)$nilai_a > (float)$nilai_b) {
      $errors[] = 'Nilai <strong>a</strong> harus ≤ nilai <strong>b</strong>.';
    }
    if ((float)$nilai_b > (float)$nilai_c) {
      $errors[] = 'Nilai <strong>b</strong> harus ≤ nilai <strong>c</strong>.';
    }
    if ($tipe === 'trapmf' && is_numeric($nilai_d) && (float)$nilai_c > (float)$nilai_d) {
      $errors[] = 'Nilai <strong>c</strong> harus ≤ nilai <strong>d</strong>.';
    }
  }

  // ── Simpan ke database ──
  if (empty($errors)) {
    try {
      $dVal = ($tipe === 'trapmf') ? (float)$nilai_d : null;

      if ($formAction === 'edit' && $formId > 0) {
        $stmt = $conn->prepare("
          UPDATE himpunan_fuzzy
          SET id_kriteria   = ?,
              nama_himpunan = ?,
              tipe_fungsi   = ?,
              nilai_a       = ?,
              nilai_b       = ?,
              nilai_c       = ?,
              nilai_d       = ?
          WHERE id_himpunan = ?
        ");
        $stmt->execute([
          $id_kriteria, $nama_himpunan, $tipe,
          (float)$nilai_a, (float)$nilai_b, (float)$nilai_c,
          $dVal, $formId,
        ]);
      } else {
        $stmt = $conn->prepare("
          INSERT INTO himpunan_fuzzy (id_kriteria, nama_himpunan, tipe_fungsi, nilai_a, nilai_b, nilai_c, nilai_d)
          VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
          $id_kriteria, $nama_himpunan, $tipe,
          (float)$nilai_a, (float)$nilai_b, (float)$nilai_c,
          $dVal,
        ]);
      }

      echo "<script>window.location.href='app.php?page=himpunan&success=" . ($formAction === 'edit' ? 'edited' : 'added') . "';</script>";
      exit;
    } catch (\PDOException $e) {
      $errors[] = 'Gagal menyimpan data: ' . $e->getMessage();
    }
  }

  // Isi ulang field jika ada error
  $formData['id_kriteria']   = $id_kriteria;
  $formData['nama_himpunan'] = $nama_himpunan;
  $formData['tipe']          = $tipe;
  $formData['nilai_a']       = $nilai_a;
  $formData['nilai_b']       = $nilai_b;
  $formData['nilai_c']       = $nilai_c;
  $formData['nilai_d']       = $nilai_d;
  $formData['id_himpunan']   = $formId;
}

// Kriteria terpilih (untuk info domain)
$selectedKriteria = getKriteriaById($kriteriaList, (int)$formData['id_kriteria']);
$isTrapmf         = ($formData['tipe'] === 'trapmf');
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

      <!-- ── Page Header ── -->
      <div class="page-header">
        <div>
          <h1 class="page-title"><?= htmlspecialchars($pageTitle) ?></h1>
          <p class="page-subtitle">
            <?= $isEdit
              ? 'Perbarui parameter fungsi keanggotaan himpunan fuzzy.'
              : 'Definisikan fungsi keanggotaan baru untuk variabel linguistik.'
            ?>
          </p>
        </div>
        <a href="app.php?page=himpunan" class="btn-secondary">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
          </svg>
          Kembali
        </a>
      </div>

      <!-- ── Error Alert ── -->
      <?php if (!empty($errors)): ?>
      <div class="alert alert-danger" style="max-width:720px;">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
        </svg>
        <div>
          <strong>Terjadi kesalahan, harap perbaiki berikut:</strong>
          <ul class="alert-list">
            <?php foreach ($errors as $e): ?>
              <li><?= $e ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>
      <?php endif; ?>

      <!-- ── Peringatan tidak ada kriteria ── -->
      <?php if (empty($kriteriaList)): ?>
      <div class="alert alert-warning" style="max-width:720px;background:#fffbeb;color:#92400e;border-color:#fde68a;">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
        </svg>
        <div>
          Belum ada kriteria yang tersedia.
          <a href="app.php?page=kriteria/add&action=add" style="color:#92400e;font-weight:700;text-decoration:underline;">
            Tambah kriteria terlebih dahulu
          </a> sebelum mendefinisikan himpunan fuzzy.
        </div>
      </div>
      <?php endif; ?>

      <div style="display:grid;grid-template-columns:1fr 320px;gap:24px;align-items:start;max-width:100%;">

        <!-- ── Form Card ── -->
        <div class="form-card">

          <div class="form-card-header">
            <div class="form-card-icon">
              <?php if ($isEdit): ?>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
              <?php else: ?>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
              <?php endif; ?>
            </div>
            <div>
              <p class="form-card-title"><?= htmlspecialchars($pageTitle) ?></p>
              <p class="form-card-desc">
                <?= $isEdit
                  ? 'Ubah parameter a, b, c (dan d untuk trapesium).'
                  : 'Pilih kriteria, nama himpunan, tipe, lalu isi parameter.'
                ?>
              </p>
            </div>
          </div>

          <form
            method="POST"
            action="app.php?page=himpunan/<?= $isEdit ? 'edit&action=edit&id=' . $editId : 'add&action=add' ?>"
            id="formHimpunan"
            novalidate>

            <input type="hidden" name="form_action" value="<?= $isEdit ? 'edit' : 'add' ?>">
            <?php if ($isEdit): ?>
              <input type="hidden" name="himpunan_id" value="<?= (int)($formData['id_himpunan'] ?? $editId) ?>">
            <?php endif; ?>

            <div class="form-card-body">

              <!-- ── SECTION 1: Identitas ── -->
              <p class="form-section-title">Identitas Himpunan</p>

              <!-- Kriteria -->
              <div class="form-group">
                <label class="form-label" for="id_kriteria">
                  Kriteria <span class="required">*</span>
                </label>
                <div class="select-wrap">
                  <select
                    class="form-control"
                    id="id_kriteria"
                    name="id_kriteria"
                    required
                    onchange="updateKriteriaInfo(this)">
                    <option value="">-- Pilih Kriteria --</option>
                    <?php foreach ($kriteriaList as $k): ?>
                      <option
                        value="<?= $k['id_kriteria'] ?>"
                        data-min="<?= $k['nilai_min'] ?>"
                        data-max="<?= $k['nilai_max'] ?>"
                        data-satuan="<?= htmlspecialchars($k['satuan'] ?? '') ?>"
                        <?= (int)$formData['id_kriteria'] === (int)$k['id_kriteria'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($k['nama_kriteria']) ?>
                        <?= $k['satuan'] ? '(' . htmlspecialchars($k['satuan']) . ')' : '' ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <!-- Info domain kriteria terpilih -->
                <div id="kriteriaDomainInfo" style="
                  display: <?= $selectedKriteria ? 'flex' : 'none' ?>;
                  align-items:center;gap:8px;
                  margin-top:8px;padding:9px 13px;
                  background:var(--primary-light);
                  border:1.5px solid #c7d2fe;
                  border-radius:9px;
                  font-size:12.5px;
                ">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:15px;height:15px;color:var(--primary);flex-shrink:0;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/>
                  </svg>
                  <span style="color:var(--primary);">
                    Domain:
                    <strong id="infoMin"><?= $selectedKriteria['nilai_min'] ?? '' ?></strong>
                    –
                    <strong id="infoMax"><?= $selectedKriteria['nilai_max'] ?? '' ?></strong>
                    <span id="infoSatuan"><?= $selectedKriteria ? htmlspecialchars($selectedKriteria['satuan'] ?? '') : '' ?></span>
                    &nbsp;·&nbsp; Pastikan parameter berada dalam rentang ini.
                  </span>
                </div>
                <span class="form-hint">Parameter fuzzy mengacu pada domain nilai kriteria ini.</span>
              </div>

              <!-- Nama Himpunan + Tipe -->
              <div class="form-row">
                <div class="form-group">
                  <label class="form-label" for="nama_himpunan">
                    Nama Himpunan <span class="required">*</span>
                  </label>
                  <input
                    type="text"
                    id="nama_himpunan"
                    name="nama_himpunan"
                    class="form-control"
                    placeholder="cth: Rendah, Sedang, Tinggi"
                    value="<?= htmlspecialchars($formData['nama_himpunan']) ?>"
                    maxlength="100"
                    required
                    oninput="updatePreview()">
                  <span class="form-hint">Label linguistik himpunan ini.</span>
                </div>
                <div class="form-group">
                  <label class="form-label" for="tipe">
                    Tipe Fungsi <span class="required">*</span>
                  </label>
                  <div class="select-wrap">
                    <select
                      class="form-control"
                      id="tipe"
                      name="tipe"
                      onchange="toggleDField(this.value); updatePreview()">
                      <option value="trimf" <?= $formData['tipe'] === 'trimf' ? 'selected' : '' ?>>
                        Segitiga (trimf)
                      </option>
                      <option value="trapmf" <?= $formData['tipe'] === 'trapmf' ? 'selected' : '' ?>>
                        Trapesium (trapmf)
                      </option>
                    </select>
                  </div>
                  <span class="form-hint">trimf = 3 titik, trapmf = 4 titik.</span>
                </div>
              </div>

              <hr class="form-divider">

              <!-- ── SECTION 2: Parameter Fungsi ── -->
              <p class="form-section-title">
                Parameter Fungsi
                <span style="font-size:11px;font-weight:400;text-transform:none;letter-spacing:0;color:var(--text-muted);margin-left:6px;" id="tipeLabel">
                  <?= $isTrapmf ? '(trapesium — 4 titik)' : '(segitiga — 3 titik)' ?>
                </span>
              </p>

              <!-- Baris a & b -->
              <div class="form-row">
                <div class="form-group">
                  <label class="form-label" for="nilai_a">
                    Nilai a <span class="required">*</span>
                  </label>
                  <input
                    type="number"
                    id="nilai_a"
                    name="nilai_a"
                    class="form-control"
                    placeholder="0"
                    value="<?= htmlspecialchars((string)$formData['nilai_a']) ?>"
                    step="0.01"
                    required
                    oninput="updatePreview()">
                  <span class="form-hint">Kaki kiri (batas bawah kiri).</span>
                </div>
                <div class="form-group">
                  <label class="form-label" for="nilai_b">
                    Nilai b <span class="required">*</span>
                  </label>
                  <input
                    type="number"
                    id="nilai_b"
                    name="nilai_b"
                    class="form-control"
                    placeholder="0"
                    value="<?= htmlspecialchars((string)$formData['nilai_b']) ?>"
                    step="0.01"
                    required
                    oninput="updatePreview()">
                  <span id="hintB" class="form-hint">
                    <?= $isTrapmf ? 'Kaki kiri atas (awal plateau).' : 'Puncak segitiga (derajat 1).' ?>
                  </span>
                </div>
              </div>

              <!-- Baris c & d -->
              <div class="form-row">
                <div class="form-group">
                  <label class="form-label" for="nilai_c">
                    Nilai c <span class="required">*</span>
                  </label>
                  <input
                    type="number"
                    id="nilai_c"
                    name="nilai_c"
                    class="form-control"
                    placeholder="0"
                    value="<?= htmlspecialchars((string)$formData['nilai_c']) ?>"
                    step="0.01"
                    required
                    oninput="updatePreview()">
                  <span id="hintC" class="form-hint">
                    <?= $isTrapmf ? 'Kaki kanan atas (akhir plateau).' : 'Kaki kanan (batas bawah kanan).' ?>
                  </span>
                </div>
                <div class="form-group" id="wrapD">
                  <label class="form-label" for="nilai_d">
                    Nilai d
                    <span class="required" id="requiredD" style="<?= !$isTrapmf ? 'display:none' : '' ?>">*</span>
                  </label>
                  <input
                    type="number"
                    id="nilai_d"
                    name="nilai_d"
                    class="form-control"
                    placeholder="0"
                    value="<?= htmlspecialchars((string)($formData['nilai_d'] ?? '')) ?>"
                    step="0.01"
                    <?= !$isTrapmf ? 'disabled' : '' ?>
                    style="<?= !$isTrapmf ? 'opacity:.4;' : '' ?>"
                    oninput="updatePreview()">
                  <span class="form-hint" id="hintD">
                    <?= $isTrapmf ? 'Kaki kanan bawah (batas kanan).' : 'Hanya untuk fungsi Trapesium.' ?>
                  </span>
                </div>
              </div>

            </div><!-- /form-card-body -->

            <!-- ── Actions ── -->
            <div class="form-actions">
              <a href="app.php?page=himpunan" class="btn-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Batal
              </a>
              <button
                type="submit"
                class="btn btn-primary"
                id="submitBtn"
                <?= empty($kriteriaList) ? 'disabled' : '' ?>>
                <?php if ($isEdit): ?>
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                  </svg>
                  Simpan Perubahan
                <?php else: ?>
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                  </svg>
                  Tambah Himpunan
                <?php endif; ?>
              </button>
            </div>

          </form>
        </div><!-- /form-card -->

        <!-- ── Panel Preview Canvas ── -->
        <div style="position:sticky;top:24px;">
          <div class="card">
            <div class="card-header" style="padding:16px 20px;">
              <div style="display:flex;align-items:center;gap:8px;">
                <div style="width:32px;height:32px;border-radius:8px;background:var(--primary-light);color:var(--primary);display:flex;align-items:center;justify-content:center;">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:16px;height:16px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941"/>
                  </svg>
                </div>
                <div>
                  <div class="card-title" style="font-size:13px;">Preview Fungsi</div>
                  <div style="font-size:11px;color:var(--text-muted);">Real-time</div>
                </div>
              </div>
            </div>
            <div style="padding:16px 20px;">
              <!-- Badge tipe -->
              <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                <span id="previewNama" style="font-size:13px;font-weight:700;color:var(--text);">—</span>
                <span id="previewTipeBadge" style="
                  font-family:'IBM Plex Mono',monospace;font-size:10.5px;font-weight:700;
                  padding:3px 9px;border-radius:20px;
                  background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;">
                  trimf
                </span>
              </div>

              <!-- Canvas preview -->
              <canvas id="previewCanvas" width="560" height="160"
                style="width:100%;height:140px;background:var(--surface-2);border-radius:8px;border:1.5px solid var(--border);">
              </canvas>

              <!-- Parameter summary -->
              <div id="paramSummary" style="
                margin-top:12px;display:grid;grid-template-columns:repeat(4,1fr);gap:6px;">
                <?php
                $params = [
                  ['label'=>'a','id'=>'pA','hint'=>'kiri bawah'],
                  ['label'=>'b','id'=>'pB','hint'=>'puncak'],
                  ['label'=>'c','id'=>'pC','hint'=>'kanan'],
                  ['label'=>'d','id'=>'pD','hint'=>'kanan bawah'],
                ];
                foreach ($params as $p): ?>
                <div style="text-align:center;padding:6px 4px;background:var(--surface-2);border:1.5px solid var(--border);border-radius:7px;" id="wrap<?= strtoupper($p['label']) ?>">
                  <div style="font-family:'IBM Plex Mono',monospace;font-size:13px;font-weight:700;color:var(--primary);" id="<?= $p['id'] ?>">—</div>
                  <div style="font-size:9.5px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;"><?= $p['label'] ?></div>
                </div>
                <?php endforeach; ?>
              </div>

              <!-- Info urutan -->
              <div id="previewInfo" style="
                margin-top:10px;padding:8px 12px;
                background:var(--surface-2);border-radius:7px;
                font-size:11.5px;color:var(--text-muted);
                font-family:'IBM Plex Mono',monospace;
                display:none;">
              </div>
            </div>
          </div>

          <!-- Panduan Parameter -->
          <div class="card" style="margin-top:16px;">
            <div style="padding:16px 20px;">
              <p style="font-size:12px;font-weight:700;color:var(--text);margin-bottom:10px;">Panduan Parameter</p>
              <div id="guideContent">
                <!-- diisi JS -->
              </div>
            </div>
          </div>
        </div><!-- /sticky panel -->

      </div><!-- /grid -->

    </div>
  </main>
</div>

<script>
// ── Toggle field d ───────────────────────────────────────────
function toggleDField(tipe) {
  const input     = document.getElementById('nilai_d');
  const reqStar   = document.getElementById('requiredD');
  const hintD     = document.getElementById('hintD');
  const hintB     = document.getElementById('hintB');
  const hintC     = document.getElementById('hintC');
  const tipeLabel = document.getElementById('tipeLabel');
  const badge     = document.getElementById('previewTipeBadge');

  const isTrapmf = tipe === 'trapmf';

  input.disabled  = !isTrapmf;
  input.style.opacity = isTrapmf ? '1' : '.4';
  if (!isTrapmf) { input.value = ''; }

  reqStar.style.display = isTrapmf ? '' : 'none';
  hintD.textContent     = isTrapmf ? 'Kaki kanan bawah (batas kanan).' : 'Hanya untuk fungsi Trapesium.';
  hintB.textContent     = isTrapmf ? 'Kaki kiri atas (awal plateau).'  : 'Puncak segitiga (derajat 1).';
  hintC.textContent     = isTrapmf ? 'Kaki kanan atas (akhir plateau).' : 'Kaki kanan (batas bawah kanan).';
  tipeLabel.textContent = isTrapmf ? '(trapesium — 4 titik)' : '(segitiga — 3 titik)';

  // Badge
  if (isTrapmf) {
    badge.style.background = '#f0fdf4'; badge.style.color = '#15803d'; badge.style.borderColor = '#bbf7d0';
    badge.textContent = 'trapmf';
  } else {
    badge.style.background = '#eff6ff'; badge.style.color = '#1d4ed8'; badge.style.borderColor = '#bfdbfe';
    badge.textContent = 'trimf';
  }

  updateGuide(tipe);
  drawPreview();
}

// ── Update info domain saat kriteria berubah ─────────────────
function updateKriteriaInfo(select) {
  const opt  = select.options[select.selectedIndex];
  const info = document.getElementById('kriteriaDomainInfo');

  if (!select.value || !opt) { if(info) info.style.display='none'; return; }

  document.getElementById('infoMin').textContent    = opt.dataset.min || '—';
  document.getElementById('infoMax').textContent    = opt.dataset.max || '—';
  document.getElementById('infoSatuan').textContent = opt.dataset.satuan ? ' ' + opt.dataset.satuan : '';
  if (info) info.style.display = 'flex';

  drawPreview();
}

// ── Membership function ──────────────────────────────────────
function getMembership(tipe, params, x) {
  if (tipe === 'trimf') {
    const [a, b, c] = params;
    if (x <= a || x >= c) return 0;
    if (x <= b) return (x - a) / (b - a || 1);
    return (c - x) / (c - b || 1);
  } else {
    const [a, b, c, d] = params;
    if (x <= a || x >= d) return 0;
    if (x >= b && x <= c) return 1;
    if (x < b) return (x - a) / (b - a || 1);
    return (d - x) / (d - c || 1);
  }
}

// ── Draw canvas preview ──────────────────────────────────────
function drawPreview() {
  const tipe  = document.getElementById('tipe').value;
  const a     = parseFloat(document.getElementById('nilai_a').value);
  const b     = parseFloat(document.getElementById('nilai_b').value);
  const c     = parseFloat(document.getElementById('nilai_c').value);
  const d     = parseFloat(document.getElementById('nilai_d').value);
  const nama  = document.getElementById('nama_himpunan').value.trim() || '—';

  // Update badge & nama
  document.getElementById('previewNama').textContent = nama;

  // Update param summary
  const vals = { pA: a, pB: b, pC: c, pD: (tipe==='trapmf' && !isNaN(d)) ? d : null };
  const wrapD = document.getElementById('wrapD2') || document.getElementById('wrapD');

  ['A','B','C','D'].forEach(l => {
    const el   = document.getElementById('p' + l);
    const val  = vals['p' + l];
    if (!el) return;
    if (l === 'D' && tipe !== 'trapmf') {
      el.textContent = '—';
      el.style.color = 'var(--text-light)';
    } else {
      el.textContent = isNaN(val) || val === null ? '—' : val;
      el.style.color = !isNaN(val) && val !== null ? 'var(--primary)' : 'var(--text-light)';
    }
  });

  // Tentukan range x dari kriteria yang dipilih
  const kriteriaSelect = document.getElementById('id_kriteria');
  const opt = kriteriaSelect.options[kriteriaSelect.selectedIndex];
  let xMin = (opt && opt.dataset.min) ? parseFloat(opt.dataset.min) : 0;
  let xMax = (opt && opt.dataset.max) ? parseFloat(opt.dataset.max) : 100;

  // Fallback ke params jika kriteria belum dipilih
  const allVals = [a, b, c, ...(tipe==='trapmf' && !isNaN(d) ? [d] : [])].filter(v => !isNaN(v));
  if (allVals.length && (!opt || !opt.dataset.min)) {
    xMin = Math.min(...allVals) - Math.abs(Math.min(...allVals)) * 0.1;
    xMax = Math.max(...allVals) + Math.abs(Math.max(...allVals)) * 0.1;
  }

  // Canvas
  const canvas = document.getElementById('previewCanvas');
  const ctx    = canvas.getContext('2d');
  const W = canvas.width, H = canvas.height;
  const pad = { l: 36, r: 12, t: 14, b: 28 };
  const pw = W - pad.l - pad.r, ph = H - pad.t - pad.b;

  ctx.clearRect(0, 0, W, H);

  // Background
  ctx.fillStyle = '#f8fafc';
  ctx.fillRect(0, 0, W, H);

  // Grid lines
  ctx.strokeStyle = '#e2e8f0'; ctx.lineWidth = 1;
  [0, 0.25, 0.5, 0.75, 1].forEach(v => {
    const y = pad.t + ph - v * ph;
    ctx.beginPath(); ctx.moveTo(pad.l, y); ctx.lineTo(W - pad.r, y); ctx.stroke();
  });

  // Y labels
  ctx.fillStyle = '#94a3b8'; ctx.font = '9px IBM Plex Mono'; ctx.textAlign = 'right';
  [0, 0.5, 1].forEach(v => {
    ctx.fillText(v.toFixed(1), pad.l - 4, pad.t + ph - v * ph + 3);
  });

  // Params valid?
  const hasValid = !isNaN(a) && !isNaN(b) && !isNaN(c) && (tipe === 'trimf' || !isNaN(d));
  const params   = tipe === 'trimf' ? [a, b, c] : [a, b, c, d];

  if (hasValid && xMax > xMin) {
    const steps = 300;
    const color = '#4f46e5';

    // Fill
    ctx.beginPath();
    ctx.fillStyle = color + '20';
    for (let i = 0; i <= steps; i++) {
      const x  = xMin + (xMax - xMin) * (i / steps);
      const mu = getMembership(tipe, params, x);
      const cx = pad.l + ((x - xMin) / (xMax - xMin)) * pw;
      const cy = pad.t + ph - mu * ph;
      i === 0 ? ctx.moveTo(cx, cy) : ctx.lineTo(cx, cy);
    }
    ctx.lineTo(pad.l + pw, pad.t + ph); ctx.lineTo(pad.l, pad.t + ph); ctx.closePath(); ctx.fill();

    // Stroke
    ctx.beginPath();
    ctx.strokeStyle = color; ctx.lineWidth = 2;
    for (let i = 0; i <= steps; i++) {
      const x  = xMin + (xMax - xMin) * (i / steps);
      const mu = getMembership(tipe, params, x);
      const cx = pad.l + ((x - xMin) / (xMax - xMin)) * pw;
      const cy = pad.t + ph - mu * ph;
      i === 0 ? ctx.moveTo(cx, cy) : ctx.lineTo(cx, cy);
    }
    ctx.stroke();
  } else {
    // Placeholder text
    ctx.fillStyle = '#cbd5e1';
    ctx.font = '11px Plus Jakarta Sans, sans-serif';
    ctx.textAlign = 'center';
    ctx.fillText('Isi parameter untuk melihat preview', W / 2, H / 2);
  }

  // X labels
  ctx.fillStyle = '#94a3b8'; ctx.textAlign = 'center'; ctx.font = '9px IBM Plex Mono';
  for (let i = 0; i <= 4; i++) {
    const val = xMin + (xMax - xMin) * (i / 4);
    const cx  = pad.l + (i / 4) * pw;
    ctx.fillText(Math.round(val), cx, H - 6);
  }
}

// ── updatePreview (alias) ────────────────────────────────────
function updatePreview() { drawPreview(); }

// ── Panduan per tipe ─────────────────────────────────────────
function updateGuide(tipe) {
  const el = document.getElementById('guideContent');
  if (!el) return;

  if (tipe === 'trimf') {
    el.innerHTML = `
      <div style="display:grid;gap:6px;font-size:12px;">
        <div style="display:flex;gap:8px;align-items:flex-start;">
          <span style="font-family:'IBM Plex Mono',monospace;font-weight:700;color:var(--primary);min-width:14px;">a</span>
          <span style="color:var(--text-muted);">Kaki kiri — derajat keanggotaan 0 dimulai di sini</span>
        </div>
        <div style="display:flex;gap:8px;align-items:flex-start;">
          <span style="font-family:'IBM Plex Mono',monospace;font-weight:700;color:var(--primary);min-width:14px;">b</span>
          <span style="color:var(--text-muted);">Puncak — derajat keanggotaan mencapai 1</span>
        </div>
        <div style="display:flex;gap:8px;align-items:flex-start;">
          <span style="font-family:'IBM Plex Mono',monospace;font-weight:700;color:var(--primary);min-width:14px;">c</span>
          <span style="color:var(--text-muted);">Kaki kanan — derajat keanggotaan kembali ke 0</span>
        </div>
        <div style="margin-top:4px;padding:6px 10px;background:var(--surface-2);border-radius:6px;font-family:'IBM Plex Mono',monospace;font-size:10.5px;color:var(--text-muted);">
          Syarat: a ≤ b ≤ c
        </div>
      </div>`;
  } else {
    el.innerHTML = `
      <div style="display:grid;gap:6px;font-size:12px;">
        <div style="display:flex;gap:8px;align-items:flex-start;">
          <span style="font-family:'IBM Plex Mono',monospace;font-weight:700;color:var(--primary);min-width:14px;">a</span>
          <span style="color:var(--text-muted);">Kaki kiri bawah — mulai naik dari 0</span>
        </div>
        <div style="display:flex;gap:8px;align-items:flex-start;">
          <span style="font-family:'IBM Plex Mono',monospace;font-weight:700;color:var(--primary);min-width:14px;">b</span>
          <span style="color:var(--text-muted);">Kaki kiri atas — plateau dimulai (derajat = 1)</span>
        </div>
        <div style="display:flex;gap:8px;align-items:flex-start;">
          <span style="font-family:'IBM Plex Mono',monospace;font-weight:700;color:var(--primary);min-width:14px;">c</span>
          <span style="color:var(--text-muted);">Kaki kanan atas — plateau berakhir</span>
        </div>
        <div style="display:flex;gap:8px;align-items:flex-start;">
          <span style="font-family:'IBM Plex Mono',monospace;font-weight:700;color:var(--success);min-width:14px;">d</span>
          <span style="color:var(--text-muted);">Kaki kanan bawah — kembali ke 0</span>
        </div>
        <div style="margin-top:4px;padding:6px 10px;background:var(--surface-2);border-radius:6px;font-family:'IBM Plex Mono',monospace;font-size:10.5px;color:var(--text-muted);">
          Syarat: a ≤ b ≤ c ≤ d
        </div>
      </div>`;
  }
}

// ── Validasi client-side ─────────────────────────────────────
document.getElementById('formHimpunan').addEventListener('submit', function(e) {
  let hasError = false;
  const fields = ['id_kriteria','nama_himpunan','nilai_a','nilai_b','nilai_c'];
  fields.forEach(id => {
    const el = document.getElementById(id);
    if (!el) return;
    el.classList.remove('is-invalid');
    if (!el.value.trim()) { el.classList.add('is-invalid'); hasError = true; }
  });

  const tipe = document.getElementById('tipe').value;
  if (tipe === 'trapmf') {
    const d = document.getElementById('nilai_d');
    if (!d.value.trim()) { d.classList.add('is-invalid'); hasError = true; }
  }

  // Urutan a ≤ b ≤ c (≤ d)
  const a = parseFloat(document.getElementById('nilai_a').value);
  const b = parseFloat(document.getElementById('nilai_b').value);
  const c = parseFloat(document.getElementById('nilai_c').value);
  const d = parseFloat(document.getElementById('nilai_d').value);

  if (!isNaN(a) && !isNaN(b) && a > b) {
    document.getElementById('nilai_a').classList.add('is-invalid');
    document.getElementById('nilai_b').classList.add('is-invalid');
    hasError = true;
  }
  if (!isNaN(b) && !isNaN(c) && b > c) {
    document.getElementById('nilai_b').classList.add('is-invalid');
    document.getElementById('nilai_c').classList.add('is-invalid');
    hasError = true;
  }
  if (tipe === 'trapmf' && !isNaN(c) && !isNaN(d) && c > d) {
    document.getElementById('nilai_c').classList.add('is-invalid');
    document.getElementById('nilai_d').classList.add('is-invalid');
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

// ── Init ─────────────────────────────────────────────────────
(function init() {
  const tipe = document.getElementById('tipe')?.value || 'trimf';
  updateGuide(tipe);
  toggleDField(tipe);  // pastikan state awal benar
  drawPreview();

  // Init kriteria info jika sudah ada nilai (mode edit atau error POST)
  const sel = document.getElementById('id_kriteria');
  if (sel && sel.value) updateKriteriaInfo(sel);
})();
</script>
</body>
</html>