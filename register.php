<?php
require 'config.php';

// Jika user sudah login, langsung arahkan ke dashboard
if (isset($_SESSION['login'])) {
    header("Location: index.php");
    exit;
}

$error = '';
$success = '';

// Logika pemrosesan form registrasi
if (isset($_POST['register'])) {
    // Sanitasi input
    $username = mysqli_real_escape_string($conn, strtolower(stripslashes($_POST['username'])));
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);

    // Cek konfirmasi password
    if ($password !== $confirm_password) {
        $error = "Konfirmasi password tidak cocok!";
    } else {
        // Cek apakah username sudah ada di database
        $cek_username = mysqli_query($conn, "SELECT username FROM users WHERE username = '$username'");
        
        if (mysqli_fetch_assoc($cek_username)) {
            $error = "Username sudah terdaftar, silakan gunakan yang lain!";
        } else {
            // Enkripsi password menggunakan BCRYPT
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Masukkan data ke database dengan role default 'guest'
            $query = "INSERT INTO users (username, password, role) VALUES ('$username', '$hashed_password', 'guest')";
            
            if (mysqli_query($conn, $query)) {
                $success = "Registrasi berhasil! Silakan kembali ke halaman masuk.";
            } else {
                $error = "Gagal mendaftarkan akun. Kesalahan server.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - Smart Home Dashboard</title>
    <style>
        :root {
            --bg-color: #f4f7f6;
            --card-bg: #ffffff;
            --text-main: #333333;
            --text-muted: #888888;
            --accent-color: #7ba2c5;
            --accent-hover: #5d8ab2;
            --error-color: #e74c3c;
            --success-color: #2ecc71;
        }

        body {
            background-color: var(--bg-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            color: var(--text-main);
        }

        .auth-card {
            background: var(--card-bg);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
            width: 100%;
            max-width: 320px;
            text-align: center;
        }

        .auth-card h2 {
            margin-top: 0;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .auth-card p {
            color: var(--text-muted);
            font-size: 14px;
            margin-bottom: 25px;
        }

        .input-group {
            margin-bottom: 15px;
            text-align: left;
        }

        .input-group label {
            display: block;
            font-size: 13px;
            margin-bottom: 8px;
            color: var(--text-muted);
        }

        .input-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #e1e5e8;
            border-radius: 10px;
            box-sizing: border-box;
            font-size: 14px;
            background-color: #fafbfc;
            transition: all 0.3s ease;
        }

        .input-group input:focus {
            outline: none;
            border-color: var(--accent-color);
            background-color: #ffffff;
            box-shadow: 0 0 0 3px rgba(123, 162, 197, 0.15);
        }

        .btn-auth {
            width: 100%;
            padding: 12px;
            margin-top: 10px;
            background-color: var(--accent-color);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn-auth:hover {
            background-color: var(--accent-hover);
        }

        .alert {
            font-size: 13px;
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 8px;
        }

        .alert-error {
            color: var(--error-color);
            background-color: #fdecea;
        }

        .alert-success {
            color: #1e8449;
            background-color: #e9f7ef;
        }

        .redirect-link {
            display: block;
            margin-top: 20px;
            font-size: 13px;
            color: var(--text-muted);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .redirect-link:hover {
            color: var(--accent-color);
        }
    </style>
</head>
<body>

    <div class="auth-card">
        <h2>Buat Akun</h2>
        <p>Daftar untuk mengontrol perangkat</p>

        <?php if($error) : ?>
            <div class="alert alert-error"><?= $error; ?></div>
        <?php endif; ?>

        <?php if($success) : ?>
            <div class="alert alert-success"><?= $success; ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="input-group">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" autocomplete="off" required>
            </div>
            
            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required>
            </div>

            <div class="input-group">
                <label for="confirm_password">Konfirmasi Password</label>
                <input type="password" name="confirm_password" id="confirm_password" required>
            </div>

            <button type="submit" name="register" class="btn-auth">Daftar Sekarang</button>
        </form>

        <a href="login.php" class="redirect-link">Sudah punya akun? Masuk di sini</a>
    </div>

</body>
</html>