<?php require '../config/database.php'; require_admin();
$rows = $pdo->query('SELECT * FROM relawan ORDER BY id DESC')->fetchAll();
head('Data Relawan');
echo '<p class="mut">Relawan baru otomatis mendapat akun login: username <b>relawan</b>+ID, password <b>password</b>.</p>';
crud_view(['file'=>'relawan.php','title'=>'Relawan',
 'cols'=>[['nama','Nama'],['email','Email'],['telepon','Telepon'],['alamat','Alamat']],
 'fields'=>[['nama','Nama','text'],['email','Email','email'],['telepon','Telepon','text'],['alamat','Alamat','textarea']]], $rows);
foot();
