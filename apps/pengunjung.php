<?php
session_start();
require_once '../config.php';
require_once '../utils/utils.php';

$tgl_filter = $_GET['tanggal'] ?? '';
$bulan_filter = $_GET['bulan'] ?? '';
$range_awal = $_GET['mulai'] ?? '';
$range_akhir = $_GET['sampai'] ?? '';
$semua = isset($_GET['semua']);

$pengunjung = [];
if ($range_awal && $range_akhir) {
    $pengunjung = select("pengunjung", [], true, "updated DESC", null, [
        "DATE(updated) BETWEEN" => [$range_awal, $range_akhir]
    ]);
} elseif ($tgl_filter) {
    $pengunjung = select("pengunjung", [], true, "updated DESC", null, [
        "DATE(updated)" => $tgl_filter
    ]);
} elseif ($bulan_filter) {
    $pengunjung = select("pengunjung", [], true, "updated DESC", null, [
        "MONTH(updated)" => date('m', strtotime($bulan_filter)),
        "YEAR(updated)" => date('Y', strtotime($bulan_filter)),
    ]);
} elseif($semua){
    $pengunjung = select("pengunjung", [], true, "updated DESC");
}else{
  $pengunjung = select("pengunjung", [], true, "updated DESC", null, [
        "DATE(updated)" => date("Y-m-d", strtotime($waktu_sekarang))
    ]);
}

function idTime($datetime) {
    $timestamp = strtotime($datetime);
    if (!$timestamp) return '-';
    return date('d/m/Y H:i', $timestamp);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Data Pengunjung <?= $today ?></title>
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- DataTables & Buttons CDN -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css" />
</head>
<body class="bg-gray-100 px-4 pb-32">
  <div class="max-w-6xl mx-auto">
    <?php require_once("../navigasi.php") ?>
    <h1 class="text-3xl font-bold text-center my-6">Daftar Pengunjung <span class="text-blue-500" id="today"></span></h1>
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
    <div class="bg-white p-2 rounded overflow-x-auto">
      <table id="pengunjungTable" class="min-w-full text-sm text-left text-gray-700">
        <thead class="bg-gray-200 text-gray-800 uppercase text-xs">
          <tr>
            <th>No</th>
            <th>Nama</th>
            <th>Kelas</th>
            <th>Jenis Kelamin</th>
            <th>Pinjam</th>
            <th>Waktu Kunjung</th>
            <th>
              <?php if(isset($_SESSION["role"]) && $_SESSION["role"]==="admin"){
                echo("Aksi");
              }else{
                echo("Status");
              }
              ?>
            </th>
          </tr>
        </thead>
        <tbody>
          <?php if (count($pengunjung) > 0): ?>
            <?php
            foreach ($pengunjung as $i => $p):
            $pnjmn = select("pinjaman", ["id_pengunjung"=>$p["id"]], false);
            ?>
              <tr>
                <td><?= $i + 1 ?></td>
                <td><?= htmlspecialchars($p['nama']) ?></td>
                <td><?= htmlspecialchars($p['kelas']) ?></td>
                <td><?= $p['jk'] === 'L' ? 'Laki-laki' : 'Perempuan' ?></td>
                <td>
                  <?php
                  if($p["pinjam"]==="true"){
                    echo "Ya";
                  }elseif($p["pinjam"]==="false" && !empty($pnjmn["tgl_kembali"])){
                    echo "Ya";
                  }else{
                    echo "Tidak";
                  }
                  ?>
                
                </td>
                <td><?= idTime($p['updated']) ?></td>
                <td class="text-center">
                  <?php
                  if($_SESSION["role"]==="admin" && empty($pnjmn["tgl_kembali"])){
                    echo("<a href='edit-pengunjung.php?id=".$p['id']."' class='underline text-white bg-gray-600 p-1'>Edit</a>");
                  }elseif($p["pinjam"]==="true" && !empty($pnjmn["tgl_kembali"])){
                  echo("<span class='p-1 bg-green-500 text-white'>dikembalikan</span>");
                  }elseif($p["pinjam"]==="true" && empty($pnjmn["tgl_kembali"])){
                    echo("<span class='p-1 bg-yellow-500 text-white'>dipinjam</span>");
                  }elseif($p["pinjam"]==="false" && !empty($pnjmn["tgl_kembali"])){
                    echo("<span class='p-1 bg-violet-500 text-white'>batal pinjam/dikembalikan</span>");
                  }else{
                    echo("<span class='p-1 bg-blue-500 text-white'>berkunjung</span>");
                  }
                  ?>
                </td>
              </tr>
            <?php endforeach ?>
          <?php endif ?>
        </tbody>
      </table>
    </div>
  </form>

  <!-- jQuery & Data Tables -->
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

  <!-- Init Data Tables-->
  <script>
    $(document).ready(function () {
      $('#pengunjungTable').DataTable({
        dom: 'Blfrtip',
        buttons: ['excelHtml5', 'pdfHtml5', 'print'],
        responsive: true,
        pageLength: 10,
        lengthMenu: [ [10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"] ]
      });
    });
  function formatTanggalIndonesia(date) {
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
    document.getElementById("today").textContent = formatTanggalIndonesia(new Date());
  }, 1000);
  </script>
</body>
</html>