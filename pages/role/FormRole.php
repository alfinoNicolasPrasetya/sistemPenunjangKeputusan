<?php
// Memanggil file koneksi
require 'config/DBConfig.php';

$errors = [];
$success = false;
$roles = [];
$userData = ['role' => '', 'id_role' => ''];

// Deteksi mode: add atau edit
$action = $_GET['action'] ?? 'add';
$editId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$isEdit = ($action === 'edit' && $editId > 0);
$pageTitle = $isEdit ? 'Edit Role' : 'Tambah Role';


// ── Jika EDIT: ambil data role yang akan diedit ──
if ($conn && $isEdit) {
  try {
    $stmt = $conn->prepare("SELECT * FROM role WHERE id_role = ?");
    $stmt->execute([$editId]);
    $row = $stmt->fetch();
    if ($row) {
      $userData = $row;
    } else {
      $errors[] = 'Role dengan ID ' . $editId . ' tidak ditemukan.';
      $isEdit = false;
    }
  } catch (\PDOException $e) {
    $errors[] = 'Gagal mengambil data role: ' . $e->getMessage();
  }
}

// ── Proses Form Submit ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $conn) {
  $name = trim($_POST['name'] ?? '');
  $formAction = $_POST['form_action'] ?? 'add';
  $formId = (int) ($_POST['role_id'] ?? 0);

  // ── Validasi ──
  if (empty($name)) {
    $errors[] = 'Nama Role tidak boleh kosong.';
  } elseif (strlen($name) > 100) {
    $errors[] = 'Nama maksimal 100 karakter.';
  } else {
    // Cek duplikat role (kecuali role yang sedang diedit)
    try {
      $chkStmt = $conn->prepare(
        "SELECT id_role FROM role WHERE role = ? AND id_role != ?"
      );
      $chkStmt->execute([$name, $formId]);
      if ($chkStmt->fetch()) {
        $errors[] = 'Nama Role sudah digunakan.';
      }
    } catch (\PDOException $e) { /* abaikan */
    }
  }

  // ── Jika tidak ada error, simpan ke database ──
  if (empty($errors)) {
    try {
      if ($formAction === 'edit' && $formId > 0) {
        // UPDATE
        $stmt = $conn->prepare(
          "UPDATE role SET role = ? WHERE id_role = ?"
        );
        $stmt->execute([$name, $formId]);
      } else {
        // INSERT
        $hashedPass = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare(
          "INSERT INTO role (role) VALUES (?)"
        );
        $stmt->execute([$name]);
      }
      // Redirect ke User.php setelah berhasil
      echo "<script>window.location.href='app.php?page=roles&success=" . ($formAction === 'edit' ? 'edited' : 'added') . "';</script>";
      exit;
    } catch (\PDOException $e) {
      $errors[] = 'Gagal menyimpan data: ' . $e->getMessage();
    }
  }



  // Isi ulang field jika ada error (agar tidak kosong)
  $userData['name'] = $name;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <link rel="stylesheet" href="assets/app.css">
</head>

<body>
  <!-- ── MAIN ── -->
  <div>
    <!-- Content -->
    <main class="app-content">
      <div class="page-view">

        <!-- Page Header -->
        <div class="page-header">
          <div>
            <h1 class="page-title"><?= htmlspecialchars($pageTitle) ?></h1>
            <p class="page-subtitle">
              <?= $isEdit
                ? 'Ubah informasi role yang sudah terdaftar di sistem.'
                : 'Tambahkan role baru ke dalam sistem SPK Fuzzy Sugeno.'
                ?>
            </p>
          </div>
          <a href="app.php?page=roles" class="btn-secondary">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali
          </a>
        </div>

        <!-- Error list -->
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
                  <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          </div>
        <?php endif; ?>

        <!-- FORM CARD -->
        <div class="form-card" style="width: 100%; max-width: none;">
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
                    d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
              <?php endif; ?>
            </div>
            <div>
              <p class="form-card-title"><?= htmlspecialchars($pageTitle) ?></p>
              <p class="form-card-desc">
                <?= $isEdit ? 'Perbarui data nama, email, dan role pengguna.' : 'Lengkapi semua field yang diperlukan untuk membuat akun baru.' ?>
              </p>
            </div>
          </div>

          <form method="POST"
            action="app.php?page=roles/<?= $isEdit ? 'edit&action=edit&id=' . $editId : 'add&action=add' ?>"
            id="userForm" novalidate>
            <input type="hidden" name="form_action" value="<?= $isEdit ? 'edit' : 'add' ?>">
            <?php if ($isEdit): ?>
              <input type="hidden" name="role_id" value="<?= (int) $userData['id_role'] ?>">
            <?php endif; ?>

            <div class="form-card-body">

              <!-- ── INFORMASI DASAR ── -->
              <p class="form-section-title">Informasi Dasar</p>

              <div class="form-group">
                <label class="form-label" for="name">
                  Nama Role <span class="required">*</span>
                </label>
                <input type="text" id="name" name="name"
                  class="form-control <?= isset($errors) && !empty($errors) && empty(trim($_POST['role'] ?? '')) ? 'is-invalid' : '' ?>"
                  placeholder="Masukkan nama role" value="<?= htmlspecialchars($userData['role']) ?>" maxlength="100"
                  autocomplete="name" required />
                <span class="form-hint">Maksimal 100 karakter.</span>
              </div>
            </div><!-- /form-card-body -->

            <!-- Actions -->
            <div class="form-actions">
              <a href="app.php?page=roles" class="btn-secondary">
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
                  Tambah Role
                <?php endif; ?>
              </button>
            </div>
          </form>
        </div><!-- /form-card -->

      </div>
    </main>
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

    // ── Client-side form validation sebelum submit ──
    document.getElementById('userForm').addEventListener('submit', function (e) {
      let hasError = false;

      // Nama
      const nameVal = document.getElementById('name').value.trim();
      if (!nameVal) {
        document.getElementById('name').classList.add('is-invalid');
        hasError = true;
      }

      // Email
      const emailVal = emailInput ? emailInput.value.trim() : '';
      if (!emailVal || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailVal)) {
        if (emailInput) emailInput.classList.add('is-invalid');
        hasError = true;
      }

      // Role
      const roleVal = document.getElementById('id_role').value;
      if (!roleVal) {
        document.getElementById('id_role').classList.add('is-invalid');
        hasError = true;
      }

      <?php if (!$isEdit): ?>
        // Password
        const pVal = passInput ? passInput.value : '';
        const rpVal = rePassInput ? rePassInput.value : '';
        if (!pVal || pVal.length < 6) {
          if (passInput) passInput.classList.add('is-invalid');
          hasError = true;
        }
        if (!rpVal || pVal !== rpVal) {
          if (rePassInput) rePassInput.classList.add('is-invalid');
          hasError = true;
        }
      <?php endif; ?>

      if (hasError) {
        e.preventDefault();
        window.scrollTo({ top: 0, behavior: 'smooth' });
      }
    });

    // Clear invalid on focus
    document.querySelectorAll('.form-control').forEach(el => {
      el.addEventListener('focus', () => el.classList.remove('is-invalid'));
    });
  </script>
</body>

</html>