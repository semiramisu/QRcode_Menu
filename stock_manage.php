<?php


require_once('db.php');

// 在庫状態変更処理（確認ダイアログはなし）
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['to_soldout'])) {
        $product_id = (int)$_POST['to_soldout'];
        $stmt = $conn->prepare("UPDATE products SET stock = 0 WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        if ($stmt->execute()) {
            $message = "商品ID {$product_id} を売り切れにしました。";
        } else {
            $message = "更新エラー: " . $stmt->error;
        }
    } elseif (isset($_POST['to_instock'])) {
        $product_id = (int)$_POST['to_instock'];
        $stmt = $conn->prepare("UPDATE products SET stock = 1 WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        if ($stmt->execute()) {
            $message = "商品ID {$product_id} を在庫ありにしました。";
        } else {
            $message = "更新エラー: " . $stmt->error;
        }
    }
}

// 在庫あり商品の取得
$inStockQuery = "SELECT * FROM products WHERE stock > 0 ORDER BY id DESC";
$inStockResult = $conn->query($inStockQuery);

// 売り切れ商品の取得
$outOfStockQuery = "SELECT * FROM products WHERE stock = 0 ORDER BY id DESC";
$outOfStockResult = $conn->query($outOfStockQuery);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>在庫管理ページ</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .container {
            max-width: 1000px;
            margin: 30px auto;
            background: #fff;
            padding: 20px 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        h1, h2 {
            text-align: center;
            color: #444;
        }
        .message {
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
        }
        .success-message {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error-message {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .columns {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            flex-wrap: wrap;
        }
        .column {
            flex: 1;
            min-width: 300px;
            background: #fafafa;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            padding: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        button {
            background: #007bff;
            color: #fff;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        button:hover {
            background: #0069d9;
        }
        .product-img {
            max-width: 80px;
            height: auto;
        }
        .footer {
            background: #f7f7f7;
            border-top: 1px solid #ddd;
            padding: 15px 0;
            margin-top: 40px;
            text-align: center;
        }
        .footer-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 0 20px;
        }
        .footer a {
            text-decoration: none;
            color: #007bff;
            font-weight: bold;
        }
        .footer a:hover {
            color: #0056b3;
        }

    </style>
</head>
<body>
    <div class="container">
        <h1>在庫管理ページ</h1>
        <?php if(isset($message)) { ?>
            <div class="message success-message"><?php echo htmlspecialchars($message); ?></div>
        <?php } ?>
        <div class="columns">
            <!-- 在庫あり -->
            <div class="column">
                <h2>在庫あり</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>商品画像</th>
                            <th>商品名</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $inStockResult->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['id']); ?></td>
                                <td>
                                    <?php if (!empty($row['image'])) { ?>
                                        <img src="<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" class="product-img">
                                    <?php } else { ?>
                                        [画像なし]
                                    <?php } ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td>
                                    <form method="POST" style="display:inline;">
                                        <button type="submit" name="to_soldout" value="<?php echo $row['id']; ?>">
                                            売り切れにする
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <!-- 売り切れ -->
            <div class="column">
                <h2>売り切れ</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>商品画像</th>
                            <th>商品名</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $outOfStockResult->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['id']); ?></td>
                                <td>
                                    <?php if (!empty($row['image'])) { ?>
                                        <img src="<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" class="product-img">
                                    <?php } else { ?>
                                        [画像なし]
                                    <?php } ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td>
                                    <form method="POST" style="display:inline;">
                                        <button type="submit" name="to_instock" value="<?php echo $row['id']; ?>">
                                            在庫ありにする
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div><!-- .columns -->
    </div><!-- .container -->
    <footer class="footer">
        <div class="footer-container">
            <p><a href="index.php">メインページに戻る</a></p>
        </div>
    </footer>
</body>
</html>