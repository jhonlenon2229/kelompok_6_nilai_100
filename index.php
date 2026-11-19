
<?php
require 'config/database.php';

$in = !empty($_SESSION['user']);

$home = $in
    ? ($_SESSION['user']['role'] === 'admin' ? 'frontend/dashboard.php' : 'frontend/saya.php')
    : 'login.php';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Sistem Informasi Manajemen Relawan Sosial</title>

    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

    <header class="top">

        <a class="brand" href="index.php">
            <span class="logo"><?= ic('heart', 19) ?></span>
            <b>SIRELA</b>
        </a>

        <nav></nav>

        <div class="who">
            <a class="btn y s" href="<?= $home ?>">
                <?= $in ? 'Buka aplikasi' : 'Login' ?>
            </a>
        </div>

    </header>

    <section class="hero">

        <h1>Sistem informasi manajemen relawan sosial</h1>

        <p>
            Kelola data relawan, kegiatan, pendaftaran, pembagian tugas,
            dan kehadiran dalam satu tempat.
        </p>

        <a class="btn" href="<?= $home ?>">
            <?= $in ? 'Buka aplikasi' : 'Masuk ke sistem' ?>
        </a>

    </section>

    <section class="feat">

        <div class="panel">
            <span class="si"><?= ic('users', 20) ?></span>
            <h3>Data terpusat</h3>
            <p>Profil relawan dan kegiatan tersimpan rapi dan mudah dicari.</p>
        </div>

        <a class="panel" href="frontend/saya.php">
            <span class="si"><?= ic('check', 20) ?></span>
            <h3>Tugas dan kehadiran</h3>
            <p>Klik untuk melihat tugas dan kehadiran Anda. Perlu login terlebih dahulu.</p>
        </a>

        <div class="panel">
            <span class="si"><?= ic('chart', 20) ?></span>
            <h3>Laporan cepat</h3>
            <p>Rekap kegiatan untuk evaluasi dan pelaporan.</p>
        </div>

    </section>

</body>
</html>
