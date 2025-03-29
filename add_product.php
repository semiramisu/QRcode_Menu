<?php
session_start();
// ログインチェック（必要なら有効化）
// if (!isset($_SESSION['user'])) {
//     header("Location: login.php");
//     exit;
// }

require_once('db.php');

/**
 * すべてのカテゴリを階層構造で表示するためのオプション生成関数
 */
function buildCategoryOptions($categories, $parent_id = null, $level = 0) {
    $options = "";
    foreach ($categories as $cat) {
        if ($cat['parent_id'] == $parent_id) {
            $indent = str_repeat("&nbsp;&nbsp;&nbsp;", $level);
            $options .= '<option value="' . $cat['id'] . '">' . $indent . htmlspecialchars($cat['name']) . '</option>';
            $options .= buildCategoryOptions($categories, $cat['id'], $level + 1);
        }
    }
    return $options;
}

// すべてのカテゴリを取得（階層構築用）
$allCats = [];
$result = $conn->query("SELECT * FROM categories ORDER BY parent_id, name ASC");
while ($row = $result->fetch_assoc()) {
    $allCats[] = $row;
}

$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // バリデーション：商品名、価格、及びカテゴリ（既存、新規、または新規親子カテゴリのいずれか）が必須
    if (empty($_POST['name']) || empty($_POST['price']) ||
        (empty($_POST['existing_category']) && empty($_POST['new_category']) && (empty($_POST['new_parent_category']) || empty($_POST['new_child_category'])))) {
        $error = "商品名、価格、及びカテゴリは必須項目です。";
    } else {
        $category_id = null;
        // ① 新規親子カテゴリの追加（親子両方入力された場合）
        if (!empty($_POST['new_parent_category']) && !empty($_POST['new_child_category'])) {
            $parentName = trim($_POST['new_parent_category']);
            $childName  = trim($_POST['new_child_category']);
            
            // 親カテゴリの存在チェック（トップレベル：parent_id IS NULL）
            $stmtParent = $conn->prepare("SELECT id FROM categories WHERE name = ? AND parent_id IS NULL");
            $stmtParent->bind_param("s", $parentName);
            $stmtParent->execute();
            $resParent = $stmtParent->get_result();
            if ($resParent->num_rows > 0) {
                $rowParent = $resParent->fetch_assoc();
                $parent_id = $rowParent['id'];
            } else {
                // 新規親カテゴリ作成
                $stmtInsertParent = $conn->prepare("INSERT INTO categories (name, parent_id) VALUES (?, NULL)");
                $stmtInsertParent->bind_param("s", $parentName);
                $stmtInsertParent->execute();
                $parent_id = $stmtInsertParent->insert_id;
            }
            // 次に、子カテゴリのチェック（同じ親の下で同名かどうか）
            $stmtChild = $conn->prepare("SELECT id FROM categories WHERE name = ? AND parent_id = ?");
            $stmtChild->bind_param("si", $childName, $parent_id);
            $stmtChild->execute();
            $resChild = $stmtChild->get_result();
            if ($resChild->num_rows > 0) {
                $rowChild = $resChild->fetch_assoc();
                $category_id = $rowChild['id'];
            } else {
                // 子カテゴリ新規作成
                $stmtInsertChild = $conn->prepare("INSERT INTO categories (name, parent_id) VALUES (?, ?)");
                $stmtInsertChild->bind_param("si", $childName, $parent_id);
                $stmtInsertChild->execute();
                $category_id = $stmtInsertChild->insert_id;
            }
        }
        // ② 新規カテゴリの追加（単一カテゴリ追加）
        elseif (!empty($_POST['new_category'])) {
            $newCat = trim($_POST['new_category']);
            // 親カテゴリの指定（既存の親カテゴリから選択、任意）
            $parent_new = !empty($_POST['parent_new_category']) ? (int)$_POST['parent_new_category'] : null;
            
            if ($parent_new === null) {
                $checkStmt = $conn->prepare("SELECT id FROM categories WHERE name = ? AND parent_id IS NULL");
                $checkStmt->bind_param("s", $newCat);
            } else {
                $checkStmt = $conn->prepare("SELECT id FROM categories WHERE name = ? AND parent_id = ?");
                $checkStmt->bind_param("si", $newCat, $parent_new);
            }
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            if ($checkResult->num_rows > 0) {
                $rowCat = $checkResult->fetch_assoc();
                $category_id = $rowCat['id'];
            } else {
                $stmtCat = $conn->prepare("INSERT INTO categories (name, parent_id) VALUES (?, ?)");
                $stmtCat->bind_param("si", $newCat, $parent_new);
                $stmtCat->execute();
                $category_id = $stmtCat->insert_id;
            }
        }
        // ③ 既存カテゴリの選択
        elseif (!empty($_POST['existing_category'])) {
            $category_id = (int)$_POST['existing_category'];
        }
        
        if (empty($category_id)) {
            $error = "カテゴリの入力に問題があります。";
        } else {
            // 商品情報の取得
            $name        = $_POST['name'];
            $price       = (int)$_POST['price'];
            $description = $_POST['description'];

            // 在庫数は固定で 1
            $stock = 1;
            // 売り切れ時の非表示は不要なので固定 0
            $hide_when_sold_out = 0;
            
            // 画像アップロード処理
            $image_path = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = 'uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $tmpName   = $_FILES['image']['tmp_name'];
                $fileName  = basename($_FILES['image']['name']);
                $targetPath = $uploadDir . $fileName;
                if (move_uploaded_file($tmpName, $targetPath)) {
                    $image_path = $targetPath;
                }
            }

            // show_stockは不要なので固定 0
            $show_stock = 0;

            $stmt = $conn->prepare("
                INSERT INTO products 
                    (name, image, price, description, stock, show_stock, hide_when_sold_out, category_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("ssdsiiii", $name, $image_path, $price, $description, $stock, $show_stock, $hide_when_sold_out, $category_id);
            $stmt->execute();

            header("Location: add_product.php?success=1");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>メニュー追加</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 30px auto;
            background: #fff;
            padding: 20px 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            color: #444;
        }
        form {
            margin-top: 20px;
        }
        .form-section {
            margin-bottom: 20px;
        }
        form label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }
        form input[type="text"],
        form input[type="number"],
        form textarea,
        form select {
            width: 100%;
            padding: 8px 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        form textarea {
            resize: vertical;
        }
        form button {
            background: #28a745;
            color: #fff;
            border: none;
            padding: 12px 20px;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
        }
        form button:hover {
            background: #218838;
        }
        .message {
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
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
        <h1>新しいメニュー項目を追加</h1>
        <?php 
        if (!empty($error)) {
            echo '<div class="message error-message">' . htmlspecialchars($error) . '</div>';
        }
        if (isset($_GET['success'])) { 
            echo '<div class="message success-message">商品を追加しました。</div>'; 
        } 
        ?>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-section">
                <label>商品名：
                    <input type="text" name="name" required>
                </label>
            </div>
            <div class="form-section">
                <label>価格：
                    <input type="number" name="price" required>
                </label>
            </div>
            <div class="form-section">
                <label>説明：
                    <textarea name="description" rows="4"></textarea>
                </label>
            </div>
            <div class="form-section">
                <p>【既存カテゴリの選択】</p>
                <select name="existing_category">
                    <option value="">-- 選択しない --</option>
                    <?php echo buildCategoryOptions($allCats); ?>
                </select>
            </div>
            <div class="form-section">
                <p>【新規カテゴリの追加】</p>
                <label>カテゴリ名：
                    <input type="text" name="new_category">
                </label>
                <label>親カテゴリ（任意）：
                    <select name="parent_new_category">
                        <option value="">-- なし（トップレベル） --</option>
                        <?php echo buildCategoryOptions($allCats); ?>
                    </select>
                </label>
            </div>
            <div class="form-section">
                <p>【新規親子カテゴリの追加】（親子を同時に追加）</p>
                <label>新規親カテゴリ名：
                    <input type="text" name="new_parent_category">
                </label>
                <label>新規子カテゴリ名：
                    <input type="text" name="new_child_category">
                </label>
            </div>
            <div class="form-section">
                <label>商品写真：
                    <input type="file" name="image" accept="image/*">
                </label>
            </div>
            <div class="form-section" style="text-align: center;">
                <button type="submit">追加</button>
            </div>
        </form>
    </div>
    <footer class="footer">
        <div class="footer-container">
            <p><a href="index.php">メインページに戻る</a></p>
        </div>
    </footer>
</body>
</html>
