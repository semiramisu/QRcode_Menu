<?php
// index.php
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>メイン</title>
    <style>
        body {
            margin: 0;
            font-family: 'Helvetica Neue', Arial, sans-serif;
            background: #f7f7f7;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 20px;
            text-align: center;
        }
        h1 {
            margin-bottom: 30px;
            color: #444;
        }
        p {
            margin-bottom: 40px;
            color: #555;
        }
        .link-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }
        .link-card {
            background: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            width: 220px;
            padding: 20px;
            text-decoration: none;
            color: #333;
            transition: transform 0.2s ease, background 0.2s ease;
        }
        .link-card:hover {
            transform: translateY(-5px);
            background: #fafafa;
        }
        .link-card h2 {
            margin: 10px 0;
            font-size: 18px;
            color: #444;
        }
        @media (max-width: 480px) {
            .link-card {
                width: calc(100% - 40px);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>QRコード式メニュー表システム</h1>
        <p>以下のリンクから管理者画面またはお客様用画面にアクセスしてください。</p>
        <div class="link-grid">
            <a href="add_product.php" class="link-card">
                <h2>新しいメニューを追加</h2>
            </a>
            <a href="menu.php" class="link-card">
                <h2>メニュー表示（お客様用）</h2>
            </a>
            <a href="delete.php" class="link-card">
                <h2>メニューの削除</h2>
            </a>
            <a href="stock_manage.php" class="link-card">
                <h2>在庫管理</h2>
            </a>
        </div>
    </div>
</body>
</html>
