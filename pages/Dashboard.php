<?php
require_once __DIR__ . '/../config/DBConfig.php';
$pageTitle = 'Dashboard';

$kategoriMeta = [
  'Sangat Layak' => ['bg' => '#eff6ff', 'text' => '#1e40af', 'border' => '#bfdbfe', 'bar' => '#3b82f6', 'dot' => '#3b82f6'],
  'Layak' => ['bg' => '#f0fdf4', 'text' => '#166534', 'border' => '#bbf7d0', 'bar' => '#10b981', 'dot' => '#10b981'],
  'Cukup Layak' => ['bg' => '#fffbeb', 'text' => '#92400e', 'border' => '#fde68a', 'bar' => '#f59e0b', 'dot' => '#f59e0b'],
  'Tidak Layak' => ['bg' => '#fef2f2', 'text' => '#991b1b', 'border' => '#fecaca', 'bar' => '#ef4444', 'dot' => '#ef4444'],
];

function getRecentHasil($conn, $isLimit = true, $limit = null)
{
  $stmt = $conn->query("SELECT 
	  hp.id_hasil, 
	  s.name as siswa, 
	  s.asal_sekolah, 
	  s.jenis_kelamin as jk , 
	  s.tinggi_badan as tinggi,
	  s.berat_badan as berat,
    hp.nilai_defuzzifikasi as defuzz,
    hp.kategori,
    hp.tanggal_hitung as tgl
  FROM hasil_perhitungan hp 
  INNER JOIN siswa s ON s.id_siswa = hp.id_siswa 
  ORDER BY hp.nilai_defuzzifikasi DESC " . ($isLimit ? "LIMIT $limit" : ""));
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getDistribusi($conn)
{
  $stmt = $conn->query("SELECT kategori,COUNT(*) AS jumlah 
  FROM hasil_perhitungan 
  GROUP BY kategori
  ORDER BY jumlah DESC;");
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if ($conn) {
  $recentHasil = getRecentHasil($conn, true, 5);
  $recentAllHasil = getRecentHasil($conn, false, null);
  $distribusi = getDistribusi($conn);
  $totalHasil = array_sum(array_column($distribusi, 'jumlah'));

  // Total siswa
  $jumlahSiswa = getCount(
    $conn,
    "SELECT COUNT(*) as jumlah_siswa FROM siswa",
    "jumlah_siswa"
  );

  // Total Aturan Fuzzy
  $jumlahAturan = getCount(
    $conn,
    "SELECT COUNT(*) as jumlah_aturan FROM aturan_fuzzy",
    "jumlah_aturan"
  );

  // Total Sudah Dinilai 
  $jumlahDinilai = getCount(
    $conn,
    "SELECT COUNT(*) AS jumlah_sudah_dinilai
          FROM (
              SELECT 
                  s.id_siswa,
                  CASE 
                      WHEN COUNT(DISTINCT p.id_kriteria) < k.total_kriteria
                      THEN 'Belum Dinilai'
                      ELSE 'Sudah Dinilai'
                  END AS status
              FROM siswa s
              LEFT JOIN penilaian p 
                  ON p.id_siswa = s.id_siswa
              CROSS JOIN (
                  SELECT COUNT(*) AS total_kriteria FROM kriteria
              ) k
              GROUP BY s.id_siswa, k.total_kriteria
          ) AS hasil
          WHERE status = 'Sudah Dinilai'",
    "jumlah_sudah_dinilai"
  );

  // Total Hasil Perhitungan
  $jumlahHasil = getCount(
    $conn,
    "SELECT COUNT(*) as jumlah_hasil FROM hasil_perhitungan",
    "jumlah_hasil"
  );

  // Total Belum Dinilai
  $belumDinilai = $jumlahDinilai = getCount(
    $conn,
    "SELECT COUNT(*) AS jumlah_belum_dinilai
          FROM (
              SELECT 
                  s.id_siswa,
                  CASE 
                      WHEN COUNT(DISTINCT p.id_kriteria) < k.total_kriteria
                      THEN 'Belum Dinilai'
                      ELSE 'Sudah Dinilai'
                  END AS status
              FROM siswa s
              LEFT JOIN penilaian p 
                  ON p.id_siswa = s.id_siswa
              CROSS JOIN (
                  SELECT COUNT(*) AS total_kriteria FROM kriteria
              ) k
              GROUP BY s.id_siswa, k.total_kriteria
          ) AS hasil
          WHERE status = 'Belum Dinilai';",
    "jumlah_belum_dinilai"
  );
}

$stats = [
  [
    'label' => 'Total Siswa',
    'value' => $jumlahSiswa,
    'icon_color' => 'var(--primary)',
    'change' => '',
    'change_up' => true,
    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/>'
  ],
  [
    'label' => 'Sudah Dinilai',
    'value' => $jumlahDinilai,
    'icon_color' => 'var(--success)',
    'change' => '',
    'change_up' => true,
    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>'
  ],
  [
    'label' => 'Total Aturan Fuzzy',
    'value' => $jumlahAturan,
    'icon_color' => 'var(--warning)',
    'change' => '',
    'change_up' => null,
    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z"/>'
  ],
  [
    'label' => 'Hasil Perhitungan',
    'value' => $jumlahHasil,
    'icon_color' => 'var(--info)',
    'change' => '',
    'change_up' => null,
    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/>'
  ],
];
?>

<!-- Welcome Banner -->
<div style="
    background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #312e81 100%);
    border-radius: var(--radius-lg);
    padding: 28px 32px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
">
  <!-- Decorative circles -->
  <div
    style="position:absolute;top:-40px;right:-40px;width:200px;height:200px;border-radius:50%;background:rgba(79,70,229,0.2);pointer-events:none;">
  </div>
  <div
    style="position:absolute;bottom:-60px;right:100px;width:150px;height:150px;border-radius:50%;background:rgba(124,58,237,0.15);pointer-events:none;">
  </div>
  <div
    style="position:absolute;top:20px;right:200px;width:60px;height:60px;border-radius:50%;background:rgba(165,180,252,0.1);pointer-events:none;">
  </div>

  <div
    style="position:relative;z-index:1;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:20px;">
    <div>
      <div
        style="font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#a5b4fc;margin-bottom:6px;">
        Sistem Penunjang Keputusan
      </div>
      <h2 style="font-size:24px;font-weight:800;color:#fff;margin-bottom:6px;line-height:1.2;">
        Selamat datang, <?= htmlspecialchars($currentUser['name'] ?? 'Admin') ?> 👋
      </h2>
      <p style="font-size:13.5px;color:#94a3b8;max-width:480px;line-height:1.6;">
        Sistem SPK menggunakan metode <strong style="color:#a5b4fc;">Fuzzy Sugeno</strong> untuk
        menentukan kelayakan siswa berdasarkan data anthropometri.
      </p>
    </div>
    <div style="display:flex;flex-direction:column;align-items:flex-end;gap:8px;">
      <?php if ($belumDinilai > 0): ?>
        <a href="app.php?page=penilaian" style="
                display:inline-flex;align-items:center;gap:7px;
                padding:10px 18px;border-radius:9px;
                background:var(--primary);color:#fff;
                font-size:13px;font-weight:700;text-decoration:none;
                box-shadow:0 4px 16px rgba(79,70,229,.4);
                transition:background .2s,transform .1s;">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
            style="width:15px;height:15px;">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
          </svg>
          Input Nilai Sekarang
        </a>
        <span style="font-size:11.5px;color:#f87171;display:flex;align-items:center;gap:4px;">
          <span style="width:6px;height:6px;border-radius:50%;background:#f87171;animation:pulse 1.5s infinite;"></span>
          <?= $belumDinilai ?> siswa belum dinilai
        </span>
      <?php endif; ?>
    </div>
  </div>
</div>

<style>
  @keyframes pulse {

    0%,
    100% {
      opacity: 1;
    }

    50% {
      opacity: .3;
    }
  }
</style>

<!-- Stats Grid -->
<div class="stats-grid" style="margin-bottom:24px;">
  <?php foreach ($stats as $s): ?>
    <div class="stat-card" style="--icon-color:<?= $s['icon_color'] ?>">
      <div class="stat-card-icon">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
          stroke="currentColor"><?= $s['icon'] ?></svg>
      </div>
      <div style="flex:1;min-width:0;">
        <span class="stat-card-value"><?= $s['value'] ?></span>
        <span class="stat-card-label"><?= $s['label'] ?></span>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<!-- Row: Distribusi + Ranking -->
<div style="display:grid;grid-template-columns:1fr 1.6fr;gap:16px;margin-bottom:24px;">

  <!-- Distribusi Donut -->
  <div class="card">
    <div class="card-header">
      <div>
        <div class="card-title">Distribusi Kelayakan</div>
        <div style="font-size:11.5px;color:var(--text-muted);margin-top:1px;">Dari <?= $totalHasil ?> siswa terhitung
        </div>
      </div>
    </div>
    <div class="card-body" style="display:flex;flex-direction:column;align-items:center;gap:20px;">
      <!-- Donut SVG -->
      <div style="position:relative;width:160px;height:160px;flex-shrink:0;">
        <svg viewBox="0 0 160 160" style="width:100%;height:100%;transform:rotate(-90deg);">
          <?php
          $r = 60;
          $cx = 80;
          $cy = 80;
          $circumference = 2 * M_PI * $r;
          $offset = 0;
          foreach ($distribusi as $kat => $d):
            $pct = $d['jumlah'] / $totalHasil;
            $dash = $circumference * $pct;
            $gap = $circumference - $dash;
            ?>
            <circle cx="<?= $cx ?>" cy="<?= $cy ?>" r="<?= $r ?>" fill="none"
              stroke="<?= $kategoriMeta[$d['kategori']]['bar'] ?? '#94a3b8' ?>" stroke-width="22"
              stroke-dasharray="<?= round($dash - 3, 2) ?> <?= round($gap + 3, 2) ?>"
              stroke-dashoffset="-<?= round($circumference * $offset, 2) ?>" stroke-linecap="butt" />
            <?php $offset += $pct; endforeach; ?>
          <!-- Inner circle -->
          <circle cx="<?= $cx ?>" cy="<?= $cy ?>" r="44" fill="white" />
        </svg>
        <div
          style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;">
          <span style="font-size:26px;font-weight:800;color:var(--text);line-height:1;"><?= $totalHasil ?></span>
          <span style="font-size:10px;color:var(--text-muted);margin-top:2px;">siswa</span>
        </div>
      </div>

      <!-- Legend -->
      <div style="width:100%;display:flex;flex-direction:column;gap:10px;">
        <?php foreach ($distribusi as $kat => $d):
          $pct = round($d['jumlah'] / $totalHasil * 100);
          ?>
          <div>
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px;">
              <span style="font-size:12.5px;font-weight:600;color:var(--text);display:flex;align-items:center;gap:7px;">
                <span
                  style="width:8px;height:8px;border-radius:2px;background:<?= $kategoriMeta[$d['kategori']]['bar'] ?? '#94a3b8' ?>;flex-shrink:0;">
                </span>
                <?= $d['kategori'] ?>
              </span>
              <span
                style="font-size:12px;font-family:'IBM Plex Mono',monospace;color:var(--text-muted);"><?= $d['jumlah'] ?>
                <span style="opacity:.6;">(<?= $pct ?>%)</span></span>
            </div>
            <div style="height:4px;border-radius:2px;background:var(--border);overflow:hidden;">
              <div
                style="height:100%;width:<?= $pct ?>%;background:<?= $kategoriMeta[$d['kategori']]['bar'] ?? '#94a3b8' ?>;border-radius:2px;transition:width .7s ease;">
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Ranking Terbaru -->
  <div class="card">
    <div class="card-header">
      <div>
        <div class="card-title">Hasil Perhitungan Terbaru</div>
        <div style="font-size:11.5px;color:var(--text-muted);margin-top:1px;">Diurutkan berdasarkan nilai defuzzifikasi
        </div>
      </div>
      <a href="app.php?page=hasil"
        style="font-size:12.5px;font-weight:600;color:var(--primary);text-decoration:none;">Lihat semua →</a>
    </div>
    <div style="padding:0;">
      <?php foreach ($recentHasil as $ri => $r):
        $medals = ['🥇', '🥈', '🥉'];
        ?>
        <div style="
                display:flex;align-items:center;gap:14px;
                padding:13px 20px;
                border-bottom:1px solid var(--border);
                transition:background .15s;
            " onmouseover="this.style.background='var(--surface-2)'" onmouseout="this.style.background=''">
          <!-- Rank -->
          <?php if ($ri < 3): ?>

            <div style="font-size:22px;flex-shrink:0;">
              <?= $medals[$ri] ?>
            </div>
          <?php else: ?>
            <div style="
              width:28px;
              height:28px;
              border-radius:8px;
              background:var(--surface-2);
              border:1.5px solid var(--border);
              display:flex;
              align-items:center;
              justify-content:center;
              font-size:12px;
              font-weight:800;
              color: var(--text-muted);
              flex-shrink:0;
              font-family:'IBM Plex Mono',monospace;">
              <?= $ri + 1 ?>
            </div>
          <?php endif; ?>

          <!-- Avatar + Info -->
          <div class="user-cell" style="flex:1;min-width:0;">
            <div class="user-initials" style="width:34px;height:34px;font-size:11px;flex-shrink:0;">
              <?= strtoupper(substr($r['siswa'], 0, 2)) ?>
            </div>
            <div style="min-width:0;">
              <div class="user-name" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= $r['siswa'] ?>
              </div>
              <div class="user-email"><?= $r['asal_sekolah'] ?></div>
            </div>
          </div>

          <!-- Score bar -->
          <div style="width:90px;flex-shrink:0;">
            <div style="display:flex;justify-content:flex-end;margin-bottom:3px;">
              <span
                style="font-family:'IBM Plex Mono',monospace;font-size:13px;font-weight:700;color:var(--text);"><?= $r['defuzz'] ?></span>
            </div>
            <div style="height:5px;border-radius:3px;background:var(--border);overflow:hidden;">
              <div
                style="height:100%;width:<?= $r['defuzz'] ?>%;background:<?= $kategoriMeta[$r['kategori']]['bar'] ?? '#94a3b8' ?>;border-radius:3px;">
              </div>
            </div>
          </div>

          <!-- Kategori badge -->
          <div style="flex-shrink:0;width:100px;text-align:right;">
            <span style="
                        display:inline-block;padding:3px 10px;
                        border-radius:20px;font-size:11px;font-weight:600;
                        background:<?= $distribusi[$r['kategori']]['bg'] ?? '#f1f5f9' ?>;
                        color:<?= $distribusi[$r['kategori']]['text'] ?? '#64748b' ?>;
                        border:1px solid <?= $distribusi[$r['kategori']]['border'] ?? '#e2e8f0' ?>;
                        white-space:nowrap;">
              <?= $r['kategori'] ?>
            </span>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- Row: Trend Chart + Aktivitas + Quick Action -->
<div style="display:grid; 1fr;gap:16px;margin-bottom:24px;">

  <!-- Trend Mini Chart -->
  <!-- <div class="card">
    <div class="card-header">
      <div>
        <div class="card-title">Tren Nilai Defuzzifikasi</div>
        <div style="font-size:11.5px;color:var(--text-muted);margin-top:1px;"><?= count($recentAllHasil) ?> siswa
          terakhir diproses</div>
      </div>
    </div>
    <div class="card-body">
      <canvas id="trendChart" width="900" height="180" style="width:100%;height:180px;"></canvas>
      <div style="display:flex;justify-content:center;gap:20px;margin-top:12px;">
        <span style="font-size:12px;color:var(--text-muted);display:flex;align-items:center;gap:6px;">
          <span style="width:20px;height:3px;border-radius:2px;background:var(--primary);display:inline-block;"></span>
          Nilai Defuzzifikasi
        </span>
        <span style="font-size:12px;color:var(--text-muted);display:flex;align-items:center;gap:6px;">
          <span
            style="width:20px;height:2px;border-radius:2px;background:#e2e8f0;display:inline-block;border-top:2px dashed #cbd5e1;"></span>
          Rata-rata (<?= round(array_sum(array_column($recentAllHasil, 'defuzz')) / count($recentAllHasil), 1) ?>)
        </span>
      </div>
    </div>
  </div>
</div> -->

<!-- Quick Actions -->
<div class="card" style="margin-bottom: 4px;">
  <div class="card-header">
    <div class="card-title">Akses Cepat</div>
    <span style="font-size:12px;color:var(--text-muted);">Navigasi langsung ke fitur utama</span>
  </div>
  <div class="card-body">
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:12px;">
      <?php
      $quickLinks = [
        [
          'label' => 'Tambah Siswa',
          'page' => 'siswa',
          'color' => 'var(--primary)',
          'desc' => 'Data master siswa',
          'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z"/>'
        ],
        [
          'label' => 'Input Penilaian',
          'page' => 'penilaian',
          'color' => 'var(--info)',
          'desc' => 'Nilai per kriteria',
          'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/>'
        ],
        [
          'label' => 'Perhitungan',
          'page' => 'perhitungan',
          'color' => 'var(--warning)',
          'desc' => 'Proses fuzzy sugeno',
          'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 15.75V18m-7.5-6.75h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V13.5Zm0 2.25h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V18Zm2.498-6.75h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V13.5Zm0 2.25h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V18Zm2.504-6.75h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V13.5Zm0 2.25h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V18Zm2.498-6.75h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V13.5ZM8.25 6h7.5v2.25h-7.5V6ZM12 2.25c-1.892 0-3.758.11-5.593.322C5.307 2.7 4.5 3.616 4.5 4.718V19.5a1.5 1.5 0 0 0 1.5 1.5h12a1.5 1.5 0 0 0 1.5-1.5V4.718c0-1.002-.78-1.00-1.02-1.396A48.658 48.658 0 0 0 12 2.25Z"/>'
        ],
        [
          'label' => 'Lihat Hasil',
          'page' => 'hasil',
          'color' => 'var(--success)',
          'desc' => 'Output defuzzifikasi',
          'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/>'
        ],
        [
          'label' => 'Himpunan Fuzzy',
          'page' => 'himpunan',
          'color' => '#7c3aed',
          'desc' => 'Fungsi keanggotaan',
          'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941"/>'
        ],
        [
          'label' => 'Aturan Fuzzy',
          'page' => 'aturan',
          'color' => '#0891b2',
          'desc' => 'Rule base IF-THEN',
          'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z"/>'
        ],
      ];
      foreach ($quickLinks as $q): ?>
        <a href="app.php?page=<?= $q['page'] ?>" style="
                display:flex;flex-direction:column;gap:12px;
                padding:16px;border-radius:var(--radius);
                border:1.5px solid var(--border);
                background:var(--surface);
                text-decoration:none;
                transition:border-color .2s,box-shadow .2s,transform .15s;
            "
          onmouseover="this.style.borderColor='<?= $q['color'] ?>';this.style.boxShadow='0 0 0 4px <?= $q['color'] ?>18';this.style.transform='translateY(-2px)'"
          onmouseout="this.style.borderColor='var(--border)';this.style.boxShadow='none';this.style.transform='translateY(0)'">
          <div style="
                    width:38px;height:38px;border-radius:10px;
                    background:color-mix(in srgb, <?= $q['color'] ?> 12%, transparent);
                    color:<?= $q['color'] ?>;
                    display:flex;align-items:center;justify-content:center;">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
              stroke="currentColor" style="width:19px;height:19px;"><?= $q['icon'] ?></svg>
          </div>
          <div>
            <div style="font-size:13.5px;font-weight:700;color:var(--text);"><?= $q['label'] ?></div>
            <div style="font-size:11.5px;color:var(--text-muted);margin-top:2px;"><?= $q['desc'] ?></div>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<script>
  // Trend chart
  // (function () {
  //   const canvas = document.getElementById('trendChart');
  //   if (!canvas) return;
  //   const ctx = canvas.getContext('2d');
  //   const W = canvas.width, H = canvas.height;
  //   const pad = { l: 36, r: 20, t: 16, b: 32 };
  //   const pw = W - pad.l - pad.r;
  //   const ph = H - pad.t - pad.b;

  //   const data = [
  //     { name: 'Anca', val: 55.4 },
  //     { name: 'Budi', val: 78.2 },
  //     { name: 'Dian', val: 85.0 },
  //     { name: 'Eka', val: 72.1 },
  //     { name: 'Fira', val: 30.5 },
  //   ];
  //   const avg = data.reduce((s, d) => s + d.val, 0) / data.length;
  //   const yMin = 0, yMax = 100;

  //   function toX(i) { return pad.l + (i / (data.length - 1)) * pw; }
  //   function toY(v) { return pad.t + ph - ((v - yMin) / (yMax - yMin)) * ph; }

  //   // Grid lines
  //   ctx.strokeStyle = '#e2e8f0'; ctx.lineWidth = 1;
  //   [0, 25, 50, 75, 100].forEach(v => {
  //     const y = toY(v);
  //     ctx.beginPath(); ctx.moveTo(pad.l, y); ctx.lineTo(W - pad.r, y); ctx.stroke();
  //     ctx.fillStyle = '#94a3b8'; ctx.font = '10px IBM Plex Mono'; ctx.textAlign = 'right';
  //     ctx.fillText(v, pad.l - 6, y + 4);
  //   });

    // Avg line
    const avgY = toY(avg);
    ctx.setLineDash([6, 4]); ctx.strokeStyle = '#cbd5e1'; ctx.lineWidth = 1.5;
    ctx.beginPath(); ctx.moveTo(pad.l, avgY); ctx.lineTo(W - pad.r, avgY); ctx.stroke();
    ctx.setLineDash([]);

    // Area fill
    const grad = ctx.createLinearGradient(0, pad.t, 0, pad.t + ph);
    grad.addColorStop(0, 'rgba(79,70,229,0.18)');
    grad.addColorStop(1, 'rgba(79,70,229,0.01)');
    ctx.beginPath();
    data.forEach((d, i) => {
      i === 0 ? ctx.moveTo(toX(i), toY(d.val)) : ctx.lineTo(toX(i), toY(d.val));
    });
    ctx.lineTo(toX(data.length - 1), pad.t + ph);
    ctx.lineTo(pad.l, pad.t + ph);
    ctx.closePath(); ctx.fillStyle = grad; ctx.fill();

    // Line
    ctx.beginPath(); ctx.strokeStyle = '#4f46e5'; ctx.lineWidth = 2.5;
    data.forEach((d, i) => {
      i === 0 ? ctx.moveTo(toX(i), toY(d.val)) : ctx.lineTo(toX(i), toY(d.val));
    });
    ctx.stroke();

    // Points
    data.forEach((d, i) => {
      const x = toX(i), y = toY(d.val);
      ctx.beginPath(); ctx.arc(x, y, 5, 0, Math.PI * 2);
      ctx.fillStyle = '#fff'; ctx.fill();
      ctx.strokeStyle = '#4f46e5'; ctx.lineWidth = 2.5; ctx.stroke();

      // Value label
      ctx.fillStyle = '#0f172a'; ctx.font = '600 11px Plus Jakarta Sans'; ctx.textAlign = 'center';
      ctx.fillText(d.val, x, y - 10);

      // Name label
      ctx.fillStyle = '#94a3b8'; ctx.font = '10px IBM Plex Mono';
      ctx.fillText(d.name, x, H - 8);
    });
  })();
</script>