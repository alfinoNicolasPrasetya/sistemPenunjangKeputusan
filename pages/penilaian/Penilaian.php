<?php
// Memanggil file koneksi
require 'config/DBConfig.php';
$pageTitle = 'Penilaian';
$successMsg = '';
$errorMsg = '';

if ($conn && isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
  $deleteId = (int) $_GET['id'];
  try {
    $stmt = $conn->prepare("DELETE FROM penilaian WHERE id_siswa = ?");
    $stmt->execute([$deleteId]);
    $successMsg = 'Penilaian berhasil dihapus.';
  } catch (\PDOException $e) {
    $errorMsg = 'Gagal menghapus penilaian: ' . $e->getMessage();
  }
}

function getListPenilaian(PDO $conn): array
{
  try {
    $sql = "SELECT 
    s.id_siswa,
    s.name AS siswa,
    s.asal_sekolah,
    s.jenis_kelamin AS jk,
    s.tinggi_badan AS tinggi,
    s.berat_badan AS berat,
    MAX(p.created_at) AS tgl,
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
    GROUP BY 
        s.id_siswa,
        s.name,
        s.asal_sekolah,
        s.jenis_kelamin,
        s.tinggi_badan,
        s.berat_badan,
        k.total_kriteria
    ORDER BY s.name ASC;";
    $stmt = $conn->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (\PDOException $e) {
    global $errors;
    $errors[] = 'Database error: ' . $e->getMessage();
    return [];
  }
}

// Pastikan koneksi ada
if ($conn) {

  // Total siswa
  $jumlahSiswa = getCount(
    $conn,
    "SELECT COUNT(*) as jumlah_siswa FROM siswa",
    "jumlah_siswa"
  );

  // Total kriteria
  $jumlahKriteria = getCount(
    $conn,
    "SELECT COUNT(*) as jumlah_kriteria FROM kriteria",
    "jumlah_kriteria"
  );

  // Jumlah siswa belum dinilai
  $jumlahBelum = getCount(
    $conn,
    "SELECT COUNT(*) as jumlah_belum
      FROM siswa s
      LEFT JOIN penilaian p ON p.id_siswa = s.id_siswa
      WHERE p.id_penilaian IS NULL",
    "jumlah_belum"
  );

  // Jumlah siswa sudah dinilai
  $jumlahSudah = getCount(
    $conn,
    "SELECT COUNT(DISTINCT s.id_siswa) as jumlah_sudah
      FROM siswa s
      INNER JOIN penilaian p ON p.id_siswa = s.id_siswa",
    "jumlah_sudah"
  );

  $penilaianList = getListPenilaian($conn);
}

if (isset($_GET['success'])) {
  if ($_GET['success'] === 'added') {
    $successMsg = 'Penilaian berhasil ditambahkan.';
  } elseif ($_GET['success'] === 'edited') {
    $successMsg = 'Penilaian berhasil diperbarui.';
  }
}

if (isset($_GET['error'])) {
  $errorMsg = htmlspecialchars($_GET['error']);
}

if ($successMsg || $errorMsg) {
  $type = $successMsg ? 'success' : 'error';
  $title = $successMsg ?: 'Error';
  $text = $successMsg ?: $errorMsg;
  setAlert($type, $title, $text);
}
?>

<div class="page-header">
  <div>
    <h1 class="page-title">Penilaian Siswa</h1>
    <p class="page-subtitle">Input nilai kriteria per siswa sebagai data masukan fuzzy</p>
  </div>
  <a href="app.php?page=penilaian/add&action=add" class="btn btn-primary">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round"
        d="M16.862 4.487 18.549 2.8a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
    </svg>
    Input Nilai
  </a>
</div>

<!-- Progress bar overall -->
<div class="card" style="margin-bottom:24px;padding:0;overflow:hidden;">
  <div style="padding:18px 24px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
    <div>
      <div style="font-size:13px;font-weight:600;color:var(--text);margin-bottom:2px;">Progress Penilaian</div>
      <div style="font-size:12px;color:var(--text-muted);">
        <?= $jumlahSudah ?> dari
        <?= $jumlahSiswa ?> siswa sudah dinilai
      </div>
    </div>
    <div style="font-size:22px;font-weight:700;color:var(--primary);font-family:'IBM Plex Mono',monospace;">
      <?= round($jumlahSudah / $jumlahSiswa * 100) ?>%
    </div>
  </div>
  <div style="height:6px;background:var(--border);margin:0;">
    <div
      style="height:100%;width:<?= round($jumlahSudah / $jumlahSiswa * 100) ?>%;background:linear-gradient(90deg,var(--primary),#7c3aed);transition:width .6s ease;">
    </div>
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
    <div><span class="stat-card-value">
        <?= $jumlahSiswa ?>
      </span><span class="stat-card-label">Total Siswa</span></div>
  </div>
  <div class="stat-card" style="--icon-color:var(--success)">
    <div class="stat-card-icon">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round"
          d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
      </svg>
    </div>
    <div><span class="stat-card-value">
        <?= $jumlahSudah ?>
      </span><span class="stat-card-label">Sudah Dinilai</span></div>
  </div>
  <div class="stat-card" style="--icon-color:var(--warning)">
    <div class="stat-card-icon">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
      </svg>
    </div>
    <div><span class="stat-card-value">
        <?= $jumlahBelum ?>
      </span><span class="stat-card-label">Belum Dinilai</span></div>
  </div>
  <div class="stat-card" style="--icon-color:var(--info)">
    <div class="stat-card-icon">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round"
          d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
      </svg>
    </div>
    <div><span class="stat-card-value"><?= $jumlahKriteria ?></span><span class="stat-card-label">Kriteria
        Aktif</span></div>
  </div>
</div>

<!-- Tabel -->
<div class="card">
  <div class="card-header">
    <div class="card-title">Data Penilaian</div>
    <div style="display:flex;gap:8px;">
      <div style="position:relative;">
        <input type="text" class="form-control" placeholder="Cari siswa..."
          style="padding:8px 13px 8px 36px;font-size:13px;width:200px;" oninput="filterPenilaian(this.value)">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
          style="width:15px;height:15px;position:absolute;left:11px;top:50%;transform:translateY(-50%);color:var(--text-muted);pointer-events:none;">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 15.803a7.5 7.5 0 0 0 10.607 0Z" />
        </svg>
      </div>
    </div>
  </div>
  <?php if (empty($penilaianList)): ?>
    <div class="empty-state">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
        class="size-6">
        <path stroke-linecap="round" stroke-linejoin="round"
          d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
      </svg>

      <p class="empty-state-title">Belum ada data penilaian</p>
      <p class="empty-state-desc">Klik tombol <strong>Tambah Penilaian</strong> untuk menambahkan data penilaian baru.
      </p>
    </div>
  <?php else: ?>
    <div class="table-wrapper" style="border:none;border-radius:0;">
      <table class="data-table" id="penilaianTable">
        <thead>
          <tr>
            <th class="td-no">#</th>
            <th>Siswa</th>
            <th>Asal Sekolah</th>
            <th>Status</th>
            <th>Tanggal Input</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($penilaianList as $i => $p):
            $isDone = $p['status'] === 'Sudah Dinilai';
            ?>
            <tr>
              <td class="td-no">
                <?= $i + 1 ?>
              </td>
              <td>
                <div class="user-cell">
                  <div class="user-initials" style="width:32px;height:32px;font-size:11px;">
                    <?= strtoupper(substr($p['siswa'], 0, 2)) ?>
                  </div>
                  <div>
                    <div class="user-name">
                      <?= $p['siswa'] ?>
                    </div>
                    <div class="user-email">
                      <?= $p['jk'] === 'L' ? 'Laki-laki' : 'Perempuan' ?>
                    </div>
                  </div>
                </div>
              </td>
              <td><span style="font-size:12.5px;font-weight:600;color:var(--text-muted);">
                  <?= $p['asal_sekolah'] ?>
                </span></td>
              <td>
                <span style="
                            display:inline-flex;align-items:center;gap:5px;
                            padding:4px 10px;border-radius:20px;font-size:11.5px;font-weight:600;
                            background:<?= $isDone ? '#ecfdf5' : '#fffbeb' ?>;
                            color:<?= $isDone ? '#166534' : '#92400e' ?>;
                            border:1px solid <?= $isDone ? '#a7f3d0' : '#fde68a' ?>;">
                  <span
                    style="width:6px;height:6px;border-radius:50%;background:<?= $isDone ? '#10b981' : '#f59e0b' ?>;"></span>
                  <?= $p['status'] ?>
                </span>
              </td>
              <td style="font-size:12.5px;color:var(--text-muted);">
                <?= $p['tgl'] ? date('d M Y', strtotime($p['tgl'])) : '<span style="color:var(--text-light)">—</span>' ?>
              </td>
              <td>
                <div class="action-group">
                  <a href="app.php?page=penilaian/edit&action=edit&id=<?= $p['id_siswa'] ?>&isEdit=<?= $isDone ?>"
                    class="btn-edit">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                      stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round"
                        d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                    </svg>
                    <?= $isDone ? 'Edit Nilai' : 'Input Nilai' ?>
                  </a>
                  <?php if ($isDone): ?>
                    <button class="btn-delete" onclick="confirmHapus(<?= $p['id_siswa'] ?>)">
                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                          d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                      </svg>
                      Hapus
                    </button>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<!-- Modal Delete -->
<div class="modal-overlay" id="modalDelete">
  <div class="modal-box">
    <div class="modal-icon">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round"
          d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
      </svg>
    </div>
    <h3 class="modal-title">Hapus Nilai Penilaian?</h3>
    <p class="modal-desc">Data nilai siswa akan dihapus. Anda perlu menginput ulang jika ingin memproses perhitungan
      fuzzy.</p>
    <div class="modal-actions">
      <button class="btn-outline" onclick="closeModal('modalDelete')">Batal</button>
      <a href="#" class="btn-danger" id="deleteConfirmBtn">Ya, Hapus</a>
    </div>
  </div>
</div>

<script>
  function openModal(id) { document.getElementById(id).classList.add('is-open'); }
  function closeModal(id) { document.getElementById(id).classList.remove('is-open'); }
  document.querySelectorAll('.modal-overlay').forEach(m => {
    m.addEventListener('click', e => { if (e.target === m) m.classList.remove('is-open'); });
  });

  function confirmHapus(id) {
    openModal('modalDelete');
    const btn = document.getElementById('deleteConfirmBtn');
    btn.href = `app.php?page=penilaian&action=delete&id=${id}`;
  }

  function updateSlider(field, val) {
    document.getElementById('slider' + field.charAt(0).toUpperCase() + field.slice(1)).value = val;
  }

  function filterPenilaian(q) {
    const rows = document.querySelectorAll('#penilaianTable tbody tr');
    rows.forEach(row => {
      const text = row.textContent.toLowerCase();
      row.style.display = text.includes(q.toLowerCase()) ? '' : 'none';
    });
  }
</script>