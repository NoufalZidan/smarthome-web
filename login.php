<?php
require 'config.php';

// Bantuan otomatis: Buat akun admin default jika tabel users masih kosong
$cek_users = mysqli_query($conn, "SELECT * FROM users");
if(mysqli_num_rows($cek_users) == 0) {
    $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
    mysqli_query($conn, "INSERT INTO users (username, password, role) VALUES ('admin', '$hashed_password', 'admin')");
}

// Jika user sudah login, langsung arahkan ke dashboard
if (isset($_SESSION['login'])) {
    header("Location: index.php");
    exit;
}

$error = false;

// Logika pemrosesan form login
if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $result = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");

    // Cek ketersediaan username
    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        
        // Verifikasi kecocokan password dengan hash di database
        if (password_verify($password, $row['password'])) {
            // Set session
            $_SESSION['login'] = true;
            $_SESSION['role'] = $row['role'];
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            
            header("Location: index.php");
            exit;
        }
    }
    $error = true;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Smart Home Dashboard</title>
    <!-- Untuk sementara CSS disatukan di sini agar mudah dites, nanti bisa dipindah ke style.css -->
    <style>
        :root {
            --bg-color: #f4f7f6;
            --card-bg: #ffffff;
            --text-main: #333333;
            --text-muted: #888888;
            --accent-color: #7ba2c5; /* Biru pastel kalem */
            --accent-hover: #5d8ab2;
            --error-color: #e74c3c;
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

        .login-card {
            background: var(--card-bg);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04); /* Bayangan sangat halus */
            width: 100%;
            max-width: 320px;
            text-align: center;
        }

        .login-card h2 {
            margin-top: 0;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .login-card p {
            color: var(--text-muted);
            font-size: 14px;
            margin-bottom: 30px;
        }

        .input-group {
            margin-bottom: 20px;
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

        .btn-login {
            width: 100%;
            padding: 12px;
            background-color: var(--accent-color);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn-login:hover {
            background-color: var(--accent-hover);
        }

        .error-msg {
            color: var(--error-color);
            font-size: 13px;
            margin-bottom: 15px;
            background-color: #fdecea;
            padding: 10px;
            border-radius: 8px;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <h2>Smart Home</h2>
        <p>Masuk ke panel kendali IoT</p>

        <?php if($error) : ?>
            <div class="error-msg">Username atau password salah!</div>
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

            <button type="submit" name="login" class="btn-login">Masuk</button>
        </form>
        <a href="register.php" style="display:block; margin-top:20px; font-size:13px; color:#888888; text-decoration:none;">Belum punya akun? Daftar di sini</a>
    </div>

</body>
</html>