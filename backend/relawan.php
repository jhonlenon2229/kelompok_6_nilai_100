<?php require '../config/database.php';
// Relawan baru otomatis dibuatkan akun: relawan{ID} / password
crud_handle('relawan', ['nama','email','telepon','alamat'], 'relawan.php', function($new, $mode) use ($pdo) {
  if ($mode == 'add') {
    $pdo->prepare("INSERT INTO users(username,password,nama,role,relawan_id) VALUES(?,?,?,'relawan',?)")
        ->execute(['relawan'.$new, password_hash('password', PASSWORD_DEFAULT), trim($_POST['nama']), $new]);
  } elseif ($mode == 'edit') {
    $pdo->prepare('UPDATE users SET nama=? WHERE relawan_id=?')->execute([trim($_POST['nama']), (int)$_POST['id']]);
  }
});
