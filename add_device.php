<?php
session_start();
require 'config.php';

// Proteksi halaman
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

if (isset($_POST['submit'])) {
    $nama = htmlspecialchars($_POST['nama_perangkat']);
    $mac = htmlspecialchars($_POST['mac_address']);
    $wifi = htmlspecialchars($_POST['jaringan_wifi']);
    $kategori = $_POST['kategori'];
    $lokasi = $_POST['lokasi_ruangan'];

    // Auto-generate JSON default secara otomatis berdasarkan kategori
    if ($kategori == 'SistemAC') {
        $spesifikasi = json_encode(["suhu" => 22, "fan_speed" => "Medium", "mode" => "Cool"]);
    } elseif ($kategori == 'LampuPintar') {
        $spesifikasi = json_encode(["brightness" => 80, "color_mode" => "Warm"]);
    } elseif ($kategori == 'PintuPintar') {
        $spesifikasi = json_encode(["is_locked" => 1, "auto_lock_sec" => 30]);
    } else {
        $spesifikasi = json_encode([]);
    }

    $spesifikasi_db = mysqli_real_escape_string($conn, $spesifikasi);

    $user_id = (int)$_SESSION['user_id'];

    $query = "INSERT INTO perangkat_iot (user_id, nama_perangkat, mac_address, jaringan_wifi, kategori, status_daya, spesifikasi_ekstra, lokasi_ruangan) 
          VALUES ($user_id, '$nama', '$mac', '$wifi', '$kategori', 0, '$spesifikasi_db', '$lokasi')";

    if (mysqli_query($conn, $query)) {
        echo "<script>
                alert('Perangkat berhasil ditambahkan!');
                window.location.href = 'index.php';
              </script>";
    } else {
        echo "<script>alert('Gagal menambahkan perangkat!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Perangkat - Spatial Smart Home OS</title>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>

    <div class="form-container">
        <div class="form-header">
            <a href="index.php" class="back-btn"><i class="ph ph-arrow-left"></i></a>
            <h2 style="margin: 0; font-weight: 500; font-size: 22px;">Tambah Perangkat</h2>
        </div>

        <form action="" method="POST">
            <div class="form-group">
                <label>Nama Perangkat</label>
                <input type="text" name="nama_perangkat" class="form-control" placeholder="Contoh: Lampu Teras" required>
            </div>

            <div class="form-group">
                <label>Lokasi Ruangan</label>
                <select name="lokasi_ruangan" class="form-control" required>
                    <option value="Ruang Tamu">Ruang Tamu</option>
                    <option value="Kamar Tidur">Kamar Tidur</option>
                    <option value="Dapur">Dapur</option>
                    <option value="Halaman">Halaman</option>
                </select>
            </div>

            <div class="form-group">
                <label>Kategori</label>
                <select name="kategori" class="form-control" required>
                    <option value="LampuPintar">Lampu Pintar</option>
                    <option value="SistemAC">Sistem AC</option>
                    <option value="PintuPintar">Pintu Pintar</option>
                </select>
            </div>

            <div class="form-group">
                <label>MAC Address (Opsional/Bisa Dummy)</label>
                <input type="text" name="mac_address" class="form-control" placeholder="00:1A:2B:3C:4D:5E" required>
            </div>

            <div class="form-group">
                <label>Jaringan WiFi</label>
                <input type="text" name="jaringan_wifi" class="form-control" placeholder="Nama WiFi / SSID" required>
            </div>

            <button type="submit" name="submit" class="btn-submit">Simpan Perangkat</button>
        </form>
    </div>

</body>

</html>