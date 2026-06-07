<?php
/**
 * export_excel.php
 * Fitur Export Rekapitulasi Absensi ke Excel
 * Branch: hasbi
 * Dibuat oleh: Hasbi
 */

include "../config/koneksi.php";

// Ambil parameter filter dari GET
$cari    = isset($_GET['cari'])    ? trim($_GET['cari'])    : '';
$kelas   = isset($_GET['kelas'])   ? trim($_GET['kelas'])   : '';
$tanggal = isset($_GET['tanggal']) ? trim($_GET['tanggal']) : '';
$mode    = isset($_GET['mode'])    ? trim($_GET['mode'])    : 'detail'; // 'detail' atau 'rekap'

// ─── Query Data ───────────────────────────────────────────────────────────────

if ($mode === 'rekap') {
    // Mode Rekap: Ringkasan kehadiran per siswa
    try {
        $sql = "
            SELECT
                s.nis,
                s.nama,
                s.kelas,
                COUNT(a.id)                                        AS total_absensi,
                SUM(CASE WHEN a.status = 'Hadir'      THEN 1 ELSE 0 END) AS total_hadir,
                SUM(CASE WHEN a.status = 'Terlambat'  THEN 1 ELSE 0 END) AS total_terlambat,
                MIN(a.tanggal)::TEXT                               AS pertama_absen,
                MAX(a.tanggal)::TEXT                               AS terakhir_absen
            FROM siswa s
            LEFT JOIN absensi a ON s.nis = a.nis
        ";
        $params = [];

        if ($cari !== '') {
            $sql .= " WHERE (s.nama ILIKE :cari OR s.nis ILIKE :cari)";
            $params[':cari'] = "%$cari%";
        }

        if ($kelas !== '') {
            $whereKelas = $cari !== '' ? " AND" : " WHERE";
            $sql .= "$whereKelas s.kelas = :kelas";
            $params[':kelas'] = $kelas;
        }

        $sql .= " GROUP BY s.nis, s.nama, s.kelas ORDER BY s.kelas ASC, s.nama ASC";
        $stmt = $koneksi->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }

    // ─── Header Excel ───────────────────────────────────────────────────────
    $namaFile = "Rekap_Absensi_Siswa_" . date("Y-m-d_His") . ".xls";
    header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
    header("Content-Disposition: attachment; filename=\"$namaFile\"");
    header("Cache-Control: max-age=0");

    echo "\xEF\xBB\xBF"; // BOM UTF-8 agar Excel baca dengan benar

    // ─── Mulai Output XML Excel ─────────────────────────────────────────────
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
         xmlns:o="urn:schemas-microsoft-com:office:office"
         xmlns:x="urn:schemas-microsoft-com:office:excel"
         xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
         xmlns:html="http://www.w3.org/TR/REC-html40">' . "\n";

    // Style
    echo '<Styles>
      <Style ss:ID="h1">
        <Font ss:Bold="1" ss:Size="14"/>
        <Alignment ss:Horizontal="Center"/>
      </Style>
      <Style ss:ID="h2">
        <Font ss:Bold="1" ss:Size="11" ss:Color="#FFFFFF"/>
        <Interior ss:Color="#8B008B" ss:Pattern="Solid"/>
        <Alignment ss:Horizontal="Center"/>
        <Borders>
          <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
          <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
          <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
          <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
        </Borders>
      </Style>
      <Style ss:ID="hadir">
        <Font ss:Color="#006400"/>
        <Borders>
          <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
          <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
          <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
        </Borders>
      </Style>
      <Style ss:ID="terlambat">
        <Font ss:Color="#CC0000"/>
        <Borders>
          <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
          <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
          <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
        </Borders>
      </Style>
      <Style ss:ID="normal">
        <Borders>
          <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
          <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
          <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
        </Borders>
      </Style>
      <Style ss:ID="zebra">
        <Interior ss:Color="#F5E6FF" ss:Pattern="Solid"/>
        <Borders>
          <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
          <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
          <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
        </Borders>
      </Style>
      <Style ss:ID="total">
        <Font ss:Bold="1"/>
        <Interior ss:Color="#EDE0FF" ss:Pattern="Solid"/>
        <Borders>
          <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="2"/>
          <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="2"/>
          <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
          <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
        </Borders>
      </Style>
    </Styles>' . "\n";

    echo '<Worksheet ss:Name="Rekap Per Siswa">' . "\n";
    echo '<Table ss:DefaultColumnWidth="120">' . "\n";

    // Lebar kolom
    echo '<Column ss:Width="60"/>';   // No
    echo '<Column ss:Width="100"/>';  // NIS
    echo '<Column ss:Width="200"/>';  // Nama
    echo '<Column ss:Width="100"/>';  // Kelas
    echo '<Column ss:Width="100"/>';  // Total Absensi
    echo '<Column ss:Width="100"/>';  // Total Hadir
    echo '<Column ss:Width="110"/>';  // Total Terlambat
    echo '<Column ss:Width="130"/>';  // Pertama Absen
    echo '<Column ss:Width="130"/>';  // Terakhir Absen

    // Judul utama
    echo '<Row><Cell ss:MergeAcross="8" ss:StyleID="h1"><Data ss:Type="String">REKAPITULASI KEHADIRAN SISWA</Data></Cell></Row>' . "\n";
    echo '<Row><Cell ss:MergeAcross="8"><Data ss:Type="String">Sistem Absensi Face ID | Tanggal Cetak: ' . date("d/m/Y H:i") . '</Data></Cell></Row>' . "\n";
    echo '<Row><Cell><Data ss:Type="String"></Data></Cell></Row>' . "\n"; // baris kosong

    // Header tabel
    echo '<Row>' . "\n";
    $headers = ['No', 'NIS', 'Nama Lengkap', 'Kelas', 'Total Absensi', 'Total Hadir', 'Total Terlambat', 'Pertama Absen', 'Terakhir Absen'];
    foreach ($headers as $h) {
        echo '<Cell ss:StyleID="h2"><Data ss:Type="String">' . htmlspecialchars($h) . '</Data></Cell>';
    }
    echo "\n</Row>\n";

    // Data baris
    $no = 1;
    $grandTotalAbsensi   = 0;
    $grandTotalHadir     = 0;
    $grandTotalTerlambat = 0;

    foreach ($rows as $i => $row) {
        $style = ($i % 2 === 0) ? 'normal' : 'zebra';
        $grandTotalAbsensi   += (int)$row['total_absensi'];
        $grandTotalHadir     += (int)$row['total_hadir'];
        $grandTotalTerlambat += (int)$row['total_terlambat'];

        $pertama  = $row['pertama_absen']  ? date("d/m/Y", strtotime($row['pertama_absen']))  : '-';
        $terakhir = $row['terakhir_absen'] ? date("d/m/Y", strtotime($row['terakhir_absen'])) : '-';

        echo '<Row>' . "\n";
        echo '<Cell ss:StyleID="' . $style . '"><Data ss:Type="Number">' . $no++ . '</Data></Cell>';
        echo '<Cell ss:StyleID="' . $style . '"><Data ss:Type="String">' . htmlspecialchars($row['nis']) . '</Data></Cell>';
        echo '<Cell ss:StyleID="' . $style . '"><Data ss:Type="String">' . htmlspecialchars($row['nama']) . '</Data></Cell>';
        echo '<Cell ss:StyleID="' . $style . '"><Data ss:Type="String">' . htmlspecialchars($row['kelas']) . '</Data></Cell>';
        echo '<Cell ss:StyleID="' . $style . '"><Data ss:Type="Number">' . (int)$row['total_absensi'] . '</Data></Cell>';
        echo '<Cell ss:StyleID="hadir"><Data ss:Type="Number">' . (int)$row['total_hadir'] . '</Data></Cell>';
        echo '<Cell ss:StyleID="terlambat"><Data ss:Type="Number">' . (int)$row['total_terlambat'] . '</Data></Cell>';
        echo '<Cell ss:StyleID="' . $style . '"><Data ss:Type="String">' . $pertama . '</Data></Cell>';
        echo '<Cell ss:StyleID="' . $style . '"><Data ss:Type="String">' . $terakhir . '</Data></Cell>';
        echo "\n</Row>\n";
    }

    // Baris total
    echo '<Row>' . "\n";
    echo '<Cell ss:MergeAcross="3" ss:StyleID="total"><Data ss:Type="String">TOTAL KESELURUHAN</Data></Cell>';
    echo '<Cell ss:StyleID="total"><Data ss:Type="Number">' . $grandTotalAbsensi . '</Data></Cell>';
    echo '<Cell ss:StyleID="total"><Data ss:Type="Number">' . $grandTotalHadir . '</Data></Cell>';
    echo '<Cell ss:StyleID="total"><Data ss:Type="Number">' . $grandTotalTerlambat . '</Data></Cell>';
    echo '<Cell ss:StyleID="total"><Data ss:Type="String"></Data></Cell>';
    echo '<Cell ss:StyleID="total"><Data ss:Type="String"></Data></Cell>';
    echo "\n</Row>\n";

    echo '</Table>' . "\n";
    echo '</Worksheet>' . "\n";
    echo '</Workbook>' . "\n";

} else {
    // Mode Detail: Log absensi lengkap per-baris
    try {
        $sql = "SELECT id, nis, nama, kelas, tanggal, jam, status FROM absensi WHERE 1=1";
        $params = [];

        if ($cari !== '') {
            $sql .= " AND (nama ILIKE :cari OR nis ILIKE :cari)";
            $params[':cari'] = "%$cari%";
        }

        if ($kelas !== '') {
            $sql .= " AND kelas = :kelas";
            $params[':kelas'] = $kelas;
        }

        if ($tanggal !== '') {
            $sql .= " AND tanggal = :tanggal";
            $params[':tanggal'] = $tanggal;
        }

        $sql .= " ORDER BY tanggal DESC, jam DESC";
        $stmt = $koneksi->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }

    // ─── Header File Excel ───────────────────────────────────────────────────
    $namaFile = "Log_Absensi_" . ($tanggal ?: date("Y-m-d")) . "_" . date("His") . ".xls";
    header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
    header("Content-Disposition: attachment; filename=\"$namaFile\"");
    header("Cache-Control: max-age=0");

    echo "\xEF\xBB\xBF"; // BOM UTF-8

    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
         xmlns:o="urn:schemas-microsoft-com:office:office"
         xmlns:x="urn:schemas-microsoft-com:office:excel"
         xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
         xmlns:html="http://www.w3.org/TR/REC-html40">' . "\n";

    // Style
    echo '<Styles>
      <Style ss:ID="h1">
        <Font ss:Bold="1" ss:Size="14"/>
        <Alignment ss:Horizontal="Center"/>
      </Style>
      <Style ss:ID="h2">
        <Font ss:Bold="1" ss:Size="11" ss:Color="#FFFFFF"/>
        <Interior ss:Color="#8B008B" ss:Pattern="Solid"/>
        <Alignment ss:Horizontal="Center"/>
        <Borders>
          <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
          <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
          <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
          <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
        </Borders>
      </Style>
      <Style ss:ID="hadir">
        <Font ss:Bold="1" ss:Color="#006400"/>
        <Borders>
          <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
          <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
          <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
        </Borders>
        <Alignment ss:Horizontal="Center"/>
      </Style>
      <Style ss:ID="terlambat">
        <Font ss:Bold="1" ss:Color="#CC0000"/>
        <Borders>
          <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
          <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
          <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
        </Borders>
        <Alignment ss:Horizontal="Center"/>
      </Style>
      <Style ss:ID="normal">
        <Borders>
          <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
          <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
          <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
        </Borders>
      </Style>
      <Style ss:ID="zebra">
        <Interior ss:Color="#F5E6FF" ss:Pattern="Solid"/>
        <Borders>
          <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
          <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
          <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
        </Borders>
      </Style>
      <Style ss:ID="center">
        <Alignment ss:Horizontal="Center"/>
        <Borders>
          <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
          <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
          <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
        </Borders>
      </Style>
      <Style ss:ID="center_zebra">
        <Alignment ss:Horizontal="Center"/>
        <Interior ss:Color="#F5E6FF" ss:Pattern="Solid"/>
        <Borders>
          <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
          <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
          <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
        </Borders>
      </Style>
    </Styles>' . "\n";

    echo '<Worksheet ss:Name="Log Absensi">' . "\n";
    echo '<Table ss:DefaultColumnWidth="120">' . "\n";

    // Lebar kolom
    echo '<Column ss:Width="50"/>';   // No
    echo '<Column ss:Width="100"/>';  // NIS
    echo '<Column ss:Width="200"/>';  // Nama
    echo '<Column ss:Width="100"/>';  // Kelas
    echo '<Column ss:Width="110"/>';  // Tanggal
    echo '<Column ss:Width="100"/>';  // Jam Absen
    echo '<Column ss:Width="110"/>';  // Status

    // Judul
    $filterInfo = [];
    if ($cari)    $filterInfo[] = "Pencarian: $cari";
    if ($kelas)   $filterInfo[] = "Kelas: $kelas";
    if ($tanggal) $filterInfo[] = "Tanggal: " . date("d/m/Y", strtotime($tanggal));
    $filterStr = !empty($filterInfo) ? implode(' | ', $filterInfo) : 'Semua Data';

    echo '<Row><Cell ss:MergeAcross="6" ss:StyleID="h1"><Data ss:Type="String">LOG REKAPITULASI ABSENSI FACE ID</Data></Cell></Row>' . "\n";
    echo '<Row><Cell ss:MergeAcross="6"><Data ss:Type="String">Sistem Absensi Face ID | Filter: ' . htmlspecialchars($filterStr) . '</Data></Cell></Row>' . "\n";
    echo '<Row><Cell ss:MergeAcross="6"><Data ss:Type="String">Tanggal Cetak: ' . date("d/m/Y H:i:s") . '</Data></Cell></Row>' . "\n";
    echo '<Row><Cell><Data ss:Type="String"></Data></Cell></Row>' . "\n";

    // Header kolom
    echo '<Row>' . "\n";
    $headers = ['No', 'NIS', 'Nama Lengkap', 'Kelas', 'Tanggal', 'Jam Absen', 'Status'];
    foreach ($headers as $h) {
        echo '<Cell ss:StyleID="h2"><Data ss:Type="String">' . htmlspecialchars($h) . '</Data></Cell>';
    }
    echo "\n</Row>\n";

    // Data
    $no = 1;
    foreach ($rows as $i => $row) {
        $isZebra = ($i % 2 !== 0);
        $styleNormal = $isZebra ? 'zebra' : 'normal';
        $styleCenter = $isZebra ? 'center_zebra' : 'center';
        $styleStatus = ($row['status'] === 'Hadir') ? 'hadir' : 'terlambat';

        echo '<Row>' . "\n";
        echo '<Cell ss:StyleID="' . $styleCenter . '"><Data ss:Type="Number">' . $no++ . '</Data></Cell>';
        echo '<Cell ss:StyleID="' . $styleNormal . '"><Data ss:Type="String">' . htmlspecialchars($row['nis']) . '</Data></Cell>';
        echo '<Cell ss:StyleID="' . $styleNormal . '"><Data ss:Type="String">' . htmlspecialchars($row['nama']) . '</Data></Cell>';
        echo '<Cell ss:StyleID="' . $styleCenter . '"><Data ss:Type="String">' . htmlspecialchars($row['kelas']) . '</Data></Cell>';
        echo '<Cell ss:StyleID="' . $styleCenter . '"><Data ss:Type="String">' . date("d/m/Y", strtotime($row['tanggal'])) . '</Data></Cell>';
        echo '<Cell ss:StyleID="' . $styleCenter . '"><Data ss:Type="String">' . htmlspecialchars($row['jam']) . '</Data></Cell>';
        echo '<Cell ss:StyleID="' . $styleStatus . '"><Data ss:Type="String">' . htmlspecialchars($row['status']) . '</Data></Cell>';
        echo "\n</Row>\n";
    }

    // Baris info jumlah
    $totalHadir    = count(array_filter($rows, fn($r) => $r['status'] === 'Hadir'));
    $totalTerlambat = count(array_filter($rows, fn($r) => $r['status'] === 'Terlambat'));

    echo '<Row><Cell><Data ss:Type="String"></Data></Cell></Row>' . "\n";
    echo '<Row>
      <Cell ss:MergeAcross="4"><Data ss:Type="String">Total Record: ' . count($rows) . '</Data></Cell>
      <Cell><Data ss:Type="String">Hadir: ' . $totalHadir . '</Data></Cell>
      <Cell><Data ss:Type="String">Terlambat: ' . $totalTerlambat . '</Data></Cell>
    </Row>' . "\n";

    echo '</Table>' . "\n";
    echo '</Worksheet>' . "\n";
    echo '</Workbook>' . "\n";
}
?>
