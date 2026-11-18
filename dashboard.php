<?php
session_start();
if (!isset($_SESSION['user'])) { header('Location: index.php'); exit; }
$user = $_SESSION['user'];
if (isset($_GET['logout'])) { $_SESSION = []; session_destroy(); header('Location: index.php'); exit; }
$file = __DIR__ . '/data.json';
$seed = json_decode(file_get_contents($file), true);
function loadData($file, $seed) {
    $raw = @file_get_contents($file); $data = json_decode($raw, true);
    return is_array($data) ? $data : $seed;
}
function saveData($file, $data) {
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE), LOCK_EX);
}
function e($v) { return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
$db = loadData($file, $seed);
$entities = [
 'relawan'=>['label'=>'Relawan','add'=>'Tambah relawan','fields'=>[
  'nama'=>['Nama relawan','text',true], 'telepon'=>['Telepon','text',false], 'alamat'=>['Alamat','text',false], 'status'=>['Status','select',['Aktif','Nonaktif']]]],
 'kegiatan'=>['label'=>'Kegiatan','add'=>'Tambah kegiatan','fields'=>[
  'nama'=>['Nama kegiatan','text',true], 'tanggal'=>['Tanggal','date',true], 'lokasi'=>['Lokasi','text',false], 'status'=>['Status','select',['Rencana','Berjalan','Selesai']]]],
 'tugas'=>['label'=>'Tugas','add'=>'Tambah tugas','fields'=>[
  'judul'=>['Judul tugas','text',true], 'relawanId'=>['Relawan','relawan',true], 'kegiatanId'=>['Kegiatan','kegiatan',true], 'tenggat'=>['Tenggat','date',false], 'status'=>['Status','select',['Belum','Proses','Selesai']]]],
 'laporan'=>['label'=>'Laporan','add'=>'Tambah laporan','fields'=>[
  'judul'=>['Judul laporan','text',true], 'kegiatanId'=>['Kegiatan','kegiatan',true], 'relawanId'=>['Pelapor','relawan',true], 'tanggal'=>['Tanggal','date',false], 'isi'=>['Isi laporan','textarea',false]]]
];
$view = $_GET['view'] ?? 'dashboard';
if (!isset($entities[$view]) && $view !== 'dashboard') $view='dashboard';
function relatedName($db,$type,$id) {
    foreach (($db[$type] ?? []) as $row) if ((string)$row['id']===(string)$id) return $row['nama'] ?? $row['judul'] ?? '(dihapus)';
    return '(dihapus)';
}
function displayValue($db,$view,$key,$value) {
    if ($key==='relawanId') return relatedName($db,'relawan',$value);
    if ($key==='kegiatanId') return relatedName($db,'kegiatan',$value);
    if (in_array($key,['tanggal','tenggat']) && $value) return date('d M Y',strtotime($value));
    return $value;
}
function statusBadge($v) {
    $map=['Aktif'=>'ok','Selesai'=>'ok','Proses'=>'wr','Berjalan'=>'wr','Belum'=>'in','Rencana'=>'in','Nonaktif'=>'er'];
    return '<span class="badge b-'.($map[$v]??'in').'">'.e($v).'</span>';
}
$message='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $action=$_POST['action']??'';
    $view=$_POST['view']??'dashboard';
    if (!isset($entities[$view])) $view='dashboard';
    if ($action==='save' && isset($entities[$view])) {
        $id=(int)($_POST['id']??0); $record=[];
        foreach($entities[$view]['fields'] as $key=>$field) {
            $value=trim($_POST[$key]??'');
            $record[$key]=in_array($key,['relawanId','kegiatanId'])?(int)$value:$value;
        }
        if ($id) {
            foreach($db[$view] as &$row) if ((int)$row['id']===$id) { $row=array_merge($row,$record); break; }
            unset($row); $message='Perubahan disimpan.';
        } else {
            $record['id']=empty($db[$view])?1:max(array_column($db[$view],'id'))+1;
            $db[$view][]=$record; $message='Data berhasil ditambahkan.';
        }
        saveData($file,$db);
    } elseif ($action==='delete' && isset($entities[$view])) {
        $id=(int)($_POST['id']??0);
        $db[$view]=array_values(array_filter($db[$view],fn($r)=>(int)$r['id']!==$id));
        saveData($file,$db); $message='Data berhasil dihapus.';
    }
    header('Location: dashboard.php?view='.urlencode($view).'&msg='.urlencode($message)); exit;
}
if(isset($_GET['msg'])) $message=$_GET['msg'];
$q=trim($_GET['q']??'');
?>
<!DOCTYPE html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css"><title>Dashboard - SIM Relawan Desa</title></head><body>
<div class="app"><aside class="side"><div class="brand"><i class="dot"></i>SIM Relawan</div>
<button class="nav <?= $view==='dashboard'?'on':'' ?>" onclick="location.href='dashboard.php'">Ringkasan</button>
<?php foreach($entities as $key=>$entity): ?><button class="nav <?= $view===$key?'on':'' ?>" onclick="location.href='dashboard.php?view=<?= e($key) ?>'"><?= e($entity['label']) ?></button><?php endforeach; ?>
<div class="who"><div><b><?= e($user['name']) ?></b><?= e($user['role']) ?></div><a class="btn ghost sm" style="display:inline-block;margin-top:10px;color:#fff;border-color:rgba(255,255,255,.3);text-decoration:none" href="?logout=1">Keluar</a></div>
</aside><main>
<?php if($message): ?><p class="note"><?= e($message) ?></p><?php endif; ?>
<?php if($view==='dashboard'): ?>
<div class="top"><h2>Selamat datang, <?= e($user['name']) ?></h2></div>
<div class="stats"><?php foreach(['relawan'=>'Relawan terdaftar','kegiatan'=>'Kegiatan','tugas'=>'Tugas','laporan'=>'Laporan masuk'] as $key=>$label): ?><div class="card stat"><b><?= count($db[$key]) ?></b><span><?= e($label) ?></span></div><?php endforeach; ?></div>
<div class="card sec"><h3>Tugas yang perlu diselesaikan</h3><div class="tw"><table style="min-width:520px"><thead><tr><th>Tugas</th><th>Relawan</th><th>Tenggat</th><th>Status</th></tr></thead><tbody>
<?php $pending=array_values(array_filter($db['tugas'],fn($t)=>$t['status']!=='Selesai')); usort($pending,fn($a,$b)=>strcmp($a['tenggat']??'',$b['tenggat']??'')); foreach(array_slice($pending,0,5) as $t): ?>
<tr><td><?= e($t['judul']) ?></td><td><?= e(relatedName($db,'relawan',$t['relawanId'])) ?></td><td><?= e(displayValue($db,'tugas','tenggat',$t['tenggat']??'')) ?></td><td><?= statusBadge($t['status']) ?></td></tr>
<?php endforeach; if(!$pending): ?><tr><td colspan="4" class="empty">Semua tugas sudah selesai.</td></tr><?php endif; ?></tbody></table></div></div>
<?php else: $entity=$entities[$view]; $rows=$db[$view]; if($q!=='') $rows=array_values(array_filter($rows,function($r)use($q,$db,$view,$entity){foreach($entity['fields'] as $key=>$f){$v=displayValue($db,$view,$key,$r[$key]??'');if(stripos((string)$v,$q)!==false)return true;}return false;})); ?>
<div class="top"><h2><?= e($entity['label']) ?></h2><div class="tools"><form method="get" style="display:flex;gap:10px;flex-wrap:wrap"><input type="hidden" name="view" value="<?= e($view) ?>"><input name="q" type="search" placeholder="Cari <?= e(strtolower($entity['label'])) ?>" value="<?= e($q) ?>"><button class="btn ghost" type="submit">Cari</button></form><button class="btn" onclick="document.getElementById('addForm').style.display='flex'"><?= e($entity['add']) ?></button></div></div>
<div class="card tw"><table><thead><tr><?php foreach($entity['fields'] as $f): ?><th><?= e($f[0]) ?></th><?php endforeach; ?><th>Aksi</th></tr></thead><tbody>
<?php foreach($rows as $r): ?><tr><?php foreach($entity['fields'] as $key=>$f): ?><td><?php $v=$r[$key]??''; if($key==='status') echo statusBadge($v); else echo e(displayValue($db,$view,$key,$v)); ?></td><?php endforeach; ?>
<td><div class="act"><button class="btn ghost sm" onclick='editRecord(<?= json_encode($r,JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_TAG|JSON_HEX_AMP) ?>)'>Ubah</button><form method="post" onsubmit="return confirm('Yakin ingin menghapus data ini?')"><input type="hidden" name="action" value="delete"><input type="hidden" name="view" value="<?= e($view) ?>"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><button class="btn del sm">Hapus</button></form></div></td></tr>
<?php endforeach; if(!$rows): ?><tr><td colspan="<?= count($entity['fields'])+1 ?>" class="empty"><?= $q?'Tidak ada data yang cocok.':'Belum ada data.' ?></td></tr><?php endif; ?>
</tbody></table></div>
<div class="ov" id="addForm" style="display:none"><div class="md"><h3 id="formTitle">Tambah <?= e(strtolower($entity['label'])) ?></h3><form method="post" id="recordForm" style="display:grid;gap:14px"><input type="hidden" name="action" value="save"><input type="hidden" name="view" value="<?= e($view) ?>"><input type="hidden" name="id" id="recordId" value="0">
<?php foreach($entity['fields'] as $key=>$f): ?><div><label for="<?= e($key) ?>"><?= e($f[0]) ?></label>
<?php if($f[1]==='select'): ?><select name="<?= e($key) ?>" id="<?= e($key) ?>"><?php foreach($f[2] as $option): ?><option><?= e($option) ?></option><?php endforeach; ?></select>
<?php elseif(in_array($f[1],['relawan','kegiatan'])): ?><select name="<?= e($key) ?>" id="<?= e($key) ?>" required><?php foreach($db[$f[1]] as $option): ?><option value="<?= (int)$option['id'] ?>"><?= e($option['nama']) ?></option><?php endforeach; ?></select>
<?php elseif($f[1]==='textarea'): ?><textarea name="<?= e($key) ?>" id="<?= e($key) ?>" rows="4"></textarea>
<?php else: ?><input type="<?= e($f[1]) ?>" name="<?= e($key) ?>" id="<?= e($key) ?>" <?= !empty($f[2])?'required':'' ?>><?php endif; ?></div><?php endforeach; ?>
<div class="row"><button type="button" class="btn ghost" onclick="closeForm()">Batal</button><button class="btn" type="submit">Simpan</button></div></form></div></div>
<script>
const fields=<?= json_encode(array_keys($entity['fields'])) ?>;
function editRecord(data){document.getElementById('recordForm').reset();document.getElementById('recordId').value=data.id;fields.forEach(k=>{const el=document.getElementById(k);if(el)el.value=data[k]??'';});document.getElementById('formTitle').textContent='Ubah <?= e(strtolower($entity['label'])) ?>';document.getElementById('addForm').style.display='flex';}
document.querySelector('.tools .btn:not(.ghost)').addEventListener('click',()=>{document.getElementById('recordForm').reset();document.getElementById('recordId').value='0';document.getElementById('formTitle').textContent='Tambah <?= e(strtolower($entity['label'])) ?>';});
function closeForm(){document.getElementById('addForm').style.display='none';}
document.getElementById('addForm').addEventListener('click',e=>{if(e.target.id==='addForm')closeForm();});
</script>
<?php endif; ?>
</main></div></body></html>
