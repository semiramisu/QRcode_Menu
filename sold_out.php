<?php

require_once('db.php');

// POSTで在庫更新リクエストが送られてきた場合の処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = (int)$_POST['product_id'];
    // 在庫を1に更新し、hide_when_sold_outフラグを解除（0にする）
    $new_stock = 1;
    $stmt = $conn->prepare("UPDATE products SET stock = ?, hide_when_sold_out = 0 WHERE id = ?");
    $stmt->bind_param("ii", $new_stock, $product_id);
    if ($stmt->execute()) {
        $message = "商品ID {$product_id} を在庫ありに更新しました。";
    } else {
        $message = "更新に失敗しました: " . $stmt->error;
    }
}

// 売り切れ商品の一覧（stock == 0）の取得
$query = "SELECT * FROM products WHERE stock = 0";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>売り切れ商品</title>
    <style>
        table, th, td {
            border: 1px solid #ccc;
            border-collapse: collapse;
            padding: 8px;
        }
        th {
            background-color: #eee;
        }
    </style>
</head>
<body>
    <h1>売り切れ商品管理ページ</h1>
    <?php if (isset($message)) { ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php } ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>商品名</th>
                <th>価格</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['price']); ?>円</td>
                    <td>
                        <!-- 在庫ありに変更するフォーム -->
                        <form method="POST" onsubmit="return confirm('在庫ありに更新しますか？');">
                            <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($row['id']); ?>">
                            <button type="submit">在庫ありに変更</button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</body>
</html>
