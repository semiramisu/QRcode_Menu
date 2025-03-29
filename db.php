<?php
// db.php
$servername = "127.0.0.1"; // localhostから127.0.0.1に変更
$username = "root";  // 例: "root"など
$password = "root";  // ご利用のパスワードに置き換え
$dbname = "menu_db"; // 例: "menu_db"
$port = 3306; // 必要に応じてポート番号を変更（MAMPの場合は8889など）

// MySQLi を使った接続
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// 接続確認
if ($conn->connect_error) {
    die("接続に失敗しました: " . $conn->connect_error);
}
?>
