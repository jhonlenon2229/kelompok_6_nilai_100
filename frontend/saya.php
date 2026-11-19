<?php require '../config/database.php';
if (empty($_SESSION['user'])) { header('Location: ../login.php'); exit; }
if ($_SESSION['user']['role'] !== 'relawan') { header('Location: dashboard.php'); exit; }
$id = (int)$_SESSION['user']['relawan_id'];
$q = fn($sql) => (function($sql, $id){ global $pdo; $s = $pdo->prepare($sql); $s->execute([$id]); return $s; })($sql, $id);
$r  = $q('SELECT * FROM relawan WHERE id=?')->fetch();
$tg = $q('SELECT t.judul, t.tenggat, k.nama kegiatan FROM tugas t JOIN kegiatan k ON k.id=t.kegiatan_id WHERE t.relawan_id=? ORDER BY t.id DESC')->fetchAll();
$kh = $q('SELECT h.tanggal, k.nama kegiatan FROM kehadiran h JOIN kegiatan k ON k.id=h.kegiatan_id WHERE h.relawan_id=? ORDER BY h.tanggal DESC')->fetchAll();
$jp = $q('SELECT COUNT(*) FROM pendaftaran WHERE relawan_id=?')->fetchColumn();
head('Halo, ' . ($r['nama'] ?? $_SESSION['user']['nama'])); ?>
<div class="panel" style="margin-bottom:16px"><h3>Profil</h3>
  <div class="row"><span class="mut">Nama</span><span><?= e($r['nama']) ?></span></div>
  <div class="row"><span class="mut">Email</span><span><?= e($r['email']) ?></span></div>
  <div class="row"><span class="mut">Telepon</span><span><?= e($r['telepon']) ?></span></div></div>
<div class="cards">
  <div class="stat"><div><small>Kegiatan diikuti</small><b><?= $jp ?></b></div><span class="si"><?= ic('file', 20) ?></span></div>
  <div class="stat"><div><small>Total tugas</small><b><?= count($tg) ?></b></div><span class="si"><?= ic('check', 20) ?></span></div>
  <div class="stat"><div><small>Total kehadiran</small><b><?= count($kh) ?></b></div><span class="si"><?= ic('clock', 20) ?></span></div></div>
<div class="two">
  <div class="panel"><h3>Tugas saya</h3>
    <?php foreach ($tg as $t): ?><div class="row"><div><?= e($t['judul']) ?><small><?= e($t['kegiatan']) ?></small></div><span class="mut"><?= e($t['tenggat']) ?></span></div><?php endforeach; ?>
    <?php if (!$tg) echo '<p class="mut">Belum ada tugas.</p>'; ?></div>
  <div class="panel"><h3>Kehadiran saya</h3>
    <?php foreach ($kh as $h): ?><div class="row"><div><?= e($h['kegiatan']) ?></div><span class="mut"><?= e($h['tanggal']) ?></span></div><?php endforeach; ?>
    <?php if (!$kh) echo '<p class="mut">Belum ada data kehadiran.</p>'; ?></div>
</div>
<?php foot();
