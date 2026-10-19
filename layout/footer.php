<?php
/**
 * Component: Footer
 * Dipanggil dari app.php, berada di dalam .app-content
 */
?>
<footer class="app-footer">
  <div class="footer-inner">
    <span class="footer-copy">
      &copy; <?= date('Y') ?> <strong><?= APP_NAME ?></strong>
      &mdash; Sistem Penunjang Keputusan Metode Fuzzy Sugeno
    </span>
    <span class="footer-meta">
      PHP <?= PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION ?>
      &bull; v<?= APP_VERSION ?>
      &bull;
      <?php
      // Waktu generate halaman
      $genTime = round((microtime(true) - ($_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true))) * 1000, 2);
      echo $genTime . ' ms';
      ?>
    </span>
  </div>
</footer>