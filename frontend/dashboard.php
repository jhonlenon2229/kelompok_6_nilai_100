<?php require '../config/database.php'; require_admin();
$st = [['relawan','Relawan','users'],['kegiatan','Kegiatan','calendar'],['pendaftaran','Pendaftaran','file'],['tugas','Tugas','check'],['kehadiran','Kehadiran','clock']];
$kg = $pdo->query('SELECT nama,lokasi,tanggal FROM kegiatan ORDER BY tanggal DESC LIMIT 5')->fetchAll();
$pd = $pdo->query('SELECT r.nama relawan, k.nama kegiatan, p.tanggal_daftar FROM pendaftaran p JOIN relawan r ON r.id=p.relawan_id JOIN kegiatan k ON k.id=p.kegiatan_id ORDER BY p.id DESC LIMIT 5')->fetchAll();
head('Dashboard'); ?>
<div class="cards">
<?php foreach ($st as $s): $n = $pdo->query("SELECT COUNT(*) FROM $s[0]")->fetchColumn(); ?>
  <div class="stat"><div><small><?= $s[1] ?></small><b><?= $n ?></b></div><span class="si"><?= ic($s[2], 20) ?></span></div>
<?php endforeach; ?>
</div>
<div class="two">
  <div class="panel"><h3>Kegiatan terbaru</h3>
    <?php foreach ($kg as $k): ?><div class="row"><div><?= e($k['nama']) ?><small><?= e($k['lokasi']) ?></small></div><span class="mut"><?= e($k['tanggal']) ?></span></div><?php endforeach; ?>
    <?php if (!$kg) echo '<p class="mut">Belum ada data.</p>'; ?></div>
  <div class="panel"><h3>Pendaftaran terbaru</h3>
    <?php foreach ($pd as $p): ?><div class="row"><div><?= e($p['relawan']) ?><small><?= e($p['kegiatan']) ?></small></div><span class="mut"><?= e($p['tanggal_daftar']) ?></span></div><?php endforeach; ?>
    <?php if (!$pd) echo '<p class="mut">Belum ada data.</p>'; ?></div>
</div>
<?php foot();
