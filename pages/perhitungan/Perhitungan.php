<?php
require_once 'config/DBConfig.php';

$pageTitle = 'Perhitungan';
$successMsg = '';
$errorMsg = '';
$userId = $_SESSION['user']['id'] ?? 0;

if (isset($_GET['success'])) {
  $map = [
    'hitung_ok' => 'Perhitungan berhasil disimpan.',
    'semua_ok' => 'Semua siswa berhasil dihitung.',
    'reset_ok' => 'Hasil perhitungan berhasil direset.',
  ];
  $successMsg = $map[$_GET['success']] ?? '';
}
if (isset($_GET['error'])) {
  $errorMsg = htmlspecialchars($_GET['error']);
}

// ── Engine ────────────────────────────────────────────────────
function fuzzyMembership(string $tipe, float $a, float $b, float $c, ?float $d, float $x): float
{
  if ($tipe === 'trimf') {
    if ($x <= $a || $x >= $c)
      return 0.0;
    if ($x <= $b)
      return ($b - $a) == 0 ? 1.0 : ($x - $a) / ($b - $a);
    return ($c - $b) == 0 ? 1.0 : ($c - $x) / ($c - $b);
  } else {
    $d = $d ?? $c;
    if ($x <= $a || $x >= $d)
      return 0.0;
    if ($x >= $b && $x <= $c)
      return 1.0;
    if ($x < $b)
      return ($b - $a) == 0 ? 1.0 : ($x - $a) / ($b - $a);
    return ($d - $c) == 0 ? 1.0 : ($d - $x) / ($d - $c);
  }
}

function kategorisasi(float $nilai): string
{
  if ($nilai >= 75)
    return 'Sangat Layak';
  if ($nilai >= 55)
    return 'Layak';
  if ($nilai >= 35)
    return 'Cukup Layak';
  return 'Tidak Layak';
}

function hitungFuzzySugeno(int $idSiswa, PDO $conn): array
{
  $log = ['siswa_id' => $idSiswa, 'nilai_input' => [], 'fuzzifikasi' => [], 'inferensi' => [], 'defuzzifikasi' => 0.0, 'kategori' => '', 'error' => ''];
  $stmt = $conn->prepare("SELECT p.id_kriteria,p.nilai_input,k.nama_kriteria,k.satuan FROM penilaian p JOIN kriteria k ON k.id_kriteria=p.id_kriteria WHERE p.id_siswa=?");
  $stmt->execute([$idSiswa]);
  $nilaiRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
  if (empty($nilaiRows)) {
    $log['error'] = 'Tidak ada nilai penilaian untuk siswa ini.';
    return $log;
  }
  $nilaiMap = [];
  foreach ($nilaiRows as $r) {
    $nilaiMap[$r['id_kriteria']] = (float) $r['nilai_input'];
    $log['nilai_input'][$r['id_kriteria']] = ['nama' => $r['nama_kriteria'], 'nilai' => (float) $r['nilai_input'], 'satuan' => $r['satuan']];
  }
  $stmtH = $conn->query("SELECT h.id_himpunan,h.id_kriteria,h.nama_himpunan,h.tipe_fungsi,h.nilai_a,h.nilai_b,h.nilai_c,h.nilai_d,k.nama_kriteria FROM himpunan_fuzzy h JOIN kriteria k ON k.id_kriteria=h.id_kriteria ORDER BY h.id_kriteria ASC,h.id_himpunan ASC");
  $muMap = [];
  foreach ($stmtH->fetchAll(PDO::FETCH_ASSOC) as $h) {
    $xVal = $nilaiMap[$h['id_kriteria']] ?? null;
    $mu = $xVal === null ? 0.0 : fuzzyMembership($h['tipe_fungsi'], (float) $h['nilai_a'], (float) $h['nilai_b'], (float) $h['nilai_c'], isset($h['nilai_d']) ? (float) $h['nilai_d'] : null, $xVal);
    $muMap[$h['id_himpunan']] = $mu;
    $log['fuzzifikasi'][] = ['id_himpunan' => $h['id_himpunan'], 'nama_kriteria' => $h['nama_kriteria'], 'nama_himpunan' => $h['nama_himpunan'], 'x' => $xVal ?? '—', 'tipe' => $h['tipe_fungsi'], 'params' => [$h['nilai_a'], $h['nilai_b'], $h['nilai_c'], $h['nilai_d']], 'mu' => round($mu, 4)];
  }
  $stmtA = $conn->query("SELECT a.id_aturan,a.nama_aturan,a.output_nilai,a.keterangan,d.id_himpunan,d.operator FROM aturan_fuzzy a JOIN detail_aturan_fuzzy d ON d.id_aturan=a.id_aturan ORDER BY a.id_aturan ASC,d.id_detail ASC");
  $aturanMap = [];
  foreach ($stmtA->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $aid = $row['id_aturan'];
    if (!isset($aturanMap[$aid]))
      $aturanMap[$aid] = ['id_aturan' => $aid, 'nama_aturan' => $row['nama_aturan'], 'output_nilai' => (float) $row['output_nilai'], 'keterangan' => $row['keterangan'], 'kondisi' => []];
    $aturanMap[$aid]['kondisi'][] = ['id_himpunan' => $row['id_himpunan'], 'operator' => $row['operator']];
  }
  if (empty($aturanMap)) {
    $log['error'] = 'Tidak ada aturan fuzzy yang terdefinisi.';
    return $log;
  }
  $sumAlphaZ = $sumAlpha = 0.0;
  foreach ($aturanMap as $aturan) {
    $alpha = null;
    foreach ($aturan['kondisi'] as $kond) {
      $mu = $muMap[$kond['id_himpunan']] ?? 0.0;
      if ($alpha === null)
        $alpha = $mu;
      elseif ($kond['operator'] === 'OR')
        $alpha = max($alpha, $mu);
      else
        $alpha = min($alpha, $mu);
    }
    $alpha = $alpha ?? 0.0;
    $z = $aturan['output_nilai'];
    $log['inferensi'][] = ['nama_aturan' => $aturan['nama_aturan'], 'keterangan' => $aturan['keterangan'], 'alpha' => round($alpha, 4), 'z' => $z, 'alpha_z' => round($alpha * $z, 4)];
    $sumAlphaZ += $alpha * $z;
    $sumAlpha += $alpha;
  }
  if ($sumAlpha == 0) {
    $log['error'] = 'Semua aturan memiliki alpha = 0.';
    $log['defuzzifikasi'] = 0.0;
    $log['kategori'] = 'Tidak Dapat Dihitung';
    return $log;
  }
  $defuzz = $sumAlphaZ / $sumAlpha;
  $log['defuzzifikasi'] = round($defuzz, 4);
  $log['sum_alpha_z'] = round($sumAlphaZ, 4);
  $log['sum_alpha'] = round($sumAlpha, 4);
  $log['kategori'] = kategorisasi($defuzz);
  return $log;
}

// ── Aksi ─────────────────────────────────────────────────────
$hasilHitung = null;
$targetSiswa = null;
// 'view' = tampilkan detail siswa yang sudah dihitung (tanpa hitung ulang)
// 'hitung' = hitung + simpan + tampilkan detail
$currentView = $_GET['view'] ?? null; // 'list' | 'detail'

if (isset($_GET['do']) && $conn) {
  $do = $_GET['do'];

  if ($do === 'hitung' && isset($_GET['id'])) {
    $idSiswa = (int) $_GET['id'];
    $hasilHitung = hitungFuzzySugeno($idSiswa, $conn);
    if (empty($hasilHitung['error'])) {
      try {
        $conn->prepare("DELETE FROM hasil_perhitungan WHERE id_siswa=?")->execute([$idSiswa]);
        $conn->prepare("INSERT INTO hasil_perhitungan (id_siswa,nilai_defuzzifikasi,kategori,id_user) VALUES (?,?,?,?)")
          ->execute([$idSiswa, $hasilHitung['defuzzifikasi'], $hasilHitung['kategori'], $userId]);
        $successMsg = 'Perhitungan berhasil disimpan.';
      } catch (\PDOException $e) {
        $errorMsg = 'Gagal menyimpan: ' . $e->getMessage();
      }
    }
    $stmtS = $conn->prepare("SELECT * FROM siswa WHERE id_siswa=?");
    $stmtS->execute([$idSiswa]);
    $targetSiswa = $stmtS->fetch(PDO::FETCH_ASSOC);
    $currentView = 'detail';

  } elseif ($do === 'detail' && isset($_GET['id'])) {
    // Tampilkan detail tanpa hitung ulang — re-run engine dari data yang ada
    $idSiswa = (int) $_GET['id'];
    $hasilHitung = hitungFuzzySugeno($idSiswa, $conn);
    $stmtS = $conn->prepare("SELECT * FROM siswa WHERE id_siswa=?");
    $stmtS->execute([$idSiswa]);
    $targetSiswa = $stmtS->fetch(PDO::FETCH_ASSOC);
    $currentView = 'detail';

  } elseif ($do === 'hitung_semua') {
    $stmtAll = $conn->query("SELECT DISTINCT id_siswa FROM penilaian ORDER BY id_siswa ASC");
    $allIds = $stmtAll->fetchAll(PDO::FETCH_COLUMN);
    $berhasil = 0;
    foreach ($allIds as $id) {
      $res = hitungFuzzySugeno((int) $id, $conn);
      if (empty($res['error'])) {
        try {
          $conn->prepare("DELETE FROM hasil_perhitungan WHERE id_siswa=?")->execute([$id]);
          $conn->prepare("INSERT INTO hasil_perhitungan (id_siswa,nilai_defuzzifikasi,kategori,id_user) VALUES (?,?,?,?)")
            ->execute([$id, $res['defuzzifikasi'], $res['kategori'], $userId]);
          $berhasil++;
        } catch (\PDOException $e) {
        }
      }
    }
    echo "<script>window.location.href='app.php?page=perhitungan&success=semua_ok';</script>";
    exit;

  } elseif ($do === 'reset') {
    $conn->exec("DELETE FROM hasil_perhitungan");
    echo "<script>window.location.href='app.php?page=perhitungan&success=reset_ok';</script>";
    exit;
  }
}

// ── Data ─────────────────────────────────────────────────────
$siswaList = [];
if ($conn) {
  try {
    $stmt = $conn->query("
      SELECT s.id_siswa,s.name,s.asal_sekolah,s.jenis_kelamin,
             h.nilai_defuzzifikasi,h.kategori,h.tanggal_hitung,
             COUNT(DISTINCT p.id_kriteria) AS jumlah_nilai
      FROM siswa s
      LEFT JOIN penilaian p         ON p.id_siswa=s.id_siswa
      LEFT JOIN hasil_perhitungan h ON h.id_siswa=s.id_siswa
      GROUP BY s.id_siswa
      ORDER BY h.nilai_defuzzifikasi DESC, s.name ASC
    ");
    $siswaList = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (\PDOException $e) {
    $errorMsg = 'Gagal mengambil data: ' . $e->getMessage();
  }
}

$totalSiswa = count($siswaList);
$sudahHitung = count(array_filter($siswaList, fn($s) => $s['nilai_defuzzifikasi'] !== null));
$belumHitung = $totalSiswa - $sudahHitung;
$punyaNilai = count(array_filter($siswaList, fn($s) => (int) $s['jumlah_nilai'] > 0));
$totalAturan = 0;
$isAlreadyCalculated = $sudahHitung > 0;
$semuaSudah = ($sudahHitung === $punyaNilai && $punyaNilai > 0);
$sebagianSudah = ($sudahHitung > 0 && $sudahHitung < $punyaNilai);

if ($conn) {
  try {
    $totalAturan = (int) $conn->query("SELECT COUNT(*) FROM aturan_fuzzy")->fetchColumn();
  } catch (\PDOException $e) {
  }
}

$katColors = [
  'Sangat Layak' => ['bg' => '#eff6ff', 'text' => '#1e40af', 'border' => '#bfdbfe', 'bar' => '#3b82f6', 'dot' => '#3b82f6'],
  'Layak' => ['bg' => '#f0fdf4', 'text' => '#166534', 'border' => '#bbf7d0', 'bar' => '#10b981', 'dot' => '#10b981'],
  'Cukup Layak' => ['bg' => '#fffbeb', 'text' => '#92400e', 'border' => '#fde68a', 'bar' => '#f59e0b', 'dot' => '#f59e0b'],
  'Tidak Layak' => ['bg' => '#fef2f2', 'text' => '#991b1b', 'border' => '#fecaca', 'bar' => '#ef4444', 'dot' => '#ef4444'],
  'Tidak Dapat Dihitung' => ['bg' => '#f8fafc', 'text' => '#64748b', 'border' => '#e2e8f0', 'bar' => '#94a3b8', 'dot' => '#94a3b8'],
];
$ranges = ['Tidak Layak' => '< 35', 'Cukup Layak' => '35–54', 'Layak' => '55–74', 'Sangat Layak' => '≥ 75'];

if (!$isAlreadyCalculated) {
  $btnHitungLabel = 'Hitung Semua';
  $btnHitungConfirm = 'Mulai perhitungan untuk semua siswa yang sudah dinilai?';
  $btnHitungIcon = '<path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z"/>';
} elseif ($sebagianSudah) {
  $btnHitungLabel = 'Hitung Sisanya';
  $btnHitungConfirm = 'Hitung siswa yang belum dihitung? Siswa yang sudah akan dihitung ulang.';
  $btnHitungIcon = '<path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z"/>';
} else {
  $btnHitungLabel = 'Hitung Ulang Semua';
  $btnHitungConfirm = 'Semua siswa sudah dihitung. Hitung ulang seluruh data?';
  $btnHitungIcon = '<path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/>';
}
?>

<?php if ($successMsg): ?>
  <div id="alert-success" class="alert alert-success" style="margin-bottom:16px;transition:opacity .5s,transform .5s;">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round"
        d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
    </svg>
    <?= htmlspecialchars($successMsg) ?>
  </div>
<?php endif; ?>
<?php if ($errorMsg): ?>
  <div id="alert-error" class="alert alert-danger" style="margin-bottom:16px;transition:opacity .5s,transform .5s;">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round"
        d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
    </svg>
    <?= htmlspecialchars($errorMsg) ?>
  </div>
<?php endif; ?>

<!-- ══════════════════════════════════════════════════════════
     VIEW: DETAIL PERHITUNGAN (tampil saat ?do=hitung|detail)
     ══════════════════════════════════════════════════════════ -->
<?php if ($currentView === 'detail' && $hasilHitung && $targetSiswa):
  $logH = $hasilHitung;
  $katC = $katColors[$logH['kategori']] ?? $katColors['Tidak Dapat Dihitung'];
  $sumAlphaZ = $logH['sum_alpha_z'] ?? 0;
  $sumAlpha = $logH['sum_alpha'] ?? 0;
  ?>

  <!-- Page header: mode detail -->
  <div class="page-header" style="margin-bottom:20px;">
    <div style="display:flex;align-items:center;gap:12px;">
      <a href="app.php?page=perhitungan"
        style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:8px;background:var(--surface-2);border:1.5px solid var(--border);color:var(--text-muted);text-decoration:none;flex-shrink:0;transition:background .15s;"
        onmouseover="this.style.background='var(--border)'" onmouseout="this.style.background='var(--surface-2)'">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"
          style="width:16px;height:16px;">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
        </svg>
      </a>
      <div>
        <h1 class="page-title" style="margin:0;">Detail Perhitungan</h1>
        <p class="page-subtitle" style="margin:0;">Langkah inferensi Fuzzy Sugeno untuk
          <?= htmlspecialchars($targetSiswa['name']) ?>
        </p>
      </div>
    </div>
    <div style="display:flex;gap:8px;">
      <a href="app.php?page=perhitungan&do=hitung&id=<?= $targetSiswa['id_siswa'] ?>" class="btn-secondary">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
        </svg>
        Hitung Ulang
      </a>
    </div>
  </div>

  <!-- Progress steps indicator -->
  <div
    style="display:flex;align-items:center;gap:0;margin-bottom:28px;padding:16px 20px;background:var(--surface-2);border:1.5px solid var(--border);border-radius:12px;">
    <?php
    $steps = [
      ['num' => '1', 'label' => 'Nilai Input', 'sub' => 'Data penilaian siswa'],
      ['num' => '2', 'label' => 'Fuzzifikasi', 'sub' => 'Derajat keanggotaan µ'],
      ['num' => '3', 'label' => 'Inferensi', 'sub' => 'Firing strength α'],
      ['num' => '4', 'label' => 'Defuzzifikasi', 'sub' => 'Weighted average'],
    ];
    foreach ($steps as $si => $st): ?>
      <div style="flex:1;display:flex;align-items:center;gap:0;">
        <div style="display:flex;align-items:center;gap:10px;flex:1;">
          <div style="
        width:34px;height:34px;border-radius:50%;flex-shrink:0;
        background:var(--primary);color:#fff;
        display:flex;align-items:center;justify-content:center;
        font-size:13px;font-weight:800;font-family:'IBM Plex Mono',monospace;">
            <?= $st['num'] ?>
          </div>
          <div>
            <div style="font-size:12.5px;font-weight:700;color:var(--text);"><?= $st['label'] ?></div>
            <div style="font-size:11px;color:var(--text-muted);"><?= $st['sub'] ?></div>
          </div>
        </div>
        <?php if ($si < count($steps) - 1): ?>
          <div style="width:32px;flex-shrink:0;display:flex;align-items:center;justify-content:center;margin:0 4px;">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
              style="width:16px;height:16px;color:var(--text-light);">
              <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
            </svg>
          </div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>

  <?php if (!empty($logH['error'])): ?>
    <div class="alert alert-danger" style="margin-bottom:20px;">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round"
          d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
      </svg>
      <?= htmlspecialchars($logH['error']) ?>
    </div>
  <?php else: ?>

    <div style="display:grid;grid-template-columns:1fr 300px;gap:20px;margin-bottom:24px;align-items:start;">
      <div style="display:flex;flex-direction:column;gap:16px;">

        <!-- Siswa card -->
        <div class="card" style="padding:0;overflow:hidden;">
          <div style="display:flex;align-items:stretch;">
            <div style="width:6px;background:<?= $katC['bar'] ?>;flex-shrink:0;border-radius:12px 0 0 12px;"></div>
            <div style="padding:16px 20px;display:flex;align-items:center;gap:14px;flex:1;">
              <div class="user-initials"
                style="width:44px;height:44px;font-size:14px;background:<?= $katC['bg'] ?>;color:<?= $katC['text'] ?>;flex-shrink:0;">
                <?= strtoupper(substr($targetSiswa['name'], 0, 2)) ?>
              </div>
              <div>
                <div style="font-size:15px;font-weight:700;color:var(--text);"><?= htmlspecialchars($targetSiswa['name']) ?>
                </div>
                <div style="font-size:12.5px;color:var(--text-muted);"><?= htmlspecialchars($targetSiswa['asal_sekolah']) ?> ·
                  <?= $targetSiswa['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan' ?>
                </div>
              </div>
              <div style="margin-left:auto;display:flex;align-items:center;gap:12px;">
                <div style="text-align:right;">
                  <div
                    style="font-size:10.5px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:2px;">
                    Nilai z*</div>
                  <span
                    style="font-family:'IBM Plex Mono',monospace;font-size:30px;font-weight:900;color:<?= $katC['text'] ?>;"><?= $logH['defuzzifikasi'] ?></span>
                </div>
                <span
                  style="padding:6px 14px;border-radius:20px;font-size:12.5px;font-weight:700;background:<?= $katC['bg'] ?>;color:<?= $katC['text'] ?>;border:1.5px solid <?= $katC['border'] ?>;">
                  <?= htmlspecialchars($logH['kategori']) ?>
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- STEP 1 -->
        <div class="card" id="step-1">
          <div class="card-header">
            <div style="display:flex;align-items:center;gap:10px;">
              <div
                style="width:28px;height:28px;border-radius:8px;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;flex-shrink:0;">
                1</div>
              <div>
                <div class="card-title">Nilai Input</div>
                <div style="font-size:12px;color:var(--text-muted);">Data penilaian siswa dari tabel penilaian</div>
              </div>
            </div>
          </div>
          <div class="card-body" style="display:flex;flex-wrap:wrap;gap:12px;padding-top:0;">
            <?php foreach ($logH['nilai_input'] as $niv): ?>
              <div
                style="flex:1;min-width:130px;padding:14px 16px;background:var(--surface-2);border:1.5px solid var(--border);border-radius:10px;">
                <div
                  style="font-size:10.5px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px;">
                  <?= htmlspecialchars($niv['nama']) ?>
                </div>
                <div
                  style="font-family:'IBM Plex Mono',monospace;font-size:24px;font-weight:900;color:var(--text);line-height:1;">
                  <?= $niv['nilai'] ?>
                  <span
                    style="font-size:12px;font-weight:400;color:var(--text-muted);">&nbsp;<?= htmlspecialchars($niv['satuan'] ?? '') ?></span>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- STEP 2 -->
        <div class="card" id="step-2">
          <div class="card-header">
            <div style="display:flex;align-items:center;gap:10px;">
              <div
                style="width:28px;height:28px;border-radius:8px;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;flex-shrink:0;">
                2</div>
              <div>
                <div class="card-title">Fuzzifikasi — Derajat Keanggotaan µ(x)</div>
                <div style="font-size:12px;color:var(--text-muted);">Nilai input dicocokkan ke setiap himpunan fuzzy
                  menggunakan fungsi keanggotaan</div>
              </div>
            </div>
          </div>
          <div class="table-wrapper" style="border:none;border-radius:0;">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Kriteria</th>
                  <th>Himpunan</th>
                  <th>Tipe</th>
                  <th>x</th>
                  <th>Parameter [a,b,c,d]</th>
                  <th style="text-align:right;">µ(x)</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $prevKriteria = null;
                foreach ($logH['fuzzifikasi'] as $fz):
                  $muPct = round($fz['mu'] * 100, 1);
                  $muClr = $fz['mu'] > 0 ? 'var(--primary)' : 'var(--text-light)';
                  $params = array_filter($fz['params'], fn($v) => $v !== null);
                  $isTrimf = $fz['tipe'] === 'trimf';
                  $newKriteria = $prevKriteria !== $fz['nama_kriteria'];
                  $prevKriteria = $fz['nama_kriteria'];
                  ?>
                  <?php if ($newKriteria && (int) $prevKriteria - (int) $prevKriteria !== null): ?>
                  <?php endif; ?>
                  <tr
                    style="<?= $newKriteria && $fz !== $logH['fuzzifikasi'][0] ? 'border-top:2px solid var(--border);' : '' ?>">
                    <td style="font-weight:600;font-size:13px;color:var(--text);">
                      <?= htmlspecialchars($fz['nama_kriteria']) ?>
                    </td>
                    <td>
                      <span style="font-weight:700;font-size:13px;"><?= htmlspecialchars($fz['nama_himpunan']) ?></span>
                    </td>
                    <td>
                      <span
                        style="font-family:'IBM Plex Mono',monospace;font-size:11px;font-weight:700;padding:2px 8px;border-radius:20px;
                  background:<?= $isTrimf ? '#eff6ff' : '#f0fdf4' ?>;color:<?= $isTrimf ? '#1d4ed8' : '#15803d' ?>;border:1px solid <?= $isTrimf ? '#bfdbfe' : '#bbf7d0' ?>;">
                        <?= $fz['tipe'] ?>
                      </span>
                    </td>
                    <td style="font-family:'IBM Plex Mono',monospace;font-size:13px;font-weight:700;"><?= $fz['x'] ?></td>
                    <td style="font-family:'IBM Plex Mono',monospace;font-size:11.5px;color:var(--text-muted);">
                      [<?= implode(', ', $params) ?>]</td>
                    <td style="text-align:right;">
                      <div style="display:flex;align-items:center;justify-content:flex-end;gap:10px;">
                        <div style="width:60px;height:5px;background:var(--border);border-radius:3px;overflow:hidden;">
                          <div style="height:100%;width:<?= $muPct ?>%;background:var(--primary);border-radius:3px;"></div>
                        </div>
                        <span
                          style="font-family:'IBM Plex Mono',monospace;font-size:13px;font-weight:700;color:<?= $muClr ?>;min-width:48px;text-align:right;"><?= $fz['mu'] ?></span>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- STEP 3 -->
        <div class="card" id="step-3">
          <div class="card-header">
            <div style="display:flex;align-items:center;gap:10px;">
              <div
                style="width:28px;height:28px;border-radius:8px;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;flex-shrink:0;">
                3</div>
              <div>
                <div class="card-title">Inferensi — Firing Strength α per Aturan</div>
                <div style="font-size:12px;color:var(--text-muted);">α = MIN(µ) untuk AND, MAX(µ) untuk OR antar kondisi
                  satu aturan</div>
              </div>
            </div>
          </div>
          <div class="table-wrapper" style="border:none;border-radius:0;">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Aturan</th>
                  <th>Kategori</th>
                  <th style="text-align:right;">α</th>
                  <th style="text-align:right;">z (output)</th>
                  <th style="text-align:right;">α × z</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($logH['inferensi'] as $inf):
                  $isActive = $inf['alpha'] > 0;
                  $oc = $katColors[$inf['keterangan']] ?? $katColors['Tidak Layak'];
                  ?>
                  <tr style="<?= !$isActive ? 'opacity:.4;' : '' ?>">
                    <td style="font-size:12.5px;font-weight:600;"><?= htmlspecialchars($inf['nama_aturan']) ?></td>
                    <td>
                      <span
                        style="padding:3px 9px;border-radius:20px;font-size:11px;font-weight:600;background:<?= $oc['bg'] ?>;color:<?= $oc['text'] ?>;border:1px solid <?= $oc['border'] ?>;"><?= $inf['keterangan'] ?></span>
                    </td>
                    <td style="text-align:right;">
                      <?php if ($isActive): ?>
                        <span
                          style="font-family:'IBM Plex Mono',monospace;font-size:13px;font-weight:800;color:var(--primary);"><?= $inf['alpha'] ?></span>
                      <?php else: ?>
                        <span style="font-family:'IBM Plex Mono',monospace;font-size:13px;color:var(--text-light);">0</span>
                      <?php endif; ?>
                    </td>
                    <td style="text-align:right;font-family:'IBM Plex Mono',monospace;font-size:13px;"><?= $inf['z'] ?></td>
                    <td
                      style="text-align:right;font-family:'IBM Plex Mono',monospace;font-size:13px;font-weight:700;color:<?= $isActive ? 'var(--text)' : 'var(--text-light)' ?>;">
                      <?= $inf['alpha_z'] ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
              <tfoot>
                <tr style="background:var(--surface-2);">
                  <td colspan="2"
                    style="text-align:right;font-size:12px;font-weight:700;color:var(--text-muted);padding:10px 16px;">Σ
                    Jumlah</td>
                  <td
                    style="text-align:right;font-family:'IBM Plex Mono',monospace;font-size:13px;padding:10px 16px;font-weight:700;">
                    <?= $sumAlpha ?>
                  </td>
                  <td></td>
                  <td
                    style="text-align:right;font-family:'IBM Plex Mono',monospace;font-size:13px;font-weight:800;color:var(--primary);padding:10px 16px;">
                    <?= $sumAlphaZ ?>
                  </td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>

        <!-- STEP 4 -->
        <div class="card" id="step-4">
          <div class="card-header">
            <div style="display:flex;align-items:center;gap:10px;">
              <div
                style="width:28px;height:28px;border-radius:8px;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;flex-shrink:0;">
                4</div>
              <div>
                <div class="card-title">Defuzzifikasi — Weighted Average</div>
                <div style="font-size:12px;color:var(--text-muted);">Nilai crisp akhir dihitung dari rata-rata tertimbang
                  seluruh aturan aktif</div>
              </div>
            </div>
          </div>
          <div class="card-body">
            <!-- Formula box -->
            <div
              style="background:var(--surface-2);border:1.5px solid var(--border);border-radius:10px;padding:24px;text-align:center;margin-bottom:16px;">
              <div
                style="font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.08em;margin-bottom:16px;">
                Formula Weighted Average (Sugeno)</div>
              <div
                style="font-size:16px;font-weight:700;color:var(--text);font-family:'IBM Plex Mono',monospace;line-height:2.2;">
                z* =
                <span
                  style="display:inline-block;padding:0 10px;border-top:2.5px solid var(--primary);color:var(--primary);">Σ
                  (αᵢ × zᵢ)</span>
                &nbsp;÷&nbsp; Σ αᵢ
              </div>
              <div
                style="margin-top:16px;padding:14px 20px;background:var(--surface);border-radius:8px;border:1px solid var(--border);display:inline-block;">
                <span style="font-family:'IBM Plex Mono',monospace;font-size:14px;color:var(--text-muted);">
                  z* =
                  <span style="color:var(--text);font-weight:700;"><?= $sumAlphaZ ?></span>
                  &nbsp;÷&nbsp;
                  <span style="color:var(--text);font-weight:700;"><?= $sumAlpha ?></span>
                  &nbsp;=&nbsp;
                  <span
                    style="font-size:20px;font-weight:900;color:<?= $katC['bar'] ?>;"><?= $logH['defuzzifikasi'] ?></span>
                </span>
              </div>
            </div>
            <!-- Hasil akhir -->
            <div
              style="padding:20px 22px;border-radius:12px;background:<?= $katC['bg'] ?>;border:2px solid <?= $katC['border'] ?>;">
              <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
                <div>
                  <div
                    style="font-size:11px;font-weight:700;color:<?= $katC['text'] ?>;text-transform:uppercase;letter-spacing:.07em;margin-bottom:6px;">
                    Hasil Akhir Defuzzifikasi</div>
                  <div
                    style="font-family:'IBM Plex Mono',monospace;font-size:36px;font-weight:900;color:<?= $katC['text'] ?>;line-height:1;">
                    <?= $logH['defuzzifikasi'] ?>
                  </div>
                </div>
                <div style="text-align:right;">
                  <div
                    style="font-size:11px;color:<?= $katC['text'] ?>;opacity:.7;margin-bottom:6px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">
                    Kategori</div>
                  <div style="font-size:20px;font-weight:800;color:<?= $katC['text'] ?>;">
                    <?= htmlspecialchars($logH['kategori']) ?>
                  </div>
                  <div style="font-size:11.5px;color:<?= $katC['text'] ?>;opacity:.7;margin-top:4px;">
                    <?= $ranges[$logH['kategori']] ?? '' ?>
                  </div>
                </div>
              </div>
              <div style="height:10px;background:rgba(0,0,0,.1);border-radius:5px;overflow:hidden;">
                <div
                  style="height:100%;width:<?= min(100, $logH['defuzzifikasi']) ?>%;background:<?= $katC['bar'] ?>;border-radius:5px;transition:width 1s ease;">
                </div>
              </div>
              <div
                style="display:flex;justify-content:space-between;margin-top:6px;font-family:'IBM Plex Mono',monospace;font-size:10.5px;color:<?= $katC['text'] ?>;opacity:.6;">
                <span>0</span><span>25</span><span>50</span><span>75</span><span>100</span>
              </div>
            </div>
          </div>
        </div>

      </div><!-- /langkah -->

      <!-- Panel Kanan -->
      <div style="position:sticky;top:24px;display:flex;flex-direction:column;gap:14px;">

        <!-- Ringkasan -->
        <div class="card">
          <div style="padding:16px 18px 4px;">
            <p
              style="font-size:11px;font-weight:800;color:var(--text);margin-bottom:14px;text-transform:uppercase;letter-spacing:.07em;">
              Ringkasan Hasil</p>
            <?php
            $summary = [
              ['label' => 'Nilai z*', 'val' => $logH['defuzzifikasi'], 'mono' => true, 'highlight' => true],
              ['label' => 'Σ (α × z)', 'val' => $sumAlphaZ, 'mono' => true, 'highlight' => false],
              ['label' => 'Σ α', 'val' => $sumAlpha, 'mono' => true, 'highlight' => false],
              ['label' => 'Aturan aktif', 'val' => count(array_filter($logH['inferensi'], fn($i) => $i['alpha'] > 0)) . ' / ' . count($logH['inferensi']), 'mono' => false, 'highlight' => false],
              ['label' => 'Kategori', 'val' => $logH['kategori'], 'mono' => false, 'highlight' => true],
            ];
            foreach ($summary as $s): ?>
              <div
                style="display:flex;align-items:center;justify-content:space-between;padding:9px 0;border-bottom:1px solid var(--border);">
                <span style="font-size:12px;color:var(--text-muted);"><?= $s['label'] ?></span>
                <span
                  style="font-size:13px;font-weight:<?= $s['highlight'] ? '800' : '700' ?>;color:<?= $s['highlight'] ? $katC['text'] : 'var(--text)' ?>;<?= $s['mono'] ? "font-family:'IBM Plex Mono',monospace;" : ''; ?>">
                  <?= htmlspecialchars((string) $s['val']) ?>
                </span>
              </div>
            <?php endforeach; ?>
            <div style="height:8px;"></div>
          </div>
          <!-- Meter -->
          <div
            style="padding:14px 18px;border-top:1px solid var(--border);background:<?= $katC['bg'] ?>;border-radius:0 0 12px 12px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
              <span style="font-size:11px;font-weight:700;color:<?= $katC['text'] ?>;">Posisi pada skala 0–100</span>
              <span
                style="font-family:'IBM Plex Mono',monospace;font-size:13px;font-weight:800;color:<?= $katC['text'] ?>;"><?= $logH['defuzzifikasi'] ?></span>
            </div>
            <div style="height:8px;background:rgba(0,0,0,.08);border-radius:4px;overflow:hidden;">
              <div
                style="height:100%;width:<?= min(100, $logH['defuzzifikasi']) ?>%;background:<?= $katC['bar'] ?>;border-radius:4px;">
              </div>
            </div>
            <div style="margin-top:8px;display:flex;justify-content:center;">
              <span
                style="font-size:13px;font-weight:800;color:<?= $katC['text'] ?>;padding:4px 14px;border-radius:20px;background:rgba(0,0,0,.06);"><?= htmlspecialchars($logH['kategori']) ?></span>
            </div>
          </div>
        </div>

        <!-- Navigasi step -->
        <div class="card">
          <div style="padding:14px 18px;">
            <p
              style="font-size:11px;font-weight:800;color:var(--text);margin-bottom:12px;text-transform:uppercase;letter-spacing:.07em;">
              Navigasi Langkah</p>
            <?php foreach ($steps as $si => $st): ?>
              <a href="#step-<?= $si + 1 ?>"
                style="display:flex;align-items:center;gap:10px;padding:8px 10px;border-radius:8px;text-decoration:none;margin-bottom:4px;transition:background .15s;"
                onmouseover="this.style.background='var(--surface-2)'" onmouseout="this.style.background='transparent'">
                <div
                  style="width:24px;height:24px;border-radius:6px;background:var(--primary-light);color:var(--primary);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;flex-shrink:0;">
                  <?= $si + 1 ?>
                </div>
                <div>
                  <div style="font-size:12.5px;font-weight:600;color:var(--text);"><?= $st['label'] ?></div>
                  <div style="font-size:11px;color:var(--text-muted);"><?= $st['sub'] ?></div>
                </div>
              </a>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Panduan Kategori -->
        <div class="card">
          <div style="padding:14px 18px;">
            <p
              style="font-size:11px;font-weight:800;color:var(--text);margin-bottom:12px;text-transform:uppercase;letter-spacing:.07em;">
              Panduan Kategori</p>
            <?php foreach ($katColors as $kat => $c):
              if ($kat === 'Tidak Dapat Dihitung')
                continue;
              $isKatAktif = $kat === $logH['kategori'];
              ?>
              <div
                style="display:flex;align-items:center;gap:8px;margin-bottom:7px;padding:6px 8px;border-radius:7px;background:<?= $isKatAktif ? $c['bg'] : 'transparent' ?>;border:1px solid <?= $isKatAktif ? $c['border'] : 'transparent' ?>;">
                <span style="width:8px;height:8px;border-radius:50%;background:<?= $c['dot'] ?>;flex-shrink:0;"></span>
                <span
                  style="font-size:12px;font-weight:<?= $isKatAktif ? '700' : '400' ?>;color:<?= $isKatAktif ? $c['text'] : 'var(--text-muted)' ?>;flex:1;"><?= $kat ?></span>
                <span
                  style="font-family:'IBM Plex Mono',monospace;font-size:11px;color:<?= $isKatAktif ? $c['text'] : 'var(--text-muted)' ?>;font-weight:<?= $isKatAktif ? '700' : '400' ?>;"><?= $ranges[$kat] ?? '' ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

      </div><!-- /panel kanan -->
    </div>

  <?php endif; // end if error ?>

<?php else: ?>
  <!-- ══════════════════════════════════════════════════════════
     VIEW: DAFTAR SISWA (default)
     ══════════════════════════════════════════════════════════ -->

  <!-- Banner kontekstual -->
  <?php if ($punyaNilai === 0): ?>
    <div class="alert" style="margin-bottom:16px;background:#fffbeb;border-color:#fde68a;color:#92400e;">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round"
          d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
      </svg>
      <span>Belum ada siswa yang dinilai. <a href="app.php?page=penilaian"
          style="font-weight:700;color:#92400e;text-decoration:underline;">Input penilaian terlebih dahulu</a> sebelum
        menjalankan perhitungan.</span>
    </div>
  <?php elseif ($sebagianSudah): ?>
    <div class="alert" style="margin-bottom:16px;background:#eff6ff;border-color:#bfdbfe;color:#1e40af;">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round"
          d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
      </svg>
      <span><strong><?= $sudahHitung ?> dari <?= $punyaNilai ?> siswa</strong> sudah dihitung. <?= $belumHitung ?> siswa
        masih menunggu. Klik <strong>"<?= $btnHitungLabel ?>"</strong> untuk melanjutkan.</span>
    </div>
  <?php elseif ($semuaSudah): ?>
    <div class="alert" style="margin-bottom:16px;background:#f0fdf4;border-color:#bbf7d0;color:#166534;">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round"
          d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
      </svg>
      <span>Semua <strong><?= $punyaNilai ?> siswa</strong> sudah dihitung. Klik <strong>"Lihat Detail"</strong> pada baris
        siswa untuk melihat langkah-langkah perhitungannya.</span>
    </div>
  <?php elseif (!$isAlreadyCalculated && $punyaNilai > 0): ?>
    <div class="alert" style="margin-bottom:16px;background:#f0fdf4;border-color:#bbf7d0;color:#166534;">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round"
          d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
      </svg>
      <span><strong><?= $punyaNilai ?> siswa</strong> siap dihitung. Klik <strong>"Hitung Semua"</strong> untuk memulai
        inferensi Fuzzy Sugeno.</span>
    </div>
  <?php endif; ?>

  <!-- Page Header -->
  <div class="page-header">
    <div>
      <h1 class="page-title">Perhitungan Fuzzy Sugeno</h1>
      <p class="page-subtitle">Proses inferensi dan defuzzifikasi weighted average per siswa</p>
    </div>
    <div style="display:flex;gap:8px;">
      <?php if ($punyaNilai > 0): ?>
        <a class="btn btn-primary"
          onclick="setAlertOnJs('info','<?= htmlspecialchars($btnHitungLabel) ?>','<?= htmlspecialchars($btnHitungConfirm) ?>',function(){window.location.href='app.php?page=perhitungan&do=hitung_semua';});">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <?= $btnHitungIcon ?>
          </svg>
          <?= htmlspecialchars($btnHitungLabel) ?>
        </a>
      <?php endif; ?>
      <?php if ($isAlreadyCalculated): ?>
        <a class="btn-secondary"
          onclick="setAlertOnJs('warning','Reset Hasil Perhitungan','Reset semua hasil perhitungan? Data akan terhapus permanen.',function(){window.location.href='app.php?page=perhitungan&do=reset';});">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
          </svg>
          Reset Hasil
        </a>
      <?php endif; ?>
    </div>
  </div>

  <!-- Stats -->
  <div class="stats-grid" style="margin-bottom:24px;">
    <div class="stat-card" style="--icon-color:var(--primary)">
      <div class="stat-card-icon">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
        </svg>
      </div>
      <div><span class="stat-card-value"><?= $totalSiswa ?></span><span class="stat-card-label">Total Siswa</span></div>
    </div>
    <div class="stat-card" style="--icon-color:var(--success)">
      <div class="stat-card-icon">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
        </svg>
      </div>
      <div>
        <span class="stat-card-value"><?= $sudahHitung ?></span>
        <span class="stat-card-label">Sudah Dihitung</span>
        <?php if ($sudahHitung > 0 && $punyaNilai > 0): ?>
          <div style="margin-top:6px;height:3px;border-radius:2px;background:var(--border);overflow:hidden;">
            <div
              style="height:100%;width:<?= round($sudahHitung / $punyaNilai * 100) ?>%;background:var(--success);border-radius:2px;">
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
    <div class="stat-card" style="--icon-color:<?= $belumHitung > 0 ? 'var(--warning)' : 'var(--success)' ?>">
      <div class="stat-card-icon">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
        </svg>
      </div>
      <div>
        <span class="stat-card-value"><?= $belumHitung ?></span>
        <span class="stat-card-label">Belum Dihitung</span>
        <?php if ($belumHitung === 0 && $punyaNilai > 0): ?>
          <div style="margin-top:4px;font-size:10.5px;font-weight:600;color:var(--success);">✓ Semua selesai</div>
        <?php endif; ?>
      </div>
    </div>
    <div class="stat-card" style="--icon-color:var(--info)">
      <div class="stat-card-icon">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z" />
        </svg>
      </div>
      <div><span class="stat-card-value"><?= $totalAturan ?></span><span class="stat-card-label">Aturan Aktif</span></div>
    </div>
  </div>

  <!-- Tabel -->
  <div class="card">
    <div class="card-header">
      <div>
        <div class="card-title">Daftar Siswa & Status Perhitungan</div>
        <div style="font-size:12px;color:var(--text-muted);margin-top:2px;">
          <?= $isAlreadyCalculated ? 'Diurutkan berdasarkan nilai z* tertinggi' : 'Klik "Hitung" untuk menjalankan inferensi Fuzzy Sugeno' ?>
        </div>
      </div>
      <?php if ($isAlreadyCalculated && $sudahHitung > 0): ?>
        <div style="display:flex;align-items:center;gap:8px;font-size:12px;color:var(--text-muted);">
          <span
            style="font-family:'IBM Plex Mono',monospace;font-weight:700;color:var(--primary);"><?= $sudahHitung ?>/<?= $punyaNilai ?></span>
          dihitung
          <div style="width:80px;height:5px;background:var(--border);border-radius:3px;overflow:hidden;">
            <div
              style="height:100%;width:<?= $punyaNilai > 0 ? round($sudahHitung / $punyaNilai * 100) : 0 ?>%;background:var(--primary);border-radius:3px;">
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>
    <div class="table-wrapper" style="border:none;border-radius:0;">
      <table class="data-table">
        <thead>
          <tr>
            <th class="td-no"><?= $isAlreadyCalculated ? 'Rank' : '#' ?></th>
            <th>Siswa</th>
            <th>Asal Sekolah</th>
            <th>Penilaian</th>
            <th style="text-align:right;">Nilai z*</th>
            <th>Kategori</th>
            <th>Tgl Hitung</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($siswaList as $i => $s):
            $siswaIni_sudah = $s['nilai_defuzzifikasi'] !== null;
            $punya = (int) $s['jumlah_nilai'] > 0;
            $oc = $siswaIni_sudah ? ($katColors[$s['kategori']] ?? $katColors['Layak']) : null;
            ?>
            <tr>
              <!-- Rank / No -->
              <td class="td-no">
                <?php if ($isAlreadyCalculated && $siswaIni_sudah): ?>
                  <div style="
              width:26px;height:26px;border-radius:50%;margin:0 auto;
              background:<?= $i === 0 ? 'var(--primary)' : ($i === 1 ? '#64748b' : ($i === 2 ? '#b45309' : 'var(--surface-2)')) ?>;
              color:<?= $i < 3 ? '#fff' : 'var(--text-muted)' ?>;
              font-size:11px;font-weight:800;
              display:flex;align-items:center;justify-content:center;">
                    <?= $i + 1 ?>
                  </div>
                <?php else: ?>
                  <span style="color:var(--text-muted);"><?= $i + 1 ?></span>
                <?php endif; ?>
              </td>

              <!-- Siswa -->
              <td>
                <div class="user-cell">
                  <div class="user-initials"
                    style="width:32px;height:32px;font-size:11px;<?= $siswaIni_sudah && $oc ? 'background:' . $oc['bg'] . ';color:' . $oc['text'] . ';' : '' ?>">
                    <?= strtoupper(substr($s['name'], 0, 2)) ?>
                  </div>
                  <div>
                    <div class="user-name"><?= htmlspecialchars($s['name']) ?></div>
                    <div class="user-email"><?= $s['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan' ?></div>
                    <div class="user-email"><?= htmlspecialchars($s['asal_sekolah']) ?></div>
                  </div>
                </div>
              </td>

              <td style="font-size:12.5px;font-weight:600;color:var(--text-muted);"><?= htmlspecialchars($s['asal_sekolah']) ?>
              </td>

              <!-- Status penilaian -->
              <td>
                <?php if ($punya): ?>
                  <span
                    style="display:inline-flex;align-items:center;gap:5px;font-size:12px;font-weight:600;color:#166534;background:#f0fdf4;padding:3px 9px;border-radius:20px;border:1px solid #bbf7d0;">
                    <span style="width:6px;height:6px;border-radius:50%;background:#10b981;"></span>
                    <?= $s['jumlah_nilai'] ?> kriteria
                  </span>
                <?php else: ?>
                  <span style="font-size:12px;color:var(--text-light);font-style:italic;">Belum dinilai</span>
                <?php endif; ?>
              </td>

              <!-- Nilai z* -->
              <td style="text-align:right;">
                <?php if ($siswaIni_sudah): ?>
                  <span
                    style="font-family:'IBM Plex Mono',monospace;font-size:14px;font-weight:800;color:<?= $oc['text'] ?>;">
                    <?= number_format((float) $s['nilai_defuzzifikasi'], 4) ?>
                  </span>
                <?php elseif ($punya): ?>
                  <span
                    style="font-size:11px;color:var(--warning);font-weight:600;background:#fffbeb;padding:2px 8px;border-radius:20px;border:1px solid #fde68a;">Belum
                    dihitung</span>
                <?php else: ?>
                  <span style="color:var(--text-light);font-size:12px;">—</span>
                <?php endif; ?>
              </td>

              <!-- Kategori -->
              <td>
                <?php if ($siswaIni_sudah && $oc): ?>
                  <span
                    style="display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:20px;font-size:11.5px;font-weight:600;background:<?= $oc['bg'] ?>;color:<?= $oc['text'] ?>;border:1px solid <?= $oc['border'] ?>;">
                    <span style="width:6px;height:6px;border-radius:50%;background:<?= $oc['dot'] ?>;"></span>
                    <?= htmlspecialchars($s['kategori']) ?>
                  </span>
                <?php else: ?>
                  <span style="color:var(--text-light);font-size:12px;">—</span>
                <?php endif; ?>
              </td>

              <!-- Tgl Hitung -->
              <td style="font-size:12px;color:var(--text-muted);">
                <?= $s['tanggal_hitung'] ? date('d M Y H:i', strtotime($s['tanggal_hitung'])) : '<span style="color:var(--text-light)">—</span>' ?>
              </td>

              <!-- Aksi: tombol Hitung + Lihat Detail -->
              <td>
                <div class="action-group" style="flex-wrap:wrap;">
                  <?php if ($punya): ?>
                    <!-- Hitung / Hitung Ulang -->
                    <a href="app.php?page=perhitungan&do=hitung&id=<?= $s['id_siswa'] ?>" class="btn-edit">
                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                        stroke="currentColor" style="width:13px;height:13px;">
                        <path stroke-linecap="round" stroke-linejoin="round"
                          d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z" />
                      </svg>
                      <?= $siswaIni_sudah ? 'Hitung Ulang' : 'Hitung' ?>
                    </a>

                    <!-- Lihat Detail — hanya jika sudah dihitung -->
                    <?php if ($siswaIni_sudah): ?>
                      <a href="app.php?page=perhitungan&do=detail&id=<?= $s['id_siswa'] ?>"
                        style="display:inline-flex;align-items:center;gap:5px;padding:5px 10px;border-radius:7px;font-size:12px;font-weight:600;color:var(--primary);background:var(--primary-light);border:1px solid #c7d2fe;text-decoration:none;transition:background .15s;"
                        onmouseover="this.style.background='#c7d2fe'" onmouseout="this.style.background='var(--primary-light)'">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                          stroke="currentColor" style="width:13px;height:13px;">
                          <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                          <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        Lihat Detail
                      </a>
                    <?php endif; ?>

                  <?php else: ?>
                    <span style="font-size:11.5px;color:var(--text-light);padding:4px 8px;font-style:italic;">Belum ada
                      nilai</span>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

<?php endif; // end view: list ?>

<script>
  function autoHideAlert(id) {
    const el = document.getElementById(id);
    if (!el) return;
    setTimeout(() => {
      el.style.opacity = '0';
      el.style.transform = 'translateY(-8px)';
      setTimeout(() => el.remove(), 500);
    }, 3000);
  }
  autoHideAlert('alert-success');
  autoHideAlert('alert-error');
</script>