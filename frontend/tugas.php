<?php require '../config/database.php'; require_admin();
$rows = $pdo->query('SELECT t.*, r.nama relawan, k.nama kegiatan FROM tugas t JOIN relawan r ON r.id=t.relawan_id JOIN kegiatan k ON k.id=t.kegiatan_id ORDER BY t.id DESC')->fetchAll();
head('Tugas Relawan');
crud_view(['file'=>'tugas.php','title'=>'Tugas',
 'cols'=>[['judul','Tugas'],['kegiatan','Kegiatan'],['relawan','Ditugaskan ke'],['tenggat','Tenggat']],
 'fields'=>[['judul','Judul Tugas','text'],['kegiatan_id','Kegiatan','select',opts('SELECT id,nama FROM kegiatan ORDER BY nama')],
  ['relawan_id','Relawan','select',opts('SELECT id,nama FROM relawan ORDER BY nama')],['tenggat','Tenggat','date','opt']]], $rows);
foot();
