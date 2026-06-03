<?php
include "koneksi.php";
$pesan = ''; $tipePesan = 'info';

// Handle pesan redirect dari tambah_siswa.php
if (isset($_GET['pesan']) && $_GET['pesan'] === 'tambah_ok') {
    $pesan = "Siswa baru berhasil ditambahkan!"; $tipePesan = "ok";
}
if (isset($_GET['pesan_err'])) {
    $pesan = htmlspecialchars($_GET['pesan_err']); $tipePesan = "err";
}

// Hapus
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        $stmt = $koneksi->prepare("DELETE FROM siswa WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $pesan = "Data siswa berhasil dihapus!"; $tipePesan = "ok";
    } catch (PDOException $e) {
        $pesan = "Gagal menghapus: " . $e->getMessage(); $tipePesan = "err";
    }
}

// Update
if (isset($_POST['action']) && $_POST['action'] === 'update') {
    $id = (int)$_POST['id'];
    $nama = trim($_POST['nama']); $kelas = trim($_POST['kelas']); $nis = trim($_POST['nis']);
    if (!$nama || !$kelas || !$nis) {
        $pesan = "Semua kolom harus diisi!"; $tipePesan = "err";
    } else {
        try {
            $stmt = $koneksi->prepare("UPDATE siswa SET nis=:nis, nama=:nama, kelas=:kelas WHERE id=:id");
            $stmt->execute([':nis'=>$nis,':nama'=>$nama,':kelas'=>$kelas,':id'=>$id]);
            $pesan = "Data siswa berhasil diperbarui!"; $tipePesan = "ok";
        } catch (PDOException $e) {
            $pesan = "Gagal update: " . $e->getMessage(); $tipePesan = "err";
        }
    }
}

// Read + Filter
$cari  = isset($_GET['cari'])  ? trim($_GET['cari'])  : '';
$kelas = isset($_GET['kelas']) ? trim($_GET['kelas']) : '';
$listSiswa = [];
try {
    $where = []; $params = [];
    if ($cari !== '') { $where[] = "(nama ILIKE :cari OR nis ILIKE :cari)"; $params[':cari'] = "%$cari%"; }
    if ($kelas !== '') { $where[] = "kelas = :kelas"; $params[':kelas'] = $kelas; }
    $sql = "SELECT id, nis, nama, kelas, wajah FROM siswa" . ($where ? " WHERE " . implode(" AND ", $where) : "") . " ORDER BY kelas ASC, nama ASC";
    $stmt = $koneksi->prepare($sql);
    $stmt->execute($params);
    $listSiswa = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $pesan = "Error: " . $e->getMessage(); $tipePesan = "err";
}

// Ambil daftar kelas unik
$listKelas = [];
try {
    $s = $koneksi->query("SELECT DISTINCT kelas FROM siswa WHERE kelas IS NOT NULL AND kelas <> '' ORDER BY kelas ASC");
    $listKelas = $s->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kelola Data Siswa – Face Absensi</title>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Outfit',sans-serif;}
body{min-height:100vh;background:linear-gradient(135deg,#000000,#130113,#240024);color:#fff;padding:30px 20px;}
.wrapper{max-width:1100px;margin:0 auto;}

/* Header */
header{display:flex;justify-content:space-between;align-items:center;margin-bottom:28px;border-bottom:1px solid rgba(255,255,255,0.1);padding-bottom:20px;}
h1{font-size:26px;font-weight:700;} h1 span{color:#ff1493;}
.btn-row{display:flex;gap:10px;}
.btn-act{padding:10px 18px;border-radius:10px;color:#fff;text-decoration:none;font-size:14px;font-weight:600;transition:.3s;border:1px solid rgba(255,255,255,0.15);background:rgba(255,255,255,0.08);}
.btn-act:hover{background:rgba(255,255,255,0.18);transform:translateY(-2px);}
.btn-reg{background:linear-gradient(90deg,#ff1493,#ff69b4);border:none;}
.btn-reg:hover{box-shadow:0 0 14px #ff69b4;}

/* Alert */
.alert{padding:14px 18px;border-radius:12px;margin-bottom:20px;font-size:14px;display:flex;align-items:center;gap:10px;animation:fadeIn .4s ease;}
.alert-ok{background:rgba(127,255,127,.12);border:1px solid #7fff7f;color:#7fff7f;}
.alert-err{background:rgba(255,107,107,.12);border:1px solid #ff6b6b;color:#ff6b6b;}
.alert-info{background:rgba(0,191,255,.12);border:1px solid #00bfff;color:#00bfff;}

/* Stats */
.stats-row{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px;}
.stat-card{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:16px;padding:18px 22px;display:flex;align-items:center;gap:14px;}
.stat-icon{width:46px;height:46px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px;}
.stat-icon.pink{background:rgba(255,20,147,.2);color:#ff1493;}
.stat-icon.blue{background:rgba(0,191,255,.2);color:#00bfff;}
.stat-icon.green{background:rgba(127,255,127,.2);color:#7fff7f;}
.stat-val{font-size:26px;font-weight:700;line-height:1;}
.stat-lbl{font-size:12px;color:#aaa;margin-top:4px;}

/* Filter */
.filter-wrap{background:rgba(255,255,255,.04);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,.1);border-radius:16px;padding:20px;margin-bottom:22px;}
.filter-form{display:flex;gap:14px;align-items:flex-end;flex-wrap:wrap;}
.filter-group{flex:1;min-width:180px;}
.filter-group label{display:block;font-size:12px;color:#bbb;margin-bottom:7px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;}
.filter-group input,.filter-group select{width:100%;padding:11px 14px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);border-radius:10px;color:#fff;font-size:14px;outline:none;font-family:'Outfit',sans-serif;}
.filter-group select option{background:#1a001a;color:#fff;}
.btn-filter{padding:11px 22px;background:linear-gradient(90deg,#ff1493,#ff69b4);border:none;border-radius:10px;color:#fff;font-size:14px;font-weight:700;cursor:pointer;transition:.3s;white-space:nowrap;}
.btn-filter:hover{box-shadow:0 0 12px #ff69b4;transform:translateY(-1px);}
.btn-reset{padding:11px 18px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);border-radius:10px;color:#ccc;font-size:14px;cursor:pointer;transition:.3s;text-decoration:none;display:inline-flex;align-items:center;gap:6px;}
.btn-reset:hover{background:rgba(255,255,255,.15);}

/* Table */
.table-container{background:rgba(255,255,255,.03);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,.08);border-radius:16px;overflow:hidden;box-shadow:0 8px 32px rgba(0,0,0,.3);}
.table-header{display:flex;justify-content:space-between;align-items:center;padding:18px 22px;border-bottom:1px solid rgba(255,255,255,.08);}
.table-title{font-size:15px;font-weight:700;color:#ff9edb;}
.table-count{font-size:13px;color:#aaa;}
table{width:100%;border-collapse:collapse;text-align:left;}
th{background:rgba(255,255,255,.06);color:#ff9edb;padding:14px 18px;font-size:13px;font-weight:600;border-bottom:1px solid rgba(255,255,255,.1);}
td{padding:14px 18px;font-size:14px;border-bottom:1px solid rgba(255,255,255,.05);vertical-align:middle;}
tr:last-child td{border-bottom:none;}
tr:hover td{background:rgba(255,255,255,.03);}
.avatar{width:44px;height:44px;border-radius:50%;border:2px solid #ff1493;object-fit:cover;background:#222;}
.avatar-none{width:44px;height:44px;border-radius:50%;border:2px dashed rgba(255,255,255,.25);display:flex;align-items:center;justify-content:center;font-size:16px;color:#666;background:rgba(255,255,255,.04);}
.badge-kelas{background:rgba(255,20,147,.12);color:#ff9edb;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:700;border:1px solid rgba(255,20,147,.25);}
.btn-edit{padding:7px 14px;background:rgba(0,191,255,.12);color:#00bfff;border:1px solid rgba(0,191,255,.3);border-radius:8px;cursor:pointer;font-weight:600;font-size:12px;transition:.3s;margin-right:5px;}
.btn-edit:hover{background:rgba(0,191,255,.25);transform:translateY(-1px);}
.btn-delete{padding:7px 14px;background:rgba(255,107,107,.12);color:#ff6b6b;border:1px solid rgba(255,107,107,.3);border-radius:8px;cursor:pointer;font-weight:600;font-size:12px;transition:.3s;}
.btn-delete:hover{background:rgba(255,107,107,.25);transform:translateY(-1px);}
.btn-foto{padding:7px 14px;background:rgba(127,255,127,.1);color:#7fff7f;border:1px solid rgba(127,255,127,.3);border-radius:8px;cursor:pointer;font-weight:600;font-size:12px;transition:.3s;margin-right:5px;}
.btn-foto:hover{background:rgba(127,255,127,.22);transform:translateY(-1px);}
.empty-state{text-align:center;padding:60px;color:#666;}
.empty-state i{font-size:40px;display:block;margin-bottom:14px;color:#444;}

/* Modal Edit */
.modal{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.75);backdrop-filter:blur(8px);z-index:1000;justify-content:center;align-items:center;}
.modal-content{background:linear-gradient(160deg,#1a001a,#0d000d);border:1px solid rgba(255,255,255,.15);width:440px;max-width:95vw;border-radius:20px;padding:28px;box-shadow:0 20px 60px rgba(0,0,0,.6);animation:zoomIn .25s ease;}
.modal-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:22px;}
.modal-header h3{font-size:18px;color:#ff9edb;}
.btn-close{background:none;border:none;color:#aaa;font-size:22px;cursor:pointer;transition:.2s;line-height:1;}
.btn-close:hover{color:#fff;transform:rotate(90deg);}
.form-group{margin-bottom:16px;}
.form-group label{display:block;font-size:12px;color:#bbb;margin-bottom:7px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;}
.form-group input{width:100%;padding:12px 14px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);border-radius:10px;color:#fff;font-size:14px;outline:none;transition:.3s;font-family:'Outfit',sans-serif;}
.form-group input:focus{border-color:#ff1493;box-shadow:0 0 0 3px rgba(255,20,147,.15);}
.btn-save{width:100%;padding:13px;margin-top:8px;background:linear-gradient(90deg,#ff1493,#ff69b4);border:none;border-radius:10px;color:#fff;font-weight:700;font-size:15px;cursor:pointer;transition:.3s;}
.btn-save:hover{box-shadow:0 0 18px #ff69b4;transform:translateY(-1px);}

/* Modal Tambah */
.modal-add .modal-content{width:480px;}

/* Animasi */
@keyframes fadeIn{from{opacity:0;transform:translateY(-8px)}to{opacity:1;transform:translateY(0)}}
@keyframes zoomIn{from{transform:scale(.9);opacity:0}to{transform:scale(1);opacity:1}}
</style>
</head>
<body>
<div class="wrapper">

  <header>
    <h1><i class="fa-solid fa-users-gear" style="color:#ff1493;margin-right:10px"></i>Kelola Data <span>Siswa</span></h1>
    <div class="btn-row">
      <button class="btn-act btn-reg" onclick="bukaModalTambah()"><i class="fa-solid fa-user-plus" style="margin-right:6px"></i>Tambah Siswa</button>
      <a href="register.php" class="btn-act" style="background:rgba(127,255,127,.12);border-color:rgba(127,255,127,.3);color:#7fff7f"><i class="fa-solid fa-camera" style="margin-right:6px"></i>Daftar Wajah</a>
      <a href="index.php" class="btn-act"><i class="fa-solid fa-house" style="margin-right:6px"></i>Menu</a>
    </div>
  </header>

  <?php if ($pesan !== ''): ?>
  <div class="alert alert-<?= $tipePesan ?>">
    <i class="fa-solid <?= $tipePesan==='ok'?'fa-circle-check':($tipePesan==='err'?'fa-circle-exclamation':'fa-circle-info') ?>"></i>
    <span><?= htmlspecialchars($pesan) ?></span>
  </div>
  <?php endif; ?>

  <!-- Stats -->
  <?php
    $totalSiswa = count($listSiswa);
    $totalDgFoto = count(array_filter($listSiswa, fn($r) => !empty($r['wajah'])));
    $totalKelasUnik = count($listKelas);
  ?>
  <div class="stats-row">
    <div class="stat-card">
      <div class="stat-icon pink"><i class="fa-solid fa-users"></i></div>
      <div><div class="stat-val"><?= $totalSiswa ?></div><div class="stat-lbl">Total Siswa <?= ($cari||$kelas)?'(Filter)':'' ?></div></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon green"><i class="fa-solid fa-face-smile"></i></div>
      <div><div class="stat-val"><?= $totalDgFoto ?></div><div class="stat-lbl">Sudah Ada Foto</div></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon blue"><i class="fa-solid fa-layer-group"></i></div>
      <div><div class="stat-val"><?= $totalKelasUnik ?></div><div class="stat-lbl">Total Kelas</div></div>
    </div>
  </div>

  <!-- Filter & Pencarian -->
  <div class="filter-wrap">
    <form method="GET" class="filter-form">
      <div class="filter-group">
        <label><i class="fa-solid fa-magnifying-glass" style="margin-right:4px"></i>Cari Nama / NIS</label>
        <input type="text" name="cari" placeholder="Ketik nama atau NIS..." value="<?= htmlspecialchars($cari) ?>">
      </div>
      <div class="filter-group" style="max-width:200px">
        <label><i class="fa-solid fa-layer-group" style="margin-right:4px"></i>Filter Kelas</label>
        <select name="kelas">
          <option value="">-- Semua Kelas --</option>
          <?php foreach ($listKelas as $k): ?>
          <option value="<?= htmlspecialchars($k) ?>" <?= $kelas===$k?'selected':'' ?>><?= htmlspecialchars($k) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <button type="submit" class="btn-filter"><i class="fa-solid fa-magnifying-glass" style="margin-right:6px"></i>Cari</button>
      <?php if ($cari !== '' || $kelas !== ''): ?>
      <a href="siswa.php" class="btn-reset"><i class="fa-solid fa-rotate-left"></i>Reset</a>
      <?php endif; ?>
    </form>
  </div>

  <!-- Tabel Siswa -->
  <div class="table-container">
    <div class="table-header">
      <span class="table-title"><i class="fa-solid fa-table-list" style="margin-right:8px"></i>Daftar Siswa</span>
      <span class="table-count"><?= count($listSiswa) ?> siswa ditemukan</span>
    </div>
    <table>
      <thead>
        <tr>
          <th style="width:60px;text-align:center">Foto</th>
          <th>NIS</th>
          <th>Nama Lengkap</th>
          <th>Kelas</th>
          <th>Status Wajah</th>
          <th style="text-align:center;width:220px">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (count($listSiswa) > 0): ?>
          <?php foreach ($listSiswa as $row): ?>
          <tr>
            <td style="text-align:center">
              <?php if (!empty($row['wajah'])): ?>
                <img src="<?= $row['wajah'] ?>" alt="Foto" class="avatar">
              <?php else: ?>
                <div class="avatar-none"><i class="fa-solid fa-user"></i></div>
              <?php endif; ?>
            </td>
            <td style="font-weight:700;color:#ff9edb"><?= htmlspecialchars($row['nis']) ?></td>
            <td style="font-weight:600"><?= htmlspecialchars($row['nama']) ?></td>
            <td><span class="badge-kelas"><?= htmlspecialchars($row['kelas']) ?></span></td>
            <td>
              <?php if (!empty($row['wajah'])): ?>
                <span style="color:#7fff7f;font-size:13px"><i class="fa-solid fa-circle-check" style="margin-right:5px"></i>Terdaftar</span>
              <?php else: ?>
                <span style="color:#ff6b6b;font-size:13px"><i class="fa-solid fa-circle-xmark" style="margin-right:5px"></i>Belum ada</span>
              <?php endif; ?>
            </td>
            <td style="text-align:center">
              <button class="btn-foto" onclick="window.location='register.php'" title="Daftarkan Wajah"><i class="fa-solid fa-camera"></i></button>
              <button class="btn-edit" onclick="bukaModalEdit(<?= $row['id'] ?>, '<?= addslashes($row['nis']) ?>', '<?= addslashes($row['nama']) ?>', '<?= addslashes($row['kelas']) ?>')"><i class="fa-solid fa-pen-to-square" style="margin-right:4px"></i>Edit</button>
              <button class="btn-delete" onclick="konfirmasiHapus(<?= $row['id'] ?>, '<?= addslashes($row['nama']) ?>')"><i class="fa-solid fa-trash" style="margin-right:4px"></i>Hapus</button>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="6" class="empty-state">
            <i class="fa-solid fa-users-slash"></i>
            <?= ($cari||$kelas) ? 'Tidak ada siswa yang cocok dengan filter.' : 'Belum ada siswa terdaftar.' ?>
          </td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- ── Modal Tambah Siswa ── -->
<div id="modalTambah" class="modal modal-add">
  <div class="modal-content">
    <div class="modal-header">
      <h3><i class="fa-solid fa-user-plus" style="margin-right:8px"></i>Tambah Siswa Baru</h3>
      <button class="btn-close" onclick="tutupModal('modalTambah')">&times;</button>
    </div>
    <form method="POST" action="tambah_siswa.php">
      <input type="hidden" name="action" value="tambah">
      <div class="form-group">
        <label>NIS Siswa</label>
        <input type="text" name="nis" placeholder="Masukkan NIS..." required>
      </div>
      <div class="form-group">
        <label>Nama Lengkap</label>
        <input type="text" name="nama" placeholder="Masukkan nama lengkap..." required>
      </div>
      <div class="form-group">
        <label>Kelas</label>
        <input type="text" name="kelas" placeholder="Contoh: X-IPA-1" required>
      </div>
      <button type="submit" class="btn-save"><i class="fa-solid fa-user-plus" style="margin-right:6px"></i>Tambah Siswa</button>
    </form>
  </div>
</div>

<!-- ── Modal Edit Siswa ── -->
<div id="modalEdit" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3><i class="fa-solid fa-user-pen" style="margin-right:8px"></i>Edit Data Siswa</h3>
      <button class="btn-close" onclick="tutupModal('modalEdit')">&times;</button>
    </div>
    <form method="POST" action="siswa.php">
      <input type="hidden" name="action" value="update">
      <input type="hidden" id="edit-id" name="id">
      <div class="form-group">
        <label>NIS Siswa</label>
        <input type="text" id="edit-nis" name="nis" required>
      </div>
      <div class="form-group">
        <label>Nama Lengkap</label>
        <input type="text" id="edit-nama" name="nama" required>
      </div>
      <div class="form-group">
        <label>Kelas</label>
        <input type="text" id="edit-kelas" name="kelas" required>
      </div>
      <button type="submit" class="btn-save"><i class="fa-solid fa-floppy-disk" style="margin-right:6px"></i>Simpan Perubahan</button>
    </form>
  </div>
</div>

<script>
function bukaModalEdit(id, nis, nama, kelas) {
    document.getElementById('edit-id').value = id;
    document.getElementById('edit-nis').value = nis;
    document.getElementById('edit-nama').value = nama;
    document.getElementById('edit-kelas').value = kelas;
    document.getElementById('modalEdit').style.display = 'flex';
}
function bukaModalTambah() {
    document.getElementById('modalTambah').style.display = 'flex';
}
function tutupModal(id) {
    document.getElementById(id).style.display = 'none';
}
window.onclick = function(e) {
    ['modalEdit','modalTambah'].forEach(id => {
        const m = document.getElementById(id);
        if (e.target === m) m.style.display = 'none';
    });
}
function konfirmasiHapus(id, nama) {
    if (confirm("Hapus data siswa '" + nama + "'?\nData absensi terkait tidak akan terhapus.")) {
        window.location.href = "siswa.php?action=delete&id=" + id;
    }
}
</script>
</body>
</html>
