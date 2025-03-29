<?php


require_once('db.php');

// 削除処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = (int)$_POST['delete_id'];
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        $message = "商品ID {$delete_id} の商品を削除しました。";
    } else {
        $message = "削除に失敗しました: " . $stmt->error;
    }
}

// 商品一覧を取得（image カラムも含む）
$query = "SELECT id, name, price, description, image FROM products ORDER BY id DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>商品削除ページ</title>
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
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }
    th, td {
      border: 1px solid #ddd;
      padding: 10px;
      text-align: center;
    }
    th {
      background-color: #f0f0f0;
    }
    button {
      background: #dc3545;
      color: #fff;
      border: none;
      padding: 8px 12px;
      border-radius: 4px;
      cursor: pointer;
      font-size: 14px;
    }
    button:hover {
      background: #c82333;
    }
    .product-img {
      max-width: 100px;
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
    <h1>商品削除ページ</h1>
    <?php if(isset($message)) { ?>
      <div class="message <?php echo (strpos($message, "失敗") !== false) ? "error-message" : "success-message"; ?>">
        <?php echo htmlspecialchars($message); ?>
      </div>
    <?php } ?>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>商品写真</th>
          <th>商品名</th>
          <th>価格</th>
          <th>説明</th>
          <th>操作</th>
        </tr>
      </thead>
      <tbody>
        <?php while($row = $result->fetch_assoc()) { ?>
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
            <td><?php echo htmlspecialchars($row['price']); ?>円</td>
            <td><?php echo nl2br(htmlspecialchars($row['description'])); ?></td>
            <td>
              <form method="POST" onsubmit="return confirm('本当に削除しますか？');">
                <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                <button type="submit">削除</button>
              </form>
            </td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
  <footer class="footer">
        <div class="footer-container">
            <p><a href="index.php">メインページに戻る</a></p>
        </div>
    </footer>
</body>
</html>

