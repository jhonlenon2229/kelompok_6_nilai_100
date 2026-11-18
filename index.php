<?php
session_start();
if (isset($_SESSION['user'])) { header('Location: dashboard.php'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $users = [
        'admin' => ['password' => 'admin123', 'name' => 'Admin Desa', 'role' => 'Administrator'],
        'koordinator' => ['password' => 'relawan123', 'name' => 'Koordinator Lapangan', 'role' => 'Koordinator']
    ];
    if (isset($users[$username]) && hash_equals($users[$username]['password'], $password)) {
        session_regenerate_id(true);
        $_SESSION['user'] = ['username'=>$username, 'name'=>$users[$username]['name'], 'role'=>$users[$username]['role']];
        header('Location: dashboard.php'); exit;
    }
    $error = 'Nama pengguna atau kata sandi salah. Coba lagi.';
}
?>
<!DOCTYPE html>
<html lang="id"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css"><title>Masuk - SIM Relawan Desa</title></head>
<body><div class="login">
<section class="hero"><i class="ring"></i><i class="ring b"></i><h1>Gotong royong, tercatat rapi.</h1>
<p>Sistem informasi untuk mengelola relawan, kegiatan, tugas, dan laporan masyarakat desa.</p></section>
<section class="lbox"><form method="post" action="">
<h2>Login Admin</h2>
<div><label for="username">Nama pengguna</label><input id="username" name="username" autocomplete="username" required></div>
<div><label for="password">Kata sandi</label><input id="password" name="password" type="password" autocomplete="current-password" required></div>
<p class="err"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
<button class="btn" type="submit">Masuk</button>
<div class="note">Akun demo: <b>admin</b> / <b>admin123</b><br>Koordinator: <b>koordinator</b> / <b>relawan123</b></div>
</form></section></div></body></html>
