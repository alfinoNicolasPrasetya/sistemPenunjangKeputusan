<?php
require 'config/DBConfig.php';
$pageTitle = 'Himpunan Fuzzy';
$successMsg = '';
$errorMsg = '';
$fuzzyData = [];

if ($conn && isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
  $deleteId = (int) $_GET['id'];
  try {
    $stmt = $conn->prepare("DELETE FROM himpunan_fuzzy WHERE id_himpunan = ?");
    $stmt->execute([$deleteId]);
    $successMsg = 'Penilaian berhasil dihapus.';
  } catch (\PDOException $e) {
    $errorMsg = 'Gagal menghapus penilaian: ' . $e->getMessage();
  }
}

/* ── Helpers ────────────────────────────────────────────── */
function getKriteriaList(PDO $conn): array
{
  try {
    return $conn->query("SELECT * FROM kriteria ORDER BY urutan ASC")->fetchAll(PDO::FETCH_ASSOC);
  } catch (\PDOException $e) {
    return [];
  }
}

function getHimpunanList(PDO $conn): array
{
  try {
    return $conn->query("
      SELECT hf.id_himpunan AS id,
             k.nama_kriteria AS kriteria,
             k.nilai_min, k.nilai_max, k.satuan,
             hf.nama_himpunan AS nama,
             hf.tipe_fungsi   AS tipe,
             hf.nilai_a AS a, hf.nilai_b AS b,
             hf.nilai_c AS c, hf.nilai_d AS d
      FROM himpunan_fuzzy hf
      INNER JOIN kriteria k ON hf.id_kriteria = k.id_kriteria
      ORDER BY k.urutan ASC, hf.id_himpunan ASC
    ")->fetchAll(PDO::FETCH_ASSOC);
  } catch (\PDOException $e) {
    return [];
  }
}

/* ── Palette ────────────────────────────────────────────── */
$palette = ['#ef4444', '#f59e0b', '#10b981', '#3b82f6', '#8b5cf6', '#ec4899', '#14b8a6', '#f97316'];
$tipeClr = [
  'trimf' => ['bg' => '#eff6ff', 'text' => '#1d4ed8', 'border' => '#bfdbfe'],
  'trapmf' => ['bg' => '#f0fdf4', 'text' => '#15803d', 'border' => '#bbf7d0'],
];

/* ── Ambil data ─────────────────────────────────────────── */
$jHimpunan = 0;
$jKriteria = 0;
$jSegitiga = 0;
$jTrapesium = 0;
$kriteriaList = [];
$himpunanList = [];
$himpunanColors = [];

if ($conn) {
  try {
    $jHimpunan = (int) $conn->query("SELECT COUNT(*) FROM himpunan_fuzzy")->fetchColumn();
    $jKriteria = (int) $conn->query("SELECT COUNT(*) FROM kriteria")->fetchColumn();
    $jSegitiga = (int) $conn->query("SELECT COUNT(*) FROM himpunan_fuzzy WHERE tipe_fungsi='trimf'")->fetchColumn();
    $jTrapesium = (int) $conn->query("SELECT COUNT(*) FROM himpunan_fuzzy WHERE tipe_fungsi='trapmf'")->fetchColumn();
  } catch (\PDOException $e) {
  }

  $kriteriaList = getKriteriaList($conn);
  $himpunanList = getHimpunanList($conn);

  $idxMap = [];
  foreach ($himpunanList as $h) {
    $k = $h['kriteria'];
    if (!isset($idxMap[$k]))
      $idxMap[$k] = 0;
    $color = $palette[$idxMap[$k]++ % count($palette)];
    $himpunanColors[$h['id']] = $color;

    $params = [(float) $h['a'], (float) $h['b'], (float) $h['c']];
    if ($h['tipe'] === 'trapmf' && $h['d'] !== null)
      $params[] = (float) $h['d'];

    $fuzzyData[$k][] = [
      'nama' => $h['nama'],
      'tipe' => $h['tipe'],
      'params' => $params,
      'color' => $color,
      /* domain dari DB kriteria — DIGUNAKAN untuk xMin/xMax canvas */
      'xMin' => (float) $h['nilai_min'],
      'xMax' => (float) $h['nilai_max'],
      'satuan' => $h['satuan'] ?? '',
    ];
  }
}

/* ── Flash ──────────────────────────────────────────────── */
if (isset($_GET['success'])) {
  $map = ['added' => 'Himpunan berhasil ditambahkan.', 'edited' => 'Himpunan berhasil diperbarui.', 'deleted' => 'Himpunan berhasil dihapus.'];
  $successMsg = $map[$_GET['success']] ?? '';
}
if (isset($_GET['error']))
  $errorMsg = htmlspecialchars($_GET['error']);
?>

<?php if ($successMsg): ?>
  <div id="alert-ok" class="alert alert-success" style="margin-bottom:16px;transition:opacity .5s,transform .5s;">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round"
        d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
    </svg><?= htmlspecialchars($successMsg) ?>
  </div>
<?php endif; ?>
<?php if ($errorMsg): ?>
  <div id="alert-err" class="alert alert-danger" style="margin-bottom:16px;transition:opacity .5s,transform .5s;">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round"
        d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
    </svg><?= htmlspecialchars($errorMsg) ?>
  </div>
<?php endif; ?>

<!-- ── Page Header ── -->
<div class="page-header">
  <div>
    <h1 class="page-title">Himpunan Fuzzy</h1>
    <p class="page-subtitle">Konfigurasi fungsi keanggotaan untuk setiap variabel linguistik</p>
  </div>
  <a href="app.php?page=himpunan/add&action=add" class="btn btn-primary">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
    </svg>
    Tambah Himpunan
  </a>
</div>

<!-- ── Stats ── -->
<div class="stats-grid" style="grid-template-columns:repeat(auto-fill,minmax(160px,1fr));margin-bottom:24px;">
  <div class="stat-card" style="--icon-color:var(--primary)">
    <div class="stat-card-icon">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round"
          d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
      </svg>
    </div>
    <div><span class="stat-card-value"><?= $jHimpunan ?></span><span class="stat-card-label">Total Himpunan</span></div>
  </div>
  <div class="stat-card" style="--icon-color:var(--info)">
    <div class="stat-card-icon">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round"
          d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6Z" />
      </svg>
    </div>
    <div><span class="stat-card-value"><?= $jKriteria ?></span><span class="stat-card-label">Kriteria Aktif</span></div>
  </div>
  <div class="stat-card" style="--icon-color:var(--success)">
    <div class="stat-card-icon">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round"
          d="M5.25 8.25h15m-16.5 7.5h15m-1.8-13.5-3.9 19.5m-2.1-19.5-3.9 19.5" />
      </svg>
    </div>
    <div><span class="stat-card-value"><?= $jTrapesium ?></span><span class="stat-card-label">Fungsi Trapesium</span>
    </div>
  </div>
  <div class="stat-card" style="--icon-color:var(--warning)">
    <div class="stat-card-icon">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round"
          d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3" />
      </svg>
    </div>
    <div><span class="stat-card-value"><?= $jSegitiga ?></span><span class="stat-card-label">Fungsi Segitiga</span>
    </div>
  </div>
</div>

<!-- ── Visualisasi ── -->
<div class="card" style="margin-bottom:24px;">
  <div class="card-header">
    <div style="display:flex;align-items:center;gap:10px;">
      <div
        style="width:36px;height:36px;border-radius:9px;background:var(--primary-light);color:var(--primary);display:flex;align-items:center;justify-content:center;">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
          style="width:18px;height:18px;">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941" />
        </svg>
      </div>
      <div>
        <div class="card-title">Visualisasi Fungsi Keanggotaan</div>
        <div style="font-size:12px;color:var(--text-muted);">Domain menggunakan nilai_min &amp; nilai_max dari tabel
          kriteria · Hover untuk lihat µ(x)</div>
      </div>
    </div>
    <div class="select-wrap" style="width:190px;">
      <select class="form-control" id="vizKriteria" style="padding:8px 13px;font-size:13px;">
        <?php foreach ($kriteriaList as $k): ?>
          <option value="<?= htmlspecialchars($k['nama_kriteria']) ?>"><?= htmlspecialchars($k['nama_kriteria']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>
  <div class="card-body">
    <!-- Tooltip -->
    <div id="cvTip" style="
      position:fixed;pointer-events:none;display:none;
      background:#0f0f14;color:#fff;font-size:12px;
      font-family:'IBM Plex Mono',monospace;
      padding:9px 14px;border-radius:9px;z-index:200;
      white-space:nowrap;box-shadow:0 6px 24px rgba(0,0,0,.3);line-height:1.9;
    "></div>
    <!-- Canvas -->
    <div style="position:relative;">
      <canvas id="fuzzyCanvas" width="1200" height="260"
        style="width:100%;height:260px;border-radius:10px;background:#f8fafc;cursor:crosshair;display:block;"></canvas>
    </div>
    <!-- Legend -->
    <div id="vizLegend" style="display:flex;flex-wrap:wrap;gap:10px;margin-top:14px;"></div>
    <!-- Panel µ(x) live -->
    <div id="muPanel"
      style="display:none;margin-top:14px;background:var(--surface-2);border:1.5px solid var(--border);border-radius:10px;padding:14px 20px;">
      <div
        style="font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--text-muted);margin-bottom:10px;">
        Derajat Keanggotaan µ(x) — x = <span id="muXlbl"
          style="color:var(--primary);font-size:13px;font-family:'IBM Plex Mono',monospace;"></span>
      </div>
      <div id="muRows" style="display:flex;flex-wrap:wrap;gap:16px;"></div>
    </div>
  </div>
</div>

<!-- ── Tabel ── -->
<div class="card">
  <div class="card-header">
    <div class="card-title">Daftar Himpunan Fuzzy</div>
    <div class="select-wrap" style="width:190px;">
      <select class="form-control" id="filterKriteria" onchange="filterTable()"
        style="padding:8px 13px;font-size:13px;">
        <option value="Semua">Semua Kriteria</option>
        <?php foreach ($kriteriaList as $k): ?>
          <option value="<?= htmlspecialchars($k['nama_kriteria']) ?>"><?= htmlspecialchars($k['nama_kriteria']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>
  <div class="table-wrapper" style="border:none;border-radius:0;">
    <table class="data-table" id="himpunanTable">
      <thead>
        <tr>
          <th class="td-no">#</th>
          <th>Kriteria</th>
          <th>Nama Himpunan</th>
          <th>Tipe Fungsi</th>
          <th style="text-align:center;">a</th>
          <th style="text-align:center;">b</th>
          <th style="text-align:center;">c</th>
          <th style="text-align:center;">d</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($himpunanList as $i => $h):
          $clr = $himpunanColors[$h['id']] ?? '#64748b';
          $tc = $tipeClr[$h['tipe']] ?? $tipeClr['trimf'];
          ?>
          <tr data-kriteria="<?= htmlspecialchars($h['kriteria']) ?>">
            <td class="td-no"><?= $i + 1 ?></td>
            <td>
              <span
                style="display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:6px;font-size:12px;font-weight:600;background:#f1f5f9;color:var(--text-muted);border:1px solid var(--border);">
                <?= htmlspecialchars($h['kriteria']) ?>
              </span>
            </td>
            <td>
              <span
                style="display:inline-flex;align-items:center;gap:7px;font-weight:600;font-size:13.5px;color:var(--text);">
                <span style="width:9px;height:9px;border-radius:50%;background:<?= $clr ?>;flex-shrink:0;"></span>
                <?= htmlspecialchars($h['nama']) ?>
              </span>
            </td>
            <td>
              <span
                style="display:inline-flex;padding:4px 10px;border-radius:20px;font-size:11.5px;font-weight:600;font-family:'IBM Plex Mono',monospace;background:<?= $tc['bg'] ?>;color:<?= $tc['text'] ?>;border:1px solid <?= $tc['border'] ?>;">
                <?= $h['tipe'] ?>
              </span>
            </td>
            <?php foreach (['a', 'b', 'c'] as $p): ?>
              <td style="text-align:center;font-family:'IBM Plex Mono',monospace;font-size:13px;"><?= $h[$p] ?></td>
            <?php endforeach; ?>
            <td style="text-align:center;font-family:'IBM Plex Mono',monospace;font-size:13px;">
              <?= $h['d'] !== null ? $h['d'] : '<span style="color:var(--text-light)">—</span>' ?>
            </td>
            <td>
              <div class="action-group">
                <a href="app.php?page=himpunan/edit&action=edit&id=<?= $h['id'] ?>" class="btn-edit">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                  </svg>Edit
                </a>
                <button class="btn-delete" onclick="confirmDelete(<?= $h['id'] ?>, '<?= addslashes($h['nama']) ?>')">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 <|fim_suffix|0 0-7.5 0" />
                  </svg>
                  Hapus
                </button>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($himpunanList)): ?>
          <tr>
            <td colspan="9" style="text-align:center;padding:48px;color:var(--text-muted);">
              <div style="font-size:32px;margin-bottom:8px;">📊</div>
              <div style="font-weight:600;margin-bottom:4px;">Belum ada himpunan fuzzy</div>
              <div style="font-size:13px;">Klik "Tambah Himpunan" untuk memulai</div>
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal Delete -->
<div class="modal-overlay" id="deleteModal">
  <div class="modal-box">
    <div class="modal-icon">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round"
          d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
      </svg>
    </div>
    <h3 class="modal-title">Hapus Himpunan Fuzzy?</h3>
    <p class="modal-desc" id="deleteModalDesc">Data himpunan akan dihapus. Anda perlu menginput ulang jika ingin
      memproses perhitungan fuzzy.</p>
    <div class="modal-actions">
      <button class="btn-outline" onclick="closeDeleteModal()">Batal</button>
      <a href="#" class="btn-danger" id="deleteConfirmBtn">Ya, Hapus</a>
    </div>
  </div>
</div>

<script>
  var FDATA = <?= json_encode($fuzzyData, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK) ?>;

  function getMu(tipe, params, x) {
    if (tipe === 'trimf') {
      var a = params[0], b = params[1], c = params[2];

      if (a === b) {

        if (x >= c) return 0;
        if (x <= b) return 1;                          // plateau kiri
        return (c - x) / (c - b);                     // turun
      }

      if (b === c) {
        if (x <= a) return 0;
        if (x >= b) return 1;                          // plateau kanan
        return (x - a) / (b - a);                     // naik
      }

      if (x <= a || x >= c) return 0;
      if (x <= b) return (x - a) / (b - a);
      return (c - x) / (c - b);

    } else {
      var a = params[0], b = params[1], c = params[2], d = params[3];

      if (a === b) {
        if (x >= d) return 0;
        if (x >= c) return (d - x) / (d - c);         // turun kanan
        return 1;                                       // plateau (termasuk x<=b=a)
      }

      if (c === d) {
        if (x <= a) return 0;
        if (x >= c) return 1;                          // plateau (termasuk x>=d=c)
        if (x >= b) return 1;                          // plateau tengah
        return (x - a) / (b - a);                     // naik kiri
      }

      /* Trapmf normal — contoh: Normal(50,55,70,75) */
      if (x <= a || x >= d) return 0;
      if (x >= b && x <= c) return 1;
      if (x < b) return (x - a) / (b - a);
      return (d - x) / (d - c);
    }
  }

  /* ════════════════════════════════════════════════════════════
     CANVAS
     ════════════════════════════════════════════════════════════ */
  var cv = document.getElementById('fuzzyCanvas');
  var ctx = cv.getContext('2d');
  var tip = document.getElementById('cvTip');
  var SETS = [];                        // himpunan aktif
  var PAD = { l: 52, r: 24, t: 24, b: 38 };

  /* Ambil domain dari data (semua set dalam satu kriteria punya xMin/xMax sama) */
  function getDomain(sets) {
    if (!sets.length) return { mn: 0, mx: 100, sat: '' };
    return { mn: sets[0].xMin, mx: sets[0].xMax, sat: sets[0].satuan };
  }

  /* ── Draw utama ─────────────────────────────────────────── */
  function drawCanvas(sets, hx) {
    var W = cv.width, H = cv.height, pw = W - PAD.l - PAD.r, ph = H - PAD.t - PAD.b;
    var dom = getDomain(sets), xMn = dom.mn, xMx = dom.mx, sat = dom.sat;

    ctx.clearRect(0, 0, W, H);

    /* Background */
    ctx.fillStyle = '#f8fafc'; ctx.fillRect(0, 0, W, H);

    /* Grid horizontal */
    ctx.setLineDash([4, 5]); ctx.lineWidth = 1;
    for (var gi = 0; gi <= 4; gi++) {
      var gy = PAD.t + (ph / 4) * gi;
      ctx.strokeStyle = gi === 4 ? '#cbd5e1' : '#e2e8f0';
      ctx.beginPath(); ctx.moveTo(PAD.l, gy); ctx.lineTo(W - PAD.r, gy); ctx.stroke();
    }
    /* Grid vertikal */
    ctx.strokeStyle = '#f0f4f8';
    for (var vi = 1; vi < 6; vi++) {
      var vx = PAD.l + (pw / 6) * vi;
      ctx.beginPath(); ctx.moveTo(vx, PAD.t); ctx.lineTo(vx, PAD.t + ph); ctx.stroke();
    }
    ctx.setLineDash([]);

    /* Label Y */
    ctx.font = 'bold 10px "IBM Plex Mono",monospace'; ctx.fillStyle = '#94a3b8'; ctx.textAlign = 'right';
    for (var yi = 0; yi <= 4; yi++) {
      ctx.fillText((1 - yi / 4).toFixed(2), PAD.l - 8, PAD.t + (ph / 4) * yi + 4);
    }
    /* Label X — menggunakan domain dari DB kriteria */
    ctx.textAlign = 'center'; ctx.font = '10px "IBM Plex Mono",monospace'; ctx.fillStyle = '#94a3b8';
    for (var xi = 0; xi <= 6; xi++) {
      var xv = xMn + (xMx - xMn) * (xi / 6);
      var cx = PAD.l + (pw / 6) * xi;
      ctx.fillText(Math.round(xv * 10) / 10, cx, H - 12);
    }
    /* Satuan axis X */
    ctx.font = 'bold 10px "IBM Plex Mono",monospace'; ctx.fillStyle = '#94a3b8';
    if (sat) ctx.fillText('(' + sat + ')', PAD.l + pw, H - 1);
    /* Label axis Y */
    ctx.save(); ctx.translate(12, PAD.t + ph / 2); ctx.rotate(-Math.PI / 2);
    ctx.textAlign = 'center'; ctx.fillText('µ(x)', 0, 0); ctx.restore();

    var STEPS = 800;

    /* Render kurva tiap himpunan */
    sets.forEach(function (s) {
      /* Area fill */
      ctx.beginPath(); ctx.fillStyle = s.color + '24'; var ff = true;
      for (var i = 0; i <= STEPS; i++) {
        var x = xMn + (xMx - xMn) * (i / STEPS);
        var m = getMu(s.tipe, s.params, x);
        var cx = PAD.l + ((x - xMn) / (xMx - xMn)) * pw, cy = PAD.t + ph - m * ph;
        if (ff) { ctx.moveTo(cx, cy); ff = false; } else ctx.lineTo(cx, cy);
      }
      ctx.lineTo(PAD.l + pw, PAD.t + ph); ctx.lineTo(PAD.l, PAD.t + ph); ctx.closePath(); ctx.fill();

      /* Garis kurva */
      ctx.beginPath(); ctx.strokeStyle = s.color; ctx.lineWidth = 2.5; ctx.lineJoin = 'round'; ff = true;
      for (var i = 0; i <= STEPS; i++) {
        var x = xMn + (xMx - xMn) * (i / STEPS);
        var m = getMu(s.tipe, s.params, x);
        var cx = PAD.l + ((x - xMn) / (xMx - xMn)) * pw, cy = PAD.t + ph - m * ph;
        if (ff) { ctx.moveTo(cx, cy); ff = false; } else ctx.lineTo(cx, cy);
      }
      ctx.stroke();

      /* Titik puncak (µ=1) — cari titik pertama */
      for (var i = 0; i <= STEPS; i++) {
        var x = xMn + (xMx - xMn) * (i / STEPS);
        if (getMu(s.tipe, s.params, x) >= 0.9999) {
          var pcx = PAD.l + ((x - xMn) / (xMx - xMn)) * pw;
          ctx.beginPath(); ctx.arc(pcx, PAD.t + 4.5, 4.5, 0, Math.PI * 2);
          ctx.fillStyle = s.color; ctx.fill();
          ctx.strokeStyle = '#fff'; ctx.lineWidth = 1.5; ctx.stroke();
          break;
        }
      }
    });

    /* Crosshair + dots interaksi hover */
    if (hx !== null && hx !== undefined) {
      var hcx = PAD.l + ((hx - xMn) / (xMx - xMn)) * pw;
      if (hcx >= PAD.l && hcx <= PAD.l + pw) {
        ctx.save(); ctx.setLineDash([5, 3]); ctx.strokeStyle = '#64748b'; ctx.lineWidth = 1.2;
        ctx.beginPath(); ctx.moveTo(hcx, PAD.t); ctx.lineTo(hcx, PAD.t + ph); ctx.stroke();
        ctx.setLineDash([]);
        sets.forEach(function (s) {
          var m = getMu(s.tipe, s.params, hx);
          if (m > 0.001) {
            var cy = PAD.t + ph - m * ph;
            ctx.beginPath(); ctx.arc(hcx, cy, 5.5, 0, Math.PI * 2);
            ctx.fillStyle = s.color; ctx.fill();
            ctx.strokeStyle = '#fff'; ctx.lineWidth = 2; ctx.stroke();
          }
        });
        ctx.restore();
      }
    }

    /* Border plot area */
    ctx.strokeStyle = '#e2e8f0'; ctx.lineWidth = 1; ctx.setLineDash([]);
    ctx.strokeRect(PAD.l, PAD.t, pw, ph);
  }

  /* ── Legend ─────────────────────────────────────────────── */
  function buildLegend(sets) {
    var el = document.getElementById('vizLegend');
    el.innerHTML = sets.map(function (s) {
      var fp = s.tipe === 'trimf'
        ? 'trimf(' + s.params[0] + ', ' + s.params[1] + ', ' + s.params[2] + ')'
        : 'trapmf(' + s.params.join(', ') + ')';
      return '<div style="display:inline-flex;align-items:center;gap:8px;padding:5px 13px;'
        + 'border-radius:8px;background:#f1f5f9;border:1px solid #e2e8f0;">'
        + '<span style="width:22px;height:3px;border-radius:2px;background:' + s.color + ';display:inline-block;"></span>'
        + '<span style="font-size:12.5px;font-weight:700;color:#374151;">' + s.nama + '</span>'
        + '<span style="font-family:\'IBM Plex Mono\',monospace;font-size:11px;color:#94a3b8;">' + fp + '</span>'
        + '</div>';
    }).join('');
  }

  /* ── Render all ─────────────────────────────────────────── */
  function drawAll() {
    var k = document.getElementById('vizKriteria').value;
    SETS = FDATA[k] || [];
    drawCanvas(SETS, null);
    buildLegend(SETS);
    document.getElementById('muPanel').style.display = 'none';
  }

  /* ── Mouse hover ────────────────────────────────────────── */
  cv.addEventListener('mousemove', function (e) {
    var rect = cv.getBoundingClientRect();
    var scX = cv.width / rect.width, scY = cv.height / rect.height;
    var mx = (e.clientX - rect.left) * scX, my = (e.clientY - rect.top) * scY;
    var pw = cv.width - PAD.l - PAD.r, ph = cv.height - PAD.t - PAD.b;
    var dom = getDomain(SETS);

    if (mx < PAD.l || mx > PAD.l + pw || my < PAD.t || my > PAD.t + ph) {
      tip.style.display = 'none';
      document.getElementById('muPanel').style.display = 'none';
      drawCanvas(SETS, null); return;
    }

    var xv = dom.mn + ((mx - PAD.l) / pw) * (dom.mx - dom.mn);
    xv = Math.round(xv * 100) / 100;

    /* Tooltip fixed */
    var th = '<div style="color:rgba(255,255,255,.4);font-size:10px;margin-bottom:4px;">x = ' + xv + (dom.sat ? ' ' + dom.sat : '') + '</div>';
    SETS.forEach(function (s) {
      var m = getMu(s.tipe, s.params, xv);
      th += '<div><span style="color:' + s.color + ';">■</span> ' + s.nama + ' : <b>' + m.toFixed(4) + '</b></div>';
    });
    tip.innerHTML = th; tip.style.display = 'block';
    var tx = e.clientX + 16; if (tx + 210 > window.innerWidth) tx = e.clientX - 220;
    tip.style.left = tx + 'px'; tip.style.top = (e.clientY - 8) + 'px';

    /* Panel bawah */
    document.getElementById('muXlbl').textContent = xv + (dom.sat ? ' ' + dom.sat : '');
    var rows = '';
    SETS.forEach(function (s) {
      var m = getMu(s.tipe, s.params, xv), bw = (m * 100).toFixed(1);
      rows += '<div style="flex:1;min-width:130px;">'
        + '<div style="display:flex;justify-content:space-between;margin-bottom:4px;">'
        + '<span style="font-size:12px;font-weight:700;color:' + s.color + ';">' + s.nama + '</span>'
        + '<span style="font-family:\'IBM Plex Mono\',monospace;font-size:13px;font-weight:700;color:' + (m > 0.001 ? s.color : '#cbd5e1') + ';">' + m.toFixed(4) + '</span>'
        + '</div>'
        + '<div style="height:5px;background:#e2e8f0;border-radius:3px;overflow:hidden;">'
        + '<div style="height:100%;width:' + bw + '%;background:' + s.color + ';border-radius:3px;transition:width .1s;"></div>'
        + '</div></div>';
    });
    document.getElementById('muRows').innerHTML = rows;
    document.getElementById('muPanel').style.display = 'block';
    drawCanvas(SETS, xv);
  });

  cv.addEventListener('mouseleave', function () {
    tip.style.display = 'none';
    document.getElementById('muPanel').style.display = 'none';
    drawCanvas(SETS, null);
  });

  /* ── Filter tabel ───────────────────────────────────────── */
  function filterTable() {
    var val = document.getElementById('filterKriteria').value;
    document.querySelectorAll('#himpunanTable tbody tr[data-kriteria]').forEach(function (tr) {
      tr.style.display = (val === 'Semua' || tr.dataset.kriteria === val) ? '' : 'none';
    });
  }

  document.getElementById('vizKriteria').addEventListener('change', drawAll);

  /* ── Init ───────────────────────────────────────────────── */
  drawAll();

  /* ── Auto-hide alerts ───────────────────────────────────── */
  ['alert-ok', 'alert-err'].forEach(function (id) {
    var el = document.getElementById(id); if (!el) return;
    setTimeout(function () {
      el.style.opacity = '0'; el.style.transform = 'translateY(-8px)';
      setTimeout(function () { if (el.parentNode) el.parentNode.removeChild(el); }, 500);
    }, 3500);
  });

  // ── Delete modal ──
  function confirmDelete(id, name) {
    document.getElementById('deleteModalDesc').textContent =
      `Apakah Anda yakin ingin menghapus himpunan fuzzy "${name}"? Tindakan ini tidak dapat dibatalkan.`;
    document.getElementById('deleteConfirmBtn').href =
      `app.php?page=himpunan&action=delete&id=${id}`;
    document.getElementById('deleteModal').classList.add('is-open');
  }
  function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('is-open');
  }
  document.getElementById('deleteModal').addEventListener('click', function (e) {
    if (e.target === this) closeDeleteModal();
  });
</script>