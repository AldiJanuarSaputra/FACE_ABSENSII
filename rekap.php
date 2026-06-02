<?php
include "koneksi.php";

// Ambil parameter filter dari GET
$cari    = isset($_GET['cari']) ? trim($_GET['cari']) : '';
$kelas   = isset($_GET['kelas']) ? trim($_GET['kelas']) : '';
$tanggal = isset($_GET['tanggal']) ? trim($_GET['tanggal']) : '';

$pesanSukses = '';
// ── Handle Hapus Log Absensi (Delete) ──────────────────
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        $stmt = $koneksi->prepare("DELETE FROM absensi WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $pesanSukses = "Log absensi berhasil dihapus!";
    } catch (PDOException $e) {
        $errorDb = "Gagal menghapus log absensi: " . $e->getMessage();
    }
}

// 1. Hitung statistik
try {
    // Total Siswa
    $qSiswa = $koneksi->query("SELECT COUNT(*) FROM siswa");
    $totalSiswa = $qSiswa->fetchColumn();

    // Total Hadir Hari Ini / Semua (Hadir)
    $qHadir = $koneksi->query("SELECT COUNT(*) FROM absensi WHERE status = 'Hadir'");
    $totalHadir = $qHadir->fetchColumn();

    // Total Terlambat
    $qLambat = $koneksi->query("SELECT COUNT(*) FROM absensi WHERE status = 'Terlambat'");
    $totalLambat = $qLambat->fetchColumn();
} catch (PDOException $e) {
    $totalSiswa = 0;
    $totalHadir = 0;
    $totalLambat = 0;
}

// 2. Query Kelas Unik untuk Dropdown Filter
$listKelas = [];
try {
    $qKl = $koneksi->query("SELECT DISTINCT kelas FROM siswa UNION SELECT DISTINCT kelas FROM absensi ORDER BY kelas ASC");
    while($rk = $qKl->fetch(PDO::FETCH_ASSOC)) {
        if(!empty($rk['kelas'])) $listKelas[] = $rk['kelas'];
    }
} catch (PDOException $e) {}

// 3. Query Utama Rekap Absensi
$logs = [];
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
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errorDb = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Rekapitulasi Absensi Face ID</title>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Outfit', sans-serif; }

body {
    min-height: 100vh;
    background: linear-gradient(135deg, #000000, #130113, #240024);
    color: #fff;
    padding: 30px 20px;
}

.wrapper {
    max-width: 1000px;
    margin: 0 auto;
}

header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    padding-bottom: 20px;
}

h1 { font-size: 28px; font-weight: 700; color: #fff; }
h1 span { color: #ff1493; }
.btn-back {
    padding: 10px 18px;
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: 10px;
    color: #fff;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    transition: 0.3s;
}
.btn-back:hover { background: rgba(255,255,255,0.15); transform: translateY(-2px); }

/* Grid Statistik */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.card {
    background: rgba(255,255,255,0.06);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 16px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.25);
}

.card-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}

.card-total {
    background: rgba(0, 191, 255, 0.15);
    color: #00bfff;
    border: 1px solid rgba(0, 191, 255, 0.3);
}

.card-hadir {
    background: rgba(127, 255, 127, 0.15);
    color: #7fff7f;
    border: 1px solid rgba(127, 255, 127, 0.3);
}

.card-lambat {
    background: rgba(255, 107, 107, 0.15);
    color: #ff6b6b;
    border: 1px solid rgba(255, 107, 107, 0.3);
}

.card-info h3 { font-size: 13px; color: #aaa; text-transform: uppercase; letter-spacing: 1px; }
.card-info p { font-size: 28px; font-weight: 700; }

/* Filter Box */
.filter-wrap {
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 25px;
}

.filter-form {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    align-items: flex-end;
}

.filter-group {
    flex: 1;
    min-width: 180px;
}

.filter-group label {
    display: block;
    font-size: 13px;
    color: #bbb;
    margin-bottom: 8px;
    font-weight: 600;
}

.filter-group input, .filter-group select {
    width: 100%;
    padding: 12px;
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: 10px;
    color: #fff;
    font-size: 14px;
    outline: none;
}
.filter-group select option { background: #1a001a; color: #fff; }

.btn-filter {
    padding: 12px 24px;
    background: linear-gradient(90deg, #ff1493, #ff69b4);
    border: none;
    border-radius: 10px;
    color: #fff;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    transition: 0.3s;
    outline: none;
}
.btn-filter:hover { transform: translateY(-2px); box-shadow: 0 0 15px #ff69b4; }

.btn-reset {
    padding: 12px 20px;
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: 10px;
    color: #fff;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    text-align: center;
    transition: 0.3s;
}
.btn-reset:hover { background: rgba(255,255,255,0.2); }

.btn-print {
    padding: 12px 20px;
    background: linear-gradient(90deg, #00bfff, #1e90ff);
    border: none;
    border-radius: 10px;
    color: #fff;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    transition: 0.3s;
}
.btn-print:hover { transform: translateY(-2px); box-shadow: 0 0 15px #00bfff; }

.btn-excel {
    padding: 12px 20px;
    background: linear-gradient(90deg, #1a7a1a, #2ecc40);
    border: none;
    border-radius: 10px;
    color: #fff;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    transition: 0.3s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.btn-excel:hover { transform: translateY(-2px); box-shadow: 0 0 15px #2ecc40; }

.btn-excel-rekap {
    padding: 12px 20px;
    background: linear-gradient(90deg, #7b4f00, #f39c12);
    border: none;
    border-radius: 10px;
    color: #fff;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    transition: 0.3s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.btn-excel-rekap:hover { transform: translateY(-2px); box-shadow: 0 0 15px #f39c12; }

/* Tabs */
.tab-wrap {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
}
.tab-btn {
    padding: 10px 20px;
    border-radius: 10px;
    border: 1px solid rgba(255,255,255,0.15);
    background: rgba(255,255,255,0.05);
    color: #aaa;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.3s;
}
.tab-btn.active {
    background: linear-gradient(90deg,#ff1493,#ff69b4);
    color: #fff;
    border-color: transparent;
}
.tab-content { display: none; }
.tab-content.active { display: block; }

/* Rekap per siswa table */
.rekap-table th { color: #ffd700; }
.pct-bar-wrap { background: rgba(255,255,255,0.1); border-radius: 20px; height: 8px; width: 80px; display:inline-block; vertical-align:middle; margin-left:6px; }
.pct-bar { height: 8px; border-radius: 20px; background: linear-gradient(90deg,#00e676,#1de9b6); }

/* Table Section */
.table-container {
    background: rgba(255, 255, 255, 0.04);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 16px;
    overflow-x: auto;
    box-shadow: 0 8px 32px rgba(0,0,0,0.3);
}

table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
}

th {
    background: rgba(255, 255, 255, 0.08);
    color: #ff9edb;
    padding: 16px 20px;
    font-size: 14px;
    font-weight: 600;
    border-bottom: 1px solid rgba(255,255,255,0.12);
}

td {
    padding: 16px 20px;
    font-size: 14px;
    border-bottom: 1px solid rgba(255,255,255,0.06);
}

tr:hover td {
    background: rgba(255, 255, 255, 0.03);
}

/* Badges */
.badge {
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 700;
    display: inline-block;
}

.badge-hadir {
    background: rgba(127, 255, 127, 0.15);
    color: #7fff7f;
    border: 1px solid rgba(127, 255, 127, 0.3);
}

.badge-lambat {
    background: rgba(255, 107, 107, 0.15);
    color: #ff6b6b;
    border: 1px solid rgba(255, 107, 107, 0.3);
}

.empty-state {
    padding: 50px;
    text-align: center;
    color: #aaa;
    font-size: 16px;
}

/* Print Styles */
@media print {
    body { background: #fff !important; color: #000 !important; padding: 0; }
    .wrapper { max-width: 100% !important; }
    header, .filter-wrap, .btn-back, .btn-print, .btn-reset, .no-print { display: none !important; }
    .stats-grid { grid-template-columns: repeat(3, 1fr) !important; margin-bottom: 20px !important; }
    .card { background: none !important; border: 1px solid #ddd !important; color: #000 !important; box-shadow: none !important; }
    .card-icon { border: 1px solid #ddd !important; background: none !important; color: #000 !important; }
    .card-info h3 { color: #555 !important; }
    .card-info p { color: #000 !important; }
    .table-container { background: none !important; border: 1px solid #ccc !important; box-shadow: none !important; }
    th { background: #f0f0f0 !important; color: #000 !important; border-bottom: 2px solid #ccc !important; }
    td { border-bottom: 1px solid #eee !important; color: #000 !important; }
    .badge-hadir { background: none !important; color: green !important; border: 1px solid green !important; }
    .badge-lambat { background: none !important; color: red !important; border: 1px solid red !important; }
    
    /* Judul cetak khusus */
    body::before {
        content: "REKAPITULASI LAPORAN ABSENSI FACE ID";
        display: block;
        text-align: center;
        font-size: 22px;
        font-weight: 700;
        margin-bottom: 20px;
        color: #000;
    }
}
</style>
</head>
<body>

<div class="wrapper">
    <header>
        <h1><i class="fa-solid fa-chart-line" style="color: #ff1493; margin-right: 10px;"></i>Rekapitulasi <span>Absensi</span></h1>
        <a href="index.php" class="btn-back"><i class="fa-solid fa-house" style="margin-right: 6px;"></i>Kembali ke Menu</a>
    </header>

    <!-- Statistik Cards -->
    <div class="stats-grid">
        <div class="card">
            <div class="card-icon card-total"><i class="fa-solid fa-users"></i></div>
            <div class="card-info">
                <h3>Total Siswa</h3>
                <p><?php echo $totalSiswa; ?></p>
            </div>
        </div>
        <div class="card">
            <div class="card-icon card-hadir"><i class="fa-solid fa-circle-check"></i></div>
            <div class="card-info">
                <h3>Total Hadir</h3>
                <p><?php echo $totalHadir; ?></p>
            </div>
        </div>
        <div class="card">
            <div class="card-icon card-lambat"><i class="fa-solid fa-clock-rotate-left"></i></div>
            <div class="card-info">
                <h3>Total Terlambat</h3>
                <p><?php echo $totalLambat; ?></p>
            </div>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="filter-wrap">
        <form method="GET" class="filter-form">
            <div class="filter-group">
                <label for="cari">Cari Siswa</label>
                <input type="text" id="cari" name="cari" placeholder="Nama / NIS..." value="<?php echo htmlspecialchars($cari); ?>">
            </div>

            <div class="filter-group">
                <label for="kelas">Filter Kelas</label>
                <select id="kelas" name="kelas">
                    <option value="">-- Semua Kelas --</option>
                    <?php foreach ($listKelas as $k): ?>
                        <option value="<?php echo htmlspecialchars($k); ?>" <?php echo $kelas === $k ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($k); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label for="tanggal">Filter Tanggal</label>
                <input type="date" id="tanggal" name="tanggal" value="<?php echo htmlspecialchars($tanggal); ?>">
            </div>

            <button type="submit" class="btn-filter"><i class="fa-solid fa-magnifying-glass" style="margin-right: 6px;"></i>Filter</button>
            <a href="rekap.php" class="btn-reset"><i class="fa-solid fa-rotate-left" style="margin-right: 6px;"></i>Reset</a>
            <button type="button" class="btn-print" onclick="window.print()"><i class="fa-solid fa-print" style="margin-right: 6px;"></i>Cetak</button>
            <a id="btn-export-detail" href="export_excel.php?mode=detail<?php echo ($cari?'&cari='.urlencode($cari):'').($kelas?'&kelas='.urlencode($kelas):'').($tanggal?'&tanggal='.urlencode($tanggal):''); ?>" class="btn-excel"><i class="fa-solid fa-file-excel"></i>Export Log Excel</a>
            <a id="btn-export-rekap" href="export_excel.php?mode=rekap<?php echo ($cari?'&cari='.urlencode($cari):'').($kelas?'&kelas='.urlencode($kelas):''); ?>" class="btn-excel-rekap"><i class="fa-solid fa-file-excel"></i>Export Rekap Siswa</a>
        </form>
    </div>

    <?php if(isset($errorDb)): ?>
        <div style="background: rgba(255,107,107,0.15); border: 1px solid #ff6b6b; padding: 15px; border-radius: 10px; margin-bottom: 20px; color: #ff6b6b;">
            <i class="fa-solid fa-circle-exclamation" style="margin-right: 8px;"></i><strong>Error Database:</strong> <?php echo htmlspecialchars($errorDb); ?>
        </div>
    <?php endif; ?>

    <?php if($pesanSukses !== ''): ?>
        <div style="background: rgba(127,255,127,0.15); border: 1px solid #7fff7f; padding: 15px; border-radius: 10px; margin-bottom: 20px; color: #7fff7f; display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-circle-check"></i>
            <span><?php echo htmlspecialchars($pesanSukses); ?></span>
        </div>
    <?php endif; ?>

    <!-- Tab Navigation -->
    <div class="tab-wrap no-print">
        <button class="tab-btn active" onclick="switchTab('tab-log', this)"><i class="fa-solid fa-list" style="margin-right:6px;"></i>Log Absensi</button>
        <button class="tab-btn" onclick="switchTab('tab-rekap', this)"><i class="fa-solid fa-chart-bar" style="margin-right:6px;"></i>Rekap Per Siswa</button>
    </div>

    <!-- Tab: Log Absensi -->
    <div id="tab-log" class="tab-content active">
    <!-- Table Absensi -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th style="width: 60px;">No</th>
                    <th>NIS</th>
                    <th>Nama Lengkap</th>
                    <th>Kelas</th>
                    <th>Tanggal</th>
                    <th>Jam Absen</th>
                    <th style="width: 120px; text-align: center;">Status</th>
                    <th style="width: 100px; text-align: center;" class="no-print">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($logs) > 0): ?>
                    <?php $no = 1; foreach ($logs as $row): ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td style="font-weight: 600;"><?php echo htmlspecialchars($row['nis']); ?></td>
                            <td><?php echo htmlspecialchars($row['nama']); ?></td>
                            <td><span style="background: rgba(255,255,255,0.08); padding: 4px 10px; border-radius: 6px; font-size: 13px;"><?php echo htmlspecialchars($row['kelas']); ?></span></td>
                            <td><?php echo date("d/m/Y", strtotime($row['tanggal'])); ?></td>
                            <td style="font-family: monospace; font-size: 15px; color: #ff9edb;"><?php echo htmlspecialchars($row['jam']); ?></td>
                            <td style="text-align: center;">
                                <?php if ($row['status'] === 'Hadir'): ?>
                                    <span class="badge badge-hadir">Hadir</span>
                                <?php else: ?>
                                    <span class="badge badge-lambat">Terlambat</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;" class="no-print">
                                <button class="btn-delete" onclick="konfirmasiHapus(<?php echo $row['id']; ?>, '<?php echo addslashes($row['nama']); ?>', '<?php echo date('d/m/Y', strtotime($row['tanggal'])); ?>')" style="padding: 6px 12px; font-size: 12px; background: rgba(255, 107, 107, 0.15); color: #ff6b6b; border: 1px solid rgba(255, 107, 107, 0.3); border-radius: 6px; cursor: pointer; transition: 0.3s;">
                                    <i class="fa-solid fa-trash" style="margin-right: 4px;"></i>Hapus
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="empty-state">
                            <i class="fa-solid fa-inbox" style="margin-right: 8px;"></i>Tidak ada data absensi yang ditemukan.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    </div><!-- end tab-log -->

    <!-- Tab: Rekap Per Siswa -->
    <div id="tab-rekap" class="tab-content">
    <?php
    // Query rekap per siswa
    $rekapSiswa = [];
    try {
        $sqlRekap = "SELECT s.nis, s.nama, s.kelas,
            COUNT(a.id) AS total,
            SUM(CASE WHEN a.status='Hadir' THEN 1 ELSE 0 END) AS hadir,
            SUM(CASE WHEN a.status='Terlambat' THEN 1 ELSE 0 END) AS terlambat,
            MIN(a.tanggal)::TEXT AS pertama,
            MAX(a.tanggal)::TEXT AS terakhir
            FROM siswa s LEFT JOIN absensi a ON s.nis = a.nis";
        $pRekap = [];
        $whRekap = [];
        if ($cari !== '') { $whRekap[] = '(s.nama ILIKE :cari OR s.nis ILIKE :cari)'; $pRekap[':cari'] = "%$cari%"; }
        if ($kelas !== '') { $whRekap[] = 's.kelas = :kelas'; $pRekap[':kelas'] = $kelas; }
        if (!empty($whRekap)) $sqlRekap .= ' WHERE ' . implode(' AND ', $whRekap);
        $sqlRekap .= ' GROUP BY s.nis, s.nama, s.kelas ORDER BY s.kelas, s.nama';
        $stRekap = $koneksi->prepare($sqlRekap);
        $stRekap->execute($pRekap);
        $rekapSiswa = $stRekap->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {}
    ?>
    <div class="table-container rekap-table">
        <table>
            <thead>
                <tr>
                    <th style="width:50px">No</th>
                    <th>NIS</th>
                    <th>Nama Lengkap</th>
                    <th>Kelas</th>
                    <th style="text-align:center">Total Absen</th>
                    <th style="text-align:center">Hadir</th>
                    <th style="text-align:center">Terlambat</th>
                    <th style="text-align:center">% Hadir</th>
                    <th>Pertama Absen</th>
                    <th>Terakhir Absen</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!empty($rekapSiswa)): ?>
                <?php foreach ($rekapSiswa as $i => $rs): ?>
                <?php $pct = $rs['total'] > 0 ? round($rs['hadir'] / $rs['total'] * 100) : 0; ?>
                <tr>
                    <td><?php echo $i+1; ?></td>
                    <td style="font-weight:600"><?php echo htmlspecialchars($rs['nis']); ?></td>
                    <td><?php echo htmlspecialchars($rs['nama']); ?></td>
                    <td><span style="background:rgba(255,255,255,0.08);padding:4px 10px;border-radius:6px;font-size:13px"><?php echo htmlspecialchars($rs['kelas']); ?></span></td>
                    <td style="text-align:center;font-weight:700;color:#00bfff"><?php echo (int)$rs['total']; ?></td>
                    <td style="text-align:center;color:#7fff7f;font-weight:700"><?php echo (int)$rs['hadir']; ?></td>
                    <td style="text-align:center;color:#ff6b6b;font-weight:700"><?php echo (int)$rs['terlambat']; ?></td>
                    <td style="text-align:center">
                        <span style="font-weight:700;color:<?php echo $pct>=80?'#7fff7f':($pct>=60?'#ffd700':'#ff6b6b'); ?>"><?php echo $pct; ?>%</span>
                        <span class="pct-bar-wrap"><span class="pct-bar" style="width:<?php echo $pct; ?>%;background:<?php echo $pct>=80?'linear-gradient(90deg,#00e676,#1de9b6)':($pct>=60?'linear-gradient(90deg,#f7971e,#ffd200)':'linear-gradient(90deg,#f7071e,#ff6b6b)'); ?>"></span></span>
                    </td>
                    <td style="font-size:13px;color:#aaa"><?php echo $rs['pertama'] ? date('d/m/Y', strtotime($rs['pertama'])) : '-'; ?></td>
                    <td style="font-size:13px;color:#aaa"><?php echo $rs['terakhir'] ? date('d/m/Y', strtotime($rs['terakhir'])) : '-'; ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="10" class="empty-state"><i class="fa-solid fa-inbox" style="margin-right:8px"></i>Tidak ada data siswa.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    </div><!-- end tab-rekap -->

</div>

<script>
function konfirmasiHapus(id, nama, tanggal) {
    if (confirm("Apakah Anda yakin ingin menghapus log absensi siswa '" + nama + "' pada tanggal " + tanggal + "?")) {
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.set('action', 'delete');
        urlParams.set('id', id);
        window.location.href = "rekap.php?" + urlParams.toString();
    }
}

function switchTab(tabId, btn) {
    document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById(tabId).classList.add('active');
    btn.classList.add('active');
    // Update URL tombol export saat pindah tab
    if (tabId === 'tab-rekap') {
        document.getElementById('btn-export-rekap').style.outline = '2px solid #f39c12';
        document.getElementById('btn-export-detail').style.outline = '';
    } else {
        document.getElementById('btn-export-detail').style.outline = '2px solid #2ecc40';
        document.getElementById('btn-export-rekap').style.outline = '';
    }
}
// Highlight tombol export sesuai tab aktif saat load
document.getElementById('btn-export-detail').style.outline = '2px solid #2ecc40';
</script>
</body>
</html>
