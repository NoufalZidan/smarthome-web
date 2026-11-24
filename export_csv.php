<?php
session_start();
require 'config.php';

// Proteksi Session Login
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// Set Header HTTP agar browser mengenali ini sebagai unduhan file CSV
$filename = "log_aktivitas_smarthome_" . date('Y-m-d_H-i-s') . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Buka output stream PHP
$output = fopen('php://output', 'w');

// Tambahkan BOM (Byte Order Mark) agar huruf/karakter khusus dibaca sempurna oleh Microsoft Excel
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Tulis Judul Kolom (Header CSV)
fputcsv($output, ['No', 'Nama Perangkat', 'Kategori', 'Aksi / Perubahan', 'Waktu Aktivitas']);

// Query Join data log aktivitas dengan detail perangkatnya
$query = "SELECT l.id, p.nama_perangkat, p.kategori, l.aksi, l.waktu 
          FROM log_aktivitas l
          LEFT JOIN perangkat_iot p ON l.id_perangkat = p.id
          ORDER BY l.waktu DESC LIMIT 100";

$result = mysqli_query($conn, $query);

$no = 1;
while ($row = mysqli_fetch_assoc($result)) {
    fputcsv($output, [
        $no++,
        $row['nama_perangkat'] ? $row['nama_perangkat'] : 'Perangkat Terhapus',
        $row['kategori'] ? $row['kategori'] : '-',
        $row['aksi'],
        $row['waktu']
    ]);
}

fclose($output);
exit;
?>