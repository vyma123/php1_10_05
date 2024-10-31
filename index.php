<?php 
require_once "includes/db.inc.php";
require_once "includes/functions.php";

// get all products 
$results = select_all_products($pdo);

$searchTerm = isset($_GET['search']) ? test_input($_GET['search']) : '';
$per_page_record = 3;
$page = isset($_GET["page"]) ? $_GET["page"] : 1;
$page = filter_var($page, FILTER_VALIDATE_INT) !== false ? (int)$page : 1;

$start_from = ($page - 1) * $per_page_record;

// Fetching products with pagination
$query = "SELECT * FROM products LIMIT :start_from, :per_page";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':start_from', $start_from, PDO::PARAM_INT);
$stmt->bindParam(':per_page', $per_page_record, PDO::PARAM_INT);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);


//filter
$allowed_sort_columns = ['id', 'product_name', 'price'];
$sort_by = isset($_GET['sort_by']) && in_array($_GET['sort_by'], $allowed_sort_columns) ? $_GET['sort_by'] : 'id';
$allowed_order_directions = ['ASC', 'DESC'];
$order = isset($_GET['order']) && in_array($_GET['order'], $allowed_order_directions) ? $_GET['order'] : 'ASC';
$category = $_GET['category'] ?? 0;
$tag = $_GET['tag'] ?? 0;
$category_page = $_GET['category'] ?? 0;
$tag_page = $_GET['tag'] ?? 0;
$tag = $_GET['tag'] ?? 0;
$date_from = $_GET['date_from'] ?? null;
$date_to = $_GET['date_to'] ?? null;
$price_from = $_GET['price_from'] ?? null;
$price_to = $_GET['price_to'] ?? null;

$query = "
SELECT products.*, 
       GROUP_CONCAT(DISTINCT p_tags.name_ SEPARATOR ', ') AS tags, 
       GROUP_CONCAT(DISTINCT p_categories.name_ SEPARATOR ', ') AS categories
FROM products
LEFT JOIN product_property pp_tags ON products.id = pp_tags.product_id
LEFT JOIN property p_tags ON pp_tags.property_id = p_tags.id AND p_tags.type_ = 'tag'
LEFT JOIN product_property pp_categories ON products.id = pp_categories.product_id
LEFT JOIN property p_categories ON pp_categories.property_id = p_categories.id AND p_categories.type_ = 'category'
WHERE products.product_name LIKE :search_term
";

if ($category != 0) {
    $query .= " AND pp_categories.property_id = :category_id";
}

if ($tag != 0) {
    $query .= " AND pp_tags.property_id = :tag_id";
}

if (!empty($date_from)) {
    $query .= " AND products.date >= :date_from"; 
}

if (!empty($date_to)) {
    $query .= " AND products.date <= :date_to"; 
}

if (!empty($price_from)) {
    $query .= " AND products.price >= :price_from"; 
}

if (!empty($price_to)) {
    $query .= " AND products.price <= :price_to";
}
$query .= " GROUP BY products.id 
            ORDER BY $sort_by $order 
            LIMIT :start_from, :per_page";
$stmt = $pdo->prepare($query);

$searchTermLike = "%$searchTerm%";
$stmt->bindParam(':search_term', $searchTermLike, PDO::PARAM_STR);

if ($category != 0) {
    $stmt->bindParam(':category_id', $category, PDO::PARAM_INT);
    $category_page = $category;
}

if ($tag != 0) {
    $stmt->bindParam(':tag_id', $tag, PDO::PARAM_INT);
    $tag_page = $tag;
}

if (!empty($date_from)) {
    $stmt->bindParam(':date_from', $date_from);
}

if (!empty($date_to)) {
    $stmt->bindParam(':date_to', $date_to);
}

if (!empty($price_from)) {
    $stmt->bindParam(':price_from', $price_from);
}

if (!empty($price_to)) {
    $stmt->bindParam(':price_to', $price_to);
}

$stmt->bindParam(':start_from', $start_from, PDO::PARAM_INT);
$stmt->bindParam(':per_page', $per_page_record, PDO::PARAM_INT);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Counting total records
if (!empty($category_page) || !empty($tag_page) || (!empty($date_from) && !empty($date_to)) || (!empty($price_from) && !empty($price_to))) {
    $total_records = getRecordCount($pdo, $searchTermLike, $category_page, $tag_page, $date_from, $date_to, $price_from, $price_to);
} else {
    $count_query = "SELECT COUNT(*) FROM products WHERE product_name LIKE :search_term";
    $count_stmt = $pdo->prepare($count_query);
    $count_stmt->bindParam(':search_term', $searchTermLike, PDO::PARAM_STR);
    $count_stmt->execute();
    $total_records = $count_stmt->fetchColumn();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="styles/style.css" type="text/css">
    <link rel="stylesheet" href="styles/style2.css" type="text/css">

    <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">
    
    <!-- link semantic ui -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.5.0/semantic.min.css" integrity="sha512-KXol4x3sVoO+8ZsWPFI/r5KBVB/ssCGB5tsv2nVOKwLg33wTFP3fmnXa47FdSVIshVTgsYk/1734xSk9aFIa4A==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <title>PHP1</title>
</head>

<body>
    <section class="container">
        <h1>PHP1</h1>
        <form method="GET" action="index.php">

        <div class="product_header">
            <div class="product_header_top">
                <div>
                    <a href="edit_add.php" class="ui primary button">
                        Add product
                    </a>
                    <a href="add_property.php" class="ui button">
                            Add property
                    </a>
                    <a href="#" class="ui button">
                        Sync from VillaTheme
                    </a>
                </div>
                <div class="ui icon input">
                    <input name="search" type="text" placeholder="Search product..."  value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                    <i class="inverted circular search link icon" onclick="this.closest('form').submit()"></i>
                </div>
            </div>
            <div class="product_header_bottom">
                <select class="ui dropdown" name="sort_by" id="sort_by">
                    <option value="date" <?php if ($sort_by === 'date') echo 'selected'; ?>>Date</option>
                    <option value="product_name" <?php if ($sort_by === 'product_name') echo 'selected'; ?>>Product name</option>
                    <option value="price" <?php if ($sort_by === 'price') echo 'selected'; ?>>Price</option>
                </select>
                <select class="ui dropdown" name="order">
                    <option value="ASC" <?php if ($order === 'ASC') echo 'selected'; ?>>ASC</option>
                    <option value="DESC" <?php if ($order === 'DESC') echo 'selected'; ?>>DESC</option>
                </select>

                <select class="ui dropdown" name="category">
                    <option value="0">Category</option>

                    <?php
                     $query = "SELECT p.id, p.name_ FROM property p WHERE p.type_ = 'category' ";
                     $stmt = $pdo->prepare($query);
                     $stmt->execute();
                     $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                     $selectedCategory = $_GET['category'];

                     foreach ($categories as $category) {
                        $selected = ($category['id'] == $selectedCategory) ? 'selected' : '';
                         echo "<option $selected value=\"{$category['id']}\">" . htmlspecialchars($category['name_']) . "</option>";
                     }
                    ?>
                </select>

                <select class="ui dropdown" name="tag">
                    <option value="0">Select Tag</option>
                    <?php
                $query = "SELECT p.id, p.name_ FROM property p WHERE p.type_ = 'tag'";
                $stmt = $pdo->prepare($query);
                $stmt->execute();
                $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $selectedTag = $_GET['tag'] ?? 0; 

                foreach ($tags as $tag) {
                    $selected = ($tag['id'] == $selectedTag) ? 'selected' : '';

                    echo "<option $selected value=\"{$tag['id']}\">" . htmlspecialchars($tag['name_']) . "</option>";
                }
                ?>
                </select>
                <div class="ui input">
                    <input type="date" value="<?= $date_from?>" id="date_from" name="date_from">
                </div>
                <div class="ui input">
                    <input type="date" value="<?= $date_to?>" id="date_to" name="date_to">
                </div>
                <div class="ui input">
                    <input type="text" value="<?= $price_from?>" id="price_from" name="price_from" placeholder="price from"
                    >
                </div>
                <div class="ui input">
                    <input type="text" value="<?= $price_to?>" id="price_to" name="price_to" placeholder="price to">
                </div>
                <button type="submit" class="ui button">
                    Filter
                </button>
            </div>
        </div>
        </form>

        <!-- table -->
         <div class="box_table">

    <table class="ui compact celled table">
  <thead>
    <tr>
      <th>Date</th>
      <th>Product name</th>
      <th>SKU</th>
      <th>Price</th>
      <th>Feature Image</th>
      <th class="gallery_name">Gallery</th>
      <th >Categories</th>
      <th class="tag_name">Tags</th>
      <th>Action</th>
    </tr>
  </thead>
  <tbody>
    
  <?php if (count($results) > 0) {

      foreach ($results as $row){
        $product_id = $row['id']; ?>
     <tr>
      <td><?php echo htmlspecialchars($row['date'])?></td>
      <td class="product_name"><?php echo htmlspecialchars($row['product_name'])?></td>
      <td class="sku"><?php echo htmlspecialchars($row['sku'])?></td>
      <td><?php echo htmlspecialchars($row['price'])?></td>
      <td>
          <img height="30" src="./uploads/<?php echo $row['featured_image']; ?>">
      </td>
      <td class="gallery_images">
              <?php 
            $query = "SELECT p.name_ FROM product_property pp
                    JOIN property p ON pp.property_id = p.id
                    WHERE pp.product_id = :product_id AND p.type_ = 'gallery'";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
            $stmt->execute();
            $galleryImages = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($galleryImages as $image) {?> 

        <img height="40" src="./uploads/<?= $image['name_'] ?>">
      <?php }?>
      </td>
      <td>
      <?php 
            $query = "SELECT p.name_ FROM product_property pp
                    JOIN property p ON pp.property_id = p.id
                    WHERE pp.product_id = :product_id AND p.type_ = 'category'";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
            $stmt->execute();
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $totalCategories = count($categories);
            foreach ($categories as $index => $category) {?> 
            <span><?php echo htmlspecialchars($category['name_']);
                    if($index < $totalCategories -1 ){
                        echo ', ';
                    }
                   ?></span>
            <?php }?>
      </td>
      <td>
      <?php 
            $query = "SELECT p.name_ FROM product_property pp
                    JOIN property p ON pp.property_id = p.id
                    WHERE pp.product_id = :product_id AND p.type_ = 'tag'";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
            $stmt->execute();
            $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $totalTags = count($tags);
            foreach ($tags as $index => $tag) {?> 
            <span><?php echo htmlspecialchars($tag['name_']);
                    if($index < $totalTags -1 ){
                        echo ', ';
                    }
                   ?></span>
            <?php }?>
      </td>
      <td>
        <a class="edit_button" href="edit_add.php?product_id=<?php echo htmlspecialchars($product_id)  ?>">
        <i class="edit icon"></i>
        </a>
        <?php 
        $base_url = 'index.php?search=' . urlencode($searchTerm) . 
        '&sort_by=' . htmlspecialchars($sort_by) . 
        '&order=' . htmlspecialchars($order) . 
        '&category=' . htmlspecialchars($category_page) . 
        '&tag=' . htmlspecialchars($tag_page) . 
        '&date_from=' . htmlspecialchars($date_from) . 
        '&date_to=' . htmlspecialchars($date_to) . 
        '&price_from=' . htmlspecialchars($price_from) . 
        '&price_to=' . htmlspecialchars($price_to);
        ?>
        <a  class="delete_button" href="delete.php?product_id=<?php echo $product_id?>&delete=1&total_records=<?php echo $total_records;?>&<?php echo  $base_url;?>" onclick="return confirmDelete();">
        <i class="trash icon"></i>
        </a>
      </td>
    </tr>
    <?php }}else {?>
        <tr>
            <td colspan="9" style="text-align: center;">Product not found</td>
        </tr>
        <?php }?>
  </tbody>
</table>
</div>

<div class="pagination_box">
<div class="ui pagination menu">
                <?php
                echo "</br>";
                // Number of pages required.
                $total_pages = ceil($total_records / $per_page_record);

                $base_url = 'index.php?search=' . urlencode($searchTerm) . 
                '&sort_by=' . htmlspecialchars($sort_by) . 
                '&order=' . htmlspecialchars($order) . 
                '&category=' . htmlspecialchars($category_page) . 
                '&tag=' . htmlspecialchars($tag_page) . 
                '&date_from=' . htmlspecialchars($date_from) . 
                '&date_to=' . htmlspecialchars($date_to) . 
                '&price_from=' . htmlspecialchars($price_from) . 
                '&price_to=' . htmlspecialchars($price_to);

                    $pagLink = "";

                    if ($page >= 2) {
                        echo "<a class='item' href='" . $base_url . "&page=" . ($page - 1) . "'> Prev </a>";
                    } else {
                        echo "<a class='item disabled'> Prev </a>";
                    }
                
                    for ($i = 1; $i <= $total_pages; $i++) {
                        if ($i == $page) {
                            $pagLink .= "<a class='item active' href='" . $base_url . "&page=" . $i . "'>" . $i . " </a>";
                        } else {
                            $pagLink .= "<a class='item' href='" . $base_url . "&page=" . $i . "'>" . $i . " </a>";
                        }
                    }
                    echo $pagLink;
                
                    if ($page < $total_pages) {
                        echo "<a class='item' href='" . $base_url . "&page=" . ($page + 1) . "'> Next </a>";
                    } else {
                        echo "<a class='item disabled'> Next </a>"; 
                    }
                ?>                
            </div>
</div>
</section>

<script src="script.js">
</script>
</body>
</html>