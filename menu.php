<?php
require_once('db.php');

/* 
 * 1. カテゴリをすべて取得 
 * 2. buildCategoryTree() で階層構造を作る 
 * 3. トップレベルカテゴリだけをリストアップして横並び表示 
 * 4. それぞれのカテゴリを見出し付きで再帰的に表示（子カテゴリは開いた状態）
 */

// すべてのカテゴリを取得
$catQuery = "SELECT * FROM categories ORDER BY parent_id, name ASC";
$catResult = $conn->query($catQuery);
$categories = [];
while ($row = $catResult->fetch_assoc()) {
    $categories[] = $row;
}

// 階層構造を作成する関数
function buildCategoryTree(array $categories, $parent_id = null) {
    $branch = [];
    foreach ($categories as $cat) {
        if ($cat['parent_id'] == $parent_id) {
            $cat['children'] = buildCategoryTree($categories, $cat['id']);
            $branch[] = $cat;
        }
    }
    return $branch;
}
$categoryTree = buildCategoryTree($categories);

// トップレベルカテゴリだけを抽出
$topLevelCats = [];
foreach ($categoryTree as $cat) {
    if ($cat['parent_id'] === null) {
        $topLevelCats[] = $cat;
    }
}

/**
 * カテゴリを再帰的に表示し、そのカテゴリに属する在庫あり商品をカードで並べる。
 * 子カテゴリは常に開いた状態（折りたたみなし）。
 */
function displayCategory($category, $level = 1) {
    global $conn;
    $catId   = $category['id'];
    $catName = $category['name'];
    
    // h2, h3, ... タグのレベルを決定
    $headingTag = 'h' . min($level + 1, 6);

    // アンカー用に id をつける（トップレベルカテゴリの場合に付与）
    // ただしすべてのカテゴリに付与してもOK
    echo "<div class='category-block' id='cat-{$catId}'>";
    echo "<{$headingTag}>" . htmlspecialchars($catName) . "</{$headingTag}>";

    // 在庫あり商品の取得
    $stmt = $conn->prepare("
        SELECT * FROM products 
         WHERE category_id = ? AND stock > 0
         ORDER BY id DESC
    ");
    $stmt->bind_param("i", $catId);
    $stmt->execute();
    $resultProd = $stmt->get_result();
    
    if ($resultProd->num_rows > 0) {
        echo "<div class='product-grid'>";
        while ($prod = $resultProd->fetch_assoc()) {
            echo "<div class='product-card'>";
            if (!empty($prod['image'])) {
                echo "<img src='" . htmlspecialchars($prod['image']) . "' alt='" . htmlspecialchars($prod['name']) . "'>";
            } else {
                echo "<div class='no-image'>[画像なし]</div>";
            }
            echo "<h3>" . htmlspecialchars($prod['name']) . "</h3>";
            echo "<p class='price'>" . htmlspecialchars($prod['price']) . "円</p>";
            if (!empty($prod['description'])) {
                echo "<p class='description'>" . nl2br(htmlspecialchars($prod['description'])) . "</p>";
            }
            echo "</div>"; // .product-card
        }
        echo "</div>"; // .product-grid
    }
    
    // 子カテゴリがあれば再帰的に表示
    if (!empty($category['children'])) {
        foreach ($category['children'] as $child) {
            displayCategory($child, $level + 1);
        }
    }
    echo "</div>"; // .category-block
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>メニュー</title>
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
    /* トップレベルカテゴリを横並びで表示するナビゲーション */
    .top-level-nav {
      display: flex;
      justify-content: center;
      gap: 20px;
      margin-bottom: 40px;
    }
    .top-level-nav a {
      text-decoration: none;
      color: #333;
      background: #f0f0f0;
      padding: 10px 20px;
      border-radius: 4px;
      transition: background 0.2s ease;
    }
    .top-level-nav a:hover {
      background: #e0e0e0;
    }
    /* カテゴリブロック */
    .category-block {
      margin-bottom: 40px;
    }
    .category-block h2, 
    .category-block h3, 
    .category-block h4, 
    .category-block h5, 
    .category-block h6 {
      border-bottom: 2px solid #ddd;
      padding-bottom: 5px;
      margin-top: 30px;
      margin-bottom: 20px;
      color: #555;
    }
    /* 商品カードのグリッド */
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
    <h1>メニュー</h1>

    <!-- トップレベルカテゴリを横並びで表示するナビゲーション -->
    <div class="top-level-nav">
      <?php foreach ($topLevelCats as $cat) { ?>
        <a href="#cat-<?php echo $cat['id']; ?>">
          <?php echo htmlspecialchars($cat['name']); ?>
        </a>
      <?php } ?>
    </div>

    <!-- 各トップレベルカテゴリのセクションを表示 -->
    <?php
      foreach ($topLevelCats as $cat) {
        displayCategory($cat, 1);
      }
    ?>
  </div>
</body>
</html>
