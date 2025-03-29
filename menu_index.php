<?php
// menu_index.php
require_once('db.php');

// トップレベルカテゴリを取得 (parent_id IS NULL)
$sql = "SELECT * FROM categories WHERE parent_id IS NULL ORDER BY name ASC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>メニュー - トップページ</title>
  <style>
    body {
      margin: 0;
      font-family: 'Helvetica Neue', Arial, sans-serif;
      background: #f7f7f7;
      color: #333;
    }
    .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 20px;
    }
    h1 {
      text-align: center;
      margin-bottom: 40px;
      color: #444;
    }
    .top-level-grid {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
      justify-content: center;
    }
    .category-card {
      background: #fff;
      border: 1px solid #e0e0e0;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      padding: 20px;
      text-align: center;
      width: 200px;
      transition: transform 0.2s ease;
      text-decoration: none;
      color: #333;
    }
    .category-card:hover {
      transform: translateY(-5px);
      background: #fafafa;
    }
    .category-card h2 {
      margin: 10px 0;
      font-size: 18px;
      color: #333;
    }
    @media (max-width: 480px) {
      .category-card {
        width: calc(50% - 20px);
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>メニュー</h1>
    <div class="top-level-grid">
      <?php while ($row = $result->fetch_assoc()) { ?>
        <!-- 各トップレベルカテゴリをカードとして表示 -->
        <a class="category-card" href="category.php?cat_id=<?php echo $row['id']; ?>">
          <h2><?php echo htmlspecialchars($row['name']); ?></h2>
        </a>
      <?php } ?>
    </div>
  </div>
</body>
</html>
