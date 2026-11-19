<?php require '../config/database.php'; require_admin();
$rows = $pdo->query('SELECT p.*, r.nama relawan, k.nama kegiatan FROM pendaftaran p JOIN relawan r ON r.id=p.relawan_id JOIN kegiatan k ON k.id=p.kegiatan_id ORDER BY p.id DESC')->fetchAll();
head('Pendaftaran Relawan');
crud_view(['file'=>'pendaftaran.php','title'=>'Pendaftaran',
 'cols'=>[['relawan','Relawan'],['kegiatan','Kegiatan'],['tanggal_daftar','Tanggal Daftar']],
 'fields'=>[['relawan_id','Relawan','select',opts('SELECT id,nama FROM relawan ORDER BY nama')],
  ['kegiatan_id','Kegiatan','select',opts('SELECT id,nama FROM kegiatan ORDER BY nama')],['tanggal_daftar','Tanggal Daftar','date']]], $rows);
foot();
