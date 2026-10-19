<?php
require_once './DBConfig.php';
require_once __DIR__ . '/../vendor/autoload.php';

$format = $_GET['format'] ?? '';

$stmt = $conn->query("SELECT 
    hp.id_hasil, 
    s.name as siswa, 
    s.jenis_kelamin as jk , 
    s.tinggi_badan as tinggi,
    s.berat_badan as berat,
    hp.nilai_defuzzifikasi as defuzz,
    hp.kategori,
    hp.tanggal_hitung as tgl
FROM hasil_perhitungan hp 
INNER JOIN siswa s ON s.id_siswa = hp.id_siswa
ORDER BY hp.nilai_defuzzifikasi DESC");

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

switch ($format) {

  case 'csv':
    exportCSV($data);
    break;

  case 'pdf':
    exportPDF($data);
    break;

  default:
    exit("Format tidak valid");
}


/* ================= CSV ================= */

function exportCSV($data)
{
  header('Content-Type: text/csv');
  header('Content-Disposition: attachment; filename="hasil_perhitungan.csv"');

  $output = fopen("php://output", "w");

  fputcsv($output, [
    'Rank',
    'Siswa',
    // 'Kelas',
    'Jenis Kelamin',
    'Tinggi',
    'Berat',
    'Defuzz',
    'Kategori',
    'Tanggal'
  ]);

  $rank = 1;
  foreach ($data as $row) {

    fputcsv($output, [
      $rank++,
      $row['siswa'],
      // $row['kelas'],
      $row['jk'],
      $row['tinggi'],
      $row['berat'],
      $row['defuzz'],
      $row['kategori'],
      $row['tgl']
    ]);
  }

  fclose($output);
  exit;
}

/* ================= PDF ================= */

function exportPDF($data)
{
  $html = "
    <h2>Hasil Perhitungan Fuzzy Sugeno</h2>
    <table border='1' cellpadding='6' cellspacing='0' width='100%'>
    <tr>
        <th>Rank</th>
        <th>Siswa</th>
        <th>Tinggi Badan</th>
        <th>Berat Badan</th>
        <th>Defuzz</th>
        <th>Kategori</th>
        <th>Tanggal</th>
    </tr>
    ";

  $rank = 1;

  foreach ($data as $row) {

    $html .= "
        <tr>
            <td>{$rank}</td>
            <td>{$row['siswa']}</td>
            <td>{$row['tinggi']}</td>
            <td>{$row['berat']}</td>
            <td>{$row['defuzz']}</td>
            <td>{$row['kategori']}</td>
            <td>{$row['tgl']}</td>
        </tr>
        ";

    $rank++;
  }

  $html .= "</table>";
  $dompdf = new \Dompdf\Dompdf();
  $dompdf->loadHtml($html);
  $dompdf->setPaper('A4', 'portrait');
  $dompdf->render();
  $dompdf->stream("hasil_perhitungan.pdf");
}