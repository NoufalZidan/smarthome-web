// 1. Format Jam 24 Jam (WIB)
setInterval(() => {
  const jamElement = document.getElementById("jam-digital");
  if (jamElement) {
    // Cek elemen agar tidak error di halaman lain
    const now = new Date();
    jamElement.innerHTML =
      now.toLocaleTimeString("id-ID", {
        hour: "2-digit",
        minute: "2-digit",
        second: "2-digit",
      }) + " WIB";
  }
}, 1000);

// 2. Fetch API untuk Toggle CRUD & Sinkronisasi Semua Switch
// Fetch API untuk Toggle CRUD & Update Real-Time UI
document.querySelectorAll(".toggle-power").forEach((toggle) => {
  toggle.addEventListener("change", function () {
    const id = this.getAttribute("data-id");
    const status = this.checked ? 1 : 0;
    const card = document.getElementById("card-" + id);

    // A. Sinkronkan semua switch di layar yang punya ID sama
    document
      .querySelectorAll(`.toggle-power[data-id="${id}"]`)
      .forEach((input) => {
        input.checked = status === 1;
      });

    // B. Ubah tampilan kartu
    if (card) {
      if (status === 1) {
        card.classList.add("active");
      } else {
        card.classList.remove("active");
      }
    }

    // C. Kirim request ke backend & Update Panel Atas secara Real-Time
    fetch("api_toggle.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ id: id, status: status }),
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          // 1. UPDATE REAL-TIME PANEL LAMPU UTAMA
          const ml = data.main_lamp;
          if (ml) {
            const lampIcon = document.getElementById("main-lamp-icon");
            const lampWaktu = document.getElementById("main-lamp-waktu");
            const lampWatt = document.getElementById("main-lamp-watt");
            const lampSlider = document.getElementById("main-lamp-slider");
            const lampBrightness = document.getElementById(
              "main-lamp-brightness",
            );

            if (lampIcon)
              lampIcon.style.color =
                ml.status === 1 ? "#ffd700" : "rgba(255,255,255,0.2)";
            if (lampWaktu) lampWaktu.innerText = ml.waktu_nyala;
            if (lampWatt) lampWatt.innerText = ml.watt + " Watt";
            if (lampSlider)
              lampSlider.style.width =
                ml.status === 1 ? ml.brightness + "%" : "0%";
            if (lampBrightness)
              lampBrightness.innerText =
                ml.status === 1 ? ml.brightness + "%" : "0%";
          }

          // 2. UPDATE REAL-TIME GRAFIK KONSUMSI LISTRIK
          const powerBars = document.getElementById("power-bars");
          if (powerBars) {
            const bars = powerBars.children;
            const ac = data.active_count;
            if (bars.length >= 3) {
              bars[0].style.height = Math.min(100, ac * 25) + "%";
              bars[1].style.height = Math.min(100, (ac + 1) * 30) + "%";
              bars[2].style.height = Math.min(100, ac * 20) + "%";
            }
          }
        } else {
          alert("Gagal update status!");
        }
      })
      .catch((err) => {
        console.error(err);
      });
  });
});

// 3. Efek Active untuk Sidebar Kiri
const sideIcons = document.querySelectorAll(".floating-sidebar .side-icon");
sideIcons.forEach((icon) => {
  // Kita skip icon logout karena dia dibungkus tag <a> dan langsung pindah halaman
  if (icon.parentElement.tagName !== "A") {
    icon.addEventListener("click", function () {
      // Hapus class active dari icon lain
      document
        .querySelector(".floating-sidebar .side-icon.active")
        ?.classList.remove("active");
      // Tambahkan class active ke icon yang diklik
      this.classList.add("active");
    });
  }
});

// 4. Efek Active & Filter Sederhana untuk Navigasi Bawah (Ruangan)
const navPills = document.querySelectorAll(".floating-bottom .nav-pill");
navPills.forEach((pill) => {
  if (pill.parentElement.tagName !== "A") {
    // Skip tombol +
    pill.addEventListener("click", function () {
      document
        .querySelector(".floating-bottom .nav-pill.active")
        ?.classList.remove("active");
      this.classList.add("active");

      const selectedRoom = this.innerText.trim().toLowerCase();
      const cards = document.querySelectorAll(".toggle-card");

      cards.forEach((card) => {
        const cardRoom = (card.getAttribute("data-room") || "").toLowerCase();

        // Jika pilih "Semua" atau lokasinya cocok dengan tab ruangan
        if (selectedRoom === "semua" || cardRoom === selectedRoom) {
          card.style.display = "flex";
        } else {
          card.style.display = "none";
        }
      });
    });
  }
});
