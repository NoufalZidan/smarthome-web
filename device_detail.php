<?php
session_start();
require 'config.php';

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = (int)$_GET['id'];
$user_id = (int)$_SESSION['user_id'];
$query = mysqli_query($conn, "SELECT * FROM perangkat_iot WHERE id = $id AND user_id = $user_id");
$device = mysqli_fetch_assoc($query);

if (!$device) {
    header("Location: index.php");
    exit;
}

// Decode JSON spesifikasi_ekstra
$settings = json_decode($device['spesifikasi_ekstra'], true) ?: [];
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control Center - <?= htmlspecialchars($device['nama_perangkat']) ?></title>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .detail-wrapper {
            width: 700px;
            background: var(--glass-main);
            backdrop-filter: blur(40px) saturate(180%);
            -webkit-backdrop-filter: blur(40px);
            border: 1px solid var(--glass-border);
            border-radius: 32px;
            padding: 35px;
            box-shadow: 0 40px 80px rgba(0, 0, 0, 0.5);
            margin: 40px auto;
        }

        .setting-card {
            background: var(--glass-card);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 18px;
        }

        .range-slider {
            width: 100%;
            height: 8px;
            border-radius: 4px;
            background: rgba(255, 255, 255, 0.2);
            outline: none;
            accent-color: var(--accent-blue);
            cursor: pointer;
        }

        .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        .btn-opt {
            flex: 1;
            padding: 10px;
            border-radius: 12px;
            border: 1px solid var(--glass-border);
            background: rgba(255, 255, 255, 0.05);
            color: var(--text-muted);
            cursor: pointer;
            text-align: center;
            font-size: 13px;
            transition: 0.3s;
        }

        .btn-opt.active {
            background: var(--accent-blue);
            color: white;
            border-color: var(--accent-blue);
            box-shadow: 0 0 15px rgba(10, 132, 255, 0.4);
        }
    </style>
</head>

<body>

    <div class="detail-wrapper">
        <!-- Header Info Perangkat -->
        <div class="form-header" style="justify-content: space-between;">
            <div style="display:flex; align-items:center; gap:15px;">
                <a href="index.php" class="back-btn"><i class="ph ph-arrow-left"></i></a>
                <div>
                    <h2 style="margin:0; font-size: 22px; font-weight: 500;"><?= htmlspecialchars($device['nama_perangkat']) ?></h2>
                    <span class="text-xs"><?= $device['kategori'] ?> &bull; <?= $device['mac_address'] ?></span>
                </div>
            </div>
            <a href="edit_device.php?id=<?= $device['id'] ?>" class="back-btn" title="Pengaturan Dasar"><i class="ph ph-gear"></i></a>
        </div>

        <input type="hidden" id="device_id" value="<?= $device['id'] ?>">

        <!-- ================= CONTROLLER SISTEM AC ================= -->
        <?php if ($device['kategori'] == 'SistemAC'):
            $suhu = isset($settings['suhu']) ? $settings['suhu'] : 22;
            $fan = isset($settings['fan_speed']) ? $settings['fan_speed'] : 'Medium';
            $mode = isset($settings['mode']) ? $settings['mode'] : 'Cool';
        ?>
            <!-- Slider Suhu -->
            <div class="setting-card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                    <span style="font-weight: 500;">Suhu AC</span>
                    <span id="val-suhu" style="font-size: 28px; font-weight: 600; color: #ffffff;"><?= $suhu ?>°C</span>
                </div>
                <input type="range" class="range-slider" id="slider-suhu" min="16" max="30" value="<?= $suhu ?>">
            </div>

            <!-- Kecepatan Kipas -->
            <div class="setting-card">
                <span style="font-weight: 500; display:block; margin-bottom:8px;">Kecepatan Kipas (Fan Speed)</span>
                <div class="btn-group" id="group-fan">
                    <button type="button" class="btn-opt <?= $fan == 'Low' ? 'active' : '' ?>" data-val="Low">Low</button>
                    <button type="button" class="btn-opt <?= $fan == 'Medium' ? 'active' : '' ?>" data-val="Medium">Medium</button>
                    <button type="button" class="btn-opt <?= $fan == 'High' ? 'active' : '' ?>" data-val="High">High</button>
                    <button type="button" class="btn-opt <?= $fan == 'Auto' ? 'active' : '' ?>" data-val="Auto">Auto</button>
                </div>
            </div>

            <!-- Mode Operasi -->
            <div class="setting-card">
                <span style="font-weight: 500; display:block; margin-bottom:8px;">Mode</span>
                <div class="btn-group" id="group-mode">
                    <button type="button" class="btn-opt <?= $mode == 'Cool' ? 'active' : '' ?>" data-val="Cool"><i class="ph ph-snowflake"></i> Cool</button>
                    <button type="button" class="btn-opt <?= $mode == 'Dry' ? 'active' : '' ?>" data-val="Dry"><i class="ph ph-drop"></i> Dry</button>
                    <button type="button" class="btn-opt <?= $mode == 'Fan' ? 'active' : '' ?>" data-val="Fan"><i class="ph ph-wind"></i> Fan Only</button>
                </div>
            </div>

            <!-- ================= CONTROLLER LAMPU PINTAR ================= -->
        <?php elseif ($device['kategori'] == 'LampuPintar'):
            $bright = isset($settings['brightness']) ? $settings['brightness'] : 80;
            $color = isset($settings['color_mode']) ? $settings['color_mode'] : 'Warm';
        ?>
            <!-- Slider Dimmer Kecerahan -->
            <div class="setting-card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                    <span style="font-weight: 500;">Intensitas Cahaya</span>
                    <span id="val-bright" style="font-size: 24px; font-weight:300; color:#ffd700;"><?= $bright ?>%</span>
                </div>
                <input type="range" class="range-slider" id="slider-bright" min="5" max="100" value="<?= $bright ?>">
            </div>

            <!-- Temperatur Warna Lampu -->
            <div class="setting-card">
                <span style="font-weight: 500; display:block; margin-bottom:8px;">Suasana Warna (Color Temp)</span>
                <div class="btn-group" id="group-color">
                    <button type="button" class="btn-opt <?= $color == 'Warm' ? 'active' : '' ?>" data-val="Warm">Warm</button>
                    <button type="button" class="btn-opt <?= $color == 'Natural' ? 'active' : '' ?>" data-val="Natural">Natural</button>
                    <button type="button" class="btn-opt <?= $color == 'Cool' ? 'active' : '' ?>" data-val="Cool">Cool</button>
                </div>
            </div>

            <!-- ================= CONTROLLER PINTU PINTAR ================= -->
        <?php elseif ($device['kategori'] == 'PintuPintar'):
            $locked = isset($settings['is_locked']) ? $settings['is_locked'] : 1;
            $autolock = isset($settings['auto_lock_sec']) ? $settings['auto_lock_sec'] : 30;
        ?>
            <!-- Lock / Unlock Card -->
            <div class="setting-card" style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <div style="font-weight: 500;">Status Kunci</div>
                    <div class="text-xs" id="lbl-lock"><?= $locked ? 'Terkunci Aman' : 'Pintu Terbuka' ?></div>
                </div>
                <label class="switch">
                    <input type="checkbox" id="toggle-lock" <?= $locked ? 'checked' : '' ?>>
                    <span class="slider-sw"></span>
                </label>
            </div>

            <!-- Auto Lock Timer -->
            <div class="setting-card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                    <span style="font-weight: 500;">Auto-Lock Otomatis</span>
                    <span id="val-autolock" style="font-size: 18px; font-weight:300; color: #fff;"><?= $autolock ?> Detik</span>
                </div>
                <input type="range" class="range-slider" id="slider-autolock" min="5" max="120" step="5" value="<?= $autolock ?>">
            </div>
        <?php endif; ?>
    </div>

    <script>
        const deviceId = document.getElementById('device_id').value;

        // Fungsi Simpan ke Backend via Fetch
        function saveSetting(newSettingsObj, logMsg) {
            fetch('api_update_setting.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id: deviceId,
                    settings: newSettingsObj,
                    log_msg: logMsg
                })
            }).then(r => r.json()).then(d => {
                if (!d.success) console.error("Gagal simpan setting");
            });
        }

        // --- HANDLER SISTEM AC ---
        const sliderSuhu = document.getElementById('slider-suhu');
        if (sliderSuhu) {
            sliderSuhu.addEventListener('input', function() {
                document.getElementById('val-suhu').innerText = this.value + '°C';
            });
            sliderSuhu.addEventListener('change', function() {
                saveSetting({
                    suhu: parseInt(this.value)
                }, `Atur suhu AC ke ${this.value}°C`);
            });
        }

        // Button Group Fan & Mode
        ['group-fan', 'group-mode'].forEach(groupId => {
            const grp = document.getElementById(groupId);
            if (grp) {
                grp.querySelectorAll('.btn-opt').forEach(btn => {
                    btn.addEventListener('click', function() {
                        grp.querySelector('.active')?.classList.remove('active');
                        this.classList.add('active');

                        const suhuVal = parseInt(document.getElementById('slider-suhu').value);
                        const fanVal = document.querySelector('#group-fan .btn-opt.active').getAttribute('data-val');
                        const modeVal = document.querySelector('#group-mode .btn-opt.active').getAttribute('data-val');
                        saveSetting({
                            suhu: suhuVal,
                            fan_speed: fanVal,
                            mode: modeVal
                        }, `Mengubah mode AC ke ${modeVal} / ${fanVal}`);
                    });
                });
            }
        });

        // --- HANDLER LAMPU PINTAR ---
        const sliderBright = document.getElementById('slider-bright');
        if (sliderBright) {
            sliderBright.addEventListener('input', function() {
                document.getElementById('val-bright').innerText = this.value + '%';
            });
            sliderBright.addEventListener('change', function() {
                const colorVal = document.querySelector('#group-color .btn-opt.active')?.getAttribute('data-val') || 'Warm';
                saveSetting({
                    brightness: parseInt(this.value),
                    color_mode: colorVal
                }, `Atur kecerahan lampu ke ${this.value}%`);
            });
        }

        const grpColor = document.getElementById('group-color');
        if (grpColor) {
            grpColor.querySelectorAll('.btn-opt').forEach(btn => {
                btn.addEventListener('click', function() {
                    grpColor.querySelector('.active')?.classList.remove('active');
                    this.classList.add('active');
                    const bVal = parseInt(document.getElementById('slider-bright').value);
                    saveSetting({
                        brightness: bVal,
                        color_mode: this.getAttribute('data-val')
                    }, `Ubah warna lampu ke ${this.getAttribute('data-val')}`);
                });
            });
        }

        // --- HANDLER PINTU PINTAR ---
        const toggleLock = document.getElementById('toggle-lock');
        if (toggleLock) {
            toggleLock.addEventListener('change', function() {
                const isLocked = this.checked ? 1 : 0;
                document.getElementById('lbl-lock').innerText = isLocked ? 'Terkunci Aman' : 'Pintu Terbuka';
                const autolockVal = parseInt(document.getElementById('slider-autolock').value);
                saveSetting({
                    is_locked: isLocked,
                    auto_lock_sec: autolockVal
                }, isLocked ? "Mengunci pintu" : "Membuka kunci pintu");
            });
        }

        const sliderAutoLock = document.getElementById('slider-autolock');
        if (sliderAutoLock) {
            sliderAutoLock.addEventListener('input', function() {
                document.getElementById('val-autolock').innerText = this.value + ' Detik';
            });
            sliderAutoLock.addEventListener('change', function() {
                const isLocked = document.getElementById('toggle-lock').checked ? 1 : 0;
                saveSetting({
                    is_locked: isLocked,
                    auto_lock_sec: parseInt(this.value)
                }, `Set auto-lock ${this.value}s`);
            });
        }
    </script>
</body>

</html>