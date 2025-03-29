<?php
// category.php
require_once('db.php');

// クエリパラメータ cat_id を取得
if (!isset($_GET['cat_id'])) {
    // cat_id が指定されていなければトップページへリダイレクト
    header("Location: menu_index.php");
    exit;
}
$cat_id = (int)$_GET['cat_id'];

// カテゴリ情報を取得
$stmtCat = $conn->prepare("SELECT * FROM categories WHERE id = ?");
$stmtCat->bind_param("i", $cat_id);
$stmtCat->execute();
$resCat = $stmtCat->get_result();
if ($resCat->num_rows === 0) {
    // カテゴリが存在しない場合はトップページへ
    header("Location: menu_index.php");
    exit;
}
$category = $resCat->fetch_assoc();
$categoryName = $category['name'];

// 子カテゴリを取得
$stmtChild = $conn->prepare("SELECT * FROM categories WHERE parent_id = ? ORDER BY name ASC");
$stmtChild->bind_param("i", $cat_id);
$stmtChild->execute();
$resChild = $stmtChild->get_result();
$children = [];
while ($rowChild = $resChild->fetch_assoc()) {
    $children[] = $rowChild;
}

// 在庫あり商品の取得
$stmtProd = $conn->prepare("
    SELECT * FROM products 
     WHERE category_id = ? AND stock > 0
     ORDER BY id DESC
");
$stmtProd->bind_param("i", $cat_id);
$stmtProd->execute();
$resProd = $stmtProd->get_result();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title><?php echo htmlspecialchars($categoryName); ?> - メニュー</title>
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
      margin-bottom: 30px;
      color: #444;
    }
    .breadcrumb {
      margin-bottom: 20px;
    }
    .breadcrumb a {
      text-decoration: none;
      color: #007bff;
      margin-right: 5px;
    }
    .breadcrumb span {
      color: #555;
    }
    .child-category-grid {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
      margin-bottom: 40px;
      justify-content: flex-start;
    }
    .child-category-card {
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
    .child-category-card:hover {
      transform: translateY(-5px);
      background: #fafafa;
    }
    .child-category-card h2 {
      margin: 10px 0;
      font-size: 18px;
      color: #333;
    }
    .product-grid {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
    }
    .product-card {
      background: #fff;
      border: 1px solid #e0e0e0;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      padding: 15px;
      text-align: center;
      flex: 1 1 calc(25% - 20px);
      max-width: calc(25% - 20px);
      transition: transform 0.2s ease;
    }
    .product-card:hover {
      transform: translateY(-5px);
    }
    .product-card img {
      max-width: 100%;
      border-radius: 4px;
      margin-bottom: 10px;
    }
    .no-image {
      background: #ddd;
      height: 150px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 10px;
      border-radius: 4px;
    }
    .product-card h3 {
      font-size: 18px;
      margin: 10px 0;
      color: #333;
    }
    .product-card .price {
      font-size: 16px;
      font-weight: bold;
      color: #e67e22;
      margin: 5px 0;
    }
    .product-card .description {
      font-size: 14px;
      color: #666;
    }
    @media (max-width: 768px) {
       .product-card {
         flex: 1 1 calc(50% - 20px);
         max-width: calc(50% - 20px);
       }
    }
    @media (max-width: 480px) {
       .product-card {
         flex: 1 1 100%;
         max-width: 100%;
       }
    }
  </style>
</head>
<body>
  <div class="container">
    <h1><?php echo htmlspecialchars($categoryName); ?></h1>
    
    <!-- パンくず or トップへ戻るリンク -->
    <div class="breadcrumb">
      <a href="menu_index.php">トップ</a>
      <span> &gt; <?php echo htmlspecialchars($categoryName); ?></span>
    </div>

    <!-- 子カテゴリ一覧 -->
    <?php if (!empty($children)) { ?>
      <div class="child-category-grid">
        <?php foreach ($children as $child) { ?>
          <a class="child-category-card" href="category.php?cat_id=<?php echo $child['id']; ?>">
            <h2><?php echo htmlspecialchars($child['name']); ?></h2>
          </a>
        <?php } ?>
      </div>
    <?php } ?>

    <!-- 商品一覧（在庫あり） -->
    <?php if ($resProd->num_rows > 0) { ?>
      <div class="product-grid">
        <?php while ($prod = $resProd->fetch_assoc()) { ?>
          <div class="product-card">
            <?php if (!empty($prod['image'])) { ?>
              <img src="<?php echo htmlspecialchars($prod['image']); ?>" alt="<?php echo htmlspecialchars($prod['name']); ?>">
            <?php } else { ?>
              <div class="no-image">[画像なし]</div>
            <?php } ?>
            <h3><?php echo htmlspecialchars($prod['name']); ?></h3>
            <p class="price"><?php echo htmlspecialchars($prod['price']); ?>円</p>
            <?php if (!empty($prod['description'])) { ?>
              <p class="description"><?php echo nl2br(htmlspecialchars($prod['description'])); ?></p>
            <?php } ?>
          </div>
        <?php } ?>
      </div>
    <?php } else { ?>
      <p>このカテゴリには在庫ありの商品がありません。</p>
    <?php } ?>
  </div>
</body>
</html>
