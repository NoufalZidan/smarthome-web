<?php
session_start();
require 'config.php';

if (!isset($_SESSION['login'])) {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['id']) && isset($data['status'])) {
    $id = (int)$data['id'];
    $status = (int)$data['status']; // Pindahkan ke atas sebelum $query
    $user_id = (int)($_SESSION['user_id'] ?? 0);

    // Update status daya khusus milik user yang sedang login
    $query = "UPDATE perangkat_iot SET status_daya = $status WHERE id = $id AND user_id = $user_id";

    if (mysqli_query($conn, $query)) {
        $aksi = $status == 1 ? 'Dihidupkan' : 'Dimatikan';
        mysqli_query($conn, "INSERT INTO log_aktivitas (id_perangkat, aksi) VALUES ($id, '$aksi')");

        // 1. Hitung ulang total perangkat aktif khusus milik user ini
        $act_res = mysqli_query($conn, "SELECT COUNT(*) as active_count FROM perangkat_iot WHERE status_daya = 1 AND user_id = $user_id");
        $active_count = mysqli_fetch_assoc($act_res)['active_count'];

        // 2. Ambil data terbaru dari Lampu Utama khusus milik user ini
        $lamp_res = mysqli_query($conn, "SELECT * FROM perangkat_iot WHERE kategori = 'LampuPintar' AND user_id = $user_id LIMIT 1");
        $main_lamp = mysqli_fetch_assoc($lamp_res);

        $lamp_status = 0;
        $lamp_watt = 0;
        $lamp_brightness = 0;
        $waktu_nyala_text = "Mati";

        if ($main_lamp) {
            $lamp_status = (int)$main_lamp['status_daya'];

            // Pembacaan JSON aman
            $lamp_json = json_decode($main_lamp['spesifikasi_ekstra'], true);
            if (is_array($lamp_json) && isset($lamp_json['brightness'])) {
                $lamp_brightness = (int)$lamp_json['brightness'];
            } else {
                $lamp_brightness = (int) preg_replace('/[^0-9]/', '', $main_lamp['spesifikasi_ekstra']) ?: 70;
            }
            $lamp_brightness = min(100, max(0, $lamp_brightness));

            if ($lamp_status === 1) {
                $lamp_watt = round(10 + ($lamp_brightness * 0.62));
                $lamp_id = $main_lamp['id'];
                $log_q = mysqli_query($conn, "SELECT waktu FROM log_aktivitas WHERE id_perangkat = $lamp_id AND aksi = 'Dihidupkan' ORDER BY id DESC LIMIT 1");
                if ($log_row = mysqli_fetch_assoc($log_q)) {
                    $diff = time() - strtotime($log_row['waktu']);
                    $hours = floor($diff / 3600);
                    $minutes = floor(($diff % 3600) / 60);
                    $waktu_nyala_text = ($hours > 0) ? "$hours Jam $minutes Menit" : max(1, $minutes) . " Menit";
                } else {
                    $waktu_nyala_text = "Baru Nyala";
                }
            }
        }

        // Return JSON Lengkap
        echo json_encode([
            "success" => true,
            "active_count" => $active_count,
            "main_lamp" => [
                "id" => $main_lamp ? $main_lamp['id'] : null,
                "status" => $lamp_status,
                "watt" => $lamp_watt,
                "brightness" => $lamp_brightness,
                "waktu_nyala" => $waktu_nyala_text
            ]
        ]);
    } else {
        echo json_encode(["success" => false]);
    }
}
