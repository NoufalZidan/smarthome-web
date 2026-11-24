<?php
session_start();
require 'config.php';

// Proteksi halaman
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $user_id = (int)$_SESSION['user_id'];
    $query = "DELETE FROM perangkat_iot WHERE id = $id AND user_id = $user_id";

    // Query Hapus
    $query = "DELETE FROM perangkat_iot WHERE id = $id";

    if (mysqli_query($conn, $query)) {
        echo "<script>
                alert('Perangkat berhasil dihapus!');
                window.location.href = 'index.php';
              </script>";
    } else {
        echo "<script>
                alert('Gagal menghapus perangkat!');
                window.location.href = 'index.php';
              </script>";
    }
} else {
    header("Location: index.php");
    exit;
}
