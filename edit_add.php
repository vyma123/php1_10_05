<?php 
require_once "includes/db.inc.php";
require_once 'includes/functions.php';

$target_dir = "uploads/";
$uploadOk = 1;


if(isset($_GET['product_id'])){
    $product_id = $_GET['product_id'];

    $name = 'Edit Product';

    if (empty($_FILES['singleFile']['name'])) {
        $query = "SELECT featured_image FROM products WHERE id = :product_id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->execute();
    
        // Lấy kết quả
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $singleFileName = $row['featured_image'];
        }
    }
  
     
    if(isset($_POST['add'])){
    $overallUploadOk = 1;
    // Xử lý kiểm tra ảnh đơn lẻ
    if (isset($_FILES["singleFile"]) && $_FILES["singleFile"]["error"] == 0) {
        $single_target_file = $target_dir . basename($_FILES["singleFile"]["name"]);
        $single_imageFileType = strtolower(pathinfo($single_target_file, PATHINFO_EXTENSION));

        $check = getimagesize($_FILES["singleFile"]["tmp_name"]);
        if ($check !== false) {
            echo "Single file is an image - " . $check["mime"] . ".<br>";
        } else {
            echo "Single file is not an image.<br>";
            $overallUploadOk = 0;
        }

        if ($_FILES["singleFile"]["size"] > 500000) {
            echo "Sorry, single file is too large.<br>";
            $overallUploadOk = 0;
        }

        if (!in_array($single_imageFileType, ["jpg", "jpeg", "png", "gif"])) {
            echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed for the single file.<br>";
            $overallUploadOk = 0;
        }
    } else {
        echo "No single file was uploaded or an error occurred.<br>";
        $overallUploadOk = 0;
     }

    
      // Nếu không có lỗi nào xảy ra, tiến hành upload
    if ($overallUploadOk == 1) {
        // Upload file đơn lẻ
        if (move_uploaded_file($_FILES["singleFile"]["tmp_name"], $single_target_file)) {
            echo "The single file " . htmlspecialchars(basename($_FILES["singleFile"]["name"])) . " has been uploaded.<br>";

           


            $product_name = test_input($_POST['product_name']);
            $sku = test_input($_POST['sku']);
            $price = test_input($_POST['price']);
    
            $singleFileName = $_FILES["singleFile"]["name"];
    
            
            $sql = "UPDATE products SET product_name = :product_name, sku = :sku, price =:price, featured_image = :featured_image, date = NOW() WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(":product_name", $product_name);
            $stmt->bindParam(":sku", $sku);
            $stmt->bindParam(":price", $price);
            $stmt->bindParam(":featured_image", $singleFileName);
            $stmt->bindParam(":id", $product_id);
            $stmt->execute();
        } else {
            echo "Sorry, there was an error uploading the single file.<br>";
        }

    } else {
        echo "Upload failed due to errors in one or more files.";
    }
       

    }



}else {
    $name = 'Add Product';


}




?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $name ?></title>

    <link rel="stylesheet" href="style.css">
    <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">

    <!-- link semantic ui -->
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.5.0/semantic.min.css" integrity="sha512-KXol4x3sVoO+8ZsWPFI/r5KBVB/ssCGB5tsv2nVOKwLg33wTFP3fmnXa47FdSVIshVTgsYk/1734xSk9aFIa4A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body>
    <br>
    <h1 class="add_property"><?php echo $name ?></h1>
    <form action="" method="post" enctype="multipart/form-data">
        <div class="container_property">
            <div class="ui input">
                <input class="" value="" name="product_name" type="text" placeholder="Product Name ...">
            </div>
            <div class="ui input">
                <input class="" value="" name="sku" type="text" placeholder="SKU">
            </div>
            <div class="ui input ">
                <input class="" value="" name="price" type="text" placeholder="Price">
            </div>
            <img width="100" src="./uploads/<?php echo $singleFileName; ?>">
            <div class="ui input featured_image">
                <input class="" value="" name="singleFile" id="singleFile" type="file">
            </div>
            <div class="ui input">
                <input class="" value="" name="multipleFiles[]" id="multipleFiles" multiple type="file">
            </div>
            <div>
                <a class="ui button" href="index.php">
                    Back
                </a>
                <button type="submit" name="add" class="ui button">
                    <?php echo $name?>
                </button>
            </div>
        </div>
    </form>
</body>
</html>
