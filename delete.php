<?php
include 'includes/db.inc.php';
$product_id = $_GET['product_id'];
$total_records = $_GET['total_records'];
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
            $total_records = $total_records -1;
            $total_pages = ceil($total_records / $per_page_record);
            echo $total_pages;


        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
    else
    {
        echo "ID must be a positive integer";
    }
    header("Location: index.php?page=".$total_pages."");
    exit();

}
?>
