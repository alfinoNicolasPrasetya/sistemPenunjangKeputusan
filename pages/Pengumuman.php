<?php
require_once __DIR__ . '/../config/appConfig.php';
require_once __DIR__ . '/../config/DBConfig.php';

$userId = $currentUser['id'] ?? 0;
$userName = $currentUser['name'] ?? 'Siswa';
$userEmail = $currentUser['email'] ?? 'Tidak Berhasil ambil email';
$userRole = $currentUser['id_role'] ?? 3;

$stmt = $conn->prepare("
  SELECT hp.*, s.name AS name_siswa, s.asal_sekolah, s.jenis_kelamin
    FROM hasil_perhitungan hp
    JOIN siswa s ON s.id_siswa = hp.id_siswa
    WHERE s.email = :user_email LIMIT 1
");
$stmt->execute([':user_email' => $userEmail]);
$hasil = $stmt->fetch(PDO::FETCH_ASSOC);

$belumAda = empty($hasil);
define('THRESHOLD_LULUS', 60);

if (!$belumAda) {
  $nilaiFuzzy = (float) $hasil['nilai_defuzzifikasi'];
  $isLulus = $nilaiFuzzy >= THRESHOLD_LULUS;
  $statusText = $isLulus ? 'LULUS SELEKSI' : 'TIDAK LULUS SELEKSI';
  $namaLengkap = htmlspecialchars($hasil['name_siswa']);
  $asalSekolah = htmlspecialchars($hasil['asal_sekolah']);
  $jenisKelamin = $hasil['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan';
  $tanggal = date('d F Y', strtotime($hasil['tanggal_hitung']));
}
?>

<style>
  .pengumuman-wrapper {
    max-width: 760px;
    margin: 0 auto;
    padding-bottom: 40px;
  }

  /* ── Hero Result Card ── */
  .result-hero {
    position: relative;
    border-radius: 20px;
    overflow: hidden;
    margin-bottom: 24px;
    border: 1.5px solid var(--border);
    background: var(--surface);
  }

  .result-hero-bg {
    position: absolute;
    inset: 0;
    opacity: 0.04;
    background-size: 30px 30px;
    background-image:
      linear-gradient(to right, #64748b 1px, transparent 1px),
      linear-gradient(to bottom, #64748b 1px, transparent 1px);
    pointer-events: none;
  }

  .result-hero.is-lulus {
    border-color: #a7f3d0;
  }

  .result-hero.is-lulus .result-hero-accent {
    background: linear-gradient(135deg, #059669, #10b981);
  }

  .result-hero.is-lulus .result-status-badge {
    background: #ecfdf5;
    color: #065f46;
    border-color: #a7f3d0;
  }

  .result-hero.is-lulus .result-icon-wrap {
    background: linear-gradient(135deg, #059669, #10b981);
    box-shadow: 0 8px 32px rgba(16, 185, 129, .35);
  }

  .result-hero.is-lulus .result-score-ring {
    --ring-color: #10b981;
  }

  .result-hero.is-gagal {
    border-color: #fecaca;
  }

  .result-hero.is-gagal .result-hero-accent {
    background: linear-gradient(135deg, #dc2626, #ef4444);
  }

  .result-hero.is-gagal .result-status-badge {
    background: #fef2f2;
    color: #991b1b;
    border-color: #fecaca;
  }

  .result-hero.is-gagal .result-icon-wrap {
    background: linear-gradient(135deg, #dc2626, #ef4444);
    box-shadow: 0 8px 32px rgba(239, 68, 68, .35);
  }

  .result-hero.is-gagal .result-score-ring {
    --ring-color: #ef4444;
  }

  .result-hero.is-pending {
    border-color: #fde68a;
  }

  .result-hero.is-pending .result-hero-accent {
    background: linear-gradient(135deg, #b45309, #f59e0b);
  }

  .result-hero.is-pending .result-status-badge {
    background: #fffbeb;
    color: #92400e;
    border-color: #fde68a;
  }

  .result-hero.is-pending .result-icon-wrap {
    background: linear-gradient(135deg, #b45309, #f59e0b);
    box-shadow: 0 8px 32px rgba(245, 158, 11, .35);
  }

  .result-hero-accent {
    height: 5px;
    width: 100%;
  }

  .result-hero-body {
    padding: 32px 32px 28px;
    display: flex;
    gap: 28px;
    align-items: center;
  }

  @media (max-width: 600px) {
    .result-hero-body {
      flex-direction: column;
      text-align: center;
      padding: 24px 20px;
      gap: 20px;
    }
  }

  .result-icon-wrap {
    width: 80px;
    height: 80px;
    border-radius: 22px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }

  .result-icon-wrap svg {
    width: 42px;
    height: 42px;
    color: #fff;
  }

  .result-hero-text {
    flex: 1;
  }

  .result-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 14px;
    border-radius: 100px;
    font-size: 11.5px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    border: 1.5px solid transparent;
    margin-bottom: 12px;
  }

  .result-status-badge svg {
    width: 13px;
    height: 13px;
  }

  .result-hero-name {
    font-size: 26px;
    font-weight: 800;
    color: var(--text);
    line-height: 1.2;
    margin-bottom: 6px;
  }

  .result-hero-meta {
    font-size: 13px;
    color: var(--text-muted);
    display: flex;
    flex-wrap: wrap;
    gap: 14px;
  }

  .result-hero-meta span {
    display: flex;
    align-items: center;
    gap: 5px;
  }

  .result-hero-meta svg {
    width: 13px;
    height: 13px;
    color: var(--text-light);
  }

  .result-score-wrap {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    flex-shrink: 0;
  }

  .score-ring-svg {
    width: 90px;
    height: 90px;
    transform: rotate(-90deg);
  }

  .score-ring-bg {
    fill: none;
    stroke: var(--border);
    stroke-width: 6;
  }

  .score-ring-fill {
    fill: none;
    stroke: var(--ring-color, #6366f1);
    stroke-width: 6;
    stroke-linecap: round;
    stroke-dasharray: 220;
    stroke-dashoffset: var(--dash-offset, 220);
    transition: stroke-dashoffset 1.2s cubic-bezier(.4, 0, .2, 1);
  }

  .score-number {
    font-size: 22px;
    font-weight: 800;
    fill: var(--text);
    font-family: "IBM Plex Mono", monospace;
    dominant-baseline: central;
    text-anchor: middle;
  }

  .score-label {
    font-size: 11px;
    color: var(--text-muted);
    font-weight: 600;
    letter-spacing: 0.05em;
    text-transform: uppercase;
  }

  /* ── Info cards row ── */
  .info-row {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 14px;
    margin-bottom: 24px;
  }

  .info-chip {
    background: var(--surface);
    border: 1.5px solid var(--border);
    border-radius: 14px;
    padding: 16px 18px;
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .info-chip-icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    background: var(--primary-light);
    color: var(--primary);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }

  .info-chip-icon svg {
    width: 18px;
    height: 18px;
  }

  .info-chip-label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    color: var(--text-muted);
    margin-bottom: 2px;
  }

  .info-chip-value {
    font-size: 14px;
    font-weight: 700;
    color: var(--text);
  }

  /* ── Detail nilai card ── */
  .detail-card {
    background: var(--surface);
    border: 1.5px solid var(--border);
    border-radius: 16px;
    overflow: hidden;
    margin-bottom: 20px;
  }

  .detail-card-header {
    padding: 16px 22px;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .detail-card-header svg {
    width: 18px;
    height: 18px;
    color: var(--primary);
  }

  .detail-card-title {
    font-size: 14px;
    font-weight: 700;
    color: var(--text);
  }

  .kriteria-list {
    padding: 8px 0;
  }

  .kriteria-row {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 22px;
    border-bottom: 1px solid var(--border);
    transition: background var(--transition);
  }

  .kriteria-row:last-child {
    border-bottom: none;
  }

  .kriteria-row:hover {
    background: var(--surface-2);
  }

  .kriteria-name {
    flex: 1;
    font-size: 13.5px;
    font-weight: 600;
    color: var(--text);
  }

  .kriteria-bobot {
    font-size: 11.5px;
    font-family: "IBM Plex Mono", monospace;
    color: var(--text-muted);
    background: var(--surface-2);
    padding: 3px 8px;
    border-radius: 6px;
  }

  .kriteria-bar-wrap {
    width: 120px;
    flex-shrink: 0;
  }

  .kriteria-bar-track {
    height: 7px;
    background: var(--surface-2);
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 4px;
  }

  .kriteria-bar-fill {
    height: 100%;
    border-radius: 10px;
    background: linear-gradient(90deg, var(--primary), #7c3aed);
    transition: width 0.8s ease;
  }

  .kriteria-score {
    font-size: 12.5px;
    font-weight: 700;
    font-family: "IBM Plex Mono", monospace;
    color: var(--text);
    text-align: right;
    min-width: 38px;
  }

  @media (max-width: 540px) {
    .kriteria-bar-wrap {
      display: none;
    }

    .kriteria-bobot {
      display: none;
    }
  }

  /* ── Pending state ── */
  .pending-body {
    padding: 48px 24px;
    text-align: center;
  }

  .pending-body svg {
    width: 52px;
    height: 52px;
    color: var(--text-light);
    margin: 0 auto 16px;
    display: block;
  }

  .pending-title {
    font-size: 18px;
    font-weight: 700;
    color: var(--text);
    margin-bottom: 8px;
  }

  .pending-desc {
    font-size: 13.5px;
    color: var(--text-muted);
    line-height: 1.7;
  }

  /* ── Notice footer card ── */
  .notice-card {
    background: #fffbeb;
    border: 1.5px solid #fde68a;
    border-radius: 14px;
    padding: 16px 20px;
    display: flex;
    gap: 12px;
    align-items: flex-start;
  }

  .notice-card.is-lulus-notice {
    background: #ecfdf5;
    border-color: #a7f3d0;
  }

  .notice-card svg {
    width: 20px;
    height: 20px;
    flex-shrink: 0;
    margin-top: 1px;
    color: #b45309;
  }

  .notice-card.is-lulus-notice svg {
    color: #059669;
  }

  .notice-card-text {
    font-size: 13px;
    color: var(--text);
    line-height: 1.7;
  }

  .notice-card-text strong {
    color: var(--text);
  }

  /* ── Date stamp ── */
  .date-stamp {
    text-align: right;
    font-size: 12px;
    font-family: "IBM Plex Mono", monospace;
    color: var(--text-light);
    margin-top: 20px;
  }

  /* ── Animation ── */
  @keyframes slideUp {
    from {
      opacity: 0;
      transform: translateY(18px);
    }

    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .anim-1 {
    animation: slideUp 0.4s ease both;
  }

  .anim-2 {
    animation: slideUp 0.4s 0.1s ease both;
  }

  .anim-3 {
    animation: slideUp 0.4s 0.2s ease both;
  }

  .anim-4 {
    animation: slideUp 0.4s 0.3s ease both;
  }
</style>

<div class="pengumuman-wrapper">

  <!-- Page Header -->
  <div class="page-header anim-1">
    <div>
      <h1 class="page-title">Pengumuman Hasil Seleksi</h1>
      <p class="page-subtitle">Hasil perhitungan metode Fuzzy Sugeno untuk akun Anda</p>
    </div>
  </div>

  <?php if ($belumAda): ?>
    <!-- ── Belum ada data ── -->
    <div class="result-hero is-pending anim-1">
      <div class="result-hero-accent"></div>
      <div class="pending-body">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
        </svg>
        <p class="pending-title">Hasil Belum Tersedia</p>
        <p class="pending-desc">
          Proses perhitungan seleksi untuk akun Anda belum selesai dilakukan.<br>
          Silakan hubungi administrator untuk informasi lebih lanjut.
        </p>
      </div>
    </div>

  <?php else: ?>
    <!-- ══ ADA HASIL ══ -->

    <!-- ── Hero Result Card ── -->
    <div class="result-hero <?= $isLulus ? 'is-lulus' : 'is-gagal' ?> anim-1">
      <div class="result-hero-accent"></div>
      <div class="result-hero-bg"></div>
      <div class="result-hero-body">

        <!-- Icon -->
        <div class="result-icon-wrap">
          <?php if ($isLulus): ?>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
          <?php else: ?>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
          <?php endif; ?>
        </div>

        <!-- Text -->
        <div class="result-hero-text">
          <div class="result-status-badge">
            <?php if ($isLulus): ?>
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
              </svg>
              LULUS SELEKSI
            <?php else: ?>
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
              </svg>
              GAGAL SELEKSI
            <?php endif; ?>
          </div>

          <h2 class="result-hero-name"><?= $namaLengkap ?></h2>

          <div class="result-hero-meta">
            <!-- Asal Sekolah -->
            <span>
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.627 48.627 0 0 1 12 20.904a48.627 48.627 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.57 50.57 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
              </svg>
              <?= $asalSekolah ?>
            </span>
            <!-- Jenis Kelamin -->
            <span>
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
              </svg>
              <?= $jenisKelamin ?>
            </span>
          </div>
        </div>

        <!-- Score Ring -->
        <div class="result-score-wrap result-score-ring" id="scoreRingWrap">
          <?php
          $pct = min(100, max(0, $nilaiFuzzy));
          $circumf = 220;
          $dashOffset = $circumf - ($circumf * $pct / 100);
          ?>
          <svg class="score-ring-svg" viewBox="0 0 80 80">
            <circle class="score-ring-bg" cx="40" cy="40" r="35" />
            <circle class="score-ring-fill" cx="40" cy="40" r="35"
              style="--dash-offset: <?= $dashOffset ?>; stroke-dashoffset: <?= $circumf ?>;" id="scoreRingFill" />
            <text class="score-number" x="40" y="40"
              style="transform: rotate(90deg); transform-origin: 40px 40px; fill: var(--text);">
              <?= number_format($nilaiFuzzy, 1) ?>
            </text>
          </svg>
          <span class="score-label">Nilai Fuzzy</span>
        </div>

      </div><!-- /.result-hero-body -->
    </div><!-- /.result-hero -->

    <!-- ── Info Chips ── -->
    <div class="info-row anim-2">

      <div class="info-chip">
        <div class="info-chip-icon">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
          </svg>
        </div>
        <div>
          <div class="info-chip-label">Nilai Fuzzy</div>
          <div class="info-chip-value" style="font-family:'IBM Plex Mono',monospace;">
            <?= number_format($nilaiFuzzy, 4) ?>
          </div>
        </div>
      </div>

      <div class="info-chip">
        <div class="info-chip-icon" style="background:#ecfdf5; color:#059669;">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
          </svg>
        </div>
        <div>
          <div class="info-chip-label">Batas Lulus</div>
          <div class="info-chip-value" style="font-family:'IBM Plex Mono',monospace;">
            ≥ <?= THRESHOLD_LULUS ?>.0000
          </div>
        </div>
      </div>

      <div class="info-chip">
        <div class="info-chip-icon" style="background:#fffbeb; color:#b45309;">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
          </svg>
        </div>
        <div>
          <div class="info-chip-label">Tanggal Hasil</div>
          <div class="info-chip-value"><?= $tanggal ?></div>
        </div>
      </div>

      <div class="info-chip">
        <div class="info-chip-icon"
          style="background:<?= $isLulus ? '#ecfdf5; color:#059669' : '#fef2f2; color:#dc2626' ?>;">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
          </svg>
        </div>
        <div>
          <div class="info-chip-label">Status Akhir</div>
          <div class="info-chip-value" style="color: <?= $isLulus ? '#059669' : '#dc2626' ?>;">
            <?= $isLulus ? 'Layak' : 'Tidak Layak' ?>
          </div>
        </div>
      </div>

    </div><!-- /.info-row -->

    <!-- ── Detail Nilai Per Kriteria ── -->
    <?php if (!empty($detailNilai)): ?>
      <div class="detail-card anim-3">
        <div class="detail-card-header">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
          </svg>
          <span class="detail-card-title">Rincian Nilai Per Kriteria</span>
        </div>
        <div class="kriteria-list">
          <?php foreach ($detailNilai as $idx => $row):
            $nilaiInput = (float) $row['nilai_input'];
            $nilaiFuzzyK = (float) $row['nilai_fuzzy_kriteria'];
            $bobot = (float) $row['bobot'];
            $barWidth = min(100, max(0, $nilaiFuzzyK));
            $barColors = [
              'linear-gradient(90deg, #4f46e5, #7c3aed)',
              'linear-gradient(90deg, #0891b2, #0ea5e9)',
              'linear-gradient(90deg, #059669, #10b981)',
              'linear-gradient(90deg, #b45309, #f59e0b)',
              'linear-gradient(90deg, #dc2626, #f97316)',
            ];
            $barColor = $barColors[$idx % count($barColors)];
            ?>
            <div class="kriteria-row">
              <div class="kriteria-name"><?= htmlspecialchars($row['nama_kriteria']) ?></div>
              <div class="kriteria-bobot">Bobot: <?= number_format($bobot * 100, 0) ?>%</div>
              <div class="kriteria-bar-wrap">
                <div class="kriteria-bar-track">
                  <div class="kriteria-bar-fill" style="width: <?= $barWidth ?>%; background: <?= $barColor ?>;"
                    data-width="<?= $barWidth ?>"></div>
                </div>
                <div style="font-size:10px; color:var(--text-light); font-family:'IBM Plex Mono',monospace;">
                  Input: <?= number_format($nilaiInput, 1) ?>
                </div>
              </div>
              <div class="kriteria-score"><?= number_format($nilaiFuzzyK, 2) ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

    <!-- ── Notice ── -->
    <div class="notice-card <?= $isLulus ? 'is-lulus-notice' : '' ?> anim-4">
      <?php if ($isLulus): ?>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
        </svg>
        <div class="notice-card-text">
          <strong>Selamat!</strong> Anda dinyatakan <strong>layak</strong> berdasarkan hasil
          perhitungan metode Fuzzy Sugeno dengan nilai <strong><?= number_format($nilaiFuzzy, 4) ?></strong>
          yang melampaui batas minimum kelulusan (<strong><?= THRESHOLD_LULUS ?></strong>).
          Harap menunggu informasi lebih lanjut dari pihak sekolah.
        </div>
      <?php else: ?>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
        </svg>
        <div class="notice-card-text">
          Mohon maaf, Anda dinyatakan <strong>tidak layak</strong> berdasarkan hasil
          perhitungan metode Fuzzy Sugeno dengan nilai <strong><?= number_format($nilaiFuzzy, 4) ?></strong>
          yang berada di bawah batas minimum kelulusan (<strong><?= THRESHOLD_LULUS ?></strong>).
          Untuk informasi lebih lanjut, silakan hubungi pihak sekolah.
        </div>
      <?php endif; ?>
    </div>

    <p class="date-stamp">
      Dikeluarkan: <?= $tanggal ?> &nbsp;·&nbsp;
      Sistem SPK Fuzzy Sugeno &nbsp;·&nbsp;
      <?= htmlspecialchars(APP_NAME ?? 'SPK') ?>
    </p>

  <?php endif; ?>

</div><!-- /.pengumuman-wrapper -->

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const fill = document.getElementById('scoreRingFill');
    if (fill) {
      const target = parseFloat(
        fill.style.getPropertyValue('--dash-offset') ||
        fill.getAttribute('style').match(/--dash-offset:\s*([\d.]+)/)?.[1] || 220
      );
      requestAnimationFrame(() => { fill.style.strokeDashoffset = target; });
    }

    document.querySelectorAll('.kriteria-bar-fill').forEach(bar => {
      const w = bar.dataset.width;
      bar.style.width = '0%';
      setTimeout(() => { bar.style.width = w + '%'; }, 200);
    });
  });
</script>