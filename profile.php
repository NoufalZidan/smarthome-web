<?php
session_start();
require 'config.php';

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'] ?? 1; // Fallback jika session ID belum tersimpan
$username = $_SESSION['username'];
$role = $_SESSION['role'];

// Ambil data user dari database
$query_user = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");
$user_data = mysqli_fetch_assoc($query_user);

$msg_success = "";
$msg_error = "";

// Proses Ubah Password
if (isset($_POST['change_password'])) {
    $pass_lama = $_POST['password_lama'];
    $pass_baru = $_POST['password_baru'];
    $konfirmasi = $_POST['konfirmasi_password'];

    if ($user_data && password_verify($pass_lama, $user_data['password'])) {
        if ($pass_baru === $konfirmasi) {
            $hash_baru = password_hash($pass_baru, PASSWORD_DEFAULT);
            $update_q = "UPDATE users SET password = '$hash_baru' WHERE id = " . $user_data['id'];
            
            if (mysqli_query($conn, $update_q)) {
                $msg_success = "Password berhasil diperbarui!";
            } else {
                $msg_error = "Gagal memperbarui password di database.";
            }
        } else {
            $msg_error = "Konfirmasi password baru tidak cocok!";
        }
    } else {
        $msg_error = "Password lama Anda salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Akun - Spatial Smart Home OS</title>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

    <div class="form-container">
        <div class="form-header">
            <a href="index.php" class="back-btn"><i class="ph ph-arrow-left"></i></a>
            <h2 style="margin: 0; font-weight: 500; font-size: 22px;">Profil Akun</h2>
        </div>

        <!-- Info User Card -->
        <div style="background: rgba(255,255,255,0.08); border: 1px solid var(--glass-border); padding: 20px; border-radius: 20px; text-align: center; margin-bottom: 25px;">
            <div style="width: 70px; height: 70px; background: var(--accent-blue); border-radius: 50%; display: flex; justify-content: center; align-items: center; margin: 0 auto 15px auto; font-size: 32px; box-shadow: 0 0 20px rgba(10,132,255,0.5);">
                <i class="ph-fill ph-user"></i>
            </div>
            <h3 style="margin: 0 0 5px 0; font-weight: 500; font-size: 20px;"><?= ucfirst(htmlspecialchars($username)) ?></h3>
            <span style="background: rgba(255,255,255,0.15); padding: 4px 12px; border-radius: 12px; font-size: 12px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px;">
                Role: <?= htmlspecialchars($role) ?>
            </span>
        </div>

        <!-- Alert Notifikasi -->
        <?php if ($msg_success): ?>
            <div style="background: rgba(52, 199, 89, 0.2); border: 1px solid #34c759; color: #ffffff; padding: 12px; border-radius: 12px; margin-bottom: 20px; font-size: 14px; text-align: center;">
                <?= $msg_success ?>
            </div>
        <?php endif; ?>

        <?php if ($msg_error): ?>
            <div style="background: rgba(255, 69, 58, 0.2); border: 1px solid #ff453a; color: #ffffff; padding: 12px; border-radius: 12px; margin-bottom: 20px; font-size: 14px; text-align: center;">
                <?= $msg_error ?>
            </div>
        <?php endif; ?>

        <!-- Form Ubah Password -->
        <form action="" method="POST">
            <h4 style="margin: 0 0 15px 0; font-weight: 500; color: var(--text-muted); font-size: 14px;">GANTI PASSWORD AKUN</h4>
            
            <div class="form-group">
                <label>Password Saat Ini</label>
                <input type="password" name="password_lama" class="form-control" required placeholder="••••••••">
            </div>

            <div class="form-group">
                <label>Password Baru</label>
                <input type="password" name="password_baru" class="form-control" required placeholder="••••••••">
            </div>

            <div class="form-group">
                <label>Konfirmasi Password Baru</label>
                <input type="password" name="konfirmasi_password" class="form-control" required placeholder="••••••••">
            </div>

            <button type="submit" name="change_password" class="btn-submit">Update Password</button>
        </form>
    </div>

</body>
</html>