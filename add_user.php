<?php
// add_user.php
// ※管理者のみがアクセスできるように認証処理を追加することが望ましい

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 入力値を受け取る
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // パスワードをハッシュ化する
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // DB接続（例: db.php で接続設定を記述している場合）
    require_once('db.php');
    
    // SQL文でユーザーを追加
    $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $hashedPassword);
    
    if ($stmt->execute()) {
        echo "ユーザーを追加しました。";
    } else {
        echo "エラーが発生しました。";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>ユーザー追加</title>
</head>
<body>
    <h1>新しい管理ユーザーを追加</h1>
    <form method="POST">
        <label>ユーザー名：<input type="text" name="username" required></label><br>
        <label>パスワード：<input type="password" name="password" required></label><br>
        <button type="submit">追加</button>
    </form>
</body>
</html>
