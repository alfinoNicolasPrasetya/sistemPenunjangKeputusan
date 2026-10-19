<?php
/**
 * Component: Header / Top Navigation Bar
 * Dipanggil dari app.php
 * Memiliki akses ke: $currentPage, $currentUser (dari session)
 */

$currentUser = $_SESSION['user'] ?? ['name' => 'Guest', 'role' => 'viewer'];
?>
<header class="app-header" id="appHeader">

  <!-- Hamburger toggle (mobile & collapsed sidebar) -->
  <button class="header-toggle" id="sidebarToggle" aria-label="Toggle Sidebar" title="Toggle Sidebar">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12" />
    </svg>
  </button>

  <!-- Breadcrumb / Page title -->
  <div class="header-breadcrumb">
    <span class="header-app-name"><?= APP_NAME ?></span>
    <span class="header-sep">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
      </svg>
    </span>
    <span class="header-page-title" id="headerPageTitle">
      <?= htmlspecialchars(ucwords(str_replace(['-', '/'], [' ', ' › '], $currentPage ?? 'Dashboard'))) ?>
    </span>
  </div>

  <!-- Right section -->
  <div class="header-right">
    <!-- Project switcher badge -->
    <?php if (!empty($_SESSION['active_project'])): ?>
      <div class="header-project-badge">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
        </svg>
        <span><?= htmlspecialchars($_SESSION['active_project']['name'] ?? '') ?></span>
      </div>
    <?php endif; ?>

    <!-- User dropdown -->
    <div class="header-user-menu" id="userMenu">
      <button class="header-user-btn" id="userMenuBtn" aria-haspopup="true" aria-expanded="false">
        <div class="header-avatar">
          <?= strtoupper(substr($currentUser['name'], 0, 1)) ?>
        </div>
        <div class="header-user-info">
          <span class="header-user-name"><?= htmlspecialchars($currentUser['name']) ?></span>
          <span class="header-user-role"><?= ucfirst($currentUser['role']) ?></span>
        </div>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
          class="header-chevron">
          <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
        </svg>
      </button>

      <!-- Dropdown menu -->
      <div class="header-dropdown" id="userDropdown" role="menu">
        <a href="?page=profile" class="dropdown-item" role="menuitem">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
          </svg>
          Profil Saya
        </a>
        <div class="dropdown-divider"></div>
        <a class="dropdown-item dropdown-item--danger" role="menuitem"
          onclick="setAlertOnJs('warning', 'Logout', 'Apakah Anda yakin ingin keluar?', function() { window.location.href = 'service/Logout.php'; })">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15m-3 0-3-3m0 0 3-3m-3 3H15" />
          </svg>
          Logout
        </a>
      </div>
    </div>
  </div>
</header>