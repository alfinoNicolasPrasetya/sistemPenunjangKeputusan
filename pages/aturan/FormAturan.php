<?php
/**
 * FormAturan.php
 * Halaman form Add / Edit aturan fuzzy (IF-THEN Sugeno)
 *
 * Struktur tabel:
 *  aturan_fuzzy       : id_aturan, nama_aturan, output_nilai, keterangan
 *  detail_aturan_fuzzy: id_detail, id_aturan, id_himpunan, operator (AND|OR)
 *  himpunan_fuzzy     : id_himpunan, id_kriteria, nama_himpunan, tipe_fungsi, ...
 *  kriteria           : id_kriteria, nama_kriteria, satuan, ...
 */
require 'config/DBConfig.php';

$errors = [];
$action = $_GET['action'] ?? 'add';
$editId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$isEdit = ($action === 'edit' && $editId > 0);
$pageTitle = $isEdit ? 'Edit Aturan Fuzzy' : 'Tambah Aturan Fuzzy';

// ── Ambil kriteria + himpunan dari DB (digrup per kriteria) ──
// Struktur: [ id_kriteria => [ 'nama' => '...', 'himpunan' => [ {id, nama} ] ] ]
$kriteriaWithHimpunan = [];
$himpunanById = [];   // flat lookup: id_himpunan => row

if ($conn) {
  try {
    $stmt = $conn->query("
      SELECT
        k.id_kriteria,
        k.nama_kriteria,
        k.satuan,
        h.id_himpunan,
        h.nama_himpunan,
        h.tipe_fungsi
      FROM kriteria k
      LEFT JOIN himpunan_fuzzy h ON h.id_kriteria = k.id_kriteria
      ORDER BY k.urutan ASC, k.id_kriteria ASC, h.nama_himpunan ASC
    ");
    foreach ($stmt->fetchAll() as $row) {
      $kid = $row['id_kriteria'];
      if (!isset($kriteriaWithHimpunan[$kid])) {
        $kriteriaWithHimpunan[$kid] = [
          'id' => $kid,
          'nama' => $row['nama_kriteria'],
          'satuan' => $row['satuan'],
          'himpunan' => [],
        ];
      }
      if ($row['id_himpunan']) {
        $kriteriaWithHimpunan[$kid]['himpunan'][] = [
          'id' => $row['id_himpunan'],
          'nama' => $row['nama_himpunan'],
          'tipe' => $row['tipe_fungsi'],
        ];
        $himpunanById[$row['id_himpunan']] = $row;
      }
    }
  } catch (\PDOException $e) {
    $errors[] = 'Gagal mengambil data kriteria/himpunan: ' . $e->getMessage();
  }
}

// Kategori output yang tersedia
$kategoriOptions = ['Tidak Layak', 'Cukup Layak', 'Layak', 'Sangat Layak'];

// ── Default form data ────────────────────────────────────────
$formData = [
  'nama_aturan' => '',
  'output_nilai' => '',
  'keterangan' => '',
  // kondisi: [ ['id_himpunan' => X, 'operator' => 'AND'], ... ]
  'kondisi' => [
    ['id_himpunan' => '', 'operator' => 'IF'],
  ],
];

// ── EDIT: ambil data aturan + detail ─────────────────────────
if ($conn && $isEdit) {
  try {
    $stmt = $conn->prepare("
      SELECT id_aturan, nama_aturan, output_nilai, keterangan
      FROM aturan_fuzzy
      WHERE id_aturan = ?
    ");
    $stmt->execute([$editId]);
    $aturan = $stmt->fetch();

    if ($aturan) {
      $formData['nama_aturan'] = $aturan['nama_aturan'];
      $formData['output_nilai'] = $aturan['output_nilai'];
      $formData['keterangan'] = $aturan['keterangan'];

      // Ambil kondisi detail
      $stmtD = $conn->prepare("
        SELECT id_detail, id_himpunan, operator
        FROM detail_aturan_fuzzy
        WHERE id_aturan = ?
        ORDER BY id_detail ASC
      ");
      $stmtD->execute([$editId]);
      $details = $stmtD->fetchAll();

      if ($details) {
        $formData['kondisi'] = [];
        foreach ($details as $idx => $d) {
          $formData['kondisi'][] = [
            'id_himpunan' => $d['id_himpunan'],
            'operator' => $idx === 0 ? 'IF' : $d['operator'],
          ];
        }
      }
    } else {
      $errors[] = 'Aturan dengan ID ' . $editId . ' tidak ditemukan.';
      $isEdit = false;
    }
  } catch (\PDOException $e) {
    $errors[] = 'Gagal mengambil data aturan: ' . $e->getMessage();
  }
}

// ── Proses Form Submit ────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $conn) {
  $nama_aturan = trim($_POST['nama_aturan'] ?? '');
  $output_nilai = $_POST['output_nilai'] ?? '';
  $keterangan = trim($_POST['keterangan'] ?? '');
  $himpunanPost = $_POST['id_himpunan'] ?? [];   // array index kondisi
  $operatorPost = $_POST['operator'] ?? [];   // array index kondisi
  $formAction = $_POST['form_action'] ?? 'add';
  $formId = (int) ($_POST['aturan_id'] ?? 0);

  // ── Validasi ──
  if (empty($nama_aturan)) {
    $errors[] = 'Nama aturan tidak boleh kosong.';
  }

  if ($output_nilai === '' || !is_numeric($output_nilai)) {
    $errors[] = 'Nilai output (z) harus berupa angka.';
  } elseif ((float) $output_nilai < 0 || (float) $output_nilai > 100) {
    $errors[] = 'Nilai output harus antara 0 – 100.';
  }

  if (empty($keterangan) || !in_array($keterangan, $kategoriOptions, true)) {
    $errors[] = 'Kategori keterangan harus dipilih.';
  }

  if (empty($himpunanPost) || count(array_filter($himpunanPost)) === 0) {
    $errors[] = 'Minimal satu kondisi IF harus ditambahkan.';
  } else {
    foreach ($himpunanPost as $idx => $hid) {
      if (!$hid) {
        $errors[] = 'Kondisi baris ' . ($idx + 1) . ': himpunan harus dipilih.';
      }
    }
  }

  // ── Simpan ke DB ──
  if (empty($errors)) {
    try {
      $conn->beginTransaction();

      if ($formAction === 'edit' && $formId > 0) {
        // Update aturan
        $stmt = $conn->prepare("
          UPDATE aturan_fuzzy
          SET nama_aturan = ?, output_nilai = ?, keterangan = ?
          WHERE id_aturan = ?
        ");
        $stmt->execute([$nama_aturan, (float) $output_nilai, $keterangan, $formId]);

        // Hapus detail lama
        $conn->prepare("DELETE FROM detail_aturan_fuzzy WHERE id_aturan = ?")->execute([$formId]);
        $aturanId = $formId;
      } else {
        // Insert aturan baru
        $stmt = $conn->prepare("
          INSERT INTO aturan_fuzzy (nama_aturan, output_nilai, keterangan)
          VALUES (?, ?, ?)
        ");
        $stmt->execute([$nama_aturan, (float) $output_nilai, $keterangan]);
        $aturanId = $conn->lastInsertId();
      }

      // Insert detail kondisi
      $stmtDet = $conn->prepare("
        INSERT INTO detail_aturan_fuzzy (id_aturan, id_himpunan, operator)
        VALUES (?, ?, ?)
      ");
      foreach ($himpunanPost as $idx => $hid) {
        if (!$hid)
          continue;
        $op = ($idx === 0) ? 'AND' : ($operatorPost[$idx] ?? 'AND');
        $stmtDet->execute([$aturanId, (int) $hid, $op]);
      }

      $conn->commit();
      echo "<script>window.location.href='app.php?page=aturan&success=" . ($formAction === 'edit' ? 'edited' : 'added') . "';</script>";
      exit;
    } catch (\PDOException $e) {
      $conn->rollBack();
      $errors[] = 'Gagal menyimpan data: ' . $e->getMessage();
    }
  }

  // Isi ulang jika error
  $formData['nama_aturan'] = $nama_aturan;
  $formData['output_nilai'] = $output_nilai;
  $formData['keterangan'] = $keterangan;
  $formData['kondisi'] = [];
  foreach ($himpunanPost as $idx => $hid) {
    $formData['kondisi'][] = [
      'id_himpunan' => $hid,
      'operator' => $idx === 0 ? 'IF' : ($operatorPost[$idx] ?? 'AND'),
    ];
  }
  if (empty($formData['kondisi'])) {
    $formData['kondisi'][] = ['id_himpunan' => '', 'operator' => 'IF'];
  }
  $formId = (int) ($_POST['aturan_id'] ?? 0);
}

// Warna per kategori (untuk output preview)
$kategoriColors = [
  'Tidak Layak' => ['bg' => '#fef2f2', 'text' => '#991b1b', 'border' => '#fecaca', 'bar' => '#ef4444'],
  'Cukup Layak' => ['bg' => '#fffbeb', 'text' => '#92400e', 'border' => '#fde68a', 'bar' => '#f59e0b'],
  'Layak' => ['bg' => '#f0fdf4', 'text' => '#166534', 'border' => '#bbf7d0', 'bar' => '#10b981'],
  'Sangat Layak' => ['bg' => '#eff6ff', 'text' => '#1e40af', 'border' => '#bfdbfe', 'bar' => '#3b82f6'],
];

// JSON untuk JS
$kriteriaJson = json_encode(array_values($kriteriaWithHimpunan));
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <link rel="stylesheet" href="assets/app.css">
  <style>
    /* Condition row styles */
    .condition-row {
      display: grid;
      grid-template-columns: 56px 1fr auto 1fr auto;
      gap: 10px;
      align-items: center;
      padding: 12px;
      background: var(--surface);
      border: 1.5px solid var(--border);
      border-radius: 10px;
      transition: border-color .2s;
    }

    .condition-row:focus-within {
      border-color: var(--border-focus);
    }

    .cond-badge {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 5px 0;
      border-radius: 7px;
      font-size: 11px;
      font-weight: 800;
      letter-spacing: .07em;
      text-align: center;
      width: 100%;
    }

    .cond-badge-if {
      background: var(--primary-light);
      color: var(--primary);
      border: 1.5px solid #c7d2fe;
    }

    .cond-badge-and {
      background: #ecfdf5;
      color: #065f46;
      border: 1.5px solid #a7f3d0;
    }

    .cond-badge-select {
      padding: 5px 8px;
      border-radius: 7px;
      font-size: 11px;
      font-weight: 700;
      letter-spacing: .05em;
      border: 1.5px solid;
      background: none;
      cursor: pointer;
      font-family: inherit;
      outline: none;
    }

    .cond-label {
      font-size: 12.5px;
      color: var(--text-muted);
      text-align: center;
      white-space: nowrap;
      font-style: italic;
    }

    .cond-remove {
      width: 30px;
      height: 30px;
      border-radius: 7px;
      background: #fef2f2;
      color: var(--danger);
      border: 1.5px solid #fecaca;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      transition: background .2s, border-color .2s;
    }

    .cond-remove:hover {
      background: var(--danger);
      color: #fff;
      border-color: var(--danger);
    }

    .cond-remove svg {
      width: 14px;
      height: 14px;
    }

    /* Output preview bar */
    .output-preview {
      padding: 16px;
      border-radius: 10px;
      border: 1.5px solid;
      transition: background .3s, border-color .3s;
    }

    .output-bar-track {
      height: 8px;
      background: var(--border);
      border-radius: 4px;
      overflow: hidden;
      margin-top: 8px;
    }

    .output-bar-fill {
      height: 100%;
      border-radius: 4px;
      transition: width .5s ease, background .3s;
    }

    /* Rule preview card */
    .rule-preview {
      background: var(--surface-2);
      border: 1.5px solid var(--border);
      border-radius: 10px;
      padding: 14px 16px;
      font-size: 13px;
      line-height: 1.8;
    }

    .rule-token {
      display: inline-flex;
      align-items: center;
      padding: 2px 8px;
      border-radius: 5px;
      font-size: 11.5px;
      font-weight: 700;
      margin: 0 2px;
    }
  </style>
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
                ? 'Perbarui kondisi IF dan output THEN aturan fuzzy.'
                : 'Definisikan kondisi IF-THEN baru untuk inferensi Fuzzy Sugeno.'
                ?>
            </p>
          </div>
          <a href="app.php?page=aturan" class="btn-secondary">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali
          </a>
        </div>

        <!-- ── Error Alert ── -->
        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger" style="max-width:760px;">
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

        <!-- ── Warning tidak ada himpunan ── -->
        <?php if (empty($kriteriaWithHimpunan) || !array_filter(array_column($kriteriaWithHimpunan, 'himpunan'))): ?>
          <div class="alert alert-warning" style="max-width:760px;background:#fffbeb;color:#92400e;border-color:#fde68a;">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
            </svg>
            <div>
              Belum ada himpunan fuzzy yang tersedia.
              <a href="app.php?page=himpunan_fuzzy/add&action=add"
                style="color:#92400e;font-weight:700;text-decoration:underline;">
                Tambah himpunan fuzzy
              </a> terlebih dahulu sebelum membuat aturan.
            </div>
          </div>
        <?php endif; ?>

        <!-- ── 2 Kolom: Form | Preview ── -->
        <div style="display:grid;grid-template-columns:1fr 300px;gap:24px;align-items:start;">

          <!-- ── FORM CARD ── -->
          <div class="form-card">

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
                    ? 'Kondisi lama akan digantikan sepenuhnya dengan kondisi baru.'
                    : 'Setiap aturan terdiri dari satu atau lebih kondisi IF yang dievaluasi dengan operator AND.'
                    ?>
                </p>
              </div>
            </div>

            <form method="POST"
              action="app.php?page=aturan/<?= $isEdit ? 'edit&action=edit&id=' . $editId : 'add&action=add' ?>"
              id="formAturan" novalidate>

              <input type="hidden" name="form_action" value="<?= $isEdit ? 'edit' : 'add' ?>">
              <?php if ($isEdit): ?>
                <input type="hidden" name="aturan_id" value="<?= $editId ?>">
              <?php endif; ?>

              <div class="form-card-body">

                <!-- ── SECTION 1: Identitas Aturan ── -->
                <p class="form-section-title">Identitas Aturan</p>

                <div class="form-group">
                  <label class="form-label" for="nama_aturan">
                    Nama Aturan <span class="required">*</span>
                  </label>
                  <input type="text" id="nama_aturan" name="nama_aturan" class="form-control"
                    placeholder="cth: Aturan 1 — Tinggi Normal, Berat Normal"
                    value="<?= htmlspecialchars($formData['nama_aturan']) ?>" maxlength="200" required
                    oninput="updateRulePreview()">
                  <span class="form-hint">Label deskriptif untuk mengidentifikasi aturan ini.</span>
                </div>

                <hr class="form-divider">

                <!-- ── SECTION 2: Kondisi IF ── -->
                <p class="form-section-title">
                  Kondisi IF
                  <span
                    style="font-size:11px;font-weight:400;text-transform:none;letter-spacing:0;color:var(--text-muted);margin-left:6px;">
                    (operator AND antar kondisi)
                  </span>
                </p>

                <!-- Info jika tidak ada himpunan -->
                <?php if (empty($kriteriaWithHimpunan)): ?>
                  <p style="color:var(--text-muted);font-size:13px;padding:16px 0;text-align:center;">
                    Tidak ada kriteria / himpunan tersedia.
                  </p>
                <?php else: ?>

                  <div id="conditionList" style="display:flex;flex-direction:column;gap:8px;">
                    <?php foreach ($formData['kondisi'] as $idx => $cond):
                      $isFirst = $idx === 0;
                      $selHim = (int) ($cond['id_himpunan'] ?? 0);
                      $selOp = $cond['operator'] ?? 'AND';

                      // Tentukan kriteria yang memiliki himpunan ini
                      $selKritId = 0;
                      if ($selHim && isset($himpunanById[$selHim])) {
                        $selKritId = (int) $himpunanById[$selHim]['id_kriteria'];
                      }
                      ?>
                      <div class="condition-row" data-index="<?= $idx ?>">
                        <!-- Badge IF / operator selector -->
                        <div>
                          <?php if ($isFirst): ?>
                            <span class="cond-badge cond-badge-if">IF</span>
                            <input type="hidden" name="operator[<?= $idx ?>]" value="AND">
                          <?php else: ?>
                            <select name="operator[<?= $idx ?>]" class="cond-badge-select cond-badge-and"
                              onchange="updateOperatorBadge(this)">
                              <option value="AND" <?= $selOp === 'AND' ? 'selected' : '' ?>>AND</option>
                              <option value="OR" <?= $selOp === 'OR' ? 'selected' : '' ?>>OR</option>
                            </select>
                          <?php endif; ?>
                        </div>

                        <!-- Dropdown kriteria -->
                        <div class="select-wrap">
                          <select class="form-control cond-kriteria" data-index="<?= $idx ?>"
                            onchange="onKriteriaChange(this, <?= $idx ?>)">
                            <option value="">-- Kriteria --</option>
                            <?php foreach ($kriteriaWithHimpunan as $k): ?>
                              <option value="<?= $k['id'] ?>" <?= (int) $k['id'] === $selKritId ? 'selected' : '' ?>>
                                <?= htmlspecialchars($k['nama']) ?>
                              </option>
                            <?php endforeach; ?>
                          </select>
                        </div>

                        <!-- Label "adalah" -->
                        <span class="cond-label">adalah</span>

                        <!-- Dropdown himpunan (difilter berdasarkan kriteria) -->
                        <div class="select-wrap">
                          <select class="form-control cond-himpunan" name="id_himpunan[<?= $idx ?>]"
                            data-index="<?= $idx ?>" onchange="updateRulePreview()">
                            <option value="">-- Himpunan --</option>
                            <?php
                            // Tampilkan semua himpunan dari kriteria yang terpilih
                            if ($selKritId && isset($kriteriaWithHimpunan[$selKritId])) {
                              foreach ($kriteriaWithHimpunan[$selKritId]['himpunan'] as $h) {
                                $selected = $h['id'] === $selHim ? 'selected' : '';
                                echo "<option value=\"{$h['id']}\" $selected>{$h['nama']}</option>";
                              }
                            }
                            ?>
                          </select>
                        </div>

                        <!-- Tombol hapus (sembunyikan untuk baris pertama) -->
                        <?php if (!$isFirst): ?>
                          <button type="button" class="cond-remove" onclick="removeCondition(this)">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                              stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                          </button>
                        <?php else: ?>
                          <div style="width:30px;"></div>
                        <?php endif; ?>
                      </div>
                    <?php endforeach; ?>
                  </div>

                  <!-- Tombol tambah kondisi -->
                  <button type="button" onclick="addCondition()" style="
                  margin-top:10px;
                  display:inline-flex;align-items:center;gap:6px;
                  font-size:12.5px;font-weight:600;
                  color:var(--primary);
                  background:var(--primary-light);
                  border:1.5px dashed #a5b4fc;
                  border-radius:9px;
                  padding:9px 16px;
                  cursor:pointer;
                  width:100%;
                  justify-content:center;
                  transition:background .2s, border-color .2s;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                      stroke="currentColor" style="width:15px;height:15px;">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah Kondisi
                  </button>

                <?php endif; ?>

                <hr class="form-divider">

                <!-- ── SECTION 3: Output THEN ── -->
                <p class="form-section-title">Output THEN</p>

                <div class="form-row">
                  <!-- Nilai Output -->
                  <div class="form-group">
                    <label class="form-label" for="output_nilai">
                      Nilai Output z <span class="required">*</span>
                    </label>
                    <div style="position:relative;">
                      <input type="number" id="output_nilai" name="output_nilai" class="form-control"
                        placeholder="0 – 100" value="<?= htmlspecialchars((string) $formData['output_nilai']) ?>"
                        min="0" max="100" step="0.01" required oninput="updateOutputPreview()">
                      <span
                        style="position:absolute;right:13px;top:50%;transform:translateY(-50%);font-size:12px;font-family:'IBM Plex Mono',monospace;color:var(--text-muted);pointer-events:none;">
                        / 100
                      </span>
                    </div>
                    <input type="range" id="sliderOutput" min="0" max="100" step="0.01"
                      value="<?= htmlspecialchars((string) ($formData['output_nilai'] ?: 50)) ?>"
                      style="width:100%;margin-top:8px;accent-color:var(--primary);"
                      oninput="document.getElementById('output_nilai').value=this.value; updateOutputPreview();">
                    <span class="form-hint">Nilai crisp Sugeno — semakin tinggi semakin layak.</span>
                  </div>

                  <!-- Kategori -->
                  <div class="form-group">
                    <label class="form-label" for="keterangan">
                      Kategori <span class="required">*</span>
                    </label>
                    <div class="select-wrap">
                      <select id="keterangan" name="keterangan" class="form-control" required
                        onchange="updateOutputPreview()">
                        <option value="">-- Pilih Kategori --</option>
                        <?php foreach ($kategoriOptions as $kat): ?>
                          <option value="<?= $kat ?>" <?= $formData['keterangan'] === $kat ? 'selected' : '' ?>>
                            <?= $kat ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <span class="form-hint">Label kategorisasi hasil akhir.</span>
                  </div>
                </div>

              </div><!-- /form-card-body -->

              <!-- ── Actions ── -->
              <div class="form-actions">
                <a href="app.php?page=aturan" class="btn-secondary">
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
                    Tambah Aturan
                  <?php endif; ?>
                </button>
              </div>

            </form>
          </div><!-- /form-card -->

          <!-- ── PANEL PREVIEW (sticky) ── -->
          <div style="position:sticky;top:24px;display:flex;flex-direction:column;gap:16px;">

            <!-- Rule Preview -->
            <div class="card">
              <div class="card-header" style="padding:14px 18px;">
                <div style="display:flex;align-items:center;gap:8px;">
                  <div
                    style="width:30px;height:30px;border-radius:7px;background:var(--primary-light);color:var(--primary);display:flex;align-items:center;justify-content:center;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                      stroke="currentColor" style="width:15px;height:15px;">
                      <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z" />
                    </svg>
                  </div>
                  <div>
                    <div class="card-title" style="font-size:12.5px;">Preview Aturan</div>
                    <div style="font-size:11px;color:var(--text-muted);">Real-time</div>
                  </div>
                </div>
              </div>
              <div style="padding:14px 18px;">
                <div class="rule-preview" id="rulePreview">
                  <span style="color:var(--text-light);font-size:12.5px;font-style:italic;">
                    Isi kondisi untuk melihat preview aturan...
                  </span>
                </div>
              </div>
            </div>

            <!-- Output Preview -->
            <div class="card">
              <div class="card-header" style="padding:14px 18px;">
                <div style="display:flex;align-items:center;gap:8px;">
                  <div
                    style="width:30px;height:30px;border-radius:7px;background:#ecfdf5;color:var(--success);display:flex;align-items:center;justify-content:center;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                      stroke="currentColor" style="width:15px;height:15px;">
                      <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5" />
                    </svg>
                  </div>
                  <div>
                    <div class="card-title" style="font-size:12.5px;">Output z</div>
                    <div style="font-size:11px;color:var(--text-muted);">Nilai crisp Sugeno</div>
                  </div>
                </div>
              </div>
              <div style="padding:14px 18px;">
                <div id="outputPreviewBox" class="output-preview"
                  style="background:#f8fafc;border-color:var(--border);">
                  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
                    <span id="outputKategoriLabel"
                      style="font-size:12px;font-weight:600;color:var(--text-muted);">—</span>
                    <span id="outputNilaiLabel"
                      style="font-family:'IBM Plex Mono',monospace;font-size:18px;font-weight:800;color:var(--text-muted);">—</span>
                  </div>
                  <div class="output-bar-track">
                    <div class="output-bar-fill" id="outputBarFill" style="width:0%;background:var(--border);"></div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Panduan -->
            <div class="card">
              <div style="padding:14px 18px;">
                <p style="font-size:12px;font-weight:700;color:var(--text);margin-bottom:10px;">Panduan Nilai Output</p>
                <?php foreach ($kategoriColors as $kat => $c): ?>
                  <div style="display:flex;align-items:center;gap:8px;margin-bottom:7px;">
                    <span
                      style="width:10px;height:10px;border-radius:50%;background:<?= $c['bar'] ?>;flex-shrink:0;"></span>
                    <span style="font-size:12px;color:var(--text-muted);flex:1;"><?= $kat ?></span>
                    <span style="font-family:'IBM Plex Mono',monospace;font-size:11px;color:var(--text-muted);">
                      <?php
                      $ranges = ['Tidak Layak' => '0–40', 'Cukup Layak' => '41–60', 'Layak' => '61–80', 'Sangat Layak' => '81–100'];
                      echo $ranges[$kat] ?? '';
                      ?>
                    </span>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>

          </div><!-- /sticky panel -->
        </div><!-- /grid -->

      </div>
    </main>
  </div>

  <script>
    // ── Data kriteria dari PHP ───────────────────────────────────
    const KRITERIA_DATA = <?= $kriteriaJson ?>;

    // Lookup: id_himpunan → { id_kriteria, nama_himpunan, nama_kriteria }
    const HIMPUNAN_MAP = {};
    const KRITERIA_MAP = {};
    KRITERIA_DATA.forEach(k => {
      KRITERIA_MAP[k.id] = k;
      (k.himpunan || []).forEach(h => {
        HIMPUNAN_MAP[h.id] = { ...h, id_kriteria: k.id, nama_kriteria: k.nama };
      });
    });

    // Warna kategori
    const KATEGORI_COLORS = <?= json_encode($kategoriColors) ?>;

    // ── Indeks counter untuk rows baru ──────────────────────────
    let condIndex = <?= count($formData['kondisi']) ?>;

    // ── Tambah kondisi baru ──────────────────────────────────────
    function addCondition() {
      const list = document.getElementById('conditionList');
      const div = document.createElement('div');
      div.className = 'condition-row';
      div.dataset.index = condIndex;

      // Build options kriteria
      let kriteriaOpts = '<option value="">-- Kriteria --</option>';
      KRITERIA_DATA.forEach(k => {
        kriteriaOpts += `<option value="${k.id}">${escHtml(k.nama)}</option>`;
      });

      div.innerHTML = `
    <div>
      <select
        name="operator[${condIndex}]"
        class="cond-badge-select cond-badge-and"
        onchange="updateOperatorBadge(this)">
        <option value="AND">AND</option>
        <option value="OR">OR</option>
      </select>
    </div>
    <div class="select-wrap">
      <select class="form-control cond-kriteria" data-index="${condIndex}"
        onchange="onKriteriaChange(this, ${condIndex})">
        ${kriteriaOpts}
      </select>
    </div>
    <span class="cond-label">adalah</span>
    <div class="select-wrap">
      <select class="form-control cond-himpunan" name="id_himpunan[${condIndex}]"
        data-index="${condIndex}" onchange="updateRulePreview()">
        <option value="">-- Himpunan --</option>
      </select>
    </div>
    <button type="button" class="cond-remove" onclick="removeCondition(this)">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
      </svg>
    </button>
  `;
      list.appendChild(div);
      condIndex++;
      updateRulePreview();
    }

    // ── Hapus kondisi ────────────────────────────────────────────
    function removeCondition(btn) {
      btn.closest('.condition-row').remove();
      updateRulePreview();
    }

    // ── Update badge operator (tampilan warna) ───────────────────
    function updateOperatorBadge(sel) {
      if (sel.value === 'OR') {
        sel.style.background = '#fdf4ff';
        sel.style.color = '#7e22ce';
        sel.style.borderColor = '#d8b4fe';
      } else {
        sel.style.background = '#ecfdf5';
        sel.style.color = '#065f46';
        sel.style.borderColor = '#a7f3d0';
      }
      updateRulePreview();
    }

    // ── Isi dropdown himpunan saat kriteria berubah ───────────────
    function onKriteriaChange(sel, idx) {
      const kritId = parseInt(sel.value);
      const kriteria = KRITERIA_DATA.find(k => k.id === kritId);
      const himpSel = document.querySelector(`.cond-himpunan[data-index="${idx}"]`);
      if (!himpSel) return;

      himpSel.innerHTML = '<option value="">-- Himpunan --</option>';
      if (kriteria && kriteria.himpunan.length) {
        kriteria.himpunan.forEach(h => {
          himpSel.innerHTML += `<option value="${h.id}">${escHtml(h.nama)}</option>`;
        });
      }
      updateRulePreview();
    }

    // ── Update preview rule card ─────────────────────────────────
    function updateRulePreview() {
      const preview = document.getElementById('rulePreview');
      const rows = document.querySelectorAll('.condition-row');
      const parts = [];

      rows.forEach((row, idx) => {
        const kriteriaEl = row.querySelector('.cond-kriteria');
        const himpunanEl = row.querySelector('.cond-himpunan');
        const operatorEl = row.querySelector('[name^="operator"]');

        const kritNama = kriteriaEl?.options[kriteriaEl.selectedIndex]?.text || '';
        const himpNama = himpunanEl?.options[himpunanEl.selectedIndex]?.text || '';
        const op = operatorEl?.value || 'AND';

        if (himpNama && himpNama !== '-- Himpunan --' && kritNama && kritNama !== '-- Kriteria --') {
          parts.push({ op: idx === 0 ? 'IF' : op, kriteria: kritNama, himpunan: himpNama });
        }
      });

      const outputVal = document.getElementById('output_nilai')?.value || '';
      const katEl = document.getElementById('keterangan');
      const kat = katEl?.value || '';
      const namaTeks = document.getElementById('nama_aturan')?.value?.trim() || '';

      if (!parts.length) {
        preview.innerHTML = `<span style="color:var(--text-light);font-size:12.5px;font-style:italic;">Isi kondisi untuk melihat preview aturan...</span>`;
        return;
      }

      let html = '';
      if (namaTeks) {
        html += `<div style="font-size:11px;font-weight:700;color:var(--text-muted);margin-bottom:8px;letter-spacing:.05em;">${escHtml(namaTeks)}</div>`;
      }

      parts.forEach(p => {
        const opClass = p.op === 'IF' ? 'style="background:var(--primary-light);color:var(--primary);border:1px solid #c7d2fe;"'
          : p.op === 'OR' ? 'style="background:#fdf4ff;color:#7e22ce;border:1px solid #d8b4fe;"'
            : 'style="background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;"';
        html += `
      <span class="rule-token" ${opClass}>${p.op}</span>
      <span style="font-size:13px;color:var(--text);font-weight:500;">${escHtml(p.kriteria)}</span>
      <span style="font-size:12px;color:var(--text-muted);margin:0 2px;">adalah</span>
      <span class="rule-token" style="background:var(--surface-2);color:var(--text);border:1px solid var(--border);">${escHtml(p.himpunan)}</span>
      <br>
    `;
      });

      if (outputVal !== '') {
        const katColor = KATEGORI_COLORS[kat] || {};
        html += `
      <span class="rule-token" style="background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;margin-top:4px;">THEN</span>
      <span style="font-size:14px;font-weight:800;color:var(--text);font-family:'IBM Plex Mono',monospace;"> z = ${outputVal}</span>
    `;
        if (kat && katColor.bg) {
          html += `
        <span class="rule-token" style="background:${katColor.bg};color:${katColor.text};border:1px solid ${katColor.border};margin-left:4px;">
          ${escHtml(kat)}
        </span>
      `;
        }
      }

      preview.innerHTML = html;
    }

    // ── Update output preview bar ────────────────────────────────
    function updateOutputPreview() {
      const val = parseFloat(document.getElementById('output_nilai')?.value || 0);
      const kat = document.getElementById('keterangan')?.value || '';
      const c = KATEGORI_COLORS[kat] || {};

      // Sync slider
      const slider = document.getElementById('sliderOutput');
      if (slider && !isNaN(val)) slider.value = val;

      const box = document.getElementById('outputPreviewBox');
      const label = document.getElementById('outputKategoriLabel');
      const nilai = document.getElementById('outputNilaiLabel');
      const bar = document.getElementById('outputBarFill');

      if (kat && c.bg) {
        box.style.background = c.bg;
        box.style.borderColor = c.border;
        label.style.color = c.text;
        label.textContent = kat;
        bar.style.background = c.bar;
      } else {
        box.style.background = '#f8fafc';
        box.style.borderColor = 'var(--border)';
        label.style.color = 'var(--text-muted)';
        label.textContent = kat || '—';
        bar.style.background = 'var(--border)';
      }

      nilai.textContent = !isNaN(val) && val !== '' ? val : '—';
      nilai.style.color = (c.text || 'var(--text-muted)');
      bar.style.width = (!isNaN(val) ? Math.min(100, Math.max(0, val)) : 0) + '%';

      updateRulePreview();
    }

    // ── HTML escape helper ───────────────────────────────────────
    function escHtml(str) {
      return String(str)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;')
        .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    // ── Validasi client-side ─────────────────────────────────────
    document.getElementById('formAturan').addEventListener('submit', function (e) {
      let hasError = false;
      ['nama_aturan', 'output_nilai', 'keterangan'].forEach(id => {
        const el = document.getElementById(id);
        if (!el) return;
        el.classList.remove('is-invalid');
        if (!el.value.trim()) { el.classList.add('is-invalid'); hasError = true; }
      });

      const val = parseFloat(document.getElementById('output_nilai')?.value);
      if (!isNaN(val) && (val < 0 || val > 100)) {
        document.getElementById('output_nilai').classList.add('is-invalid');
        hasError = true;
      }

      // Cek minimal 1 himpunan dipilih
      const himpSelects = document.querySelectorAll('.cond-himpunan');
      let anyFilled = false;
      himpSelects.forEach(s => {
        s.classList.remove('is-invalid');
        if (s.value) anyFilled = true;
        else { s.classList.add('is-invalid'); hasError = true; }
      });

      if (hasError) {
        e.preventDefault();
        window.scrollTo({ top: 0, behavior: 'smooth' });
      }
    });

    document.querySelectorAll('.form-control').forEach(el => {
      el.addEventListener('focus', () => el.classList.remove('is-invalid'));
    });

    // ── Init ─────────────────────────────────────────────────────
    (function init() {
      // Init operator badge warna untuk row yang sudah ada (mode edit)
      document.querySelectorAll('.cond-badge-select').forEach(sel => updateOperatorBadge(sel));
      updateOutputPreview();
      updateRulePreview();
    })();
  </script>
</body>

</html>