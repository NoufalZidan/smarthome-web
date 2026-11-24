<?php
session_start();
require 'config.php';

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// Ambil user_id dari session (dengan fallback lookup username jika session lama masih aktif)
$user_id = $_SESSION['user_id'] ?? 0;
if (!$user_id && isset($_SESSION['username'])) {
    $u_esc = mysqli_real_escape_string($conn, $_SESSION['username']);
    $u_res = mysqli_query($conn, "SELECT id FROM users WHERE username = '$u_esc' LIMIT 1");
    if ($u_row = mysqli_fetch_assoc($u_res)) {
        $user_id = (int)$u_row['id'];
        $_SESSION['user_id'] = $user_id;
    }
}

$role = $_SESSION['role'];
$username = ucfirst($_SESSION['username']);

// 1. Fetch perangkat khusus milik user ini
$all_devices = mysqli_query($conn, "SELECT * FROM perangkat_iot WHERE user_id = $user_id");

// 2. Fetch Lampu Utama milik user ini
$lamp_query = mysqli_query($conn, "SELECT * FROM perangkat_iot WHERE kategori = 'LampuPintar' AND user_id = $user_id LIMIT 1");
$main_lamp = mysqli_fetch_assoc($lamp_query);

// 3. Fetch AC Utama milik user ini
$ac_query = mysqli_query($conn, "SELECT * FROM perangkat_iot WHERE kategori = 'SistemAC' AND user_id = $user_id LIMIT 1");
$main_ac = mysqli_fetch_assoc($ac_query);

// --- PEMPROSESAN DATA DINAMIS AC ---
$ac_temp = 22; // default
if ($main_ac && !empty($main_ac['spesifikasi_ekstra'])) {
    $ac_json = json_decode($main_ac['spesifikasi_ekstra'], true);
    if (is_array($ac_json) && isset($ac_json['suhu'])) {
        $ac_temp = (int)$ac_json['suhu'];
    } else {
        // Fallback jika berupa string lama seperti "24C"
        $ac_temp = (int) preg_replace('/[^0-9]/', '', $main_ac['spesifikasi_ekstra']) ?: 22;
    }
}
// Batasi suhu agar rasional (16C - 30C)
$ac_temp = min(30, max(16, $ac_temp));

// --- PEMPROSESAN DATA DINAMIS LAMPU UTAMA (SAFE JSON READ) ---
$lamp_brightness = 70; // default
if ($main_lamp && !empty($main_lamp['spesifikasi_ekstra'])) {
    $lamp_json = json_decode($main_lamp['spesifikasi_ekstra'], true);
    if (is_array($lamp_json) && isset($lamp_json['brightness'])) {
        $lamp_brightness = (int)$lamp_json['brightness'];
    } else {
        // Fallback jika berupa string lama seperti "80%"
        $lamp_brightness = (int) preg_replace('/[^0-9]/', '', $main_lamp['spesifikasi_ekstra']) ?: 70;
    }
}
// Batasi intensitas maksimal 100%
$lamp_brightness = min(100, max(0, $lamp_brightness));

$lamp_watt = 0;
$waktu_nyala_text = "Mati";

if ($main_lamp && $main_lamp['status_daya'] == 1) {
    // Hitung Watt secara relatif berdasarkan intensitas cahaya (misal: max 72W)
    $lamp_watt = round(10 + ($lamp_brightness * 0.62));

    // Hitung durasi nyala dari log_aktivitas terakhir
    $lamp_id = $main_lamp['id'];
    $log_q = mysqli_query($conn, "SELECT waktu FROM log_aktivitas WHERE id_perangkat = $lamp_id AND aksi = 'Dihidupkan' ORDER BY id DESC LIMIT 1");

    if ($log_row = mysqli_fetch_assoc($log_q)) {
        $start_time = strtotime($log_row['waktu']);
        $diff = time() - $start_time;
        $hours = floor($diff / 3600);
        $minutes = floor(($diff % 3600) / 60);

        if ($hours > 0) {
            $waktu_nyala_text = $hours . " Jam " . $minutes . " Menit";
        } else {
            $waktu_nyala_text = max(1, $minutes) . " Menit";
        }
    } else {
        $waktu_nyala_text = "Baru Nyala";
    }
}

// --- HITUNG KONSUMSI POWER GRAFIK DINAMIS ---
$active_q = mysqli_query($conn, "SELECT COUNT(*) as total_active FROM perangkat_iot WHERE status_daya = 1 AND user_id = $user_id");
$active_count = mysqli_fetch_assoc($active_q)['total_active'];
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spatial Smart Home OS</title>
    <!-- Phosphor Icons CDN -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>

    <div class="spatial-wrapper">

        <div class="floating-sidebar">
            <a href="index.php" style="text-decoration:none;" title="Dashboard Utama">
                <div class="side-icon <?= (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : '' ?>"><i class="ph ph-house"></i></div>
            </a>

            <a href="export_csv.php" style="text-decoration:none;" title="Unduh Laporan CSV">
                <div class="side-icon"><i class="ph ph-chart-bar"></i></div>
            </a>

            <a href="settings.php" style="text-decoration:none;" title="Pengaturan Sistem">
                <div class="side-icon <?= (basename($_SERVER['PHP_SELF']) == 'settings.php') ? 'active' : '' ?>"><i class="ph ph-gear"></i></div>
            </a>

            <a href="profile.php" style="text-decoration:none;" title="Profil Akun">
                <div class="side-icon <?= (basename($_SERVER['PHP_SELF']) == 'profile.php') ? 'active' : '' ?>"><i class="ph ph-user"></i></div>
            </a>

            <a href="logout.php" style="text-decoration:none; margin-top: auto;" title="Keluar">
                <div class="side-icon" style="color: #ff453a;"><i class="ph ph-sign-out"></i></div>
            </a>
        </div>

        <!-- 2. FLOATING BOTTOM NAV -->
        <div class="floating-bottom">
            <div class="nav-pill active">Semua</div>
            <div class="nav-pill">Ruang Tamu</div>
            <div class="nav-pill">Kamar Tidur</div>
            <div class="nav-pill">Dapur</div>
            <div class="nav-pill">Halaman</div>
            <a href="add_device.php" style="text-decoration: none;" title="Tambah Perangkat">
                <div class="nav-pill" style="background: var(--accent-blue); color: white; width: 22px; height: 22px; display:flex; justify-content:center; align-items:center; border-radius: 50%; font-weight:bold; font-size: 16px;">
                    <i class="ph ph-plus"></i>
                </div>
            </a>
        </div>

        <!-- 3. MAIN GLASS PANEL -->
        <div class="main-panel">

            <!-- 1. PANEL LAMPU UTAMA -->
            <div class="inner-card panel-lamp">
                <div class="lamp-header">
                    <!-- Tambahkan id="main-lamp-icon" -->
                    <div id="main-lamp-icon" class="lamp-icon-big" style="color: <?= ($main_lamp && $main_lamp['status_daya'] == 1) ? '#ffd700' : 'rgba(255,255,255,0.2)' ?>;">
                        <i class="ph-fill ph-sun"></i>
                    </div>
                    <div style="text-align: right;">
                        <div class="text-xs">Perangkat Utama</div>
                        <div style="font-weight: 500; font-size: 18px;"><?= $main_lamp ? htmlspecialchars($main_lamp['nama_perangkat']) : 'Tidak ada lampu' ?></div>
                    </div>
                </div>
                <div style="display: flex; gap: 10px; margin-top: 10px;">
                    <div style="background: rgba(255,255,255,0.1); padding: 10px 15px; border-radius: 12px; flex: 1;">
                        <div class="text-xs">Waktu Nyala</div>
                        <!-- Tambahkan id="main-lamp-waktu" -->
                        <div id="main-lamp-waktu" style="font-size: 18px; font-weight: 500;"><?= $waktu_nyala_text ?></div>
                    </div>
                    <div style="background: rgba(255,255,255,0.1); padding: 10px 15px; border-radius: 12px; flex: 1;">
                        <div class="text-xs">Daya</div>
                        <!-- Tambahkan id="main-lamp-watt" -->
                        <div id="main-lamp-watt" style="font-size: 18px; font-weight: 500;"><?= $lamp_watt ?> Watt</div>
                    </div>
                </div>
                <div class="slider-control">
                    <!-- Tambahkan id="main-lamp-slider" -->
                    <div id="main-lamp-slider" class="slider-fill" style="width: <?= ($main_lamp && $main_lamp['status_daya'] == 1) ? $lamp_brightness : 0 ?>%;"></div>
                </div>
                <div style="display: flex; justify-content: space-between; margin-top: 10px;" class="text-xs">
                    <span>Intensitas Cahaya</span>
                    <!-- Tambahkan id="main-lamp-brightness" -->
                    <span id="main-lamp-brightness"><?= ($main_lamp && $main_lamp['status_daya'] == 1) ? $lamp_brightness . '%' : '0%' ?></span>
                </div>
            </div>

            <!-- 2. PANEL KONSUMSI LISTRIK GRAFIK BAR -->
            <div class="inner-card panel-power">
                <div class="text-xs">Konsumsi Listrik Aktif</div>
                <!-- Tambahkan id="power-bars" -->
                <div id="power-bars" style="display: flex; align-items: flex-end; gap: 8px; height: 80px; margin-top: 20px;">
                    <div style="flex:1; background:rgba(255,255,255,0.2); height: <?= min(100, $active_count * 25) ?>%; border-radius: 4px;"></div>
                    <div style="flex:1; background:var(--accent-blue); height: <?= min(100, ($active_count + 1) * 30) ?>%; border-radius: 4px; box-shadow: 0 0 15px rgba(10,132,255,0.5);"></div>
                    <div style="flex:1; background:rgba(255,255,255,0.2); height: <?= min(100, $active_count * 20) ?>%; border-radius: 4px;"></div>
                    <div style="flex:1; background:rgba(255,255,255,0.2); height: 35%; border-radius: 4px;"></div>
                    <div style="flex:1; background:rgba(255,255,255,0.2); height: 65%; border-radius: 4px;"></div>
                </div>
            </div>

            <!-- SENSOR SUHU DINAMIS SESUAI TERMOSTAT AC -->
            <div class="panel-sensors">
                <div class="inner-card" style="padding: 15px;">
                    <div class="text-xs">Kelembapan</div>
                    <div class="text-lg"><?= 40 + ($ac_temp % 5) ?>,2%</div>
                </div>
                <div class="inner-card" style="padding: 15px;">
                    <div class="text-xs">Suhu Ruangan</div>
                    <div class="text-lg"><?= $ac_temp ?>° C</div>
                </div>
            </div>

            <div class="inner-card panel-ac">
                <div id="jam-digital" style="font-size: 20px; font-weight: 500; margin-bottom: 30px;">00:00 WIB</div>

                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div class="text-xs">Termostat AC</div>
                    <!-- SWITCH TERBUNGKUS DENGAN ATTRIBUTE API TOGGLE -->
                    <label class="switch">
                        <input type="checkbox" class="toggle-power" data-id="<?= $main_ac ? $main_ac['id'] : '' ?>" <?= ($main_ac && $main_ac['status_daya'] == 1) ? 'checked' : '' ?>>
                        <span class="slider-sw"></span>
                    </label>
                </div>

                <!-- DIAL SUHU AMBIL DARI SPESIFIKASI EKSTRA -->
                <div class="thermostat-dial" style="border-left-color: <?= ($main_ac && $main_ac['status_daya'] == 1) ? 'var(--accent-blue)' : 'rgba(255,255,255,0.1)' ?>;">
                    <div class="thermostat-inner">
                        <h2 class="temp-number"><?= $ac_temp ?>&deg;</h2>
                        <div class="temp-label">Celsius</div>
                    </div>
                </div>

                <div class="text-xs" style="margin-bottom: 5px;"><?= $main_ac ? htmlspecialchars($main_ac['nama_perangkat']) : 'AC Ruangan' ?></div>
                <div class="text-xs" style="font-family: monospace;"><?= $main_ac ? htmlspecialchars($main_ac['mac_address']) : '00:00:00:00:00' ?></div>
            </div>


            <div class="panel-toggles">
                <?php while ($row = mysqli_fetch_assoc($all_devices)) : ?>
                    <div class="toggle-card <?= $row['status_daya'] == 1 ? 'active' : '' ?>" id="card-<?= $row['id']; ?>" data-room="<?= htmlspecialchars($row['lokasi_ruangan'] ?? 'Ruang Tamu'); ?>">

                        <!-- Head Kartu (Ikon Perangkat, Tombol Edit Pensil, & Switch Power) -->
                        <div class="t-head">
                            <div class="t-icon">
                                <?php
                                if ($row['kategori'] == 'LampuPintar') echo '<i class="ph-fill ph-sun"></i>';
                                elseif ($row['kategori'] == 'SistemAC') echo '<i class="ph-fill ph-snowflake"></i>';
                                elseif ($row['kategori'] == 'PintuPintar') echo '<i class="ph-fill ph-door"></i>';
                                else echo '<i class="ph-fill ph-plug"></i>';
                                ?>
                            </div>

                            <div style="display: flex; align-items: center; gap: 10px;">
                                <!-- Tombol Edit Perangkat (Pensil) -->
                                <a href="edit_device.php?id=<?= $row['id']; ?>" style="color: var(--text-muted); text-decoration: none;" title="Edit Data Perangkat">
                                    <i class="ph ph-pencil-simple" style="font-size: 18px; cursor: pointer; transition: 0.2s;" onmouseover="this.style.color='#ffffff'" onmouseout="this.style.color='var(--text-muted)'"></i>
                                </a>

                                <!-- Switch ON/OFF -->
                                <label class="switch">
                                    <input type="checkbox" class="toggle-power" data-id="<?= $row['id']; ?>" <?= $row['status_daya'] == 1 ? 'checked' : '' ?>>
                                    <span class="slider-sw"></span>
                                </label>
                            </div>
                        </div>

                        <!-- Body Kartu (Kategori + Lokasi & Link ke Kontrol Slider Detail) -->
                        <div>
                            <div class="t-brand"><?= htmlspecialchars($row['kategori']); ?> &bull; <?= htmlspecialchars($row['lokasi_ruangan'] ?? 'Ruang Tamu'); ?></div>
                            <a href="device_detail.php?id=<?= $row['id']; ?>" style="text-decoration: none; color: inherit; display: inline-block; margin-top: 4px;" title="Atur Slider & Fitur Detail">
                                <h3 class="t-name" style="cursor: pointer; margin: 0; display: flex; align-items: center; gap: 5px;">
                                    <?= htmlspecialchars($row['nama_perangkat']); ?>
                                    <i class="ph ph-sliders" style="font-size: 13px; opacity: 0.7; color: var(--accent-blue);"></i>
                                </h3>
                            </a>
                        </div>

                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
    <script src="assets/main.js"></script>
</body>

</html>