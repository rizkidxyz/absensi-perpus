<?php
session_start();
if(!isset($_SESSION["role"]) && $_SESSION["role"]!=="admin"){
  header("Location: /");
  exit;
}
require_once("../config.php");
require_once("../utils/utils.php");

$id = $_GET["id"];
$s = select("pengunjung", ["id"=>$id], false);//ambil detail pengunjung
$sp = select("pinjaman", ["id_pengunjung"=>$id], false);//ambil detail buku
if(isset($_POST["update"])){
  $nama = htmlspecialchars($_POST["nama"]);
  $kelas = htmlspecialchars($_POST["kelas"]);
  $jk = htmlspecialchars($_POST["jk"]);
  $pinjam = htmlspecialchars($_POST["pinjam"]);
  
  $data = [
    "nama"=>$nama,
    "kelas"=>$kelas,
    "jk"=>$jk,
    "pinjam"=>$pinjam,
    "updated"=>date("Y-m-d H:i:s")
  ];
  
  if(update("pengunjung", $data, ["id"=>$id])){
    //$id_pengunjung = $pdo->lastInsertId();
    $nama_modus = ucwords($nama);
    
    if($pinjam === "true"){
      $kode_buku = htmlspecialchars($_POST["kode_buku"] ?? NULL);
      $jenis_buku = htmlspecialchars($_POST["jenis_buku"]);
      $judul_buku = htmlspecialchars($_POST["judul_buku"]);
      $qty = htmlspecialchars($_POST["qty"]);
      $data_update = [
        "kode_buku" => $kode_buku,
        "jenis_buku" => $jenis_buku,
        "judul_buku" => $judul_buku,
        "qty" => $qty,
        "updated"=>date("Y-m-d H:i:s")
      ];
      if($sp){
        if(update("pinjaman", $data_update, ["id_pengunjung"=>$id])){
          echo("<script>alert('berhasil update, tolong ikuti aturan meminjam buku yang berlaku dengan penuh tanggung jawab yaa $nama_modus makasih😀'); window.location='/'</script>");
          exit;
        } else {
          echo("<script>alert('Gagal update'); window.location='/'</script>");
          exit;
        }
      }
      $data_insert = [
        "id_pengunjung"=>$id,
        "kode_buku" => $kode_buku,
        "jenis_buku" => $jenis_buku,
        "judul_buku" => $judul_buku,
        "qty" => $qty
      ];
      if(insert("pinjaman", $data_insert)){
          echo("<script>alert('berhasil update, tolong ikuti aturan meminjam buku yang berlaku dengan penuh tanggung jawab yaa $nama_modus makasih😀'); window.location='/'</script>");
          exit;
      } else {
        echo("<script>alert('Gagal update'); window.location='/'</script>");
        exit;
      }
    }
    
    if($pinjam==="false" && !empty($sp)){
      $data_gj = [
        "tgl_kembali"=>date("Y-m-d H:i:s"),
        "updated"=>date("Y-m-d H:i:s")
      ];
      if(update("pinjaman", $data_gj, ["id_pengunjung"=>$id])){
        echo("<script>alert('berhasil update, terimakasih $nama_modus sudah mengembalikan buku 😀'); window.location='/'</script>");
        exit;
      } else {
        echo("<script>alert('Gagal update'); window.location='/'</script>");
        exit;
      }
    }
    
    echo("<script>alert('Data absensi pengunjung berhasil update, selamat berkunjung $nama_modus 😄'); window.location='/'</script>");
    exit;
  } else {
    echo("<script>alert('Gagal'); window.location='/'</script>");
    exit;
  }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Form Edit Pengunjung</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
</head>
<body class="bg-gray-100 px-4 pb-32">
  <?php require_once("../navigasi.php") ?>
  <form action="" method="post" class="bg-white shadow-lg rounded-xl p-8 w-full max-w-md space-y-4 mt-5 mx-auto">
    <h1 class="text-2xl font-bold text-center text-gray-800 mb-6">Form Perubahan Data Pengunjung</h1>
    <div>
      <input type="text" name="nama" placeholder="Nama Lengkap" required
        class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
        value="<?= htmlspecialchars($s['nama']) ?>">
    </div>

    <div>
      <input type="text" name="kelas" placeholder="Kelas" required
        class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
        value="<?= htmlspecialchars($s['kelas']) ?>">
    </div>

    <div>
      <label class="block text-gray-700 mb-1">Jenis Kelamin</label>
      <select name="jk" required
        class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        <option value="">-- Pilih --</option>
        <option value="L" <?= $s['jk'] === 'L' ? 'selected' : '' ?>>Laki-laki</option>
        <option value="P" <?= $s['jk'] === 'P' ? 'selected' : '' ?>>Perempuan</option>
      </select>
    </div>

    <div>
      <label class="block text-gray-700 mb-1">Pinjam?</label>
      <select name="pinjam" id="pinjamSelect" required
        class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        <option value="false" <?= $s['pinjam'] == 'false' ? 'selected' : '' ?>>Tidak</option>
        <option value="true" <?= $s['pinjam'] == 'true' ? 'selected' : '' ?>>Ya</option>
      </select>
    </div>
    <div id="pinjamFields" class="space-y-3 hidden"></div>
    <div class="text-center">
      <button type="submit" name="update"
        class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition duration-200">Simpan</button>
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
  <audio id="notif-audio" src="../done.mp3" class="hidden" ></audio>
  <script>
    const pinjamSelect = document.getElementById("pinjamSelect");
    const pinjamFields = document.getElementById("pinjamFields");
    const spHasData = <?= json_encode(!empty($sp)) ?>;
    const spData = <?= json_encode($sp) ?>;
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
    function createInputField({ name, type = "text", placeholder = "", required = false, value = "" }) {
      const wrapper = document.createElement("div");
      const input = document.createElement("input");
      input.type = type;
      input.name = name;
      input.placeholder = placeholder;
      input.required = required;
      input.value = value;
      input.className = "w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500";
    
      if (name === "kode_buku") {
        input.id = "kode_buku_result";
        wrapper.appendChild(input);
    
        const scanBtn = document.createElement("button");
        scanBtn.type = "button";
        scanBtn.id = "scanBtn";
        scanBtn.textContent = "📷 Scan Barcode";
        scanBtn.className = "w-full mt-2 px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 font-medium";
    
        scanBtn.addEventListener("click", () => {
          const modal = document.getElementById("scanModal");
          const qrReader = document.getElementById("qr-reader");
          modal.classList.remove("hidden");
    
          html5QrcodeScanner = new Html5Qrcode("qr-reader");
          html5QrcodeScanner.start(
            { facingMode: "environment" },
            { fps: 10, qrbox: { width: 250, height: 150 } },
            (decodedText) => {
              playNotification();
              input.value = decodedText;
              html5QrcodeScanner.stop().then(() => {
                html5QrcodeScanner.clear();
                html5QrcodeScanner = null;
                modal.classList.add("hidden");
              });
            },
            (err) => console.warn("QR Error", err)
          );
        });
    
        wrapper.appendChild(scanBtn);
      } else {
        wrapper.appendChild(input);
      }
    
      return wrapper;
    }
    
    function createSelectField({ name, required = false, selectedValue = "" }) {
      const wrapper = document.createElement("div");
      const select = document.createElement("select");
      select.name = name;
      select.required = required;
      select.className = "w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500";
    
      const options = [
        { value: "", text: "-- Pilih Jenis Buku --", disabled: true },
        { value: "Mapel", text: "Mapel" },
        { value: "Non-Mapel", text: "Non-Mapel" }
      ];
    
      options.forEach(opt => {
        const option = document.createElement("option");
        option.value = opt.value;
        option.textContent = opt.text;
        if (opt.disabled) option.disabled = true;
        if (selectedValue === opt.value) option.selected = true;
        select.appendChild(option);
      });
    
      wrapper.appendChild(select);
      return wrapper;
    }
    
    function generatePinjamFields() {
      pinjamFields.innerHTML = "";
    
      const isPinjamTrue = pinjamSelect.value === "true";
      const kodeBukuValue = (spHasData && isPinjamTrue) ? spData.kode_buku : '';
      const jenisBukuValue = (spHasData && isPinjamTrue) ? spData.jenis_buku : '';
      const judulBukuValue = (spHasData && isPinjamTrue) ? spData.judul_buku : '';
      const qtyValue = (spHasData && isPinjamTrue) ? spData.qty : '';
    
      pinjamFields.appendChild(createInputField({
        name: "kode_buku", placeholder: "Kode Buku", value: kodeBukuValue, required: false
      }));
      pinjamFields.appendChild(createSelectField({
        name: "jenis_buku", required: true, selectedValue: jenisBukuValue
      }));
      pinjamFields.appendChild(createInputField({
        name: "judul_buku", placeholder: "Judul Buku", value: judulBukuValue, required: true
      }));
      pinjamFields.appendChild(createInputField({
        name: "qty", placeholder: "Jumlah", type: "number", value: qtyValue, required: true
      }));
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
      bindModalEvents();
    });
  </script>
</body>
</html>