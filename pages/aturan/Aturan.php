<?php
// mengambil koneksi
require_once 'config/DBConfig.php';

$pageTitle = 'Aturan Fuzzy';
$successMsg = '';
$errorMsg = '';


// ── Proses DELETE ────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id']) && $conn) {
  $deleteId = (int) $_POST['delete_id'];
  try {
    $conn->beginTransaction();
    $conn->prepare("DELETE FROM detail_aturan_fuzzy WHERE id_aturan = ?")->execute([$deleteId]);
    $conn->prepare("DELETE FROM aturan_fuzzy WHERE id_aturan = ?")->execute([$deleteId]);
    $conn->commit();
    $successMsg = 'Aturan berhasil dihapus.';
  } catch (\PDOException $e) {
    $conn->rollBack();
    $errorMsg = 'Gagal menghapus aturan: ' . $e->getMessage();
  }
}

// ── Flash messages ───────────────────────────────────────────
if (isset($_GET['success'])) {
  $map = [
    'added' => 'Aturan berhasil ditambahkan.',
    'edited' => 'Aturan berhasil diperbarui.',
    'deleted' => 'Aturan berhasil dihapus.',
  ];
  $successMsg = $map[$_GET['success']] ?? '';
}
if (isset($_GET['error'])) {
  $errorMsg = htmlspecialchars($_GET['error']);
}

// ── Ambil semua aturan dari DB ───────────────────────────────
// Struktur hasil: per aturan → kondisi (array detail)
$aturanList = [];

if ($conn) {
  try {
    // 1. Ambil semua aturan
    $stmtA = $conn->query("
      SELECT id_aturan, nama_aturan, output_nilai, keterangan
      FROM aturan_fuzzy
      ORDER BY id_aturan ASC
    ");
    $aturanRows = $stmtA->fetchAll();

    // 2. Ambil semua detail sekaligus (lebih efisien dari N+1 query)
    $stmtD = $conn->query("
      SELECT
        d.id_aturan,
        d.id_himpunan,
        d.operator,
        h.nama_himpunan,
        k.nama_kriteria
      FROM detail_aturan_fuzzy d
      JOIN himpunan_fuzzy h ON h.id_himpunan = d.id_himpunan
      JOIN kriteria k       ON k.id_kriteria  = h.id_kriteria
      ORDER BY d.id_aturan ASC, d.id_detail ASC
    ");
    $detailRows = $stmtD->fetchAll();

    // 3. Grup detail per id_aturan
    $detailByAturan = [];
    foreach ($detailRows as $d) {
      $detailByAturan[$d['id_aturan']][] = $d;
    }

    // 4. Susun $aturanList
    foreach ($aturanRows as $a) {
      $aturanList[] = [
        'id' => $a['id_aturan'],
        'nama' => $a['nama_aturan'],
        'output' => $a['output_nilai'],
        'keterangan' => $a['keterangan'],
        'kondisi' => $detailByAturan[$a['id_aturan']] ?? [],
      ];
    }
  } catch (\PDOException $e) {
    $errorMsg = 'Gagal mengambil data aturan: ' . $e->getMessage();
  }
}

// ── Statistik ────────────────────────────────────────────────
$total = count($aturanList);
$groups = array_count_values(array_column($aturanList, 'keterangan'));

// ── Warna per kategori ───────────────────────────────────────
$outputColors = [
  'Tidak Layak' => ['bg' => '#fef2f2', 'text' => '#991b1b', 'border' => '#fecaca', 'bar' => '#ef4444'],
  'Cukup Layak' => ['bg' => '#fffbeb', 'text' => '#92400e', 'border' => '#fde68a', 'bar' => '#f59e0b'],
  'Layak' => ['bg' => '#f0fdf4', 'text' => '#166534', 'border' => '#bbf7d0', 'bar' => '#10b981'],
  'Sangat Layak' => ['bg' => '#eff6ff', 'text' => '#1e40af', 'border' => '#bfdbfe', 'bar' => '#3b82f6'],
];
?>

<!-- ── Flash Alert ── -->
<?php if ($successMsg): ?>
  <div class="alert alert-success" style="margin-bottom:20px;">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round"
        d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
    </svg>
    <?= htmlspecialchars($successMsg) ?>
  </div>
<?php endif; ?>

<?php if ($errorMsg): ?>
  <div class="alert alert-danger" style="margin-bottom:20px;">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round"
        d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
    </svg>
    <?= $errorMsg ?>
  </div>
<?php endif; ?>

<!-- ── Page Header ── -->
<div class="page-header">
  <div>
    <h1 class="page-title">Aturan Fuzzy</h1>
    <p class="page-subtitle">Definisi rule base IF-THEN untuk inferensi Fuzzy Sugeno</p>
  </div>
  <a href="app.php?page=aturan/add&action=add" class="btn btn-primary">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
    </svg>
    Tambah Aturan
  </a>
</div>

<!-- ── Stats ── -->
<div class="stats-grid" style="margin-bottom:24px;">
  <?php
  $statItems = [
    ['Total Aturan', $total, 'var(--primary)', '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z"/>'],
    ['Sangat Layak', $groups['Sangat Layak'] ?? 0, 'var(--info)', '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>'],
    ['Layak', $groups['Layak'] ?? 0, 'var(--success)', '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>'],
    ['Tidak Layak', ($groups['Tidak Layak'] ?? 0) + ($groups['Cukup Layak'] ?? 0), 'var(--danger)', '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>'],
  ];
  foreach ($statItems as [$label, $val, $color, $path]): ?>
    <div class="stat-card" style="--icon-color:<?= $color ?>">
      <div class="stat-card-icon">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <?= $path ?>
        </svg>
      </div>
      <div>
        <span class="stat-card-value"><?= $val ?></span>
        <span class="stat-card-label"><?= $label ?></span>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<!-- ── Rule Cards ── -->
<?php if (empty($aturanList)): ?>
  <div class="card" style="padding:48px 24px;text-align:center;">
    <div class="empty-state">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
        class="size-6">
        <path stroke-linecap="round" stroke-linejoin="round"
          d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
      </svg>
      <p class="empty-state-title">Belum ada aturan fuzzy</p>
      <p class="empty-state-desc">Klik tombol <strong>Tambah Aturan</strong> untuk menambahkan aturan baru.</p>
    </div>
  </div>

<?php else: ?>
  <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:24px;">
    <?php foreach ($aturanList as $i => $a):
      $oc = $outputColors[$a['keterangan']] ?? $outputColors['Layak'];
      $pct = min(100, max(0, (float) $a['output']));
      ?>
      <div class="card" style="overflow:visible;">
        <div style="display:flex;align-items:stretch;padding:0;">

          <!-- Nomor rule -->
          <div style="
        width:54px;
        display:flex;align-items:center;justify-content:center;
        font-family:'IBM Plex Mono',monospace;font-size:12.5px;font-weight:700;
        color:var(--text-muted);border-right:1.5px solid var(--border);
        flex-shrink:0;padding:18px 0;">
            R<?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?>
          </div>

          <!-- Konten IF-THEN -->
          <div style="flex:1;padding:14px 18px;">

            <!-- Nama aturan (jika ada) -->
            <?php if (!empty($a['nama'])): ?>
              <div
                style="font-size:11px;font-weight:700;letter-spacing:.05em;color:var(--text-muted);margin-bottom:8px;text-transform:uppercase;">
                <?= htmlspecialchars($a['nama']) ?>
              </div>
            <?php endif; ?>

            <!-- Baris IF-THEN token -->
            <div style="display:flex;align-items:center;flex-wrap:wrap;gap:6px;">

              <?php if (empty($a['kondisi'])): ?>
                <span style="font-size:12.5px;color:var(--text-light);font-style:italic;">— Tidak ada kondisi —</span>
              <?php else:
                foreach ($a['kondisi'] as $ci => $cond):
                  // Operator: baris pertama selalu IF, sisanya AND/OR dari DB
                  $isFirst = ($ci === 0);
                  $opLabel = $isFirst ? 'IF' : ($cond['operator'] ?? 'AND');
                  $opStyle = $isFirst
                    ? 'background:var(--surface-2);color:var(--text-muted);border:1px solid var(--border);'
                    : ($opLabel === 'OR'
                      ? 'background:#fdf4ff;color:#7e22ce;border:1px solid #d8b4fe;'
                      : 'background:var(--primary-light);color:var(--primary);border:1px solid #c7d2fe;');
                  ?>
                  <span
                    style="font-size:10.5px;font-weight:800;letter-spacing:.08em;padding:3px 8px;border-radius:5px;<?= $opStyle ?>">
                    <?= $opLabel ?>
                  </span>
                  <span style="font-size:13px;font-weight:500;color:var(--text);">
                    <?= htmlspecialchars($cond['nama_kriteria']) ?>
                  </span>
                  <span style="font-size:12px;color:var(--text-muted);">adalah</span>
                  <span
                    style="font-size:13px;font-weight:700;color:var(--text);background:var(--surface-2);padding:3px 10px;border-radius:6px;border:1px solid var(--border);">
                    <?= htmlspecialchars($cond['nama_himpunan']) ?>
                  </span>
                <?php endforeach; endif; ?>

              <!-- THEN -->
              <span
                style="font-size:10.5px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#059669;background:#ecfdf5;padding:3px 8px;border-radius:5px;border:1px solid #a7f3d0;margin-left:4px;">
                THEN
              </span>
              <span style="font-size:14px;font-weight:800;color:var(--text);font-family:'IBM Plex Mono',monospace;">
                z = <?= htmlspecialchars((string) $a['output']) ?>
              </span>

            </div>
          </div>

          <!-- Kanan: output badge + bar + aksi -->
          <div
            style="display:flex;align-items:center;gap:14px;padding:14px 18px;border-left:1.5px solid var(--border);flex-shrink:0;">

            <!-- Progress bar output -->
            <div style="width:76px;">
              <div
                style="font-size:10px;color:var(--text-muted);margin-bottom:4px;text-align:right;font-family:'IBM Plex Mono',monospace;">
                <?= $pct ?>
              </div>
              <div style="height:5px;border-radius:3px;background:var(--border);overflow:hidden;">
                <div
                  style="height:100%;width:<?= $pct ?>%;background:<?= $oc['bar'] ?>;border-radius:3px;transition:width .6s;">
                </div>
              </div>
            </div>

            <!-- Kategori badge -->
            <span style="
          padding:5px 12px;border-radius:20px;font-size:12px;font-weight:600;white-space:nowrap;
          background:<?= $oc['bg'] ?>;color:<?= $oc['text'] ?>;border:1px solid <?= $oc['border'] ?>;">
              <?= htmlspecialchars($a['keterangan']) ?>
            </span>

            <!-- Action buttons -->
            <div class="action-group">
              <a href="app.php?page=aturan/edit&action=edit&id=<?= $a['id'] ?>" class="btn-edit">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                  stroke="currentColor" style="width:13px;height:13px;">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                </svg>
                Edit
              </a>
              <button class="btn-delete"
                onclick="confirmDelete(<?= $a['id'] ?>, '<?= htmlspecialchars($a['keterangan'], ENT_QUOTES) ?>', '<?= htmlspecialchars($a['nama'], ENT_QUOTES) ?>')">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                  stroke="currentColor" style="width:13px;height:13px;">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                </svg>
                Hapus
              </button>
            </div>

          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<!-- ── Modal Konfirmasi Hapus ── -->
<div class="modal-overlay" id="modalDelete">
  <div class="modal-box">
    <div class="modal-icon">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round"
          d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
      </svg>
    </div>
    <h3 class="modal-title">Hapus Aturan?</h3>
    <p class="modal-desc">
      Aturan <strong id="deleteAturanLabel"></strong> akan dihapus permanen beserta semua kondisinya.
      Tindakan ini tidak dapat dikembalikan.
    </p>
    <form method="POST" action="" id="formDelete">
      <input type="hidden" name="delete_id" id="deleteInputId" value="">
      <div class="modal-actions">
        <button type="button" class="btn-outline" onclick="closeModal('modalDelete')">Batal</button>
        <button type="submit" class="btn-danger">Ya, Hapus</button>
      </div>
    </form>
  </div>
</div>

<script>
  function openModal(id) { document.getElementById(id).classList.add('is-open'); }
  function closeModal(id) { document.getElementById(id).classList.remove('is-open'); }

  // Tutup modal saat klik backdrop
  document.querySelectorAll('.modal-overlay').forEach(m => {
    m.addEventListener('click', e => { if (e.target === m) m.classList.remove('is-open'); });
  });

  // ── Confirm delete ───────────────────────────────────────────
  function confirmDelete(id, kategori, nama) {
    document.getElementById('deleteInputId').value = id;
    const label = nama ? `"${nama}" (${kategori})` : `kategori ${kategori}`;
    document.getElementById('deleteAturanLabel').textContent = label;
    openModal('modalDelete');
  }
</script>