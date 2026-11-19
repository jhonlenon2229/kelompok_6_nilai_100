
<?php
require 'config/database.php';

// Akun Admin
$ADMIN_USER = 'putra';
$ADMIN_PASS = 'jhon';

// Menentukan halaman tujuan berdasarkan role
function tujuan($role) {
    return $role === 'admin' ? 'frontend/dashboard.php' : 'frontend/saya.php';
}

// Jika sudah login, langsung arahkan ke halaman tujuan
if (!empty($_SESSION['user'])) {
    header('Location: ' . tujuan($_SESSION['user']['role']));
    exit;
}

$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!csrf_ok()) {
        $err = 'Sesi kedaluwarsa, muat ulang halaman.';
    } else {

        $user = trim($_POST['username'] ?? '');
        $pass = $_POST['password'] ?? '';

        // Login Admin
        if (hash_equals($ADMIN_USER, $user) && hash_equals($ADMIN_PASS, $pass)) {
            session_regenerate_id(true);

            $_SESSION['user'] = [
                'id' => 0,
                'nama' => 'Administrator',
                'role' => 'admin',
                'relawan_id' => null
            ];

            header('Location: frontend/dashboard.php');
            exit;
        }

        // Login Relawan
        $s = $pdo->prepare("SELECT * FROM users WHERE username=? AND role='relawan'");
        $s->execute([$user]);
        $u = $s->fetch();

        if ($u && password_verify($pass, $u['password'])) {
            session_regenerate_id(true);

            $_SESSION['user'] = [
                'id' => $u['id'],
                'nama' => $u['nama'],
                'role' => $u['role'],
                'relawan_id' => $u['relawan_id']
            ];

            header('Location: frontend/saya.php');
            exit;
        }

        $err = 'Username atau password salah.';
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Sistem Relawan</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="center">

    <form class="panel login" method="post">

        <h2>Login</h2>

        <?= csrf_field() ?>

        <label>Username</label>
        <input name="username" required autofocus>

        <label>Password</label>
        <input name="password" type="password" required>

        <p class="err"><?= e($err) ?></p>

        <button class="btn" style="width:100%">Masuk</button>

        <p style="text-align:center;margin:16px 0 0">
            <a href="index.php">Beranda</a>
        </p>

    </form>

</body>
</html>
