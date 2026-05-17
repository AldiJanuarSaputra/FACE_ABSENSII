<?php
include "koneksi.php";

$pesan = '';
$tipePesan = 'info';

// ── 1. Proses Hapus (Delete) ──────────────────────────
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        $stmt = $koneksi->prepare("DELETE FROM siswa WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $pesan = "Data siswa berhasil dihapus!";
        $tipePesan = "ok";
    } catch (PDOException $e) {
        $pesan = "Gagal menghapus data: " . $e->getMessage();
        $tipePesan = "err";
    }
}

// ── 2. Proses Edit (Update) ───────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'update') {
    $id    = (int)$_POST['id'];
    $nama  = trim($_POST['nama']);
    $kelas = trim($_POST['kelas']);
    $nis   = trim($_POST['nis']);

    if (!$nama || !$kelas || !$nis) {
        $pesan = "Semua kolom edit harus diisi!";
        $tipePesan = "err";
    } else {
        try {
            $stmt = $koneksi->prepare("UPDATE siswa SET nis = :nis, nama = :nama, kelas = :kelas WHERE id = :id");
            $stmt->execute([
                ':nis'   => $nis,
                ':nama'  => $nama,
                ':kelas' => $kelas,
                ':id'    => $id
            ]);
            $pesan = "Data siswa (NIS: $nis) berhasil diperbarui!";
            $tipePesan = "ok";
        } catch (PDOException $e) {
            $pesan = "Gagal memperbarui data: " . $e->getMessage();
            $tipePesan = "err";
        }
    }
}

// ── 3. Proses Ambil Data & Filter (Read) ──────────────
$cari = isset($_GET['cari']) ? trim($_GET['cari']) : '';
$listSiswa = [];

try {
    if ($cari !== '') {
        $stmt = $koneksi->prepare("SELECT id, nis, nama, kelas, wajah FROM siswa WHERE nama ILIKE :cari OR nis ILIKE :cari ORDER BY nama ASC");
        $stmt->execute([':cari' => "%$cari%"]);
    } else {
        $stmt = $koneksi->query("SELECT id, nis, nama, kelas, wajah FROM siswa ORDER BY nama ASC");
    }
    $listSiswa = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $pesan = "Error memuat data: " . $e->getMessage();
    $tipePesan = "err";
}
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

.btn-row {
    display: flex;
    gap: 10px;
}

.btn-act {
    padding: 10px 18px;
    border-radius: 10px;
    color: #fff;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    transition: 0.3s;
    border: 1px solid rgba(255,255,255,0.15);
    background: rgba(255,255,255,0.08);
}
.btn-act:hover { background: rgba(255,255,255,0.15); transform: translateY(-2px); }
.btn-reg {
    background: linear-gradient(90deg, #ff1493, #ff69b4);
    border: none;
}
.btn-reg:hover { box-shadow: 0 0 12px #ff69b4; }

/* Notifikasi */
.alert {
    padding: 15px 20px;
    border-radius: 12px;
    margin-bottom: 25px;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 10px;
    animation: fadeIn 0.4s ease-out;
}
.alert-ok { background: rgba(127, 255, 127, 0.15); border: 1px solid #7fff7f; color: #7fff7f; }
.alert-err { background: rgba(255, 107, 107, 0.15); border: 1px solid #ff6b6b; color: #ff6b6b; }
.alert-info { background: rgba(0, 191, 255, 0.15); border: 1px solid #00bfff; color: #00bfff; }

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
    gap: 15px;
    align-items: flex-end;
}

.filter-group {
    flex: 1;
}

.filter-group label {
    display: block;
    font-size: 13px;
    color: #bbb;
    margin-bottom: 8px;
    font-weight: 600;
}

.filter-group input {
    width: 100%;
    padding: 12px;
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: 10px;
    color: #fff;
    font-size: 14px;
    outline: none;
}

.btn-filter {
    padding: 12px 24px;
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: 10px;
    color: #fff;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    transition: 0.3s;
}
.btn-filter:hover { background: rgba(255,255,255,0.2); }

/* Table Section */
.table-container {
    background: rgba(255, 255, 255, 0.04);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 16px;
    overflow: hidden;
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
    vertical-align: middle;
}

tr:hover td {
    background: rgba(255, 255, 255, 0.03);
}

/* Avatar Wajah */
.avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    border: 2px solid #ff1493;
    object-fit: cover;
    background: #222;
}

.avatar-none {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    border: 2px dashed rgba(255,255,255,0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    color: #aaa;
    background: rgba(255,255,255,0.05);
}

/* Action Buttons */
.btn-edit {
    padding: 8px 14px;
    background: rgba(0, 191, 255, 0.15);
    color: #00bfff;
    border: 1px solid rgba(0, 191, 255, 0.3);
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    font-size: 13px;
    transition: 0.3s;
    margin-right: 5px;
}
.btn-edit:hover { background: rgba(0, 191, 255, 0.3); transform: translateY(-1px); }

.btn-delete {
    padding: 8px 14px;
    background: rgba(255, 107, 107, 0.15);
    color: #ff6b6b;
    border: 1px solid rgba(255, 107, 107, 0.3);
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    font-size: 13px;
    transition: 0.3s;
}
.btn-delete:hover { background: rgba(255, 107, 107, 0.3); transform: translateY(-1px); }

/* Modal Edit */
.modal {
    display: none;
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.7);
    backdrop-filter: blur(8px);
    z-index: 1000;
    justify-content: center;
    align-items: center;
}

.modal-content {
    background: rgba(30, 10, 30, 0.95);
    border: 1px solid rgba(255,255,255,0.15);
    width: 400px;
    border-radius: 20px;
    padding: 25px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.5);
    animation: zoomIn 0.3s ease-out;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}
.modal-header h3 { font-size: 20px; color: #ff9edb; }
.btn-close {
    background: none; border: none; color: #aaa;
    font-size: 20px; cursor: pointer; transition: 0.2s;
}
.btn-close:hover { color: #fff; }

.modal-body label {
    display: block;
    font-size: 13px;
    color: #ccc;
    margin-bottom: 6px;
    margin-top: 12px;
}
.modal-body input {
    width: 100%;
    padding: 11px;
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: 8px;
    color: #fff;
    outline: none;
    font-size: 14px;
}

.btn-save {
    width: 100%;
    padding: 12px;
    margin-top: 20px;
    background: linear-gradient(90deg, #ff1493, #ff69b4);
    border: none;
    border-radius: 10px;
    color: #fff;
    font-weight: 700;
    font-size: 14px;
    cursor: pointer;
    transition: 0.3s;
}
.btn-save:hover { box-shadow: 0 0 15px #ff69b4; transform: translateY(-1px); }

@keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
@keyframes zoomIn { from { transform: scale(0.9); opacity: 0; } to { transform: scale(1); opacity: 1; } }
</style>
</head>
<body>

<div class="wrapper">
    <header>
        <h1><i class="fa-solid fa-users-gear" style="color: #ff1493; margin-right: 10px;"></i>Kelola Data <span>Siswa</span></h1>
        <div class="btn-row">
            <a href="register.php" class="btn-act btn-reg"><i class="fa-solid fa-user-plus" style="margin-right: 6px;"></i>Daftar Wajah Baru</a>
            <a href="index.php" class="btn-act"><i class="fa-solid fa-house" style="margin-right: 6px;"></i>Menu</a>
        </div>
    </header>

    <!-- Notifikasi -->
    <?php if ($pesan !== ''): ?>
        <div class="alert alert-<?php echo $tipePesan; ?>">
            <i class="fa-solid <?php 
                echo $tipePesan === 'ok' ? 'fa-circle-check' : ($tipePesan === 'err' ? 'fa-circle-exclamation' : 'fa-circle-info'); 
            ?>"></i>
            <span><?php echo htmlspecialchars($pesan); ?></span>
        </div>
    <?php endif; ?>

    <!-- Search Box -->
    <div class="filter-wrap">
        <form method="GET" class="filter-form">
            <div class="filter-group">
                <label for="cari">Cari Nama / NIS Siswa</label>
                <input type="text" id="cari" name="cari" placeholder="Ketik nama atau NIS siswa..." value="<?php echo htmlspecialchars($cari); ?>">
            </div>
            <button type="submit" class="btn-filter"><i class="fa-solid fa-magnifying-glass" style="margin-right: 6px;"></i>Cari</button>
            <?php if ($cari !== ''): ?>
                <a href="siswa.php" class="btn-act" style="padding: 12px 18px;"><i class="fa-solid fa-rotate-left" style="margin-right: 6px;"></i>Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Table Siswa -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th style="width: 70px; text-align: center;">Foto</th>
                    <th>NIS</th>
                    <th>Nama Lengkap</th>
                    <th>Kelas</th>
                    <th style="width: 200px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($listSiswa) > 0): ?>
                    <?php foreach ($listSiswa as $row): ?>
                        <tr>
                            <td style="text-align: center;">
                                <?php if (!empty($row['wajah'])): ?>
                                    <img src="<?php echo $row['wajah']; ?>" alt="Foto Wajah" class="avatar">
                                <?php else: ?>
                                    <div class="avatar-none"><i class="fa-solid fa-user"></i></div>
                                <?php endif; ?>
                            </td>
                            <td style="font-weight: 700; color: #ff9edb;"><?php echo htmlspecialchars($row['nis']); ?></td>
                            <td style="font-weight: 600;"><?php echo htmlspecialchars($row['nama']); ?></td>
                            <td><span style="background: rgba(255,255,255,0.08); padding: 5px 12px; border-radius: 6px; font-size: 13px; font-weight: 600;"><?php echo htmlspecialchars($row['kelas']); ?></span></td>
                            <td style="text-align: center;">
                                <button class="btn-edit" onclick="bukaModal(<?php echo $row['id']; ?>, '<?php echo addslashes($row['nis']); ?>', '<?php echo addslashes($row['nama']); ?>', '<?php echo addslashes($row['kelas']); ?>')">
                                    <i class="fa-solid fa-pen-to-square" style="margin-right: 5px;"></i>Edit
                                </button>
                                <button class="btn-delete" onclick="konfirmasiHapus(<?php echo $row['id']; ?>, '<?php echo addslashes($row['nama']); ?>')">
                                    <i class="fa-solid fa-trash" style="margin-right: 5px;"></i>Hapus
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="empty-state" style="padding: 50px; text-align: center; color: #aaa;">
                            <i class="fa-solid fa-users-slash" style="font-size: 24px; margin-bottom: 10px; display: block;"></i>
                            Belum ada siswa terdaftar atau pencarian tidak ditemukan.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Edit Siswa -->
<div id="modalEdit" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fa-solid fa-user-pen" style="margin-right: 8px;"></i>Edit Data Siswa</h3>
            <button class="btn-close" onclick="tutupModal()">&times;</button>
        </div>
        <form method="POST" action="siswa.php">
            <input type="hidden" name="action" value="update">
            <input type="hidden" id="edit-id" name="id">
            
            <div class="modal-body">
                <label for="edit-nis">NIS Siswa</label>
                <input type="text" id="edit-nis" name="nis" required>

                <label for="edit-nama">Nama Lengkap</label>
                <input type="text" id="edit-nama" name="nama" required>

                <label for="edit-kelas">Kelas</label>
                <input type="text" id="edit-kelas" name="kelas" required>
            </div>

            <button type="submit" class="btn-save"><i class="fa-solid fa-floppy-disk" style="margin-right: 6px;"></i>Simpan Perubahan</button>
        </form>
    </div>
</div>

<script>
const modal = document.getElementById("modalEdit");

function bukaModal(id, nis, nama, kelas) {
    document.getElementById("edit-id").value = id;
    document.getElementById("edit-nis").value = nis;
    document.getElementById("edit-nama").value = nama;
    document.getElementById("edit-kelas").value = kelas;
    modal.style.display = "flex";
}

function tutupModal() {
    modal.style.display = "none";
}

// Tutup modal jika klik di luar box
window.onclick = function(event) {
    if (event.target == modal) {
        tutupModal();
    }
}

function konfirmasiHapus(id, nama) {
    if (confirm("Apakah Anda yakin ingin menghapus data siswa '" + nama + "'? Semua riwayat absensi siswa ini juga sebaiknya diperiksa.")) {
        window.location.href = "siswa.php?action=delete&id=" + id;
    }
}
</script>
</body>
</html>
