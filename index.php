
<?php
// ==========================================
// 1. MEMULAI SESSION
// ==========================================

session_start();

// Menghubungkan ke database
require_once 'koneksi.php';

// Jika pengguna sudah login, arahkan ke dashboard
if (isset($_SESSION['user'])) {
    header("Location: dashboard.php");
    exit;
}

// Membuat token keamanan form
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = "";


// ==========================================
// 2. PROSES LOGIN
// ==========================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? '';

    // Memeriksa token keamanan
    if (
        !hash_equals($_SESSION['csrf_token'], $csrfToken)
    ) {
        $error = "Permintaan tidak valid. Silakan coba lagi.";

    } elseif ($username === '' || $password === '') {

        $error = "Username dan password wajib diisi.";

    } else {

        try {

            // Mengambil data pengguna berdasarkan username
            $query = "SELECT id, username, password, nama, role
                      FROM users
                      WHERE username = :username
                      LIMIT 1";

            $stmt = $conn->prepare($query);

            $stmt->execute([
                ':username' => $username
            ]);

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Memeriksa password
            if (
                $user &&
                password_verify($password, $user['password'])
            ) {

                // Membuat session baru
                session_regenerate_id(true);

                $_SESSION['user'] = [
                    'id'       => $user['id'],
                    'username' => $user['username'],
                    'name'     => $user['nama'],
                    'role'     => $user['role']
                ];

                // Mengarahkan ke dashboard
                header("Location: dashboard.php");
                exit;

            } else {

                $error = "Username atau password salah.";

            }

        } catch (PDOException $e) {

            $error = "Terjadi kesalahan saat memproses login.";

        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Login Admin - SIM Relawan Desa</title>

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;700;800&display=swap"
          rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" href="style.css">

</head>

<body>

    <div class="login">

        <!-- BAGIAN FRONTEND: HERO -->

        <section class="hero">

            <i class="ring"></i>
            <i class="ring b"></i>

            <h1>Gotong royong, tercatat rapi.</h1>

            <p>
                Sistem informasi untuk mengelola relawan,
                kegiatan, tugas, dan laporan masyarakat desa.
            </p>

        </section>


        <!-- BAGIAN FRONTEND: FORM LOGIN -->

        <section class="lbox">

            <form method="POST" action="">

                <h2>Login Admin</h2>

                <!-- Token keamanan -->
                <input type="hidden"
                       name="csrf_token"
                       value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                <!-- Input Username -->
                <div>

                    <label for="username">
                        Nama Pengguna
                    </label>

                    <input
                        type="text"
                        id="username"
                        name="username"
                        placeholder="Masukkan username"
                        autocomplete="username"
                        value="<?= htmlspecialchars($username ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        required
                    >

                </div>


                <!-- Input Password -->
                <div>

                    <label for="password">
                        Kata Sandi
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Masukkan password"
                        autocomplete="current-password"
                        required
                    >

                </div>


                <!-- Pesan Error -->
                <?php if ($error !== ''): ?>

                    <p class="err">
                        <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                    </p>

                <?php endif; ?>


                <!-- Tombol Login -->
                <button class="btn" type="submit">
                    Masuk
                </button>


                <div class="note">
                    Silakan masuk menggunakan akun
                    administrator yang sudah terdaftar.
                </div>

            </form>

        </section>

    </div>

</body>
</html>
