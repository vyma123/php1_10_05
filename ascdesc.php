<?php 
require_once "includes/db.inc.php";
require_once "includes/functions.php";

// Get all products 
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
$per_page_record = 3;
$page = isset($_GET["page"]) ? (int)$_GET["page"] : 1;
$start_from = ($page - 1) * $per_page_record;

// Get sorting parameters
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'date';
$order = isset($_GET['order']) ? $_GET['order'] : 'ASC';

// Fetching products with pagination and sorting
$query = "SELECT * FROM products WHERE product_name LIKE :search_term ORDER BY $sort_by $order LIMIT :start_from, :per_page";
$stmt = $pdo->prepare($query);
$searchTermLike = "%$searchTerm%";
$stmt->bindParam(':search_term', $searchTermLike, PDO::PARAM_STR);
$stmt->bindParam(':start_from', $start_from, PDO::PARAM_INT);
$stmt->bindParam(':per_page', $per_page_record, PDO::PARAM_INT);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Counting total records
$count_query = "SELECT COUNT(*) FROM products WHERE product_name LIKE :search_term";
$count_stmt = $pdo->prepare($count_query);
$count_stmt->bindParam(':search_term', $searchTermLike, PDO::PARAM_STR);
$count_stmt->execute();
$total_records = $count_stmt->fetchColumn();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="styles/style.css" type="text/css">
    <link rel="stylesheet" href="styles/style2.css" type="text/css">

    <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.5.0/semantic.min.css" integrity="sha512-KXol4x3sVoO+8ZsWPFI/r5KBVB/ssCGB5tsv2nVOKwLg33wTFP3fmnXa47FdSVIshVTgsYk/1734xSk9aFIa4A==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <title>PHP1</title>
</head>

<body>
    <section class="container">
        <h1>PHP1</h1>
        <form method="GET" action="ascdesc.php">
            <div class="product_header">
                <div class="product_header_top">
                    <div>
                        <a href="edit_add.php" class="ui primary button">Add product</a>
                        <a href="add_property.php">
                            <button type="button" class="ui button">Add property</button>
                        </a>
                        <button type="button" class="ui button">Sync from VillaTheme</button>
                    </div>
                    <div class="ui icon input">
                        <input name="search" type="text" placeholder="Search product..." value="<?php echo htmlspecialchars($searchTerm); ?>">
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
                    <!-- Additional filters can go here -->
                    <button type="submit" class="ui button">Filter</button>
                </div>
            </div>
        </form>

        <!-- Table -->
        <div class="box_table">
            <table class="ui compact celled table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Product name</th>
                        <th>SKU</th>
                        <th>Price</th>
                        <th>Feature Image</th>
                        <th>Gallery</th>
                        <th>Categories</th>
                        <th>Tags</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($results) > 0) {
                        foreach ($results as $row) {
                            $product_id = $row['id'];
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['date']); ?></td>
                                <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['sku']); ?></td>
                                <td><?php echo htmlspecialchars($row['price']); ?></td>
                                <td><img height="30" src="./uploads/<?php echo htmlspecialchars($row['featured_image']); ?>"></td>
                                <td class="gallery_images">
                                    <?php 
                                    // Fetch gallery images
                                    $query = "SELECT p.name_ FROM product_property pp
                                              JOIN property p ON pp.property_id = p.id
                                              WHERE pp.product_id = :product_id AND p.type_ = 'gallery'";
                                    $stmt = $pdo->prepare($query);
                                    $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
                                    $stmt->execute();
                                    $galleryImages = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($galleryImages as $image) {?> 
                                        <img height="40" src="./uploads/<?= htmlspecialchars($image['name_']); ?>">
                                    <?php } ?>
                                </td>
                                <td>
                                    <?php 
                                    // Fetch categories
                                    $query = "SELECT p.name_ FROM product_property pp
                                              JOIN property p ON pp.property_id = p.id
                                              WHERE pp.product_id = :product_id AND p.type_ = 'category'";
                                    $stmt = $pdo->prepare($query);
                                    $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
                                    $stmt->execute();
                                    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($categories as $category) { ?> 
                                        <span><?php echo htmlspecialchars($category['name_']); ?></span>
                                    <?php } ?>
                                </td>
                                <td>
                                    <?php 
                                    // Fetch tags
                                    $query = "SELECT p.name_ FROM product_property pp
                                              JOIN property p ON pp.property_id = p.id
                                              WHERE pp.product_id = :product_id AND p.type_ = 'tag'";
                                    $stmt = $pdo->prepare($query);
                                    $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
                                    $stmt->execute();
                                    $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($tags as $tag) { ?> 
                                        <span><?php echo htmlspecialchars($tag['name_']); ?></span>
                                    <?php } ?>
                                </td>
                                <td>
                                    <a class="edit_button" href="edit_add.php?product_id=<?php echo $product_id; ?>">
                                        <i class="edit icon"></i>
                                    </a>
                                    <a class="delete_button" href="delete.php?product_id=<?php echo $product_id; ?>">
                                        <i class="trash icon"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php }
                    } else { ?>
                        <tr>
                            <td colspan="9" style="text-align: center;">Product not found</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <div class="pagination_box">
            <div class="ui pagination menu">
                <?php
                // Number of pages required
                $total_pages = ceil($total_records / $per_page_record);
                if ($page >= 2) {
                    echo "<a class='item' href='ascdesc.php?page=" . ($page - 1) . "&sort_by=$sort_by&order=$order'> Prev </a>";
                }
                
                for ($i = 1; $i <= $total_pages; $i++) {
                    if ($i == $page) {
                        echo "<a class='item active'>$i</a>";
                    } else {
                        echo "<a class='item' href='ascdesc.php?page=$i&sort_by=$sort_by&order=$order'>$i</a>";
                    }
                }

                if ($page < $total_pages) {
                    echo "<a class='item' href='ascdesc.php?page=" . ($page + 1) . "&sort_by=$sort_by&order=$order'> Next </a>";
                }
                ?>
            </div>
        </div>
    </section>
</body>
</html>
