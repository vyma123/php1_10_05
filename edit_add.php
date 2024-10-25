<?php 
require_once "includes/db.inc.php";
require_once 'includes/functions.php';

$target_dir = "uploads/";
$uploadOk = 1;

$product_name = $sku = $price = $name = '';


if(isset($_GET['product_id'])){

    $product_id = $_GET['product_id'];
    $name_button = 'Edit Product';

    $query = "SELECT * FROM products WHERE id = :product_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $product_name = test_input($row['product_name']);
    $sku = test_input($row['sku']);
    $price = test_input($row['price']);

    if (empty($_FILES['singleFile']['name'])) {
        $query = "SELECT featured_image FROM products WHERE id = :product_id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->execute();
    
        // Lấy kết quả
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $singleFileName = $row['featured_image'];
        }else{
            $singleFileName = '';
        }
    }


    // if (!isset($_FILES['multipleFiles']['name']) || count(array_filter($_FILES['multipleFiles']['name'])) === 0) {
    //     $query = "SELECT p.name_ FROM product_property pp
    //     JOIN property p ON pp.property_id = p.id
    //     WHERE pp.product_id = :product_id AND p.type_ = 'gallery'";

    //     $stmt = $pdo->prepare($query);
    //     $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
    //     $stmt->execute();
    //     $row = $stmt->fetch(PDO::FETCH_ASSOC);
    //     if ($row) {
    //     $multipleFiles = $row['name_'];
    //     } else {
    //     $multipleFiles = []; // Handle case where no gallery images exist
    //     }

    // }
  
     
    if(isset($_POST['add'])){
    $overallUploadOk = 1;
    $product_name = test_input($_POST['product_name']);
    $sku = test_input($_POST['sku']);
    $price = test_input($_POST['price']);
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
        $overallUploadOk = 0;
     }

      // Nếu không có lỗi nào xảy ra, tiến hành upload
      if ($overallUploadOk == 1) {
    
        // Upload file đơn lẻ
        if (move_uploaded_file(($_FILES["singleFile"]["tmp_name"]), $single_target_file) && !empty($product_name) && !empty($sku) 
            && !empty($price) && isValidInput($product_name) && isValidInput($sku) && isValidInput($price) && numbers_only($price)) {
            echo "The single file " . htmlspecialchars(basename($_FILES["singleFile"]["name"])) . " has been uploaded.<br>";

            $singleFileName = $_FILES["singleFile"]["name"];
    
            $sql = "UPDATE products SET product_name = :product_name, sku = :sku, price =:price, featured_image = :featured_image, date = NOW() WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(":product_name", $product_name);
            $stmt->bindParam(":sku", $sku);
            $stmt->bindParam(":price", $price);
            $stmt->bindParam(":featured_image", $singleFileName);
            $stmt->bindParam(":id", $product_id);
            $stmt->execute();
        }else {
            echo "Sorry, there was an error uploading the single file.<br>";
        }
    }else if(!empty($product_name) && !empty($sku) && !empty($price) && isValidInput($product_name) 
    && isValidInput($sku) && isValidInput($price) && numbers_only($price)){
        $sql = "UPDATE products SET product_name = :product_name, sku = :sku, price =:price, date = NOW() WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":product_name", $product_name);
        $stmt->bindParam(":sku", $sku);
        $stmt->bindParam(":price", $price);
        $stmt->bindParam(":id", $product_id);
        $stmt->execute();
        echo "update successfully";
    } else {
        if(!isValidInput($product_name) && !empty($product_name)){  $empty_name = 'empty_field'; echo "product name don't allow special character <br>";}
        if(empty($product_name)){$empty_name = 'empty_field'; echo 'Fill Product Name <br>  ';}
        if(!isValidInput($sku) && !empty($sku)){  $empty_sku = 'empty_field'; echo "sku don't allow special character <br>";}
        if(empty($sku)){  $empty_sku = 'empty_field'; echo 'Fill sku <br>';}
        if(!isValidInput($price) && !empty($price)){  $empty_price = 'empty_field'; echo "price don't allow special character <br>";}
        if(empty($price)){  $empty_price = 'empty_field'; echo 'Fill price';}
        if(!numbers_only($price)){  $empty_price = 'empty_field'; echo "price just allow number";}
    } 


    if (isset($_FILES['multipleFiles']) && $_FILES['multipleFiles']['error'][0] == 0) {
        // Lấy danh sách ảnh cũ từ database
        $query = "SELECT p.name_ FROM product_property pp
                  JOIN property p ON pp.property_id = p.id
                  WHERE pp.product_id = :product_id AND p.type_ = 'gallery'";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->execute();
        $old_images = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Xóa ảnh cũ khỏi thư mục uploads
        foreach ($old_images as $old_image) {
            $old_image_path = $target_dir . $old_image['name_'];
            if (file_exists($old_image_path)) {
                unlink($old_image_path); // Xóa ảnh cũ
            }
        }
    
        // Xóa các bản ghi ảnh cũ khỏi bảng `product_property`
        $query = "DELETE FROM product_property WHERE product_id = :product_id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->execute();
    
        if (isset($_FILES['multipleFiles']) && $_FILES['multipleFiles']['error'][0] == 0) {
            // Lấy danh sách ảnh cũ từ database
            $query = "SELECT p.name_ FROM product_property pp
                      JOIN property p ON pp.property_id = p.id
                      WHERE pp.product_id = :product_id AND p.type_ = 'gallery'";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
            $stmt->execute();
            $old_images = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Xóa ảnh cũ khỏi thư mục uploads
            foreach ($old_images as $old_image) {
                $old_image_path = $target_dir . $old_image['name_'];
                if (file_exists($old_image_path)) {
                    unlink($old_image_path); // Xóa ảnh cũ
                }
            }
        
            // Xóa các bản ghi ảnh cũ khỏi bảng product_property
            $query = "DELETE FROM product_property WHERE product_id = :product_id";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
            $stmt->execute();
        
            // Xử lý việc upload ảnh mới
            foreach ($_FILES['multipleFiles']['name'] as $key => $name) {
                if ($_FILES['multipleFiles']['error'][$key] == 0) {
                    $target_file = $target_dir . basename($name);
                    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
                    // Validate image
                    $check = getimagesize($_FILES['multipleFiles']['tmp_name'][$key]);
                    if ($check !== false && in_array($imageFileType, ["jpg", "jpeg", "png", "gif"]) && $_FILES['multipleFiles']['size'][$key] <= 5000000) {
                        // Upload ảnh mới
                        if (move_uploaded_file($_FILES['multipleFiles']['tmp_name'][$key], $target_file)) {
                            // Lưu ảnh mới vào bảng property
                            $query = "INSERT INTO property (name_, type_) VALUES (:name_, 'gallery')";
                            $stmt = $pdo->prepare($query);
                            $stmt->bindParam(':name_', $name);
                            $stmt->execute();
        
                            // Lấy ID của ảnh mới vừa thêm
                            $property_id = $pdo->lastInsertId();
        
                            // Thêm liên kết giữa sản phẩm và ảnh mới vào bảng product_property
                            $query = "INSERT INTO product_property (product_id, property_id) VALUES (:product_id, :property_id)";
                            $stmt = $pdo->prepare($query);
                            $stmt->bindParam(':product_id', $product_id);
                            $stmt->bindParam(':property_id', $property_id);
                            $stmt->execute();
                        } else {
                            echo "Sorry, there was an error uploading file: {$name}.<br>";
                        }
                    } else {
                        echo "Invalid file: {$name}. Only JPG, JPEG, PNG, GIF files under 500KB are allowed.<br>";
                    }
                }
            }
        }
    }
    

//viết code multipleimages dưới đây 

    

    }

}else {
    $name_button = 'Add Product';
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
     <style>
        .images_box{
    display: flex;
    flex-direction: row !important;
}
.images_box .images{
    border: #000 1px solid !important;
    margin: 1px;
}
     </style>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.5.0/semantic.min.css" integrity="sha512-KXol4x3sVoO+8ZsWPFI/r5KBVB/ssCGB5tsv2nVOKwLg33wTFP3fmnXa47FdSVIshVTgsYk/1734xSk9aFIa4A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body>
    <br>
    <h1 class="add_property"><?php echo $name_button ?></h1>
    <form action="" method="post" enctype="multipart/form-data">
        <div class="container_property">
            <div class="ui input">
                <input class="<?php echo $empty_name ?>" value="<?php echo $product_name?>" name="product_name" type="text" placeholder="Product Name ...">
            </div>
            <div class="ui input">
                <input class="<?php echo $empty_sku ?>" value="<?php echo $sku?>" name="sku" type="text" placeholder="SKU">
            </div>
            <div class="ui input ">
                <input class="<?php echo $empty_price ?>" value="<?php echo $price?>" name="price" type="text" placeholder="Price">
            </div>
            <img height="80" src="./uploads/<?php echo $singleFileName; ?>">
            <div class="ui input featured_image">
                <input class="" value="" name="singleFile" id="singleFile" type="file">
            </div>
            <div class="images_box">
                    <?php 
            $query = "SELECT p.name_ FROM product_property pp
                    JOIN property p ON pp.property_id = p.id
                    WHERE pp.product_id = :product_id AND p.type_ = 'gallery'";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
            $stmt->execute();
            $galleryImages = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($galleryImages as $image) {?> 

            <img class="images" height="80" src="./uploads/<?= $image['name_'] ?>">

            <?php }?>
            </div>
            <div class="ui input">
                <input class="" value="" name="multipleFiles[]" id="multipleFiles" multiple type="file">
            </div>
            <div>
                <a class="ui button" href="index.php">
                    Back
                </a>
                <button type="submit" name="add" class="ui button">
                    <?php echo $name_button?>
                </button>
            </div>
        </div>
    </form>
</body>
</html>


