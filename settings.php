<?php
session_start();
require 'config.php';

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$msg_info = "";

// Aksi Bersihkan Riwayat Log Aktivitas
if (isset($_POST['clear_logs'])) {
    if (mysqli_query($conn, "TRUNCATE TABLE log_aktivitas")) {
        $msg_info = "Seluruh riwayat log aktivitas berhasil dibersihkan!";
    } else {
        $msg_info = "Gagal membersihkan riwayat log.";
    }
}

// Stats Ringkasan Sistem
$total_devices = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM perangkat_iot"))['total'] ?? 0;
$total_logs = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM log_aktivitas"))['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Sistem - Spatial Smart Home OS</title>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

    <div class="form-container">
        <div class="form-header">
            <a href="index.php" class="back-btn"><i class="ph ph-arrow-left"></i></a>
            <h2 style="margin: 0; font-weight: 500; font-size: 22px;">Pengaturan Sistem</h2>
        </div>

        <?php if ($msg_info): ?>
            <div style="background: rgba(10, 132, 255, 0.2); border: 1px solid var(--accent-blue); color: #ffffff; padding: 12px; border-radius: 12px; margin-bottom: 20px; font-size: 14px; text-align: center;">
                <?= $msg_info ?>
            </div>
        <?php endif; ?>

        <!-- Ringkasan Statistik Database -->
        <h4 style="margin: 0 0 15px 0; font-weight: 500; color: var(--text-muted); font-size: 14px;">RINGKASAN DATABASE IOT</h4>
        <div style="display: flex; gap: 12px; margin-bottom: 25px;">
            <div style="flex: 1; background: var(--glass-card); border: 1px solid var(--glass-border); padding: 15px; border-radius: 16px; text-align: center;">
                <div style="font-size: 24px; font-weight: 500; color: var(--accent-blue);"><?= $total_devices ?></div>
                <div class="text-xs" style="margin-top: 4px;">Total Perangkat</div>
            </div>
            <div style="flex: 1; background: var(--glass-card); border: 1px solid var(--glass-border); padding: 15px; border-radius: 16px; text-align: center;">
                <div style="font-size: 24px; font-weight: 500; color: #ffd700;"><?= $total_logs ?></div>
                <div class="text-xs" style="margin-top: 4px;">Total Entri Log</div>
            </div>
        </div>

        <!-- Utilitas Pemeliharaan System -->
        <h4 style="margin: 0 0 15px 0; font-weight: 500; color: var(--text-muted); font-size: 14px;">PEMELIHARAAN SISTEM</h4>
        
        <div style="background: var(--glass-card); border: 1px solid var(--glass-border); padding: 18px; border-radius: 20px; margin-bottom: 20px;">
            <div style="font-weight: 500; margin-bottom: 5px;">Format Laporan Log</div>
            <div class="text-xs" style="margin-bottom: 12px;">Unduh semua transaksi status perangkat ke dalam file CSV untuk analisis Excel.</div>
            <a href="export_csv.php" style="display: inline-block; background: rgba(255,255,255,0.1); color: white; text-decoration: none; padding: 10px 16px; border-radius: 10px; font-size: 13px; transition: 0.3s;">
                <i class="ph ph-download-simple"></i> Unduh Laporan CSV
            </a>
        </div>

        <form action="" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus SELURUH riwayat log aktivitas? Data yang dihapus tidak bisa dikembalikan.');">
            <div style="background: rgba(255,69,58,0.08); border: 1px solid rgba(255,69,58,0.2); padding: 18px; border-radius: 20px;">
                <div style="font-weight: 500; color: #ff453a; margin-bottom: 5px;">Kosongkan Riwayat Log</div>
                <div class="text-xs" style="margin-bottom: 12px;">Hapus seluruh catatan audit trail saklar/slider untuk menghemat penyimpanan database.</div>
                <button type="submit" name="clear_logs" style="background: #ff453a; color: white; border: none; padding: 10px 16px; border-radius: 10px; font-size: 13px; font-weight: 500; cursor: pointer;">
                    <i class="ph ph-trash"></i> Bersihkan Semua Log
                </button>
            </div>
        </form>
    </div>

</body>
</html>