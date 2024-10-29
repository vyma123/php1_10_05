<?php
include 'includes/db.inc.php';
$product_id = $_GET['product_id'];
$total_records = $_GET['total_records'];

$sort_by = $_GET['sort_by'];
$order = $_GET['order'];
$category = $_GET['category'];
$tag = $_GET['tag'];
$date_from = $_GET['date_from'];
$date_to = $_GET['date_to'];
$price_from = $_GET['price_from'];
$price_to = $_GET['price_to'];

$searchTerm = $_GET['search'] ?? '';
$category_page = $_GET['category'] ?? '';
$tag_page = $_GET['tag'] ?? '';

$base_url = '&search=' . urlencode($searchTerm) . 
'&sort_by=' . htmlspecialchars($sort_by) . 
'&order=' . htmlspecialchars($order) . 
'&category=' . htmlspecialchars($category_page) . 
'&tag=' . htmlspecialchars($tag_page) . 
'&date_from=' . htmlspecialchars($date_from) . 
'&date_to=' . htmlspecialchars($date_to) . 
'&price_from=' . htmlspecialchars($price_from) . 
'&price_to=' . htmlspecialchars($price_to);

$per_page_record = 3;

if( isset($_GET['delete']) )
{
    $product_id = $_GET['product_id'];
    if( isset($total_records) && isset($product_id) && is_numeric( $product_id ) && $product_id > 0 )
    {
        try {
            $stmt = $pdo->prepare( "DELETE FROM products WHERE id = :product_id" );
            $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                echo "Deletion successful";
            } else {
                echo "Deletion failed: Product not found or already deleted";
            }
            if($total_records > 1){
                $total_records = $total_records - 1;
                $total_pages = ceil($total_records / $per_page_record);
                echo $total_pages;
            }else {
                $total_pages = 1; 
                echo $total_pages;
            }


        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
    else
    {
        echo "ID must be a positive integer";
    }
    echo 'hello';
    header("Location: index.php?page=".$total_pages."".$base_url."");
    exit();

}
?>
