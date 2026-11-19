
<?php
session_start();

try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=sistem_relawan;charset=utf8mb4',
        'root',
        '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    die('Koneksi database gagal. Pastikan MySQL menyala dan database sudah di-import.');
}

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

function e($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

function csrf_field() {
    return '<input type="hidden" name="csrf" value="' . e($_SESSION['csrf']) . '">';
}

function csrf_ok() {
    return hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '');
}

function flash($m, $t = 'ok') {
    $_SESSION['flash'] = [$m, $t];
}

function require_admin() {
    if (($_SESSION['user']['role'] ?? '') !== 'admin') {
        header('Location: ../login.php');
        exit;
    }
}

function opts($sql) {
    global $pdo;
    return $pdo->query($sql)->fetchAll(PDO::FETCH_KEY_PAIR);
}

function ic($n, $s = 18) {
    $p = [
        'home' => '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/>',

        'users' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',

        'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',

        'file' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M16 13H8M16 17H8"/>',

        'check' => '<path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>',

        'clock' => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',

        'chart' => '<path d="M18 20V10M12 20V4M6 20v-6"/>',

        'out' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>',

        'heart' => '<path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1-1.1a5.5 5.5 0 0 0-7.8 7.8l1 1.1L12 21l7.8-7.5 1-1.1a5.5 5.5 0 0 0 0-7.8z"/>'
    ];

    return '<svg width="' . $s . '" height="' . $s . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' . $p[$n] . '</svg>';
}

// Kerangka halaman (navbar di atas, logout di kanan atas)
function head($title) {
    $u = $_SESSION['user'] ?? [];
    $adm = ($u['role'] ?? '') === 'admin';

    $menu = $adm
        ? [
            ['dashboard.php', 'Dashboard', 'home'],
            ['relawan.php', 'Relawan', 'users'],
            ['kegiatan.php', 'Kegiatan', 'calendar'],
            ['pendaftaran.php', 'Pendaftaran', 'file'],
            ['tugas.php', 'Tugas', 'check'],
            ['kehadiran.php', 'Kehadiran', 'clock'],
            ['laporan.php', 'Laporan', 'chart']
        ]
        : [
            ['saya.php', 'Tugas &amp; Kehadiran', 'check']
        ];

    $now = basename($_SERVER['PHP_SELF']);

    echo '<!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title>' . e($title) . ' - Sistem Relawan</title>
        <link rel="stylesheet" href="../assets/css/style.css">
    </head>
    <body>
    <header class="top">

    <a class="brand" href="' . $menu[0][0] . '">
        <span class="logo">' . ic('heart', 19) . '</span>
        <b>SIRELA</b>
    </a>

    <nav>';

    foreach ($menu as $m) {
        echo '<a href="' . $m[0] . '" class="' . ($now == $m[0] ? 'on' : '') . '">'
            . ic($m[2], 16) . $m[1] . '</a>';
    }

    echo '</nav>

    <div class="who">
        <span class="av">' . e(mb_substr($u['nama'] ?? 'U', 0, 1)) . '</span>
        <span class="nm">' . e($u['nama'] ?? '') . '</span>
        <button class="btn y s" id="lo" data-href="../backend/logout.php">'
            . ic('out', 15) . ' Logout
        </button>
    </div>

    </header>
    <main>
    <h1>' . e($title) . '</h1>';

    if (!empty($_SESSION['flash'])) {
        echo '<div class="flash ' . $_SESSION['flash'][1] . '">'
            . e($_SESSION['flash'][0]) . '</div>';

        unset($_SESSION['flash']);
    }
}

function foot() {
    echo '</main>
    <script src="../assets/js/script.js"></script>
    </body>
    </html>';
}

// Tabel + form tambah/edit/hapus (dipakai semua halaman CRUD)
function crud_view($c, $rows) {
    $act = '../backend/' . $c['file'];

    echo '<div class="bar">
        <input id="q" placeholder="Cari data...">
        <button class="btn" data-add>+ Tambah</button>
    </div>

    <div class="wrap">
    <table id="tbl">
    <thead>
    <tr>';

    foreach ($c['cols'] as $x) {
        echo '<th>' . e($x[1]) . '</th>';
    }

    echo '<th></th>
    </tr>
    </thead>
    <tbody>';

    foreach ($rows as $r) {
        echo '<tr>';

        foreach ($c['cols'] as $x) {
            echo '<td>' . e($r[$x[0]]) . '</td>';
        }

        echo '<td class="act">
            <button class="btn y s" data-edit="' . e(json_encode($r)) . '">Edit</button>

            <form method="post" action="' . $act . '" onsubmit="return confirm(\'Hapus data ini?\')">'
                . csrf_field() . '
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="' . (int)$r['id'] . '">
                <button class="btn r s">Hapus</button>
            </form>
        </td>
        </tr>';
    }

    if (!$rows) {
        echo '<tr>
            <td colspan="' . (count($c['cols']) + 1) . '" class="mut">
                Belum ada data.
            </td>
        </tr>';
    }

    echo '</tbody>
    </table>
    </div>

    <div class="ov" id="modal">
    <form method="post" action="' . $act . '" class="dlg form" data-t="' . e($c['title']) . '">
        <h3 id="mt"></h3>'
        . csrf_field() . '
        <input type="hidden" name="id">';

    foreach ($c['fields'] as $f) {
        echo '<label>' . e($f[1]) . '</label>';

        if ($f[2] == 'select') {
            echo '<select name="' . $f[0] . '" required>
                <option value="">-- pilih --</option>';

            foreach ($f[3] as $id => $lb) {
                echo '<option value="' . $id . '">' . e($lb) . '</option>';
            }

            echo '</select>';

        } elseif ($f[2] == 'textarea') {
            echo '<textarea name="' . $f[0] . '" rows="3"></textarea>';

        } else {
            echo '<input name="' . $f[0] . '" type="' . $f[2] . '"'
                . (($f[3] ?? '') == 'opt' ? '' : ' required') . '>';
        }
    }

    echo '<div class="bar">
        <button type="button" class="btn y" data-close>Batal</button>
        <button class="btn">Simpan</button>
    </div>

    </form>
    </div>';
}

// Proses simpan/hapus untuk folder backend
function crud_handle($table, $fields, $page, $after = null) {
    global $pdo;

    require_admin();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_ok()) {
        header('Location: ../frontend/' . $page);
        exit;
    }

    $new = null;
    $mode = 'edit';

    try {
        if (($_POST['action'] ?? '') == 'delete') {
            $mode = 'delete';

            $pdo->prepare("DELETE FROM $table WHERE id=?")
                ->execute([(int)$_POST['id']]);

            flash('Data berhasil dihapus');

        } else {
            $v = [];

            foreach ($fields as $f) {
                $x = trim($_POST[$f] ?? '');
                $v[$f] = $x === '' ? null : $x;
            }

            if (!empty($_POST['id'])) {
                $v['id'] = (int)$_POST['id'];

                $pdo->prepare(
                    "UPDATE $table SET " .
                    implode(',', array_map(fn($f) => "$f=:$f", $fields)) .
                    " WHERE id=:id"
                )->execute($v);

                flash('Data berhasil diperbarui');

            } else {
                $pdo->prepare(
                    "INSERT INTO $table(" . implode(',', $fields) .
                    ") VALUES(:" . implode(',:', $fields) . ")"
                )->execute($v);

                $new = $pdo->lastInsertId();
                $mode = 'add';

                flash('Data berhasil ditambahkan');
            }
        }

        if ($after) {
            $after($new, $mode);
        }

    } catch (PDOException $e) {
        flash('Gagal menyimpan data, periksa isian Anda.', 'err');
    }

    header('Location: ../frontend/' . $page);
    exit;
}
