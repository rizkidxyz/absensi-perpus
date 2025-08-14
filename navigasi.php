<head>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
</head>
<?php
$routes = [
  [
    "label" => "Home",
    "path" => "/",
    "icon" => "fa-solid fa-house"
  ],
  [
    "label" => "Pengunjung",
    "path" => "/apps",
    "icon" => "fa-solid fa-users"
  ],
  [
    "label" => "Pinjaman",
    "path" => "/apps/pinjaman.php",
    "icon" => "fa-solid fa-file-invoice"
  ]
];
?>
<div class="flex justify-center z-50 w-full fixed bottom-0 left-0 font-medium p-2 shadow-lg gap-1">
  <?php foreach ($routes as $route): ?>
    <a href="<?= $route["path"] ?>" 
       class="flex items-center gap-2 bg-blue-700 hover:bg-blue-800 transition shadow-md px-4 py-2 text-white">
      <i class="<?= $route["icon"] ?> text-lg"></i>
      <span><?= $route["label"] ?></span>
    </a>
  <?php endforeach; ?>
</div>