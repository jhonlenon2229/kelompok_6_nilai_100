<?php require '../config/database.php'; require_admin();
$rows = $pdo->query('SELECT h.*, r.nama relawan, k.nama kegiatan FROM kehadiran h JOIN relawan r ON r.id=h.relawan_id JOIN kegiatan k ON k.id=h.kegiatan_id ORDER BY h.tanggal DESC, h.id DESC')->fetchAll();
head('Kehadiran Relawan');
crud_view(['file'=>'kehadiran.php','title'=>'Kehadiran',
 'cols'=>[['relawan','Relawan'],['kegiatan','Kegiatan'],['tanggal','Tanggal']],
 'fields'=>[['relawan_id','Relawan','select',opts('SELECT id,nama FROM relawan ORDER BY nama')],
  ['kegiatan_id','Kegiatan','select',opts('SELECT id,nama FROM kegiatan ORDER BY nama')],['tanggal','Tanggal Hadir','date']]], $rows);
foot();
