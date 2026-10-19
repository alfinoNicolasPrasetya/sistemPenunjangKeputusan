<?php
/**
 * ╔══════════════════════════════════════════════════════════════╗
 * ║  app.php  —  Application Entry Point                         ║
 * ║  SPK Fuzzy Sugeno  |  PHP + MySQL                            ║
 * ║                                                              ║
 * ║  Behavior:                                                   ║
 * ║  ─ Session guard (redirect ke login jika belum auth)         ║
 * ║  ─ Router berbasis query param ?page=                        ║
 * ║  ─ Layout: Header + Sidebar + Content (dari pages/) + Footer ║
 * ╚══════════════════════════════════════════════════════════════╝
 */
declare(strict_types=1);
ob_start();
require_once __DIR__ . '/config/appConfig.php';
require_once __DIR__ . '/config/helper.php';

// Session setup — harus sebelum session_start()
ini_set('session.name', SESSION_NAME);
ini_set('session.gc_maxlifetime', (string) SESSION_LIFETIME);
ini_set('session.cookie_httponly', '1');
ini_set('session.use_strict_mode', '1');
session_start();


/* ── 2. SESSION GUARD ─────────────────────────────────────── */
// Cek apakah user sudah login.
// Jika BELUM → redirect ke login.php dengan query param ?redirect= untuk kembali ke halaman yang diminta setelah login sukses.
if (empty($_SESSION['user'])) {
  $currentUri = urlencode($_SERVER['REQUEST_URI'] ?? 'app.php');
  header('Location: pages/Login.php');
  exit;
}

// Cek session timeout manual (opsional, sebagai lapisan ekstra)
$lastActivity = $_SESSION['_last_activity'] ?? time();
if ((time() - $lastActivity) > SESSION_LIFETIME) {
  session_unset();
  session_destroy();
  header('Location: login.php?reason=timeout');
  exit;
}
$_SESSION['_last_activity'] = time();


/* ── 3. ROUTING ───────────────────────────────────────────── */
/**
 * Resolves halaman yang akan ditampilkan berdasarkan ?page=
 *
 * Prioritas:
 *   1. Query param  : ?page=criteria/create
 *   2. Default      : dashboard
 *
 * @return array{ page: string, file: string, valid: bool }
 */
function resolveRoute(): array
{
  $routes = require __DIR__ . '/config/router.php';

  // Ambil path dari query string, sanitize ketat
  $rawPage = $_GET['page'] ?? 'dashboard';

  // Sanitasi: hanya huruf, angka, strip, slash
  $page = preg_replace('/[^a-z0-9\-\/]/', '', strtolower(trim($rawPage)));
  $page = trim($page, '/');

  // Fallback jika kosong setelah sanitasi
  if ($page === '') {
    $page = 'dashboard';
  }

  // Cari di routing table
  if (isset($routes[$page])) {
    $filePath = __DIR__ . '/pages/' . $routes[$page];

    // Pastikan file benar-benar ada (keamanan path traversal)
    $realFile = realpath($filePath);
    $pagesDir = realpath(__DIR__ . '/pages');

    if ($realFile && $pagesDir && str_starts_with($realFile, $pagesDir) && is_file($realFile)) {
      return [
        'page' => $page,
        'file' => $realFile,
        'valid' => true,
      ];
    }
  }

  // 404 fallback
  return [
    'page' => $page,
    'file' => __DIR__ . '/pages/404.php',
    'valid' => false,
  ];
}

$route = resolveRoute();
$currentPage = $route['page'];   // Digunakan oleh sidebar & header untuk highlight aktif


/* ── 4. ROLE-BASED ACCESS (opsional, per-route) ───────────── */
/**
 * Daftar halaman yang hanya bisa diakses admin.
 * Tambah route di sini jika perlu proteksi role.
 */
$adminOnlyRoutes = ['users'];

$currentUser = $_SESSION['user'];

$currentUser = $_SESSION['user'];

if ($currentPage === 'dashboard' && $currentUser['id_role'] !== 1 && $currentUser['id_role'] !== 3) {
    header('Location: app.php?page=pengumuman');
    exit;
}



if (in_array($currentPage, $adminOnlyRoutes, true) && $currentUser['id_role'] !== 1 && $currentUser['id_role'] !== 3) {
  // Redirect ke dashboard dengan flash message
  $_SESSION['flash'] = [
    'type' => 'error',
    'message' => 'Anda tidak memiliki akses ke halaman ini.',
  ];
  header('Location: app.php?page=dashboard');
  exit;
}



/* ── 5. RENDER — HTML OUTPUT ──────────────────────────────── */
// Buffer output untuk menangkap error page jika diperlukan
ob_end_flush();
?>
<!DOCTYPE html>
<html lang="id" data-page="<?= htmlspecialchars($currentPage) ?>">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">

  <title id="docTitle">
    <?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' — ' : '' ?>
    <?= APP_NAME ?>
  </title>

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&family=IBM+Plex+Mono:wght@400;500;600&display=swap"
    rel="stylesheet">

  <!-- App Stylesheet -->
  <link rel="stylesheet" href="assets/app.css">

  <?php if (!empty($extraHead))
    echo $extraHead; ?>
</head>

<body>
  <div class="app-shell" id="appShell">
    <!-- ── SIDEBAR  (component: sidebar.php) ── -->
    <?php require __DIR__ . '/layout/sidebar.php'; ?>

    <!-- ── MAIN AREA ── -->
    <div class="app-main" id="appMain">

      <!-- ── HEADER  (component: header.php) ── -->
      <?php require __DIR__ . '/layout/header.php'; ?>

      <!-- ── CONTENT (setara <router-view> di Vue) ── -->
      <main class="app-content" id="appContent" role="main">

        <!-- Flash message (session-based) -->
        <?php if (!empty($_SESSION['flash'])): ?>
        <?php $flash = $_SESSION['flash'];
          unset($_SESSION['flash']); ?>
        <div class="flash-message flash-<?= htmlspecialchars($flash['type']) ?>" role="alert">
          <span><?= htmlspecialchars($flash['message']) ?></span>
          <button class="flash-close" onclick="this.parentElement.remove()" aria-label="Tutup">&times;</button>
        </div>
        <style>
          .flash-message {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 13.5px;
            font-weight: 500;
            border: 1.5px solid;
            animation: slideDown .25s ease;
          }

          @keyframes slideDown {
            from {
              opacity: 0;
              transform: translateY(-8px);
            }

            to {
              opacity: 1;
              transform: translateY(0);
            }
          }

          .flash-success {
            background: #F0FDF4;
            color: #166534;
            border-color: #BBF7D0;
          }

          .flash-error {
            background: #FEF2F2;
            color: #991B1B;
            border-color: #FECACA;
          }

          .flash-warning {
            background: #FFFBEB;
            color: #92400E;
            border-color: #FDE68A;
          }

          .flash-info {
            background: #EFF6FF;
            color: #1E40AF;
            border-color: #BFDBFE;
          }

          .flash-close {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 18px;
            line-height: 1;
            opacity: .6;
            color: inherit;
            padding: 0 4px;
            transition: opacity .15s;
          }

          .flash-close:hover {
            opacity: 1;
          }
        </style>
        <?php endif; ?>

        <!-- ══════════════════════════════════════════
                 PAGE VIEW
                 Ini equivalent dengan <router-view> di Vue.
                 File di-include berdasarkan hasil resolveRoute().
            ══════════════════════════════════════════ -->
        <div class="page-view  print-area" id="pageView">
          <?php
          /**
           * Include halaman yang sesuai dengan route.
           * Setiap file page/ mendapat akses ke:
           *   - $currentPage  : string slug halaman aktif
           *   - $currentUser  : array data user dari session
           *   - $route        : array hasil resolveRoute()
           *   - Semua CONSTANT dari config/app.php
           *
           * Setiap page bisa mendefinisikan:
           *   - $pageTitle    : string judul tab browser
           *   - $extraHead    : string HTML untuk <head>
           */
          if (is_file($route['file'])) {
            include $route['file'];
          } else {
            echo '<p style="color:red;padding:20px;">Error: File halaman tidak ditemukan.</p>';
          }
          ?>
        </div>

        <!-- ── FOOTER  (component: footer.php) ── -->
        <?php require __DIR__ . '/layout/footer.php'; ?>

      </main>
      <!-- /app-content -->

    </div>
    <!-- /app-main -->
  </div>


  <!-- ═══════════════════════════════════════════════════════════
     JAVASCRIPT
══════════════════════════════════════════════════════════════ -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    (function () {
      'use strict';

      /* ── Elemen utama ── */
      const shell = document.getElementById('appShell');
      const sidebar = document.getElementById('appSidebar');
      const backdrop = document.getElementById('sidebarBackdrop');
      const toggleBtn = document.getElementById('sidebarToggle');
      const closeBtn = document.getElementById('sidebarClose');
      const userMenuBtn = document.getElementById('userMenuBtn');
      const userDropdown = document.getElementById('userDropdown');

      /* ── Deteksi mobile ── */
      const isMobile = () => window.innerWidth < 769;

      /* ────────────────────────────────────────────
         SIDEBAR TOGGLE
         Desktop: collapse/expand (kelas sidebar-collapsed di shell)
         Mobile : slide-in/out  (kelas is-open di sidebar + backdrop)
      ──────────────────────────────────────────── */
      const STORAGE_KEY = 'spk_sidebar_collapsed';

      // Restore state dari localStorage
      if (!isMobile() && localStorage.getItem(STORAGE_KEY) === '1') {
        shell.classList.add('sidebar-collapsed');
      }

      function toggleSidebar() {
        if (isMobile()) {
          sidebar.classList.toggle('is-open');
          backdrop.classList.toggle('is-visible');
        } else {
          shell.classList.toggle('sidebar-collapsed');
          localStorage.setItem(
            STORAGE_KEY,
            shell.classList.contains('sidebar-collapsed') ? '1' : '0'
          );
        }
      }

      function closeMobileSidebar() {
        sidebar.classList.remove('is-open');
        backdrop.classList.remove('is-visible');
      }

      toggleBtn ? .addEventListener('click', toggleSidebar);
      closeBtn ? .addEventListener('click', closeMobileSidebar);
      backdrop ? .addEventListener('click', closeMobileSidebar);

      // Tutup otomatis saat resize ke desktop
      window.addEventListener('resize', () => {
        if (!isMobile()) closeMobileSidebar();
      });

      /* ────────────────────────────────────────────
         USER DROPDOWN
      ──────────────────────────────────────────── */
      function toggleUserMenu(force) {
        const isOpen = force !== undefined ? force : !userDropdown.classList.contains('is-open');
        userDropdown.classList.toggle('is-open', isOpen);
        userMenuBtn ? .setAttribute('aria-expanded', String(isOpen));
      }

      userMenuBtn ? .addEventListener('click', (e) => {
        e.stopPropagation();
        toggleUserMenu();
      });

      // Klik di luar → tutup dropdown
      document.addEventListener('click', () => toggleUserMenu(false));
      userDropdown ? .addEventListener('click', (e) => e.stopPropagation());

      // Keyboard accessibility
      userMenuBtn ? .addEventListener('keydown', (e) => {
        if (e.key === 'Escape') toggleUserMenu(false);
      });

      const currentPage = document.documentElement.dataset.page;
      document.querySelectorAll('.sidebar-item').forEach(item => {
        const href = item.getAttribute('href') || '';
        const pageParam = new URLSearchParams(href.split('?')[1] || '').get('page') || '';

        const isActive = pageParam === currentPage ||
          (currentPage && currentPage.startsWith(pageParam + '/') && pageParam !== '');

        if (isActive) {
          item.classList.add('sidebar-item--active');
          item.setAttribute('aria-current', 'page');
        }
      });

      const pageTitleEl = document.getElementById('headerPageTitle');
      if (pageTitleEl) {
        const pageLabel = pageTitleEl.textContent.trim();
        if (pageLabel) {
          document.title = pageLabel + ' — <?= addslashes(APP_NAME) ?>';
        }
      }

      /* ────────────────────────────────────────────
         KEYBOARD SHORTCUT
         Alt+B  → toggle sidebar
         Alt+H  → go to dashboard
      ──────────────────────────────────────────── */
      document.addEventListener('keydown', (e) => {
        if (e.altKey && e.key === 'b') {
          e.preventDefault();
          toggleSidebar();
        }
        // if (e.altKey && e.key === 'h') { e.preventDefault(); location.href = 'app.php?page=dashboard'; }
      });

      window.spkApp = {
        /**
         * Navigate ke page lain (soft navigation dengan konfirmasi jika ada form dirty)
         * @param {string} page
         */
        navigate(page) {
          location.href = 'app.php?page=' + encodeURIComponent(page);
        },

        /**
         * Tampilkan flash message dinamis (tanpa reload)
         * @param {'success'|'error'|'warning'|'info'} type
         * @param {string} message
         */
        flash(type, message) {
          const existing = document.querySelector('.flash-message');
          if (existing) existing.remove();

          const el = document.createElement('div');
          el.className = `flash-message flash-${type}`;
          el.innerHTML = `
                <span>${message}</span>
                <button class="flash-close" onclick="this.parentElement.remove()" aria-label="Tutup">&times;</button>
            `;

          const pageView = document.getElementById('pageView');
          pageView ? .parentElement ? .insertBefore(el, pageView);

          // Auto dismiss setelah 5 detik
          setTimeout(() => el.remove(), 5000);
        },

        /**
         * Loading overlay sederhana
         */
        showLoading() {
          let overlay = document.getElementById('loadingOverlay');
          if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'loadingOverlay';
            overlay.style.cssText = `
                    position:fixed;inset:0;background:rgba(248,250,252,0.7);
                    display:flex;align-items:center;justify-content:center;
                    z-index:9999;backdrop-filter:blur(2px);
                `;
            overlay.innerHTML = `
                    <div style="
                        display:flex;flex-direction:column;align-items:center;gap:16px;
                        background:#fff;padding:28px 40px;border-radius:16px;
                        box-shadow:0 20px 60px rgba(0,0,0,0.12);border:1.5px solid #E2E8F0;
                    ">
                        <div style="
                            width:40px;height:40px;border:3px solid #E2E8F0;
                            border-top-color:#4F46E5;border-radius:50%;
                            animation:spin .7s linear infinite;
                        "></div>
                        <span style="font-size:13px;font-weight:600;color:#64748B;">Memproses...</span>
                    </div>
                    <style>@keyframes spin { to { transform: rotate(360deg); } }</style>
                `;
            document.body.appendChild(overlay);
          }
        },

        hideLoading() {
          document.getElementById('loadingOverlay') ? .remove();
        },
      };

      console.info('[SPK] app.php ready | page:', currentPage, '| user:', < ? = json_encode($currentUser['name']) ? >
        );
    })();
  </script>

  <?php if (!empty($_SESSION['_alert'])): ?>
  <script>
    Swal.fire({
      icon: '<?= $_SESSION['
      _alert ']['
      type '] ?>',
      title: '<?= addslashes($_SESSION['
      _alert ']['
      title ']) ?>',
      text: '<?= addslashes($_SESSION['
      _alert ']['
      message ']) ?>',
      confirmButtonText: 'OK'
    }).then((result) => {
      if (result.isConfirmed) {
        <
        ? php
        if (!empty($_SESSION['_alert']['onConfirm'])): ? >
          <
          ? = $_SESSION['_alert']['onConfirm'] ? >
          <
          ? php endif; ? >
      }
    });
  </script>
  <?php unset($_SESSION['_alert']); endif; ?>

  <?php if (!empty($extraScript))
    echo $extraScript; ?>

  <script>
    function setAlertOnJs(icon, title, message, onConfirmJs, onCancelJs = null) {
      Swal.fire({
        icon: icon,
        title: title,
        text: message,
        showCancelButton: true,
        cancelButtonText: 'Batal',
        confirmButtonText: 'OK'
      }).then((result) => {
        if (result.isConfirmed) {
          if (typeof onConfirmJs === 'function') {
            onConfirmJs();
          }
        }

        if (result.isDismissed) {
          if (typeof onCancelJs === 'function') {
            onCancelJs();
          }
        }
      });
    }
  </script>

</body>

</html>