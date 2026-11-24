<?php
session_start();
require 'config.php';

// Proteksi halaman
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = (int)$_GET['id'];
$user_id = (int)$_SESSION['user_id'];
$query_get = mysqli_query($conn, "SELECT * FROM perangkat_iot WHERE id = $id AND user_id = $user_id");
// Dan pada query update:
$query_update = "UPDATE perangkat_iot SET ... WHERE id = $id AND user_id = $user_id";
$device = mysqli_fetch_assoc($query_get);

if (!$device) {
    header("Location: index.php");
    exit;
}

// Proses Update
if (isset($_POST['submit'])) {
    $nama = htmlspecialchars($_POST['nama_perangkat']);
    $mac = htmlspecialchars($_POST['mac_address']);
    $wifi = htmlspecialchars($_POST['jaringan_wifi']);
    $kategori = $_POST['kategori'];
    $lokasi = $_POST['lokasi_ruangan'];

    // Update data dasar tanpa merusak JSON spesifikasi_ekstra yang ada
    $query_update = "UPDATE perangkat_iot SET 
                     nama_perangkat = '$nama', 
                     mac_address = '$mac', 
                     jaringan_wifi = '$wifi', 
                     kategori = '$kategori', 
                     lokasi_ruangan = '$lokasi' 
                     WHERE id = $id";

    if (mysqli_query($conn, $query_update)) {
        echo "<script>
                alert('Perangkat berhasil diperbarui!');
                window.location.href = 'index.php';
              </script>";
    } else {
        echo "<script>alert('Gagal memperbarui perangkat!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Perangkat - Spatial Smart Home OS</title>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

    <div class="form-container">
        <div class="form-header">
            <a href="index.php" class="back-btn"><i class="ph ph-arrow-left"></i></a>
            <h2 style="margin: 0; font-weight: 500; font-size: 22px;">Edit Perangkat</h2>
        </div>

        <form action="" method="POST">
            <div class="form-group">
                <label>Nama Perangkat</label>
                <input type="text" name="nama_perangkat" class="form-control" value="<?= htmlspecialchars($device['nama_perangkat']) ?>" required>
            </div>

            <div class="form-group">
                <label>Lokasi Ruangan</label>
                <select name="lokasi_ruangan" class="form-control" required>
                    <option value="Ruang Tamu" <?= ($device['lokasi_ruangan'] ?? '') == 'Ruang Tamu' ? 'selected' : '' ?>>Ruang Tamu</option>
                    <option value="Kamar Tidur" <?= ($device['lokasi_ruangan'] ?? '') == 'Kamar Tidur' ? 'selected' : '' ?>>Kamar Tidur</option>
                    <option value="Dapur" <?= ($device['lokasi_ruangan'] ?? '') == 'Dapur' ? 'selected' : '' ?>>Dapur</option>
                    <option value="Halaman" <?= ($device['lokasi_ruangan'] ?? '') == 'Halaman' ? 'selected' : '' ?>>Halaman</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Kategori</label>
                <select name="kategori" class="form-control" required>
                    <option value="LampuPintar" <?= $device['kategori'] == 'LampuPintar' ? 'selected' : '' ?>>Lampu Pintar</option>
                    <option value="SistemAC" <?= $device['kategori'] == 'SistemAC' ? 'selected' : '' ?>>Sistem AC</option>
                    <option value="PintuPintar" <?= $device['kategori'] == 'PintuPintar' ? 'selected' : '' ?>>Pintu Pintar</option>
                </select>
            </div>

            <div class="form-group">
                <label>MAC Address</label>
                <input type="text" name="mac_address" class="form-control" value="<?= htmlspecialchars($device['mac_address']) ?>" required>
            </div>

            <div class="form-group">
                <label>Jaringan WiFi</label>
                <input type="text" name="jaringan_wifi" class="form-control" value="<?= htmlspecialchars($device['jaringan_wifi']) ?>" required>
            </div>

            <!-- Tombol Pintas ke Control Center Detail -->
            <div style="background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); padding: 12px 15px; border-radius: 12px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
                <span class="text-xs">Fitur & Slider Perangkat</span>
                <a href="device_detail.php?id=<?= $device['id'] ?>" style="color: var(--accent-blue); text-decoration: none; font-size: 13px; font-weight: 500;">
                    Buka Control Center <i class="ph ph-sliders"></i>
                </a>
            </div>

            <button type="submit" name="submit" class="btn-submit">Simpan Perubahan</button>
        </form>

        <div style="text-align: center; margin-top: 20px;">
            <a href="delete_device.php?id=<?= $id; ?>" 
               onclick="return confirm('Apakah Anda yakin ingin menghapus perangkat ini?');" 
               style="color: #ff453a; text-decoration: none; font-size: 14px; font-weight: 500;">
               Hapus Perangkat Secara Permanen
            </a>
        </div>
    </div>

</body>
</html>