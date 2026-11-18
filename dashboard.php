<?php
session_start();
require_once __DIR__ . '/koneksi.php';

// Proteksi halaman: pengguna wajib login terlebih dahulu.
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

// Fungsi bantuan.
function e($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function redirectView(string $view, string $message = ''): void
{
    $url = 'dashboard.php?view=' . urlencode($view);
    if ($message !== '') {
        $url .= '&msg=' . urlencode($message);
    }
    header('Location: ' . $url);
    exit;
}

function statusBadge(string $status): string
{
    $classes = [
        'Aktif' => 'ok',
        'Selesai' => 'ok',
        'Proses' => 'wr',
        'Berjalan' => 'wr',
        'Belum' => 'in',
        'Rencana' => 'in',
        'Nonaktif' => 'er',
    ];
    $class = $classes[$status] ?? 'in';
    return '<span class="badge b-' . $class . '">' . e($status) . '</span>';
}

function formatDate($value): string
{
    if (empty($value)) {
        return '-';
    }
    $timestamp = strtotime((string) $value);
    return $timestamp ? date('d M Y', $timestamp) : '-';
}

$user = $_SESSION['user'];

// Logout.
if (isset($_GET['logout'])) {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
    header('Location: index.php');
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Pengaturan setiap modul. Nama tabel dan kolom ditetapkan di server, bukan dari input bebas pengguna.
$entities = [
    'relawan' => [
        'label' => 'Relawan',
        'add' => 'Tambah relawan',
        'table' => 'relawan',
        'fields' => [
            'nama' => ['label' => 'Nama relawan', 'type' => 'text', 'required' => true],
            'telepon' => ['label' => 'Telepon', 'type' => 'text', 'required' => false],
            'alamat' => ['label' => 'Alamat', 'type' => 'text', 'required' => false],
            'status' => ['label' => 'Status', 'type' => 'select', 'required' => true, 'options' => ['Aktif', 'Nonaktif']],
        ],
        'columns' => [
            'nama' => 'Nama relawan',
            'telepon' => 'Telepon',
            'alamat' => 'Alamat',
            'status' => 'Status',
        ],
    ],
    'kegiatan' => [
        'label' => 'Kegiatan',
        'add' => 'Tambah kegiatan',
        'table' => 'kegiatan',
        'fields' => [
            'nama' => ['label' => 'Nama kegiatan', 'type' => 'text', 'required' => true],
            'tanggal' => ['label' => 'Tanggal', 'type' => 'date', 'required' => true],
            'lokasi' => ['label' => 'Lokasi', 'type' => 'text', 'required' => false],
            'status' => ['label' => 'Status', 'type' => 'select', 'required' => true, 'options' => ['Rencana', 'Berjalan', 'Selesai']],
        ],
        'columns' => [
            'nama' => 'Nama kegiatan',
            'tanggal' => 'Tanggal',
            'lokasi' => 'Lokasi',
            'status' => 'Status',
        ],
    ],
    'tugas' => [
        'label' => 'Tugas',
        'add' => 'Tambah tugas',
        'table' => 'tugas',
        'fields' => [
            'judul' => ['label' => 'Judul tugas', 'type' => 'text', 'required' => true],
            'relawan_id' => ['label' => 'Relawan', 'type' => 'relation', 'relation' => 'relawan', 'required' => true],
            'kegiatan_id' => ['label' => 'Kegiatan', 'type' => 'relation', 'relation' => 'kegiatan', 'required' => true],
            'tenggat' => ['label' => 'Tenggat', 'type' => 'date', 'required' => false],
            'status' => ['label' => 'Status', 'type' => 'select', 'required' => true, 'options' => ['Belum', 'Proses', 'Selesai']],
        ],
        'columns' => [
            'judul' => 'Judul tugas',
            'relawan_nama' => 'Relawan',
            'kegiatan_nama' => 'Kegiatan',
            'tenggat' => 'Tenggat',
            'status' => 'Status',
        ],
    ],
    'laporan' => [
        'label' => 'Laporan',
        'add' => 'Tambah laporan',
        'table' => 'laporan',
        'fields' => [
            'judul' => ['label' => 'Judul laporan', 'type' => 'text', 'required' => true],
            'kegiatan_id' => ['label' => 'Kegiatan', 'type' => 'relation', 'relation' => 'kegiatan', 'required' => true],
            'relawan_id' => ['label' => 'Pelapor', 'type' => 'relation', 'relation' => 'relawan', 'required' => true],
            'tanggal' => ['label' => 'Tanggal', 'type' => 'date', 'required' => false],
            'isi' => ['label' => 'Isi laporan', 'type' => 'textarea', 'required' => false],
        ],
        'columns' => [
            'judul' => 'Judul laporan',
            'kegiatan_nama' => 'Kegiatan',
            'relawan_nama' => 'Pelapor',
            'tanggal' => 'Tanggal',
            'isi' => 'Isi laporan',
        ],
    ],
];

$view = $_GET['view'] ?? 'dashboard';
if (!isset($entities[$view]) && $view !== 'dashboard') {
    $view = 'dashboard';
}

// Proses tambah, edit, dan hapus menggunakan prepared statement PDO.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postedView = $_POST['view'] ?? 'dashboard';
    if (!isset($entities[$postedView])) {
        redirectView('dashboard', 'Halaman tidak ditemukan.');
    }

    $view = $postedView;
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
        redirectView($view, 'Permintaan tidak valid. Silakan coba lagi.');
    }

    $action = $_POST['action'] ?? '';
    $table = $entities[$view]['table'];

    try {
        if ($action === 'save') {
            $id = (int) ($_POST['id'] ?? 0);
            $values = [];
            $validationError = '';

            foreach ($entities[$view]['fields'] as $fieldName => $field) {
                $value = trim((string) ($_POST[$fieldName] ?? ''));

                if ($field['required'] && $value === '') {
                    $validationError = $field['label'] . ' wajib diisi.';
                    break;
                }

                if ($field['type'] === 'select' && $value !== '' && !in_array($value, $field['options'], true)) {
                    $validationError = 'Pilihan ' . $field['label'] . ' tidak valid.';
                    break;
                }

                if ($field['type'] === 'relation') {
                    $value = ($value === '') ? null : (int) $value;
                    if ($value !== null) {
                        $check = $conn->prepare('SELECT id FROM ' . $field['relation'] . ' WHERE id = ?');
                        $check->execute([$value]);
                        if (!$check->fetchColumn()) {
                            $validationError = 'Data ' . $field['label'] . ' tidak ditemukan.';
                            break;
                        }
                    }
                } elseif ($value === '' && !$field['required']) {
                    $value = null;
                }

                $values[$fieldName] = $value;
            }

            if ($validationError !== '') {
                redirectView($view, $validationError);
            }

            if ($id > 0) {
                $sets = [];
                foreach (array_keys($values) as $column) {
                    $sets[] = '`' . $column . '` = :' . $column;
                }
                $values['id'] = $id;
                $sql = 'UPDATE `' . $table . '` SET ' . implode(', ', $sets) . ' WHERE id = :id';
                $stmt = $conn->prepare($sql);
                $stmt->execute($values);
                redirectView($view, 'Data berhasil diperbarui.');
            }

            $columns = array_keys($values);
            $quotedColumns = array_map(static fn($column) => '`' . $column . '`', $columns);
            $placeholders = array_map(static fn($column) => ':' . $column, $columns);
            $sql = 'INSERT INTO `' . $table . '` (' . implode(', ', $quotedColumns) . ')
                    VALUES (' . implode(', ', $placeholders) . ')';
            $stmt = $conn->prepare($sql);
            $stmt->execute($values);
            redirectView($view, 'Data berhasil ditambahkan.');

        } elseif ($action === 'delete') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id <= 0) {
                redirectView($view, 'ID data tidak valid.');
            }
            $stmt = $conn->prepare('DELETE FROM `' . $table . '` WHERE id = :id');
            $stmt->execute(['id' => $id]);
            redirectView($view, 'Data berhasil dihapus.');
        }

        redirectView($view, 'Aksi tidak dikenali.');
    } catch (PDOException $e) {
        // Pesan umum agar detail SQL tidak ditampilkan ke pengguna.
        redirectView($view, 'Data gagal disimpan. Periksa isian dan hubungan data yang dipilih.');
    }
}

$message = $_GET['msg'] ?? '';
$search = trim($_GET['q'] ?? '');

// Mengambil pilihan dropdown untuk relasi tugas dan laporan.
$relawanOptions = $conn->query('SELECT id, nama FROM relawan ORDER BY nama')->fetchAll();
$kegiatanOptions = $conn->query('SELECT id, nama FROM kegiatan ORDER BY nama')->fetchAll();

// Mengambil ringkasan dashboard.
$stats = [];
foreach (['relawan', 'kegiatan', 'tugas', 'laporan'] as $tableName) {
    $stats[$tableName] = (int) $conn->query('SELECT COUNT(*) FROM `' . $tableName . '`')->fetchColumn();
}

$pendingTasks = $conn->query(
    "SELECT t.id, t.judul, t.tenggat, t.status,
            r.nama AS relawan_nama
     FROM tugas t
     LEFT JOIN relawan r ON r.id = t.relawan_id
     WHERE t.status <> 'Selesai'
     ORDER BY t.tenggat IS NULL, t.tenggat ASC
     LIMIT 5"
)->fetchAll();

// Mengambil data untuk tabel pada halaman yang sedang dibuka.
$rows = [];
if ($view === 'relawan') {
    $sql = 'SELECT id, nama, telepon, alamat, status FROM relawan';
    $params = [];
    if ($search !== '') {
        $sql .= ' WHERE nama LIKE ? OR telepon LIKE ? OR alamat LIKE ? OR status LIKE ?';
        $like = '%' . $search . '%';
        $params = [$like, $like, $like, $like];
    }
    $sql .= ' ORDER BY id DESC';
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
} elseif ($view === 'kegiatan') {
    $sql = 'SELECT id, nama, tanggal, lokasi, status FROM kegiatan';
    $params = [];
    if ($search !== '') {
        $sql .= ' WHERE nama LIKE ? OR lokasi LIKE ? OR status LIKE ?';
        $like = '%' . $search . '%';
        $params = [$like, $like, $like];
    }
    $sql .= ' ORDER BY tanggal DESC, id DESC';
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
} elseif ($view === 'tugas') {
    $sql = 'SELECT t.id, t.judul, t.relawan_id, t.kegiatan_id, t.tenggat, t.status,
                   r.nama AS relawan_nama, k.nama AS kegiatan_nama
            FROM tugas t
            LEFT JOIN relawan r ON r.id = t.relawan_id
            LEFT JOIN kegiatan k ON k.id = t.kegiatan_id';
    $params = [];
    if ($search !== '') {
        $sql .= ' WHERE t.judul LIKE ? OR r.nama LIKE ? OR k.nama LIKE ? OR t.status LIKE ?';
        $like = '%' . $search . '%';
        $params = [$like, $like, $like, $like];
    }
    $sql .= ' ORDER BY t.id DESC';
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
} elseif ($view === 'laporan') {
    $sql = 'SELECT l.id, l.judul, l.kegiatan_id, l.relawan_id, l.tanggal, l.isi,
                   k.nama AS kegiatan_nama, r.nama AS relawan_nama
            FROM laporan l
            LEFT JOIN kegiatan k ON k.id = l.kegiatan_id
            LEFT JOIN relawan r ON r.id = l.relawan_id';
    $params = [];
    if ($search !== '') {
        $sql .= ' WHERE l.judul LIKE ? OR l.isi LIKE ? OR k.nama LIKE ? OR r.nama LIKE ?';
        $like = '%' . $search . '%';
        $params = [$like, $like, $like, $like];
    }
    $sql .= ' ORDER BY l.id DESC';
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SIM Relawan Desa</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="app">
    <aside class="side">
        <div class="brand"><i class="dot"></i>SIM Relawan</div>

        <button class="nav <?= $view === 'dashboard' ? 'on' : '' ?>" onclick="location.href='dashboard.php'">Ringkasan</button>
        <?php foreach ($entities as $key => $entity): ?>
            <button class="nav <?= $view === $key ? 'on' : '' ?>" onclick="location.href='dashboard.php?view=<?= e($key) ?>'">
                <?= e($entity['label']) ?>
            </button>
        <?php endforeach; ?>

        <div class="who">
            <div>
                <b><?= e($user['name']) ?></b>
                <?= e($user['role']) ?>
            </div>
            <a class="btn ghost sm" style="display:inline-block;margin-top:10px;color:#fff;border-color:rgba(255,255,255,.3);text-decoration:none" href="dashboard.php?logout=1">Keluar</a>
        </div>
    </aside>

    <main>
        <?php if ($message !== ''): ?>
            <p class="note"><?= e($message) ?></p>
        <?php endif; ?>

        <?php if ($view === 'dashboard'): ?>
            <div class="top">
                <h2>Selamat datang, <?= e($user['name']) ?></h2>
            </div>

            <div class="stats">
                <div class="card stat"><b><?= $stats['relawan'] ?></b><span>Relawan terdaftar</span></div>
                <div class="card stat"><b><?= $stats['kegiatan'] ?></b><span>Kegiatan</span></div>
                <div class="card stat"><b><?= $stats['tugas'] ?></b><span>Tugas</span></div>
                <div class="card stat"><b><?= $stats['laporan'] ?></b><span>Laporan masuk</span></div>
            </div>

            <div class="card sec">
                <h3>Tugas yang perlu diselesaikan</h3>
                <div class="tw">
                    <table style="min-width:520px">
                        <thead>
                            <tr><th>Tugas</th><th>Relawan</th><th>Tenggat</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($pendingTasks as $task): ?>
                            <tr>
                                <td><?= e($task['judul']) ?></td>
                                <td><?= e($task['relawan_nama'] ?? '-') ?></td>
                                <td><?= e(formatDate($task['tenggat'])) ?></td>
                                <td><?= statusBadge($task['status']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$pendingTasks): ?>
                            <tr><td colspan="4" class="empty">Semua tugas sudah selesai.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php else: ?>
            <?php $entity = $entities[$view]; ?>
            <div class="top">
                <h2><?= e($entity['label']) ?></h2>
                <div class="tools">
                    <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap">
                        <input type="hidden" name="view" value="<?= e($view) ?>">
                        <input type="search" name="q" placeholder="Cari <?= e(strtolower($entity['label'])) ?>" value="<?= e($search) ?>">
                        <button class="btn ghost" type="submit">Cari</button>
                    </form>
                    <button class="btn" type="button" onclick="openAddForm()"><?= e($entity['add']) ?></button>
                </div>
            </div>

            <div class="card tw">
                <table>
                    <thead>
                    <tr>
                        <?php foreach ($entity['columns'] as $columnLabel): ?>
                            <th><?= e($columnLabel) ?></th>
                        <?php endforeach; ?>
                        <th>Aksi</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <?php foreach ($entity['columns'] as $columnKey => $columnLabel): ?>
                                <td>
                                    <?php if ($columnKey === 'status'): ?>
                                        <?= statusBadge((string) $row[$columnKey]) ?>
                                    <?php elseif (in_array($columnKey, ['tanggal', 'tenggat'], true)): ?>
                                        <?= e(formatDate($row[$columnKey])) ?>
                                    <?php else: ?>
                                        <?= e($row[$columnKey] ?? '-') ?>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                            <td>
                                <div class="act">
                                    <button class="btn ghost sm" type="button" onclick='editRecord(<?= e(json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP)) ?>)'>Ubah</button>
                                    <form method="POST" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="view" value="<?= e($view) ?>">
                                        <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                                        <button class="btn del sm" type="submit">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$rows): ?>
                        <tr><td colspan="<?= count($entity['columns']) + 1 ?>" class="empty">
                            <?= $search !== '' ? 'Tidak ada data yang cocok.' : 'Belum ada data.' ?>
                        </td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Form tambah/edit data -->
            <div class="ov" id="formOverlay" style="display:none">
                <div class="md">
                    <h3 id="formTitle"><?= e($entity['add']) ?></h3>
                    <form method="POST" id="recordForm" style="display:grid;gap:14px">
                        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                        <input type="hidden" name="action" value="save">
                        <input type="hidden" name="view" value="<?= e($view) ?>">
                        <input type="hidden" name="id" id="recordId" value="0">

                        <?php foreach ($entity['fields'] as $fieldName => $field): ?>
                            <div>
                                <label for="<?= e($fieldName) ?>"><?= e($field['label']) ?></label>

                                <?php if ($field['type'] === 'select'): ?>
                                    <select name="<?= e($fieldName) ?>" id="<?= e($fieldName) ?>" <?= $field['required'] ? 'required' : '' ?>>
                                        <?php foreach ($field['options'] as $option): ?>
                                            <option value="<?= e($option) ?>"><?= e($option) ?></option>
                                        <?php endforeach; ?>
                                    </select>

                                <?php elseif ($field['type'] === 'relation'): ?>
                                    <?php $options = $field['relation'] === 'relawan' ? $relawanOptions : $kegiatanOptions; ?>
                                    <select name="<?= e($fieldName) ?>" id="<?= e($fieldName) ?>" <?= $field['required'] ? 'required' : '' ?>>
                                        <option value="">Pilih <?= e(strtolower($field['label'])) ?></option>
                                        <?php foreach ($options as $option): ?>
                                            <option value="<?= (int) $option['id'] ?>"><?= e($option['nama']) ?></option>
                                        <?php endforeach; ?>
                                    </select>

                                <?php elseif ($field['type'] === 'textarea'): ?>
                                    <textarea name="<?= e($fieldName) ?>" id="<?= e($fieldName) ?>" rows="4" <?= $field['required'] ? 'required' : '' ?>></textarea>

                                <?php else: ?>
                                    <input type="<?= e($field['type']) ?>" name="<?= e($fieldName) ?>" id="<?= e($fieldName) ?>" <?= $field['required'] ? 'required' : '' ?>>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>

                        <div class="row">
                            <button type="button" class="btn ghost" onclick="closeForm()">Batal</button>
                            <button type="submit" class="btn">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>

            <script>
                const fieldNames = <?= json_encode(array_keys($entity['fields'])) ?>;
                const formOverlay = document.getElementById('formOverlay');

                function openAddForm() {
                    document.getElementById('recordForm').reset();
                    document.getElementById('recordId').value = '0';
                    document.getElementById('formTitle').textContent = <?= json_encode($entity['add']) ?>;
                    formOverlay.style.display = 'flex';
                }

                function editRecord(data) {
                    document.getElementById('recordForm').reset();
                    document.getElementById('recordId').value = data.id;
                    fieldNames.forEach(function (fieldName) {
                        const input = document.getElementById(fieldName);
                        if (input) {
                            input.value = data[fieldName] ?? '';
                        }
                    });
                    document.getElementById('formTitle').textContent = 'Ubah <?= e(strtolower($entity['label'])) ?>';
                    formOverlay.style.display = 'flex';
                }

                function closeForm() {
                    formOverlay.style.display = 'none';
                }

                formOverlay.addEventListener('click', function (event) {
                    if (event.target === formOverlay) {
                        closeForm();
                    }
                });
            </script>
        <?php endif; ?>
    </main>
</div>
</body>
</html>
