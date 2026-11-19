<?php require '../config/database.php'; require_admin();
$rows = $pdo->query('SELECT * FROM kegiatan ORDER BY tanggal DESC')->fetchAll();
head('Data Kegiatan');
crud_view(['file'=>'kegiatan.php','title'=>'Kegiatan',
 'cols'=>[['nama','Kegiatan'],['lokasi','Lokasi'],['tanggal','Tanggal'],['kuota','Kuota']],
 'fields'=>[['nama','Nama Kegiatan','text'],['lokasi','Lokasi','text'],['tanggal','Tanggal','date'],['kuota','Kuota','number'],['deskripsi','Deskripsi','textarea']]], $rows);
foot();
