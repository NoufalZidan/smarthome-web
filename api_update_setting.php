<?php
session_start();
require 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['login'])) {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['id']) && isset($data['settings'])) {
    $id = (int)$data['id'];
    // Encode kembali settings ke JSON string yang aman
    $json_settings = mysqli_real_escape_string($conn, json_encode($data['settings']));

    $query = "UPDATE perangkat_iot SET spesifikasi_ekstra = '$json_settings' WHERE id = $id";
    
    if (mysqli_query($conn, $query)) {
        // Catat ke log aktivitas
        $log_text = isset($data['log_msg']) ? $data['log_msg'] : "Mengubah pengaturan perangkat";
        mysqli_query($conn, "INSERT INTO log_aktivitas (id_perangkat, aksi) VALUES ($id, '$log_text')");

        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "Gagal update database"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Data tidak lengkap"]);
}
?>