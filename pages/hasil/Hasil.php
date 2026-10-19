<?php
// mengambil koneksi
require_once 'config/DBConfig.php';

$pageTitle = 'Hasil Perhitungan';

$kategoriMeta = [
  'Sangat Layak' => ['bg' => '#eff6ff', 'text' => '#1e40af', 'border' => '#bfdbfe', 'bar' => '#3b82f6', 'dot' => '#3b82f6'],
  'Layak' => ['bg' => '#f0fdf4', 'text' => '#166534', 'border' => '#bbf7d0', 'bar' => '#10b981', 'dot' => '#10b981'],
  'Cukup Layak' => ['bg' => '#fffbeb', 'text' => '#92400e', 'border' => '#fde68a', 'bar' => '#f59e0b', 'dot' => '#f59e0b'],
  'Tidak Layak' => ['bg' => '#fef2f2', 'text' => '#991b1b', 'border' => '#fecaca', 'bar' => '#ef4444', 'dot' => '#ef4444'],
];


if ($conn) {
  $stmt = $conn->query("SELECT 
	  hp.id_hasil, 
	  s.name as siswa, 
	  s.asal_sekolah as asal_sekolah,
	  s.jenis_kelamin as jk , 
	  s.tinggi_badan as tinggi,
	  s.berat_badan as berat,
    hp.nilai_defuzzifikasi as defuzz,
    hp.kategori,
    hp.tanggal_hitung as tgl
  FROM hasil_perhitungan hp 
  INNER JOIN siswa s ON s.id_siswa = hp.id_siswa"
  );
  $hasilList = $stmt->fetchAll();
}

$groups = array_count_values(array_column($hasilList, 'kategori'));
$avgDefuzz = round(array_sum(array_column($hasilList, 'defuzz')) / count($hasilList), 2);
?>

<div class="page-header">
  <div>
    <h1 class="page-title">Hasil Perhitungan</h1>
    <p class="page-subtitle">Output defuzzifikasi weighted average Fuzzy Sugeno</p>
  </div>
  <div style="display:flex;gap:8px;">
    <!-- <button class="btn-outline" onclick="window.print()"
      style="display:inline-flex;align-items:center;gap:7px;padding:9px 16px;border-radius:9px;font-size:13.5px;font-weight:600;cursor:pointer;background:none;border:1.5px solid var(--border);color:var(--text);text-decoration:none;">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
        style="width:16px;height:16px;">
        <path stroke-linecap="round" stroke-linejoin="round"
          d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
      </svg>
      Cetak --
    </button> -->
    <button class="btn btn-primary" onclick="openModal('modalExport')">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round"
          d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
      </svg>
      Export
    </button>
  </div>
</div>

<!-- Summary Stats -->
<div class="stats-grid hidden-print" style="margin-bottom:24px;">
  <div class="stat-card" style="--icon-color:var(--primary)">
    <div class="stat-card-icon">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round"
          d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
      </svg>
    </div>
    <div><span class="stat-card-value">
        <?= count($hasilList) ?>
      </span><span class="stat-card-label">Total Dihitung</span></div>
  </div>
  <div class="stat-card" style="--icon-color:var(--info)">
    <div class="stat-card-icon">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round"
          d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
      </svg>
    </div>
    <div><span class="stat-card-value">
        <?= $avgDefuzz ?>
      </span><span class="stat-card-label">Rata-rata Defuzz</span></div>
  </div>
  <div class="stat-card" style="--icon-color:var(--success)">
    <div class="stat-card-icon">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round"
          d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
      </svg>
    </div>
    <div><span class="stat-card-value">
        <?= $groups['Sangat Layak'] ?? 0 ?>
      </span><span class="stat-card-label">Sangat Layak</span></div>
  </div>
  <div class="stat-card" style="--icon-color:var(--danger)">
    <div class="stat-card-icon">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round"
          d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
      </svg>
    </div>
    <div><span class="stat-card-value">
        <?= $groups['Tidak Layak'] ?? 0 ?>
      </span><span class="stat-card-label">Tidak Layak</span></div>
  </div>
</div>

<!-- Distribusi Visual -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:24px; " class="hidden-print">
  <!-- Ranking Chart -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">Distribusi Kategori</div>
    </div>
    <div class="card-body">
      <?php
      $total = count($hasilList);
      foreach ($kategoriMeta as $kat => $meta):
        $jumlah = $groups[$kat] ?? 0;
        $pct = $total > 0 ? round($jumlah / $total * 100) : 0;
        ?>
        <div style="margin-bottom:14px;">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px;">
            <span style="font-size:13px;font-weight:600;color:var(--text);display:flex;align-items:center;gap:7px;">
              <span style="width:8px;height:8px;border-radius:50%;background:<?= $meta['dot'] ?>;"></span>
              <?= $kat ?>
            </span>
            <span style="font-size:12px;font-family:'IBM Plex Mono',monospace;color:var(--text-muted);">
              <?= $jumlah ?> siswa (
              <?= $pct ?>%)
            </span>
          </div>
          <div style="height:8px;border-radius:4px;background:var(--border);overflow:hidden;">
            <div
              style="height:100%;width:<?= $pct ?>%;background:<?= $meta['bar'] ?>;border-radius:4px;transition:width .6s ease;">
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Top Ranking -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">Peringkat Tertinggi</div>
    </div>
    <div class="card-body" style="padding:0;">
      <?php
      $sorted = $hasilList;
      usort($sorted, fn($a, $b) => $b['defuzz'] <=> $a['defuzz']);
      foreach (array_slice($sorted, 0, 3) as $ri => $r):
        $meta = $kategoriMeta[$r['kategori']] ?? $kategoriMeta['Layak'];
        $medals = ['🥇', '🥈', '🥉'];
        ?>
        <div style="display:flex;align-items:center;gap:12px;padding:14px 20px;border-bottom:1px solid var(--border);">
          <span style="font-size:22px;">
            <?= $medals[$ri] ?>
          </span>
          <div style="flex:1;">
            <div style="font-weight:600;font-size:13.5px;color:var(--text);">
              <?= $r['siswa'] ?>
            </div>
            <div style="font-size:12px;color:var(--text-muted);">
              <?= $r['asal_sekolah'] ?>
            </div>
          </div>
          <div style="text-align:right;">
            <div style="font-family:'IBM Plex Mono',monospace;font-size:16px;font-weight:700;color:var(--text);">
              <?= $r['defuzz'] ?>
            </div>
            <span
              style="padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600;background:<?= $meta['bg'] ?>;color:<?= $meta['text'] ?>;border:1px solid <?= $meta['border'] ?>;">
              <?= $r['kategori'] ?>
            </span>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- Tabel Detail -->
<div class="card">
  <div class="card-header">
    <div class="card-title">Detail Hasil Perhitungan</div>
    <div style="position:relative;">
      <input type="text" class="form-control" placeholder="Cari siswa..."
        style="padding:8px 13px 8px 36px;font-size:13px;width:200px;" oninput="filterHasil(this.value)">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
        style="width:15px;height:15px;position:absolute;left:11px;top:50%;transform:translateY(-50%);color:var(--text-muted);pointer-events:none;">
        <path stroke-linecap="round" stroke-linejoin="round"
          d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 15.803a7.5 7.5 0 0 0 10.607 0Z" />
      </svg>
    </div>
  </div>
  <div class="table-wrapper" style="border:none;border-radius:0;">
    <table class="data-table" id="hasilTable">
      <thead>
        <tr>
          <th class="td-no">Rank</th>
          <th>Siswa</th>
          <th>Asal Sekolah</th>
          <th>Tinggi (cm)</th>
          <th>Berat (kg)</th>
          <th>Nilai Defuzz</th>
          <th>Kategori</th>
          <th>Tanggal</th>
          <!-- <th>Aksi</th> -->
        </tr>
      </thead>
      <tbody>
        <?php
        $ranked = $hasilList;
        usort($ranked, fn($a, $b) => $b['defuzz'] <=> $a['defuzz']);
        foreach ($ranked as $ri => $h):
          $meta = $kategoriMeta[$h['kategori']] ?? $kategoriMeta['Layak'];
          $pct = $h['defuzz'];
          ?>
          <tr>
            <td class="td-no">
              <span style="font-weight:700;color:<?= $ri < 3 ? $meta['dot'] : 'var(--text-muted)' ?>;">
                <?= $ri + 1 ?>
              </span>
            </td>
            <td>
              <div class="user-cell">
                <div class="user-initials" style="width:32px;height:32px;font-size:11px;">
                  <?= strtoupper(substr($h['siswa'], 0, 2)) ?>
                </div>
                <div>
                  <div class="user-name">
                    <?= $h['siswa'] ?>
                  </div>
                  <div class="user-email">
                    <?= $h['jk'] === 'L' ? 'Laki-laki' : 'Perempuan' ?>
                  </div>
                </div>
              </div>
            </td>
            <td style="font-size:12.5px;color:var(--text-muted);font-weight:600;">
              <?= $h['asal_sekolah'] ?>
            </td>
            <td style="font-family:'IBM Plex Mono',monospace;font-size:13px;">
              <?= $h['tinggi'] ?>
            </td>
            <td style="font-family:'IBM Plex Mono',monospace;font-size:13px;">
              <?= $h['berat'] ?>
            </td>
            <td>
              <div style="display:flex;align-items:center;gap:10px;">
                <div style="flex:1;max-width:80px;">
                  <div style="height:5px;border-radius:3px;background:var(--border);overflow:hidden;">
                    <div style="height:100%;width:<?= $pct ?>%;background:<?= $meta['bar'] ?>;border-radius:3px;"></div>
                  </div>
                </div>
                <span style="font-family:'IBM Plex Mono',monospace;font-size:14px;font-weight:700;color:var(--text);">
                  <?= $h['defuzz'] ?>
                </span>
              </div>
            </td>
            <td>
              <span
                style="padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;background:<?= $meta['bg'] ?>;color:<?= $meta['text'] ?>;border:1px solid <?= $meta['border'] ?>;">
                <?= $h['kategori'] ?>
              </span>
            </td>
            <td style="font-size:12.5px;color:var(--text-muted);">
              <?= date('d M Y', strtotime($h['tgl'])) ?>
            </td>
            <!-- <td>
              <button class="btn-edit" onclick="openModal('modalDetail', <?= $h['id_hasil'] ?>)"
                style="white-space:nowrap;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
                Detail
              </button>
            </td> -->
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

Modal Detail Perhitungan
<!-- <div class="modal-overlay" id="modalDetail"> -->
  <!-- <div class="modal-box" style="max-width:520px;">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
      <div
        style="width:44px;height:44px;border-radius:12px;background:var(--primary-light);color:var(--primary);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
          style="width:22px;height:22px;">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
        </svg>
      </div>
      <div>
        <div class="modal-title" style="margin:0 0 2px;">Detail Proses Fuzzy Sugeno</div>
        <div style="font-size:13px;color:var(--text-muted);">Dian Pratama — X IPA 2</div>
      </div>
    </div> -->

    <!-- <div style="display:flex;flex-direction:column;gap:12px;"> -->
      <!-- Step 1 -->
      <!-- <div style="border:1.5px solid var(--border);border-radius:10px;overflow:hidden;">
        <div
          style="padding:10px 16px;background:var(--surface-2);border-bottom:1px solid var(--border);display:flex;align-items:center;gap:8px;">
          <span
            style="width:22px;height:22px;border-radius:50%;background:var(--primary);color:#fff;font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;">1</span>
          <span style="font-size:12px;font-weight:700;color:var(--text);">Fuzzifikasi</span>
        </div>
        <div style="padding:12px 16px;font-size:12.5px;">
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
            <div style="background:var(--surface-2);border-radius:7px;padding:10px;">
              <div style="color:var(--text-muted);font-size:11px;margin-bottom:4px;">Tinggi Badan (182 cm)</div>
              <div style="display:flex;flex-direction:column;gap:3px;">
                <span style="font-family:'IBM Plex Mono',monospace;font-size:12px;color:#10b981;">μ Tinggi = 0.80</span>
                <span style="font-family:'IBM Plex Mono',monospace;font-size:12px;color:#94a3b8;">μ Sedang = 0.20</span>
              </div>
            </div>
            <div style="background:var(--surface-2);border-radius:7px;padding:10px;">
              <div style="color:var(--text-muted);font-size:11px;margin-bottom:4px;">Berat Badan (75 kg)</div>
              <div style="display:flex;flex-direction:column;gap:3px;">
                <span style="font-family:'IBM Plex Mono',monospace;font-size:12px;color:#10b981;">μ Normal = 1.00</span>
                <span style="font-family:'IBM Plex Mono',monospace;font-size:12px;color:#94a3b8;">μ Gemuk = 0.00</span>
              </div>
            </div>
          </div>
        </div>
      </div>
      Step 2 -->
      <!-- <div style="border:1.5px solid var(--border);border-radius:10px;overflow:hidden;">
        <div
          style="padding:10px 16px;background:var(--surface-2);border-bottom:1px solid var(--border);display:flex;align-items:center;gap:8px;">
          <span
            style="width:22px;height:22px;border-radius:50%;background:var(--primary);color:#fff;font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;">2</span>
          <span style="font-size:12px;font-weight:700;color:var(--text);">Inferensi (Evaluasi Rule)</span>
        </div>
        <div style="padding:12px 16px;font-size:12.5px;">
          <div style="font-family:'IBM Plex Mono',monospace;font-size:11.5px;line-height:1.8;color:var(--text);">
            R3: min(0.80, 1.00) = <strong style="color:#10b981;">0.80</strong> → z=85<br>
            R2: min(0.20, 1.00) = <strong style="color:#f59e0b;">0.20</strong> → z=70
          </div>
        </div>
      </div>
      Step 3 -->
      <!-- <div style="border:1.5px solid #bbf7d0;border-radius:10px;overflow:hidden;background:#f0fdf4;">
        <div
          style="padding:10px 16px;background:#dcfce7;border-bottom:1px solid #bbf7d0;display:flex;align-items:center;gap:8px;">
          <span
            style="width:22px;height:22px;border-radius:50%;background:#16a34a;color:#fff;font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;">3</span>
          <span style="font-size:12px;font-weight:700;color:#166534;">Defuzzifikasi (Weighted Average)</span>
        </div>
        <div style="padding:14px 16px;">
          <div
            style="font-family:'IBM Plex Mono',monospace;font-size:12px;color:#166534;line-height:1.8;margin-bottom:10px;">
            z = (0.80×85 + 0.20×70) / (0.80 + 0.20)<br>
            z = (68 + 14) / 1.00<br>
            z = <strong style="font-size:15px;">82.00</strong>
          </div>
          <span
            style="padding:5px 14px;border-radius:20px;font-size:13px;font-weight:700;background:#eff6ff;color:#1e40af;border:1px solid #bfdbfe;">Sangat
            Layak</span>
        </div>
      </div>
    </div> -->

    <!-- <div style="margin-top:16px;text-align:right;">
      <button class="btn-outline" onclick="closeModal('modalDetail')">Tutup</button>
    </div>
  </div>
</div> -->

<!-- Modal Export -->
<div class="modal-overlay" id="modalExport">
  <div class="modal-box" style="max-width:380px;">
    <div class="modal-title" style="margin-bottom:6px;">Export Hasil</div>
    <p class="modal-desc" style="margin-bottom:16px;">Pilih format export yang diinginkan</p>
    <div style="display:flex;flex-direction:column;gap:8px;margin-bottom:20px;">
      <?php foreach ([['tipe' => 'PDF', 'format' => 'pdf'], ['tipe' => 'CSV', 'format' => 'csv']] as $item): ?>
        <button
          style="display:flex;align-items:center;gap:12px;padding:12px 16px;border-radius:9px;border:1.5px solid var(--border);background:var(--surface);cursor:pointer;text-align:left;transition:border-color .2s,background .2s;"
          onmouseover="this.style.borderColor='var(--primary)';this.style.background='var(--primary-light)'"
          onmouseout="this.style.borderColor='var(--border)';this.style.background='var(--surface)'"
          onclick="window.location.href='config/export.php?format=<?= urlencode($item['format']) ?>'">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
            style="width:20px;height:20px;color:var(--primary);flex-shrink:0;">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
          </svg>
          <span style="font-size:13.5px;font-weight:600;color:var(--text);">
            <?= $item['tipe'] ?>
          </span>
        </button>
      <?php endforeach; ?>
    </div>
    <div style="text-align:right;"><button class="btn-outline" onclick="closeModal('modalExport')">Batal</button></div>
  </div>
</div>

<script>
  function openModal(id) { document.getElementById(id).classList.add('is-open'); }
  function closeModal(id) { document.getElementById(id).classList.remove('is-open'); }
  document.querySelectorAll('.modal-overlay').forEach(m => {
    m.addEventListener('click', e => { if (e.target === m) m.classList.remove('is-open'); });
  });
  function filterHasil(q) {
    document.querySelectorAll('#hasilTable tbody tr').forEach(tr => {
      tr.style.display = tr.textContent.toLowerCase().includes(q.toLowerCase()) ? '' : 'none';
    });
  }
</script>