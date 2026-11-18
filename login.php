
<?php
session_start();

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    if ($username == "lenon" && $password == "123") {
        $_SESSION["username"] = $username;

        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Nama user atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #141e30, #243b55);
        }

        .login-container {
            width: 350px;
            padding: 35px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }

        h2 {
            text-align: center;
            margin-bottom: 10px;
            color: #243b55;
        }

        .subtitle {
            text-align: center;
            color: gray;
            margin-bottom: 25px;
        }

        .input-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 7px;
            outline: none;
        }

        input:focus {
            border-color: #243b55;
        }

        button {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 7px;
            background: #243b55;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background: #141e30;
        }

        .error {
            color: red;
            text-align: center;
            margin-bottom: 15px;
            font-size: 14px;
        }
    </style>
</head>

<body>

    <div class="login-container">
        <h2>Login Admin</h2>
        <p class="subtitle">Silakan login ke akun admin Anda</p>

        <?php if ($error != "") { ?>
            <p class="error"><?php echo $error; ?></p>
        <?php } ?>

        <form method="POST" action="">
            <div class="input-group">
                <label>Nama User</label>
                <input type="text" name="username"
                    placeholder="Masukkan nama user" required>
            </div>

            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password"
                    placeholder="Masukkan password" required>
            </div>

            <button type="submit">LOGIN</button>
        </form>
    </div>

</body>
</html>
