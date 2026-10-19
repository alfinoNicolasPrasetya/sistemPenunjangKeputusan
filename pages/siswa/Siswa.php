<?php
// Memanggil file koneksi
require 'config/DBConfig.php';

$successMsg = '';
$errorMsg = '';

// Proses hapus siswa
if ($conn && isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
  $deleteId = (int) $_GET['id'];
  try {
    $stmt = $conn->prepare("DELETE FROM siswa WHERE id_siswa = ?");
    $stmt->execute([$deleteId]);
    $successMsg = 'Siswa berhasil dihapus.';
  } catch (\PDOException $e) {
    $errorMsg = 'Gagal menghapus siswa: ' . $e->getMessage();
  }
}

// Ambil data users dengan join ke tabel role
if ($conn) {
  try {
    $stmt = $conn->query(
      "SELECT * FROM siswa"
    );
    $siswa = $stmt->fetchAll();
  } catch (\PDOException $e) {
    $errorMsg = 'Gagal mengambil data: ' . $e->getMessage();
  }
}

if (isset($_GET['success'])) {
  if ($_GET['success'] === 'added') {
    $successMsg = 'Siswa berhasil ditambahkan.';
  } elseif ($_GET['success'] === 'edited') {
    $successMsg = 'Siswa berhasil diperbarui.';
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
<!DOCTYPE html>
<html lang="id">

<head>
  <link rel="stylesheet" href="assets/app.css" />
</head>

<body>
  <!-- ── MAIN ── -->
  <div>
    <!-- Content -->
    <div>
      <div>

        <!-- Page Header -->
        <div class="page-header">
          <div>
            <h1 class="page-title">Manajemen Siswa</h1>
            <p class="page-subtitle">Kelola data siswa </p>
          </div>
          <a href="app.php?page=siswa/add&action=add" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Siswa
          </a>
        </div>

        <!-- Stats -->
        <div class="stats-grid" style="--icon-color: var(--primary);">
          <div class="stat-card">
            <div class="stat-card-icon" style="--icon-color: var(--primary);">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
            </div>
            <div>
              <span class="stat-card-value"><?= count($siswa) ?></span>
              <span class="stat-card-label">Total Siswa</span>
            </div>
          </div>
        </div>

        <!-- Table Card -->
        <div class="card">
          <div class="card-header">
            <span class="card-title">Daftar Siswa</span>
          </div>

          <?php if (empty($siswa)): ?>
            <div class="empty-state">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
              </svg>
              <p class="empty-state-title">Belum ada data siswa</p>
              <p class="empty-state-desc">Klik tombol <strong>Tambah Siswa</strong> untuk menambahkan siswa baru.</p>
            </div>
          <?php else: ?>
            <div class="table-wrapper">
              <table class="data-table">
                <thead>
                  <tr>
                    <th class="td-no">#</th>
                    <th>Nama</th>
                    <th>Asal Sekolah</th>
                    <th>Email</th>
                    <th>Jenis Kelamin</th>
                    <th>Tinggi Badan</th>
                    <th>Berat Badan</th>
                    <th style="text-align:center;">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($siswa as $i => $u): ?>
                    <?php
                    $initials = strtoupper(substr($u['name'] ?? '?', 0, 1));
                    if (strpos($u['name'], ' ') !== false) {
                      $parts = explode(' ', $u['name']);
                      $initials = strtoupper(substr($parts[0], 0, 1) . substr(end($parts), 0, 1));
                    }
                    $roleSlug = strtolower(str_replace(' ', '-', $u['role_name'] ?? ''));
                    ?>
                    <tr>
                      <td class="td-no"><?= $i + 1 ?></td>
                      <td>
                        <div class="user-cell">
                          <div class="user-initials"><?= htmlspecialchars($initials) ?></div>
                          <div>
                            <div class="user-name"><?= htmlspecialchars($u['name']) ?></div>
                          </div>
                        </div>
                      </td>
                      <td>
                        <div class="user-name"><?= htmlspecialchars(string: $u['asal_sekolah']) ?></div>
                      </td>
                      <td>
                        <div class="user-name"><?= htmlspecialchars(string: $u['email']) ?></div>
                      </td>
                      <td>
                        <div class="user-name">
                          <?= htmlspecialchars(string: $u['jenis_kelamin']) === 'L' ? 'Laki-laki' : 'Perempuan' ?>
                        </div>
                      </td>
                      <td>
                        <div class="user-name"><?= htmlspecialchars(string: $u['tinggi_badan']) ?> CM</div>
                      </td>
                      <td>
                        <div class="user-name"><?= htmlspecialchars(string: $u['berat_badan']) ?> KG</div>
                      </td>
                      <td>
                        <div class="action-group" style="justify-content:center;">
                          <a href="app.php?page=siswa/edit&action=edit&id=<?= $u['id_siswa'] ?>" class="btn-edit">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                              stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Edit
                          </a>
                          <button type="button" class="btn-delete"
                            onclick="confirmDelete(<?= $u['id_siswa'] ?>, '<?= htmlspecialchars(addslashes($u['name'])) ?>')">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                              stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Hapus
                          </button>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>

      </div>
    </div>
  </div>

  <!-- ── CONFIRM DELETE MODAL ── -->
  <div class="modal-overlay" id="deleteModal">
    <div class="modal-box">
      <div class="modal-icon">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
        </svg>
      </div>
      <p class="modal-title">Hapus User?</p>
      <p class="modal-desc" id="deleteModalDesc">Apakah Anda yakin ingin menghapus user ini? Tindakan ini tidak dapat
        dibatalkan.</p>
      <div class="modal-actions">
        <button class="btn-outline" onclick="closeDeleteModal()">Batal</button>
        <a href="#" class="btn-danger" id="deleteConfirmBtn">Ya, Hapus</a>
      </div>
    </div>
  </div>

  <script>
    // ── Sidebar toggle ──
    const shell = document.getElementById('appShell');
    const sidebar = document.getElementById('appSidebar');
    const backdrop = document.getElementById('sidebarBackdrop');
    const toggleBtn = document.getElementById('sidebarToggle');
    const closeBtn = document.getElementById('sidebarClose');
    const isMobile = () => window.innerWidth <= 768;

    toggleBtn.addEventListener('click', () => {
      if (isMobile()) {
        sidebar.classList.toggle('is-open');
        backdrop.classList.toggle('is-visible');
      } else {
        shell.classList.toggle('sidebar-collapsed');
      }
    });
    closeBtn.addEventListener('click', () => {
      sidebar.classList.remove('is-open');
      backdrop.classList.remove('is-visible');
    });
    backdrop.addEventListener('click', () => {
      sidebar.classList.remove('is-open');
      backdrop.classList.remove('is-visible');
    });

    // ── User dropdown ──
    const userBtn = document.getElementById('userMenuBtn');
    const dropdown = document.getElementById('userDropdown');
    userBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      const open = dropdown.classList.toggle('is-open');
      userBtn.setAttribute('aria-expanded', open);
    });
    document.addEventListener('click', () => {
      dropdown.classList.remove('is-open');
      userBtn.setAttribute('aria-expanded', 'false');
    });

    // ── Delete modal ──
    function confirmDelete(id, name) {
      document.getElementById('deleteModalDesc').textContent =
        `Apakah Anda yakin ingin menghapus siswa "${name}"? Tindakan ini tidak dapat dibatalkan.`;
      document.getElementById('deleteConfirmBtn').href =
        `app.php?page=siswa&action=delete&id=${id}`;
      document.getElementById('deleteModal').classList.add('is-open');
    }
    function closeDeleteModal() {
      document.getElementById('deleteModal').classList.remove('is-open');
    }
    document.getElementById('deleteModal').addEventListener('click', function (e) {
      if (e.target === this) closeDeleteModal();
    });
  </script>
</body>

</html>