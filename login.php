<?php
session_start();
require_once("config.php");
require_once("utils/utils.php");
if(isset($_POST["submit"])){
  $nama = htmlspecialchars($_POST["name"]);
  $pass = htmlspecialchars($_POST["password"]);
  if($cek = select("users", ["name"=>$nama], false)){
    if($pass === $cek["pass"]){
      $_SESSION["role"]=$cek["role"];
      header("Location: /");
      exit;
    }else {
      echo("<script>alert('Login Gagal!')</script>");
    }
  }else{
    echo("<script>alert('User tidak di temukan!')</script>");
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Login</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
  <form action="" method="post" accept-charset="utf-8" class="flex flex-col w-full md:max-w-lg p-3 gap-3">
    <h1 class="text-2xl font-semibold">Login</h1>
    <input type="text" name="name" placeholder="Nama" class="w-full p-2 border-2 border-blue-600"/>
    <input type="password" name="password" placeholder="Password" class="border-2 border-blue-600 w-full p-2">
    <button type="submit" name="submit" class="bg-blue-600 text-white p-2 w-full">Masuk</button>
  </form>
</body>
</html>