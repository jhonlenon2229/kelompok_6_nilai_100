<?php require '../config/database.php'; require_admin();
$rows = $pdo->query('SELECT k.nama, k.tanggal,
  (SELECT COUNT(*) FROM pendaftaran WHERE kegiatan_id=k.id) pendaftar,
  (SELECT COUNT(*) FROM kehadiran WHERE kegiatan_id=k.id) hadir,
  (SELECT COUNT(*) FROM tugas WHERE kegiatan_id=k.id) tugas
  FROM kegiatan k ORDER BY k.tanggal DESC')->fetchAll();
head('Laporan'); ?>
<div class="wrap"><table><thead><tr><th>Kegiatan</th><th>Tanggal</th><th>Pendaftar</th><th>Hadir</th><th>Tugas</th></tr></thead><tbody>
<?php foreach ($rows as $r): ?>
  <tr><td><?= e($r['nama']) ?></td><td><?= e($r['tanggal']) ?></td><td><?= $r['pendaftar'] ?></td><td><?= $r['hadir'] ?></td><td><?= $r['tugas'] ?></td></tr>
<?php endforeach; ?>
<?php if (!$rows): ?><tr><td colspan="5" class="mut">Belum ada data.</td></tr><?php endif; ?>
</tbody></table></div>
<?php foot();
