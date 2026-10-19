<?php
// Memanggil file koneksi
require 'config/DBConfig.php';

$errors = [];
$successMsg = '';
$roles = [];

$isEdit = true;
$pageTitle = 'Profil Saya';
$userData = [
  'id' => $currentUser['id'],
  'name' => $currentUser['name'],
  'email' => $currentUser['email'],
  'id_role' => $currentUser['id_role'] ?? '',
  'role' => $currentUser['role'] ?? ''
];

// ── Ambil daftar role untuk dropdown ──
if ($conn) {
  try {
    $stmt = $conn->query("SELECT id_role, role FROM role ORDER BY id_role ASC");
    $roles = $stmt->fetchAll();
  } catch (\PDOException $e) {
    $errors[] = 'Gagal mengambil data role: ' . $e->getMessage();
  }
}

// ── Proses Form Submit ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $conn) {
  $name = trim($_POST['name'] ?? '');
  $password = $_POST['password'] ?? '';
  $repass = $_POST['re_password'] ?? '';
  $formId = $currentUser['id'];

  // ── Validasi Nama ──
  if (empty($name)) {
    $errors[] = 'Nama tidak boleh kosong.';
  } elseif (strlen($name) > 100) {
    $errors[] = 'Nama maksimal 100 karakter.';
  }

  // ── Validasi Password jika diisi ──
  $updatePassword = false;
  if (!empty($password)) {
    if (strlen($password) < 6) {
      $errors[] = 'Password minimal 6 karakter.';
    } elseif ($password !== $repass) {
      $errors[] = 'Konfirmasi password tidak cocok.';
    } else {
      $updatePassword = true;
    }
  }

  // ── Jika tidak ada error, simpan ke database ──
  if (empty($errors)) {
    try {
      if ($updatePassword) {
        $hashedPass = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare(
          "UPDATE users SET name = ?, password = ?, updated_at = NOW() WHERE id = ?"
        );
        $stmt->execute([$name, $hashedPass, $formId]);
      } else {
        $stmt = $conn->prepare(
          "UPDATE users SET name = ?, updated_at = NOW() WHERE id = ?"
        );
        $stmt->execute([$name, $formId]);
      }

      // Update data user di session agar langsung berubah di layout
      $_SESSION['user']['name'] = $name;

      // Redirect ke profil dengan status sukses
      echo "<script>window.location.href='app.php?page=profile&success=1';</script>";
      exit;
    } catch (\PDOException $e) {
      $errors[] = 'Gagal menyimpan data: ' . $e->getMessage();
    }
  }

  // Jika gagal, isi ulang data form agar input user tidak hilang
  $userData['name'] = $name;
}

// ── Menangani Notifikasi Sukses ──
if (isset($_GET['success']) && $_GET['success'] === '1') {
  $successMsg = 'Profil berhasil diperbarui.';
  setAlert('success', 'Berhasil', $successMsg);
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

          <form method="POST"
            action="app.php?page=profile"
            id="userForm" novalidate>
            <input type="hidden" name="form_action" value="edit">
            <input type="hidden" name="user_id" value="<?= (int) $userData['id'] ?>">

            <div class="form-card-body">

              <!-- ── INFORMASI DASAR ── -->
              <p class="form-section-title">Informasi Dasar</p>

              <div class="form-group">
                <label class="form-label" for="name">
                  Nama Lengkap <span class="required">*</span>
                </label>
                <input type="text" id="name" name="name"
                  class="form-control <?= isset($errors) && !empty($errors) && empty(trim($_POST['name'] ?? '')) ? 'is-invalid' : '' ?>"
                  placeholder="Masukkan nama lengkap" value="<?= htmlspecialchars($userData['name']) ?>" maxlength="100"
                  autocomplete="name" required />
                <span class="form-hint">Maksimal 100 karakter.</span>
              </div>

              <div class="form-group">
                <label class="form-label" for="email">
                  Alamat Email <span class="required">*</span>
                </label>
                <input type="email" id="email" name="email" class="form-control" style="background-color: #dddcdcde;"
                  placeholder="contoh@domain.com" disabled value="<?= htmlspecialchars($userData['email']) ?>"
                  maxlength="150" autocomplete="email" required />
                <span class="form-hint" id="emailHint">Format: nama@domain.com</span>
              </div>

              <div class="form-group">
                <label class="form-label" for="id_role">
                  Role <span class="required">*</span>
                </label>
                <div class="select-wrap">
                  <select id="id_role" name="id_role" class="form-control" required disabled
                    style="background-color: #c9c9c9de;">
                    <option value="">— Pilih Role —</option>
                    <?php foreach ($roles as $r): ?>
                      <option value="<?= (int) $r['id_role'] ?>" <?= (int) $userData['id_role'] === (int) $r['id_role'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($r['role']) ?>
                      </option>
                    <?php endforeach; ?>
                    <?php if (empty($roles)): ?>
                      <option disabled>Data role belum tersedia</option>
                    <?php endif; ?>
                  </select>
                </div>
                <span class="form-hint">Pilih hak akses yang sesuai untuk user ini.</span>
              </div>

              <hr class="form-divider">

              <!-- ── PASSWORD (opsional saat edit profil) ── -->
              <p class="form-section-title">Keamanan Akun</p>

              <div class="form-row">
                <div class="form-group" style="margin-bottom:0;">
                  <label class="form-label" for="password">
                    Password Baru
                  </label>
                  <div class="input-password-wrap">
                    <input type="password" id="password" name="password" class="form-control"
                      placeholder="Kosongkan jika tidak ingin diubah" autocomplete="new-password" />
                    <button type="button" class="password-toggle" onclick="togglePass('password', this)"
                      aria-label="Tampilkan password">
                      <svg id="eye-pass" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round"
                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                      </svg>
                    </button>
                  </div>
                  <!-- Password strength indicator -->
                  <div class="password-strength" id="strengthWrap" style="display:none;">
                    <div class="strength-bar">
                      <div class="strength-fill" id="strengthFill"></div>
                    </div>
                    <span class="strength-label" id="strengthLabel"></span>
                  </div>
                </div>

                <div class="form-group" style="margin-bottom:0;">
                  <label class="form-label" for="re_password">
                    Konfirmasi Password Baru
                  </label>
                  <div class="input-password-wrap">
                    <input type="password" id="re_password" name="re_password" class="form-control"
                      placeholder="Ulangi password baru" autocomplete="new-password" />
                    <button type="button" class="password-toggle" onclick="togglePass('re_password', this)"
                      aria-label="Tampilkan konfirmasi password">
                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round"
                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                      </svg>
                    </button>
                  </div>
                  <!-- Match indicator -->
                  <div class="match-indicator" id="matchIndicator" style="display:none;"></div>
                </div>
              </div>


            </div><!-- /form-card-body -->

            <!-- Actions -->
            <div class="form-actions">
              <button type="submit" class="btn btn-primary" id="submitBtn">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                Simpan Perubahan
              </button>
            </div>
          </form>
        </div>

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

    // ── Toggle password visibility ──
    function togglePass(fieldId, btn) {
      const input = document.getElementById(fieldId);
      const isHidden = input.type === 'password';
      input.type = isHidden ? 'text' : 'password';
      btn.innerHTML = isHidden
        ? `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
           </svg>`
        : `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
           </svg>`;
    }

    // ── Password strength ──
    const passInput = document.getElementById('password');
    const rePassInput = document.getElementById('re_password');
    const strengthWrap = document.getElementById('strengthWrap');
    const strengthFill = document.getElementById('strengthFill');
    const strengthLabel = document.getElementById('strengthLabel');
    const matchEl = document.getElementById('matchIndicator');

    function calcStrength(val) {
      let score = 0;
      if (val.length >= 6) score++;
      if (val.length >= 10) score++;
      if (/[A-Z]/.test(val)) score++;
      if (/[0-9]/.test(val)) score++;
      if (/[^A-Za-z0-9]/.test(val)) score++;
      return score;
    }

    if (passInput) {
      passInput.addEventListener('input', () => {
        const val = passInput.value;
        if (!val) { strengthWrap.style.display = 'none'; return; }
        strengthWrap.style.display = 'block';
        const score = calcStrength(val);
        const levels = [
          { pct: '20%', color: '#ef4444', label: 'Sangat Lemah' },
          { pct: '40%', color: '#f59e0b', label: 'Lemah' },
          { pct: '60%', color: '#f59e0b', label: 'Cukup' },
          { pct: '80%', color: '#10b981', label: 'Kuat' },
          { pct: '100%', color: '#10b981', label: 'Sangat Kuat' },
        ];
        const lvl = levels[Math.min(score - 1, 4)] || levels[0];
        strengthFill.style.width = lvl.pct;
        strengthFill.style.background = lvl.color;
        strengthLabel.textContent = lvl.label;
        strengthLabel.style.color = lvl.color;
        checkMatch();
      });
    }

    function checkMatch() {
      if (!rePassInput || !matchEl) return;
      const p = passInput ? passInput.value : '';
      const r = rePassInput.value;
      if (!r) { matchEl.style.display = 'none'; return; }
      matchEl.style.display = 'flex';
      if (p === r) {
        matchEl.className = 'match-indicator match-ok';
        matchEl.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg> Password cocok`;
        rePassInput.classList.remove('is-invalid');
        rePassInput.classList.add('is-valid');
      } else {
        matchEl.className = 'match-indicator match-err';
        matchEl.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg> Password tidak cocok`;
        rePassInput.classList.add('is-invalid');
        rePassInput.classList.remove('is-valid');
      }
    }
    if (rePassInput) rePassInput.addEventListener('input', checkMatch);

    // ── Email live validation ──
    const emailInput = document.getElementById('email');
    const emailHint = document.getElementById('emailHint');
    if (emailInput) {
      emailInput.addEventListener('input', () => {
        const val = emailInput.value;
        const valid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val);
        if (!val) {
          emailInput.classList.remove('is-valid', 'is-invalid');
          emailHint.textContent = 'Format: nama@domain.com';
          emailHint.style.color = '';
          return;
        }
        if (valid) {
          emailInput.classList.add('is-valid');
          emailInput.classList.remove('is-invalid');
          emailHint.textContent = '✓ Format email valid';
          emailHint.style.color = 'var(--success)';
        } else {
          emailInput.classList.add('is-invalid');
          emailInput.classList.remove('is-valid');
          emailHint.textContent = '✗ Format email tidak valid';
          emailHint.style.color = 'var(--danger)';
        }
      });
    }

    // ── Client-side form validation sebelum submit ──
    document.getElementById('userForm').addEventListener('submit', function (e) {
      let hasError = false;

      // Nama
      const nameVal = document.getElementById('name').value.trim();
      if (!nameVal) {
        document.getElementById('name').classList.add('is-invalid');
        hasError = true;
      }

      // Password (optional saat edit profile)
      const pVal = passInput ? passInput.value : '';
      const rpVal = rePassInput ? rePassInput.value : '';
      
      if (pVal !== '') {
        if (pVal.length < 6) {
          if (passInput) passInput.classList.add('is-invalid');
          hasError = true;
        }
        if (pVal !== rpVal) {
          if (rePassInput) rePassInput.classList.add('is-invalid');
          hasError = true;
        }
      }

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