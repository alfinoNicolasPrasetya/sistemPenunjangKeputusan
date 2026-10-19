<?php
// Mengambil koneksi
require 'config/DBConfig.php';

$errors = [];
$action = $_GET['action'] ?? 'add';
$editId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$isEditFlag = isset($_GET['isEdit']) ? (int) $_GET['isEdit'] : false;
$isEdit = ($action === 'edit' && $editId > 0);
$pageTitle = $isEdit ? 'Edit Penilaian' : 'Input Penilaian';

// ── Ambil daftar kriteria aktif dari DB ──────────────────────
$kriteriaList = [];
if ($conn) {
  try {
    $stmt = $conn->query("
      SELECT id_kriteria, nama_kriteria, satuan, nilai_min, nilai_max, keterangan
      FROM kriteria
      ORDER BY urutan ASC, id_kriteria ASC
    ");
    $kriteriaList = $stmt->fetchAll();
  } catch (\PDOException $e) {
    $errors[] = 'Gagal mengambil data kriteria: ' . $e->getMessage();
  }
}

// ── Ambil daftar siswa dari DB ───────────────────────────────
$siswaList = [];
if ($conn) {
  try {
    $stmt = $conn->query("
      SELECT id_siswa, name, asal_sekolah, jenis_kelamin
      FROM siswa
      ORDER BY name ASC
    ");
    $siswaList = $stmt->fetchAll();
  } catch (\PDOException $e) {
    $errors[] = 'Gagal mengambil data siswa: ' . $e->getMessage();
  }
}

// ── Default form data ────────────────────────────────────────
$formData = [
  'id_siswa' => '',
  'nilai' => [], // ['id_kriteria' => nilai_input]
];

// ── Jika EDIT: ambil data penilaian existing ─────────────────
if ($conn && $isEdit) {
  try {
      if($isEditFlag){
        $stmt = $conn->prepare("
          SELECT p.id_siswa, p.id_kriteria, p.nilai_input
          FROM penilaian p
          WHERE p.id_siswa = ?
        ");
      $stmt->execute([$editId]);
      $rows = $stmt->fetchAll();

      if ($rows) {
        $formData['id_siswa'] = $rows[0]['id_siswa'];
        foreach ($rows as $row) {
          $formData['nilai'][$row['id_kriteria']] = $row['nilai_input'];
        }
      } else {
        $errors[] = 'Data penilaian untuk siswa ini tidak ditemukan.';
        $isEdit = false;
      }
    }else{
      $formData['id_siswa'] = $editId;
      if (!checkPenilaianByIdSiswa($conn, $editId)) {
        $errors[] = 'Siswa ini belum memiliki data penilaian. Tidak dapat masuk mode edit.';
        $isEdit = false;
      }else{
        $stmt = $conn->prepare("
          SELECT p.id_siswa, p.id_kriteria, p.nilai_input
          FROM penilaian p
          WHERE p.id_siswa = ?
        ");
        $stmt->execute([$editId]);
        $rows = $stmt->fetchAll();

        if ($rows) {
          foreach ($rows as $row) {
            $formData['nilai'][$row['id_kriteria']] = $row['nilai_input'];
          }
        } else {
          $errors[] = 'Data penilaian untuk siswa ini tidak ditemukan.';
          $isEdit = false;
        }   
      }
    }

  } catch (\PDOException $e) {
    $errors[] = 'Gagal mengambil data penilaian: ' . $e->getMessage();
  }
}

// ── Proses Form Submit ────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $conn) {
  $id_siswa = (int) ($_POST['id_siswa'] ?? 0);
  $nilaiPost = $_POST['nilai'] ?? [];
  $formAction = $_POST['form_action'] ?? 'add';
  $formSiswaId = (int) ($_POST['edit_siswa_id'] ?? 0);

  // ── Validasi ──
  if (!$id_siswa) {
    $errors[] = 'Siswa harus dipilih.';
  }

  if (empty($kriteriaList)) {
    $errors[] = 'Tidak ada kriteria yang tersedia. Tambah kriteria terlebih dahulu.';
  }

  foreach ($kriteriaList as $k) {
    $val = $nilaiPost[$k['id_kriteria']] ?? '';
    if ($val === '' || $val === null) {
      $errors[] = 'Nilai untuk kriteria <strong>' . htmlspecialchars($k['nama_kriteria']) . '</strong> tidak boleh kosong.';
    } elseif (!is_numeric($val)) {
      $errors[] = 'Nilai untuk kriteria <strong>' . htmlspecialchars($k['nama_kriteria']) . '</strong> harus berupa angka.';
    } elseif ((float) $val < (float) $k['nilai_min'] || (float) $val > (float) $k['nilai_max']) {
      $errors[] = 'Nilai <strong>' . htmlspecialchars($k['nama_kriteria']) . '</strong> harus antara '
        . $k['nilai_min'] . ' – ' . $k['nilai_max']
        . ($k['satuan'] ? ' ' . $k['satuan'] : '') . '.';
    }
  }

  // ── Cek duplikat siswa (hanya untuk ADD) ──
  if (empty($errors) && $formAction === 'add') {
    try {
      $stmt = $conn->prepare("SELECT COUNT(*) FROM penilaian WHERE id_siswa = ?");
      $stmt->execute([$id_siswa]);
      if ($stmt->fetchColumn() > 0) {
        $errors[] = 'Siswa ini sudah memiliki data penilaian. Gunakan menu Edit untuk mengubahnya.';
      }
    } catch (\PDOException $e) {
      $errors[] = 'Gagal memeriksa data: ' . $e->getMessage();
    }
  }

  // ── Simpan ke database ──
  if (empty($errors)) {
    try {
      $conn->beginTransaction();

      if ($formAction === 'edit' && $formSiswaId > 0) {
        // Hapus nilai lama lalu insert ulang (lebih aman dari update per-kriteria)
        $stmtDel = $conn->prepare("DELETE FROM penilaian WHERE id_siswa = ?");
        $stmtDel->execute([$formSiswaId]);
      }

      $stmtIns = $conn->prepare("
        INSERT INTO penilaian (id_siswa, id_kriteria, nilai_input)
        VALUES (?, ?, ?)
      ");

      foreach ($kriteriaList as $k) {
        $stmtIns->execute([
          $id_siswa,
          $k['id_kriteria'],
          (float) $nilaiPost[$k['id_kriteria']],
        ]);
      }

      $conn->commit();

      echo "<script>window.location.href='app.php?page=penilaian&success=" . ($formAction === 'edit' ? 'edited' : 'added') . "';</script>";
      exit;
    } catch (\PDOException $e) {
      $conn->rollBack();
      $errors[] = 'Gagal menyimpan data: ' . $e->getMessage();
    }
  }

  // Isi ulang field jika ada error
  $formData['id_siswa'] = $id_siswa;
  foreach ($kriteriaList as $k) {
    $formData['nilai'][$k['id_kriteria']] = $nilaiPost[$k['id_kriteria']] ?? '';
  }
}

// ── Helper: cari siswa by id ──────────────────────────────────
function getSiswaById(array $list, int $id): ?array
{
  foreach ($list as $s) {
    if ((int) $s['id_siswa'] === $id)
      return $s;
  }
  return null;
}

function checkPenilaianByIdSiswa(PDO $conn, int $id_siswa): bool
{
  try {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM penilaian WHERE id_siswa = ?");
    $stmt->execute([$id_siswa]);
    return $stmt->fetchColumn() > 0;
  } catch (\PDOException $e) {
    return false; // Anggap tidak ada penilaian jika terjadi error
  }
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

        <!-- ── Page Header ── -->
        <div class="page-header">
          <div>
            <h1 class="page-title"><?= htmlspecialchars($pageTitle) ?></h1>
            <p class="page-subtitle">
              <?= $isEdit
                ? 'Perbarui nilai kriteria siswa yang sudah terdaftar.'
                : 'Input nilai setiap kriteria untuk satu siswa sebagai data masukan fuzzy.'
                ?>
            </p>
          </div>
          <a href="app.php?page=penilaian" class="btn-secondary">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali
          </a>
        </div>

        <!-- ── Error Alert ── -->
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
                  <li><?= $e ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          </div>
        <?php endif; ?>

        <!-- ── Peringatan tidak ada kriteria ── -->
        <?php if (empty($kriteriaList)): ?>
          <div class="alert alert-warning" style="max-width:680px;background:#fffbeb;color:#92400e;border-color:#fde68a;">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
            </svg>
            <div>
              Belum ada kriteria yang tersedia.
              <a href="app.php?page=kriteria/add&action=add"
                style="color:#92400e;font-weight:700;text-decoration:underline;">
                Tambah kriteria terlebih dahulu
              </a> sebelum melakukan penilaian.
            </div>
          </div>
        <?php endif; ?>

        <!-- ── Form Card ── -->
        <div class="form-card" style="width: 100%; max-width: none;">

          <!-- Card Header -->
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
                  ? 'Nilai lama akan digantikan dengan nilai baru yang diinput.'
                  : 'Pilih siswa lalu isi nilai untuk setiap kriteria yang tersedia.'
                  ?>
              </p>
            </div>
          </div>

          <form method="POST"
            action="app.php?page=penilaian/<?= $isEdit ? 'edit&action=edit&id=' . $editId : 'add&action=add' ?>"
            id="formPenilaian" novalidate>

            <input type="hidden" name="form_action" value="<?= $isEdit ? 'edit' : 'add' ?>">
            <?php if ($isEdit): ?>
              <input type="hidden" name="edit_siswa_id" value="<?= (int) $formData['id_siswa'] ?>">
            <?php endif; ?>

            <div class="form-card-body">

              <!-- ── SECTION 1: Pilih Siswa ── -->
              <p class="form-section-title">Data Siswa</p>

              <div class="form-group">
                <label class="form-label" for="id_siswa">
                  Siswa <span class="required">*</span>
                </label>

                <?php if ($isEdit):
                  // Mode edit: siswa tidak bisa diubah (tampilkan saja nama)
                  $siswaEdit = getSiswaById($siswaList, (int) $formData['id_siswa']);
                  ?>
                  <input type="hidden" name="id_siswa" value="<?= (int) $formData['id_siswa'] ?>">
                  <div style="
                  display:flex;align-items:center;gap:10px;
                  padding:10px 13px;
                  border:1.5px solid var(--border);
                  border-radius:9px;
                  background:var(--surface-2);
                ">
                    <div class="user-initials" style="width:32px;height:32px;font-size:11px;flex-shrink:0;">
                      <?= strtoupper(substr($siswaEdit['name'] ?? 'S', 0, 2)) ?>
                    </div>
                    <div>
                      <div style="font-size:13.5px;font-weight:600;color:var(--text);">
                        <?= htmlspecialchars($siswaEdit['name'] ?? '—') ?>
                      </div>
                      <div style="font-size:12px;color:var(--text-muted);">
                        <?= htmlspecialchars($siswaEdit['asal_sekolah'] ?? '') ?> &middot;
                        <?= ($siswaEdit['jenis_kelamin'] ?? '') === 'L' ? 'Laki-laki' : 'Perempuan' ?>
                      </div>
                    </div>
                    <span style="
                    margin-left:auto;font-size:11px;font-weight:600;
                    padding:3px 9px;border-radius:20px;
                    background:var(--primary-light);color:var(--primary);border:1px solid #c7d2fe;">
                      Mode Edit
                    </span>
                  </div>
                  <span class="form-hint">Siswa tidak dapat diubah pada mode edit.</span>

                <?php else: ?>
                  <div class="select-wrap">
                    <select class="form-control" id="id_siswa" name="id_siswa" required
                      onchange="updateSiswaPreview(this)">
                      <option value="">-- Pilih Siswa --</option>
                      <?php foreach ($siswaList as $s): ?>
                        <option value="<?= $s['id_siswa'] ?>" data-asal_sekolah="<?= htmlspecialchars($s['asal_sekolah']) ?>"
                          data-jk="<?= $s['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan' ?>"
                          <?= (int) $formData['id_siswa'] === (int) $s['id_siswa'] ? 'selected' : '' ?>>
                          <?= htmlspecialchars($s['name']) ?> — <?= htmlspecialchars($s['asal_sekolah']) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>

                  <!-- Preview info siswa terpilih -->
                  <div id="siswaPreview" style="
                  display:none;
                  margin-top:10px;
                  padding:10px 13px;
                  border:1.5px solid #c7d2fe;
                  border-radius:9px;
                  background:var(--primary-light);
                  display:flex;align-items:center;gap:10px;
                ">
                    <div class="user-initials" id="previewInitials"
                      style="width:32px;height:32px;font-size:11px;flex-shrink:0;">—</div>
                    <div>
                      <div id="previewNama" style="font-size:13.5px;font-weight:600;color:var(--text);"></div>
                      <div id="previewDetail" style="font-size:12px;color:var(--text-muted);"></div>
                    </div>
                  </div>
                  <span class="form-hint">Hanya siswa yang belum memiliki penilaian yang bisa dipilih untuk mode tambah.</span>
                <?php endif; ?>
              </div>

              <hr class="form-divider">

              <!-- ── SECTION 2: Nilai Per Kriteria (dinamis) ── -->
              <p class="form-section-title">
                Nilai Per Kriteria
                <span
                  style="font-size:11px;font-weight:400;text-transform:none;letter-spacing:0;color:var(--text-muted);margin-left:6px;">
                  (<?= count($kriteriaList) ?> kriteria aktif)
                </span>
              </p>

              <?php if (empty($kriteriaList)): ?>
                <p style="color:var(--text-muted);font-size:13.5px;text-align:center;padding:24px 0;">
                  Tidak ada kriteria tersedia.
                </p>
              <?php else: ?>

                <?php foreach ($kriteriaList as $idx => $k):
                  $fieldId = 'nilai_' . $k['id_kriteria'];
                  $sliderId = 'slider_' . $k['id_kriteria'];
                  $existVal = $formData['nilai'][$k['id_kriteria']] ?? '';
                  $sliderVal = $existVal !== '' ? $existVal : ($k['nilai_min'] + ($k['nilai_max'] - $k['nilai_min']) / 2);
                  $isLast = ($idx === count($kriteriaList) - 1);
                  ?>
                  <div class="form-group" <?= $isLast ? 'style="margin-bottom:0;"' : '' ?>>

                    <!-- Label + keterangan tooltip -->
                    <label class="form-label" for="<?= $fieldId ?>">
                      <span style="display:flex;align-items:center;gap:6px;">
                        <?= htmlspecialchars($k['nama_kriteria']) ?>
                        <span class="required">*</span>
                        <?php if ($k['satuan']): ?>
                          <span style="font-size:11px;font-weight:400;color:var(--text-muted);">
                            (<?= htmlspecialchars($k['satuan']) ?>)
                          </span>
                        <?php endif; ?>
                        <?php if ($k['keterangan']): ?>
                          <span title="<?= htmlspecialchars($k['keterangan']) ?>" style="
                        width:15px;height:15px;border-radius:50%;
                        background:var(--surface-2);border:1.5px solid var(--border);
                        color:var(--text-muted);font-size:9px;font-weight:700;
                        display:inline-flex;align-items:center;justify-content:center;
                        cursor:help;">?</span>
                        <?php endif; ?>
                      </span>
                      <!-- Counter nilai live -->
                      <span id="counter_<?= $k['id_kriteria'] ?>" style="
                    font-family:'IBM Plex Mono',monospace;font-size:12px;font-weight:700;
                    color:var(--primary);background:var(--primary-light);
                    padding:2px 8px;border-radius:5px;border:1px solid #c7d2fe;">
                        <?= $existVal !== '' ? $existVal : '—' ?>
                        <?= $k['satuan'] ? htmlspecialchars($k['satuan']) : '' ?>
                      </span>
                    </label>

                    <!-- Input number + suffix satuan -->
                    <div style="position:relative;">
                      <input type="number" id="<?= $fieldId ?>" name="nilai[<?= $k['id_kriteria'] ?>]" class="form-control"
                        placeholder="<?= $k['nilai_min'] ?> – <?= $k['nilai_max'] ?>"
                        value="<?= htmlspecialchars((string) $existVal) ?>" min="<?= $k['nilai_min'] ?>"
                        max="<?= $k['nilai_max'] ?>" step="0.01" required <?= $k['satuan'] ? 'style="padding-right:52px;"' : '' ?>
                        oninput="syncSlider('<?= $k['id_kriteria'] ?>', this.value, <?= $k['nilai_min'] ?>, <?= $k['nilai_max'] ?>)">
                      <?php if ($k['satuan']): ?>
                        <span style="
                      position:absolute;right:13px;top:50%;transform:translateY(-50%);
                      font-size:12px;font-family:'IBM Plex Mono',monospace;
                      color:var(--text-muted);pointer-events:none;">
                          <?= htmlspecialchars($k['satuan']) ?>
                        </span>
                      <?php endif; ?>
                    </div>

                    <!-- Slider -->
                    <div style="padding:4px 0 0;">
                      <input type="range" id="<?= $sliderId ?>" min="<?= $k['nilai_min'] ?>" max="<?= $k['nilai_max'] ?>"
                        step="0.01" value="<?= $sliderVal ?>" style="width:100%;accent-color:var(--primary);"
                        oninput="syncInput('<?= $k['id_kriteria'] ?>', this.value)">
                      <!-- Min/Max label -->
                      <div style="display:flex;justify-content:space-between;margin-top:2px;">
                        <span style="font-size:10.5px;font-family:'IBM Plex Mono',monospace;color:var(--text-light);">
                          <?= $k['nilai_min'] ?>    <?= $k['satuan'] ? ' ' . $k['satuan'] : '' ?>
                        </span>
                        <span style="font-size:10.5px;font-family:'IBM Plex Mono',monospace;color:var(--text-light);">
                          <?= $k['nilai_max'] ?>    <?= $k['satuan'] ? ' ' . $k['satuan'] : '' ?>
                        </span>
                      </div>
                    </div>

                    <?php if ($k['keterangan']): ?>
                      <span class="form-hint"><?= htmlspecialchars($k['keterangan']) ?></span>
                    <?php else: ?>
                      <span class="form-hint">
                        Rentang valid: <?= $k['nilai_min'] ?> – <?= $k['nilai_max'] ?>
                        <?= $k['satuan'] ? htmlspecialchars($k['satuan']) : '' ?>
                      </span>
                    <?php endif; ?>

                  </div>
                <?php endforeach; ?>

              <?php endif; ?>

            </div><!-- /form-card-body -->

            <!-- ── Actions ── -->
            <div class="form-actions">
              <a href="app.php?page=penilaian" class="btn-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
                Batal
              </a>
              <button type="submit" class="btn btn-primary" id="submitBtn" <?= empty($kriteriaList) ? 'disabled' : '' ?>>
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
                  Simpan Penilaian
                <?php endif; ?>
              </button>
            </div>

          </form>
        </div><!-- /form-card -->

      </div>
    </main>
  </div>

  <script>
    document.addEventListener("DOMContentLoaded", function() {
    });
    // ── Sync slider → input ──────────────────────────────────────
    function syncInput(kriteriaId, val) {
      const input = document.getElementById('nilai_' + kriteriaId);
      if (input) input.value = val;
      updateCounter(kriteriaId, val, input);
    }

    // ── Sync input → slider ──────────────────────────────────────
    function syncSlider(kriteriaId, val, min, max) {
      const slider = document.getElementById('slider_' + kriteriaId);
      const input = document.getElementById('nilai_' + kriteriaId);

      // Clamp nilai ke range
      let clamped = parseFloat(val);
      if (!isNaN(clamped)) {
        clamped = Math.min(Math.max(clamped, min), max);
        if (slider) slider.value = clamped;
      }
      updateCounter(kriteriaId, val, input);
    }

    // ── Update counter badge ─────────────────────────────────────
    function updateCounter(kriteriaId, val, inputEl) {
      const counter = document.getElementById('counter_' + kriteriaId);
      if (!counter) return;

      const satuan = inputEl?.nextElementSibling?.textContent?.trim() || '';
      const num = parseFloat(val);
      const min = parseFloat(inputEl?.min);
      const max = parseFloat(inputEl?.max);

      if (val === '' || isNaN(num)) {
        counter.textContent = '—';
        counter.style.color = 'var(--text-muted)';
        counter.style.background = 'var(--surface-2)';
        counter.style.borderColor = 'var(--border)';
      } else if (num < min || num > max) {
        counter.textContent = num + (satuan ? ' ' + satuan : '');
        counter.style.color = 'var(--danger)';
        counter.style.background = '#fef2f2';
        counter.style.borderColor = '#fecaca';
      } else {
        counter.textContent = num + (satuan ? ' ' + satuan : '');
        counter.style.color = 'var(--primary)';
        counter.style.background = 'var(--primary-light)';
        counter.style.borderColor = '#c7d2fe';
      }
    }

    // ── Preview siswa terpilih (mode add) ────────────────────────
    function updateSiswaPreview(select) {
      const preview = document.getElementById('siswaPreview');
      const initials = document.getElementById('previewInitials');
      const nama = document.getElementById('previewNama');
      const detail = document.getElementById('previewDetail');

      if (!select.value || !preview) return;

      const opt = select.options[select.selectedIndex];
      const namaTeks = opt.text.split('—')[0].trim();

      preview.style.display = 'flex';
      initials.textContent = namaTeks.substring(0, 2).toUpperCase();
      nama.textContent = namaTeks ;
      detail.textContent = (opt.dataset.asal_sekolah || '') + ' · ' + (opt.dataset.jk || '');
    }

    // Init preview jika sudah ada nilai (kasus error POST)
    (function () {
      const select = document.getElementById('id_siswa');
      if (select && select.value) updateSiswaPreview(select);
    })();

    // ── Validasi client-side ─────────────────────────────────────
    document.getElementById('formPenilaian').addEventListener('submit', function (e) {
      let hasError = false;

      // Validasi siswa
      const siswaEl = document.getElementById('id_siswa');
      if (siswaEl && !siswaEl.value) {
        siswaEl.classList.add('is-invalid');
        hasError = true;
      }

      // Validasi tiap kriteria
      document.querySelectorAll('[id^="nilai_"]').forEach(input => {
        input.classList.remove('is-invalid');
        const val = parseFloat(input.value);
        const min = parseFloat(input.min);
        const max = parseFloat(input.max);

        if (input.value === '' || isNaN(val)) {
          input.classList.add('is-invalid');
          hasError = true;
        } else if (val < min || val > max) {
          input.classList.add('is-invalid');
          hasError = true;
        }
      });

      if (hasError) {
        e.preventDefault();
        window.scrollTo({ top: 0, behavior: 'smooth' });
      }
    });

    // Clear invalid on focus
    document.querySelectorAll('.form-control, [id^="nilai_"]').forEach(el => {
      el.addEventListener('focus', () => el.classList.remove('is-invalid'));
    });
  </script>
</body>

</html>