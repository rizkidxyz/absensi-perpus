<?php
session_start();
require_once '../config.php';
require_once '../utils/utils.php';
date_default_timezone_set('Asia/Jakarta');
$today = date("Y-m-d");
$tgl_filter = $_GET['tanggal'] ?? '';
$bulan_filter = $_GET['bulan'] ?? '';
$range_awal = $_GET['mulai'] ?? '';
$range_akhir = $_GET['sampai'] ?? '';
$semua = isset($_GET['semua']);

$pinjaman = [];

if ($range_awal && $range_akhir) {
    $pinjaman = select("pinjaman", [], true, "updated DESC", null, [
        "DATE(updated) BETWEEN" => [$range_awal, $range_akhir]
    ]);
} elseif ($tgl_filter) {
    $pinjaman = select("pinjaman", [], true, "updated DESC", null, [
        "DATE(updated)" => $tgl_filter
    ]);
} elseif ($bulan_filter) {
    $pinjaman = select("pinjaman", [], true, "updated DESC", null, [
        "MONTH(updated)" => date('m', strtotime($bulan_filter)),
        "YEAR(updated)" => date('Y', strtotime($bulan_filter)),
    ]);
} elseif ($semua) {
    $pinjaman = select("pinjaman", [], true, "updated DESC");
} else {
    $pinjaman = select("pinjaman", [], true, "updated DESC", null, [
        "DATE(updated)" => $today
    ]);
}

function idTime($datetime) {
    $timestamp = strtotime($datetime);
    if (!$timestamp) return '-';
    return date('d/m/Y H:i', $timestamp);
}
if (isset($_POST["update"])) {
  $id_update = $_POST["id-update"];
  $msg = update("pinjaman", ["tgl_kembali"=>date("Y-m-d H:i:s"), "updated"=>date("Y-m-d H:i:s")], ["id" => $id_update]) ? "Berhasil update" : "Gagal update";
  echo "<script>alert('$msg');location.href='/apps/pinjaman.php';</script>";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Data Pinjaman Buku <?= $today ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css" />
</head>
<body class="bg-gray-100 px-4 pb-32">
  <div class="max-w-7xl mx-auto">
    <?php require_once("../navigasi.php") ?>
    <h1 class="text-3xl font-bold text-center my-6">Daftar Pinjaman Buku <span id="today" class="text-blue-500"></span></h1>

    <!-- Filter Form -->
    <form method="GET" class="mb-4 bg-white p-4 rounded shadow text-sm">
      <p class="text-gray-700 mb-3 font-semibold">Filter data ditampilkan berdasarkan:</p>
      
      <div class="flex flex-wrap gap-4 items-end">
        <div class="flex flex-col">
          <label class="mb-1">Tanggal:</label>
          <input type="date" name="tanggal" value="<?= htmlspecialchars($tgl_filter) ?>" class="border p-2 rounded" />
        </div>
        <div class="flex flex-col">
          <label class="mb-1">Bulan:</label>
          <input type="month" name="bulan" value="<?= htmlspecialchars($bulan_filter) ?>" class="border p-2 rounded" />
        </div>
        <div class="flex flex-col">
          <label class="mb-1">Mulai:</label>
          <input type="date" name="mulai" value="<?= htmlspecialchars($range_awal) ?>" class="border p-2 rounded" />
        </div>
        <div class="flex flex-col">
          <label class="mb-1">Sampai:</label>
          <input type="date" name="sampai" value="<?= htmlspecialchars($range_akhir) ?>" class="border p-2 rounded" />
        </div>
      </div>
    
      <div class="flex flex-wrap items-center justify-between mt-4 gap-4">
        <label class="inline-flex items-center gap-2 text-gray-700">
          <input type="checkbox" name="semua" value="1" <?= $semua ? 'checked' : '' ?> class="w-4 h-4" />
          <span>Tampilkan semua data</span>
        </label>
    
        <div class="flex gap-2">
          <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Terapkan</button>
          <a href="<?= $_SERVER['PHP_SELF'] ?>" class="bg-red-100 text-red-600 px-4 py-2 rounded hover:bg-red-200">Reset</a>
        </div>
      </div>
    </form>
    <!-- Table -->
    <div class="bg-white p-4 rounded overflow-x-auto">
      <table id="pinjamanTable" class="min-w-full border text-sm text-left text-gray-700">
        <thead class="bg-gray-200 text-gray-800 uppercase text-xs">
          <tr>
            <th>No</th>
            <th>Nama</th>
            <th>Kelas</th>
            <th>Jenis Kelamin</th>
            <th>Kode Buku</th>
            <th>Jenis Buku</th>
            <th>Judul Buku</th>
            <th>Qty</th>
            <th>Tgl Pinjam</th>
            <th>Tgl Kembali</th>
            <?php if(isset($_SESSION["role"]) && $_SESSION["role"]==="admin"){
              echo("<th>Aksi</th>");
            }else{
              echo("<th>Status</th>");
            }
            ?>
          </tr>
        </thead>
        <tbody>
          <?php if (count($pinjaman) > 0): ?>
            <?php foreach ($pinjaman as $i => $p): ?>
              <?php
                $dt_pinjaman = select("pengunjung", ["id" => $p["id_pengunjung"]], false);
                if (!$dt_pinjaman) continue;
              ?>
              <tr>
                <td><?= $i + 1 ?></td>
                <td><?= htmlspecialchars($dt_pinjaman['nama']) ?></td>
                <td><?= htmlspecialchars($dt_pinjaman['kelas']) ?></td>
                <td><?= $dt_pinjaman['jk'] === 'L' ? 'Laki-laki' : 'Perempuan' ?></td>
                <td><?= $p["kode_buku"] ?></td>
                <td><?= $p["jenis_buku"] ?></td>
                <td><?= $p["judul_buku"] ?></td>
                <td><?= $p["qty"] ?></td>
                <td><?= idTime($p["tgl_pinjam"]) ?></td>
                <td><?= empty($p["tgl_kembali"]) ? "Belum dikembalikan" : idTime($p["tgl_kembali"]) ?></td>
                <td class="text-center">
                  <?php
                  if(!empty($p["tgl_kembali"])){
                  ?>
                  <span class="bg-green-500 text-white rounded p-0.5">Sudah di kembalikan</span>
                  <?php }elseif(empty($p["tgl_kembali"]) && $_SESSION["role"]==="admin"){ ?>
                  <form action="" method="post" class="w-fit">
                    <input type="hidden" name="id-update" value="<?= $p['id'] ?>">
                    <button onclick="return confirm('Yakin Buku & data buku dikembalikan Benar?')" class="bg-yellow-500 p-0.5 rounded text-white" name="update">
                      selesaikan
                    </button>
                  </form>
                  <?php }else{ ?>
                  <span class="bg-orange-500 text-white p-1 rounded"> Masih di pinjam</span>
                  <?php } ?>
                </td>
              </tr>
            <?php endforeach ?>
          <?php endif ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- JS & DataTables -->
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script>
    $(document).ready(function () {
      $('#pinjamanTable').DataTable({
        dom: 'Blfrtip', // 'l' untuk length changing (entries per page)
        buttons: ['excelHtml5', 'pdfHtml5', 'print'],
        responsive: true,
        pageLength: 10,
        lengthMenu: [ [10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"] ]
      });
    });
  function idDate(date) {
    const hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    const bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
                   'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    const namaHari = hari[date.getDay()];
    const tanggal = String(date.getDate()).padStart(2, '0');
    const namaBulan = bulan[date.getMonth()];
    const tahun = date.getFullYear();
    const jam = String(date.getHours()).padStart(2, '0');
    const menit = String(date.getMinutes()).padStart(2, '0');
    const detik = String(date.getSeconds()).padStart(2, '0');

    return `${namaHari}, ${tanggal} ${namaBulan} ${tahun} ${jam}:${menit}:${detik}`;
  }
  setInterval(() => {
    document.getElementById("today").textContent = idDate(new Date());
  }, 1000);
  </script>
</body>
</html>