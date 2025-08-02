<?php
require_once("config.php");
require_once("utils/utils.php");
if(isset($_POST["submit"])){
  $nama = htmlspecialchars($_POST["nama"]);
  $kelas = htmlspecialchars($_POST["kelas"]);
  $jk = htmlspecialchars($_POST["jk"]);
  $pinjam = htmlspecialchars($_POST["pinjam"]);
  
  $data = [
    "nama"=>$nama,
    "kelas"=>$kelas,
    "jk"=>$jk,
    "pinjam"=>$pinjam
  ];
  
  if(insert("pengunjung", $data)){
    $id_pengunjung = $pdo->lastInsertId();
    $nama_modus = ucwords($nama);
    
    if($pinjam === "true"){
      $kode_buku = htmlspecialchars($_POST["kode_buku"] ?? NULL);
      $jenis_buku = htmlspecialchars($_POST["jenis_buku"]);
      $judul_buku = htmlspecialchars($_POST["judul_buku"]);
      $qty = htmlspecialchars($_POST["qty"]);
      $data_pinjam = [
        "id_pengunjung" => $id_pengunjung,
        "kode_buku" => $kode_buku,
        "jenis_buku" => $jenis_buku,
        "judul_buku" => $judul_buku,
        "qty" => $qty
      ];
      
      if(insert("pinjaman", $data_pinjam)){
        echo("<script>alert('berhasil meminjam buku, tolong ikuti aturan meminjam buku yang berlaku dengan penuh tanggung jawab yaa $nama_modus ! makasih😀'); window.location='/'</script>");
        exit;
      } else {
        echo("<script>alert('Gagal Meminjam buku'); window.location='/'</script>");
        exit;
      }
    }
    
    echo("<script>alert('Data absensi pengunjung berhasil dibuat, selamat berkunjung $nama_modus 😄'); window.location='/'</script>");
    exit;
  } else {
    echo("<script>alert('Gagal'); window.location='/'</script>");
    exit;
  }
}

$class = [];
$tingkatan = ["X", "XI", "XII"];
foreach ($tingkatan as $tingkat) {
  for ($i = 1; $i <= 9; $i++) {
    $class[] = "$tingkat-$i";
  }
}
?>
<!DOCTYPE hmtl>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta http-equiv="X-UA-Compatible" content="ie=edge" />
  <title>Form Pengunjung Perpustakaan</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
</head>
<body class="bg-gray-100 px-4 pb-32">
  <?php require_once("navigasi.php") ?>
  <form action="" method="post" class="bg-white shadow-lg rounded-xl p-8 w-full max-w-md space-y-4 mt-5 mx-auto">
    <h1 class="text-2xl font-bold text-center text-gray-800 mb-6">Form Pengunjung Perpustakaan <span id="today" class="text-blue-500"></span></h1>
    <div>
      <input type="text" name="nama" placeholder="Nama Lengkap" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
    </div>
    <div>
      <label class="block text-gray-700 mb-1">Kelas</label>
      <select name="kelas" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
        <option value="" disabled selected>-- Pilih --</option>
        <?php foreach ($class as $kelas): ?>
          <option value="<?= $kelas ?>"><?= $kelas ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="block text-gray-700 mb-1">Jenis Kelamin</label>
      <select name="jk" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
        <option value="" disabled selected >-- Pilih --</option>
        <option value="P">Perempuan</option>
        <option value="L">Laki-laki</option>
      </select>
    </div>
    <div>
      <label class="block text-gray-700 mb-1">Pinjam?</label>
      <select name="pinjam" id="pinjamSelect" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
        <option value="false">Tidak</option>
        <option value="true">Ya</option>
      </select>
    </div>
    <div id="pinjamFields" class="space-y-3 hidden"></div>
    <div class="text-center">
      <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition duration-200 w-full" name="submit">Simpan</button>
    </div>
  </form>
  <div id="scanModal" class="fixed inset-0 bg-black bg-opacity-80 flex items-center justify-center z-50 hidden">
    <div class="bg-white p-4 rounded-lg shadow-xl relative w-full max-w-md mx-4">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-bold text-gray-800">Scan Barcode</h2>
        <button id="closeScan" type="button" class="text-gray-500 hover:text-red-600 text-3xl font-bold leading-none p-1">&times;</button>
      </div>
      <div id="qr-reader" class="mb-4"></div>
      <div class="flex gap-2">
        <button id="stopScan" type="button" class="flex-1 bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 font-medium">Stop Scanner</button>
      </div>
    </div>
  </div>
  <audio id="notif-audio" src="done.mp3" preload="auto" class="hidden"></audio>
  <script>
    const pinjamSelect = document.getElementById("pinjamSelect");
    const pinjamFields = document.getElementById("pinjamFields");
    let html5QrcodeScanner = null;
    
    function playNotification() {
      const audio = document.getElementById("notif-audio");
      if (audio) {
        audio.currentTime = 0;
        audio.play().catch((err) => {
          console.warn("Gagal memutar audio:", err);
        });
      }
    }
    
    function createInputField({ name, type = "text", placeholder = "", required = false }) {
      const wrapper = document.createElement("div");
      const input = document.createElement("input");
      input.type = type;
      input.name = name;
      input.placeholder = placeholder;
      input.required = required;
      input.className = "w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500";
      wrapper.appendChild(input);
      return wrapper;
    }

    function createSelectField({ name, required = false }) {
      const wrapper = document.createElement("div");
      const select = document.createElement("select");
      select.name = name;
      select.required = required;
      select.className = "w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500";

      const options = [
        { value: "", text: "-- Pilih Jenis Buku --", disabled: true, selected: true },
        { value: "Mapel", text: "Mapel" },
        { value: "Non-Mapel", text: "Non-Mapel" }
      ];

      options.forEach(opt => {
        const option = document.createElement("option");
        option.value = opt.value;
        option.textContent = opt.text;
        if (opt.disabled) option.disabled = true;
        if (opt.selected) option.selected = true;
        select.appendChild(option);
      });

      wrapper.appendChild(select);
      return wrapper;
    }

    function generatePinjamFields() {
      pinjamFields.innerHTML = "";
      const wrapperKode = document.createElement("div");
      wrapperKode.className = "space-y-2";

      const inputKode = document.createElement("input");
      inputKode.type = "text";
      inputKode.name = "kode_buku";
      inputKode.placeholder = "Kode Buku";
      inputKode.id = "kode_buku_result";
      inputKode.className = "w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500";

      const scanBtn = document.createElement("button");
      scanBtn.type = "button";
      scanBtn.id = "start-scan-btn";
      scanBtn.innerHTML = "📷 Scan Barcode";
      scanBtn.className = "w-full px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 font-medium";

      wrapperKode.appendChild(inputKode);
      wrapperKode.appendChild(scanBtn);
      pinjamFields.appendChild(wrapperKode);
      pinjamFields.appendChild(createSelectField({ name: "jenis_buku", required: true }));
      pinjamFields.appendChild(createInputField({ name: "judul_buku", placeholder: "Judul Buku", required: true }));
      pinjamFields.appendChild(createInputField({ name: "qty", placeholder: "Jumlah", type: "number", required: true }));
      setupScanner();
    }

    function setupScanner() {
      const modal = document.getElementById("scanModal");
      const scanBtn = document.getElementById("start-scan-btn");
      const closeScan = document.getElementById("closeScan");
      const stopScan = document.getElementById("stopScan");
      const inputTarget = document.getElementById("kode_buku_result");
      const qrReader = document.getElementById("qr-reader");

      function onScanSuccess(decodedText, decodedResult) {
        if (decodedText) {
          playNotification();
          inputTarget.value = decodedText;
          html5QrcodeScanner.stop().then(() => {
            modal.classList.add("hidden");
            html5QrcodeScanner.clear();
            html5QrcodeScanner = null;
          });
        }
      }

      scanBtn?.addEventListener("click", () => {
        modal.classList.remove("hidden");
        html5QrcodeScanner = new Html5Qrcode("qr-reader");
        html5QrcodeScanner.start(
          { facingMode: "environment" },
          { fps: 10, qrbox: { width: 250, height: 150 } },
          onScanSuccess,
          (err) => console.warn("QR Error", err)
        );
      });

      stopScan?.addEventListener("click", () => {
        if (html5QrcodeScanner) {
          html5QrcodeScanner.stop().then(() => {
            html5QrcodeScanner.clear();
            modal.classList.add("hidden");
            html5QrcodeScanner = null;
          });
        }
      });

      closeScan?.addEventListener("click", () => {
        if (html5QrcodeScanner) {
          html5QrcodeScanner.stop().then(() => {
            html5QrcodeScanner.clear();
            modal.classList.add("hidden");
            html5QrcodeScanner = null;
          });
        }
      });
    }
    
    pinjamSelect.addEventListener("change", function () {
      const isPinjam = this.value === "true";
      pinjamFields.classList.toggle("hidden", !isPinjam);
      if (isPinjam) generatePinjamFields();
      else pinjamFields.innerHTML = "";
    });
    
    document.addEventListener("DOMContentLoaded", () => {
      pinjamSelect.dispatchEvent(new Event("change"));
    });
    
    const now = new Date();
    document.getElementById("today").textContent = now.toLocaleDateString('en-GB', { day: 'numeric', month: 'numeric', year: '2-digit' }).replace(/\//g, '-');
  </script>
  </body>
</html>