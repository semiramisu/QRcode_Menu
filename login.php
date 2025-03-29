<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    // DB接続後、ユーザー認証を実施（例：password_verify()を利用）
    if ($login_successful) {
        $_SESSION['user'] = $username;
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "ログインに失敗しました。";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>管理画面ログイン</title>
</head>
<body>
    <h1>ログイン</h1>
    <?php if (isset($error)) echo "<p>$error</p>"; ?>
    <form method="POST">
        <label>ユーザー名：<input type="text" name="username" required></label><br>
        <label>パスワード：<input type="password" name="password" required></label><br>
        <button type="submit">ログイン</button>
    </form>
</body>
</html>
