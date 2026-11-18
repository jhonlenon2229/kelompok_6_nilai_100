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
