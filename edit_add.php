<?php 
require_once "includes/db.inc.php";
require_once 'includes/functions.php';

$target_dir = "uploads/";
$uploadOk = 1;

$product_name = $sku = $price = $name = '';
$selected_tags = isset($_POST['tags']) ? $_POST['tags'] : [];
$selected_categories = isset($_POST['categories']) ? $_POST['categories'] : [];
$execute_success = '';
$productId = null; 

if(isset($_GET['product_id'])){

    $product_id = $_GET['product_id'];
    $name_button = 'Edit Product';

    if (!is_numeric($product_id)) {
        header("Location: index.php");
        exit; 
    }

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
    
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $singleFileName = $row['featured_image'];
        }else{
            $singleFileName = '';
        }
    }
  

    if(isset($_POST['add'])){

    $overallUploadOk = 1;
    $product_name = test_input($_POST['product_name']);
    $sku = test_input($_POST['sku']);
    $price = test_input($_POST['price']);

    if (isset($_FILES["singleFile"]) && $_FILES["singleFile"]["error"] == 0) {
        $single_target_file = $target_dir . basename($_FILES["singleFile"]["name"]);
        $single_imageFileType = strtolower(pathinfo($single_target_file, PATHINFO_EXTENSION));

        $check = getimagesize($_FILES["singleFile"]["tmp_name"]);
        if ($check !== false) {
            echo "Single file is an image - " . $check["mime"] . ".<br>";
        } else {
            echo "Single file is not an image.<br>";
            $err_image = 'empty_field';
            $overallUploadOk = 0;
        }

        if ($_FILES["singleFile"]["size"] > 500000) {
            echo "Sorry, single file is too large.<br>";
            $err_image = 'empty_field';
            $overallUploadOk = 0;
        }

        if (!in_array($single_imageFileType, ["jpg", "jpeg", "png", "gif"])) {
            echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed for the single file.<br>";
            $err_image = 'empty_field';
            $overallUploadOk = 0;
        }
    } else {
        $overallUploadOk = 0;
     }

      if ($overallUploadOk == 1) {
    
        if (move_uploaded_file(($_FILES["singleFile"]["tmp_name"]), $single_target_file) && !empty($product_name) && !empty($sku) 
            && !empty($price) && isValidInput($product_name) && isValidInput($sku) && isValidNumberWithDotInput($price) && numbers_only($price)) {
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
    && isValidInput($sku) && isValidNumberWithDotInput($price) && numbers_only($price) ){
        $sql = "UPDATE products SET product_name = :product_name, sku = :sku, price =:price, date = NOW() WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":product_name", $product_name);
        $stmt->bindParam(":sku", $sku);
        $stmt->bindParam(":price", $price);
        $stmt->bindParam(":id", $product_id);
        $stmt->execute();

        foreach ($_FILES['multipleFiles']['name'] as $key => $name) {
            if (!$_FILES['multipleFiles']['error'][$key] == 0) {
                if(isset($_FILES["singleFile"]) && !$_FILES["singleFile"]["error"] == 0 && isset($_FILES['multipleFiles']) && 
                 !$_FILES['multipleFiles']['error'] == 0 ){
                    echo "update successfully";
                }
            }
        }
    } else {
        if(!isValidInput($product_name) && !empty($product_name)){  $empty_name = 'empty_field'; echo "product name don't allow special character <br>";}
        if(empty($product_name)){$empty_name = 'empty_field'; echo 'Fill Product Name <br>  ';}
        if(!isValidInput($sku) && !empty($sku)){  $empty_sku = 'empty_field'; echo "sku don't allow special character <br>";}
        if(empty($sku)){  $empty_sku = 'empty_field'; echo 'Fill sku <br>';}
        if(!isValidNumberWithDotInput($price) && !empty($price)){  $empty_price = 'empty_field'; echo "price don't allow special character <br>";}
        if(empty($price)){  $empty_price = 'empty_field'; echo 'Fill price<br>';}
        if(!numbers_only($price) && isValidNumberWithDotInput($price)){  $empty_price = 'empty_field'; echo "price just allow number";}
    } 

      
        if (isset($_FILES['multipleFiles']) && $_FILES['multipleFiles']['error'][0] == 0) {

            $query = "DELETE prop
            FROM property AS prop
            JOIN product_property AS pp ON prop.id = pp.property_id
            JOIN products AS p ON pp.product_id = p.id
            WHERE p.id = :product_id AND prop.type_ = 'gallery';";
            $relatedStmt = $pdo->prepare($query);
            $relatedStmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
            $relatedStmt->execute();

            $query = "DELETE FROM product_property WHERE product_id = :product_id";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
            $stmt->execute();

           

            $query = "SELECT p.name_ FROM product_property pp
                      JOIN property p ON pp.property_id = p.id
                      WHERE pp.product_id = :product_id AND p.type_ = 'gallery'";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
            $stmt->execute();
            $old_images = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $uploadSuccess = false;
            
            foreach ($_FILES['multipleFiles']['name'] as $key => $name) {
         
                if ($_FILES['multipleFiles']['error'][$key] == 0) {

                    $target_file = $target_dir . basename($name);
                    $imageFileTypes = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
                    $check = getimagesize($_FILES['multipleFiles']['tmp_name'][$key]);
                    
                    if ($check !== false && in_array($imageFileTypes, ["jpg", "jpeg", "png", "gif"]) && $_FILES['multipleFiles']['size'][$key] <= 5000000) {

                        if (move_uploaded_file($_FILES['multipleFiles']['tmp_name'][$key], $target_file)) {
                            
                            $query = "INSERT INTO property (name_, type_) VALUES (:name_, 'gallery')";
                            $stmt = $pdo->prepare($query);
                            $stmt->bindParam(':name_', $name);
                            $stmt->execute();
                            
                            $property_id = $pdo->lastInsertId();
                            
                            $query = "INSERT INTO product_property (product_id, property_id) VALUES (:product_id, :property_id)";
                            $stmt = $pdo->prepare($query);
                            $stmt->bindParam(':product_id', $product_id);
                            $stmt->bindParam(':property_id', $property_id);
                            $stmt->execute();
                            $uploadSuccess = true;


                        } else {
                            echo "Sorry, there was an error uploading file: {$name}.<br>";
                        }
                    } else {
                        echo "Invalid file: {$name}. Only JPG, JPEG, PNG, GIF files under 500KB are allowed.<br>";
                        $err_multiple_images = 'empty_field';
                        
                    }
                }

            }
            if ($uploadSuccess) {
                echo "Update successfully";
            }
        }   
        
    
    if(empty($checked_tag)){
        $query = " DELETE product_property 
        FROM product_property 
        JOIN property ON product_property.property_id = property.id 
        WHERE product_property.product_id = :product_id 
        AND property.type_ = 'tag'";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['product_id' => $product_id]);
    }

    if (!empty($selected_categories)) {
        $query = "DELETE pp FROM product_property pp
            JOIN property p ON pp.property_id = p.id
            WHERE pp.product_id = :product_id AND p.type_ = 'category'";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
    } else {
        $query = "DELETE pp FROM product_property pp
            JOIN property p ON pp.property_id = p.id
            WHERE pp.product_id = :product_id AND p.type_ = 'category'";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':product_id', $product_id);
            $stmt->execute();
    }

     if (!empty($selected_tags) && !empty($product_name) && !empty($sku) && !empty($price)) {
        $query = "DELETE pp FROM product_property pp
            JOIN property p ON pp.property_id = p.id
            WHERE pp.product_id = :product_id AND p.type_ = 'tag'";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
    } else {
        $query = "DELETE pp FROM product_property pp
        JOIN property p ON pp.property_id = p.id
        WHERE pp.product_id = :product_id AND p.type_ = 'tag'";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
    }

        $query = "INSERT INTO product_property (product_id, property_id) VALUES (:product_id, :property_id)";
        $stmt = $pdo->prepare($query);
        foreach($selected_tags as $tag_id) {
            $stmt->execute([
                'product_id' => $product_id,
                'property_id' => $tag_id
            ]);
        }

        $query = "INSERT INTO product_property (product_id, property_id) VALUES (:product_id, :property_id)";
        $stmt = $pdo->prepare($query);
        foreach($selected_categories as $category_id) {
            $stmt->execute([
                'product_id' => $product_id,
                'property_id' => $category_id
            ]);
        }

       
    }
    $query = "SELECT property_id FROM product_property WHERE product_id = :product_id";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['product_id' => $product_id]);
    $selected_tags = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $query = "SELECT property_id FROM product_property WHERE product_id = :product_id";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['product_id' => $product_id]);
    $selected_categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

    }

else 
    {
        $name_button = 'Add Product';

        $overallUploadOk = 1;
        $overallUploadOk2 = 1;
        $uploadDir = "uploads";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $uploadedImages = [];   
        $uploadedImages2 = [];   



        if (!empty($_POST['uploadedImages'])) {
            $uploadedImages = json_decode($_POST['uploadedImages'], true);
        }
        if (!empty($_POST['uploadedImages2'])) {
            $uploadedImages2 = json_decode($_POST['uploadedImages2'], true);
        }

        

        if(isset($_POST['add'])){
            $product_name = test_input($_POST['product_name']);
            $sku = test_input($_POST['sku']);
            $price = test_input($_POST['price']);
            $image = $_FILES['imagefile'];
            $images = $_FILES['imagefiles'];

            handleUpload($image, $target_dir);
            handleMultipleUploads($images, $target_dir);
          
             if($overallUploadOk == 1 && !empty($image['name'])){
                  $imageTmpName = $image['tmp_name'];
                  $targetFilePath = $uploadDir . '/' . basename($image['name']);

                foreach ($uploadedImages as $oldImage) {
                    if (file_exists($oldImage)) {
                        unlink($oldImage);
                    }
                }

                $uploadedImages = []; 

                if (move_uploaded_file($imageTmpName, $targetFilePath)) {
                    $uploadedImages[] = $targetFilePath; 
                } else {
                    echo "<p style='color: red;'>Failed to upload image: {$image['name']}</p>";
                }
             }

             if (!empty($images['name'][0]) && $overallUploadOk2 == 1) {
                // Clear previous images from the array and delete from the "uploads" directory
                foreach ($uploadedImages2 as $oldImage) {
                    if (file_exists($oldImage)) {
                        unlink($oldImage);
                    }
                }
                $uploadedImages2 = []; 
        
                // Process each uploaded image
                foreach ($images['name'] as $key => $imageName) {
                    if ($images['error'][$key] === 0) {
                        $imageTmpName = $images['tmp_name'][$key];
                        $targetFilePath2 = $uploadDir . '/' . basename($imageName);
        
                        // Save the new image to "uploads"
                        if (move_uploaded_file($imageTmpName, $targetFilePath2)) {
                            $uploadedImages2[] = $targetFilePath2; 
                        } else {
                            echo "<p style='color: red;'>Failed to upload image: {$imageName}</p>";
                        }
                    }
                }
            }


            if(!empty($product_name) && !empty($targetFilePath) && !empty($sku) && !empty($price)
            && isValidInput($product_name) 
            && isValidInput($sku) && isValidNumberWithDotInput($price) && numbers_only($price)){
            try {

                $imageFile1 =  htmlspecialchars(basename($targetFilePath));

                $stmt = $pdo->prepare("INSERT INTO products (product_name,sku, price, featured_image, date) 
                                    VALUES (:product_name, :sku, :price, :featured_image, NOW())");
                $stmt->bindParam(':product_name', $product_name);
                $stmt->bindParam(':sku', $sku);
                $stmt->bindParam(':price', $price);
                $stmt->bindParam(':featured_image', $imageFile1); 

                if ($stmt->execute()) {
                    echo "<p style='color: green;'>Product saved to database successfully!</p>";
                } else {
                    echo "<p style='color: red;'>Failed to save product to database.</p>";
                }
                $execute_success = 'successfully';

               
            } catch (PDOException $e) {
                echo "<p style='color: red;'>Database error: " . $e->getMessage() . "</p>";
            }
            }else if(!empty($uploadedImages) && !empty($product_name) && !empty($sku) && !empty($price)
                    && isValidInput($product_name) 
                    && isValidInput($sku) && isValidNumberWithDotInput($price) && numbers_only($price)) {
                
                    foreach ($uploadedImages as $image) {
                        $imageFile =  htmlspecialchars(basename($image));
                    
                    $stmt = $pdo->prepare("INSERT INTO products (product_name,sku, price, featured_image, date) 
                                        VALUES (:product_name, :sku, :price, :featured_image, NOW())");
                    $stmt->bindParam(':product_name', $product_name);
                    $stmt->bindParam(':sku', $sku);
                    $stmt->bindParam(':price', $price);
                    $stmt->bindParam(':featured_image', $imageFile); 
                    if ($stmt->execute()) {
                        echo "<p style='color: green;'>Product saved to database successfully!</p>";
                    } else {
                        echo "<p style='color: red;'>Failed to save product to database.</p>";
                    }
                    $execute_success = 'successfully';

                    }
            }else {
                if(!isValidInput($product_name) && !empty($product_name)){  $empty_name = 'empty_field'; echo "product name don't allow special character <br>";}
                if(empty($product_name)){$empty_name = 'empty_field'; echo 'Fill Product Name <br>  ';}
                if(!isValidInput($sku) && !empty($sku)){  $empty_sku = 'empty_field'; echo "sku don't allow special character <br>";}
                if(empty($sku)){  $empty_sku = 'empty_field'; echo 'Fill sku <br>';}
                if(!isValidNumberWithDotInput($price) && !empty($price)){  $empty_price = 'empty_field'; echo "price don't allow special character <br>";}
                if(empty($price)){  $empty_price = 'empty_field'; echo 'Fill price<br>';}
                if(!numbers_only($price) && isValidNumberWithDotInput($price)){  $empty_price = 'empty_field'; echo "price just allow number";}
                if(empty($uploadedImages)){  $empty_image = 'empty_field'; echo "please upload image";}


            }
            
            
            if ((!empty($product_name) && !empty($targetFilePath) && !empty($sku) && !empty($price)
            && isValidInput($product_name) 
            && isValidInput($sku) && isValidNumberWithDotInput($price) && numbers_only($price)) ||(
            !empty($uploadedImages) && !empty($product_name) && !empty($sku) && !empty($price)
            && isValidInput($product_name) 
            && isValidInput($sku) && isValidNumberWithDotInput($price) && numbers_only($price))) {
                try {
                       $productId = $pdo->lastInsertId(); 
        
                        foreach ($uploadedImages2 as $uploadedImage) {
                            $imageFileName = htmlspecialchars(basename($uploadedImage));
                            $propertyStmt = $pdo->prepare("INSERT INTO property (type_, name_) VALUES ('gallery', :name_)");
                            $propertyStmt->bindParam(':name_', $imageFileName);
                            $propertyStmt->execute();
                            $propertyId = $pdo->lastInsertId(); 
        
                            $linkStmt = $pdo->prepare("INSERT INTO product_property (product_id, property_id) VALUES (:product_id, :property_id)");
                            $linkStmt->bindParam(':product_id', $productId);
                            $linkStmt->bindParam(':property_id', $propertyId);
                            $linkStmt->execute();
                        }
        
        
                        $uploadedImages2 = [];
                        $uploadedImages = [];
                        $targetFilePath = [];
                        $targetFilePath2 = [];
                        $product_name = $sku = $price ='';


        
                } catch (PDOException $e) {
                    echo "<p style='color: red;'>Database error: " . $e->getMessage() . "</p>";
                }
            } 
           
            if (!empty($productId)) {

                if(empty($checked_tag)){
                    $query = " DELETE product_property 
                    FROM product_property 
                    JOIN property ON product_property.property_id = property.id 
                    WHERE product_property.product_id = :product_id 
                    AND property.type_ = 'tag'";
                    $stmt = $pdo->prepare($query);
                    $stmt->execute(['product_id' => $productId]);
                }
            
                // Check if $selected_categories is not empty before executing the delete query
                if (!empty($selected_categories)) {
                    $query = "DELETE pp FROM product_property pp
                        JOIN property p ON pp.property_id = p.id
                        WHERE pp.product_id = :product_id AND p.type_ = 'category'";
                    $stmt = $pdo->prepare($query);
                    $stmt->bindParam(':product_id', $productId);
                    $stmt->execute();
                } else {
                    $query = "DELETE pp FROM product_property pp
                        JOIN property p ON pp.property_id = p.id
                        WHERE pp.product_id = :product_id AND p.type_ = 'category'";
                        $stmt = $pdo->prepare($query);
                        $stmt->bindParam(':product_id', $productId);
                        $stmt->execute();
                }
            
                 // Check if $selected_categories is not empty before executing the delete query
                 if (!empty($selected_tags) && !empty($product_name) && !empty($sku) && !empty($price)) {
                    $query = "DELETE pp FROM product_property pp
                        JOIN property p ON pp.property_id = p.id
                        WHERE pp.product_id = :product_id AND p.type_ = 'tag'";
                    $stmt = $pdo->prepare($query);
                    $stmt->bindParam(':product_id', $productId);
                    $stmt->execute();
                } else {
                    $query = "DELETE pp FROM product_property pp
                    JOIN property p ON pp.property_id = p.id
                    WHERE pp.product_id = :product_id AND p.type_ = 'tag'";
                    $stmt = $pdo->prepare($query);
                    $stmt->bindParam(':product_id', $productId);
                    $stmt->execute();
                }
            
                    $query = "INSERT INTO product_property (product_id, property_id) VALUES (:product_id, :property_id)";
                    $stmt = $pdo->prepare($query);
                    foreach($selected_tags as $tag_id) {
                        $stmt->execute([
                            'product_id' => $productId,
                            'property_id' => $tag_id
                        ]);
                    }
            
                    $query = "INSERT INTO product_property (product_id, property_id) VALUES (:product_id, :property_id)";
                    $stmt = $pdo->prepare($query);
                    foreach($selected_categories as $category_id) {
                        $stmt->execute([
                            'product_id' => $productId,
                            'property_id' => $category_id
                        ]);
                    }
            }
            }
    }

    $query = "SELECT id, name_ FROM property WHERE type_ = 'tag'";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $tags = $stmt->fetchAll();

    $query = "SELECT id, name_ FROM property WHERE type_ = 'category'";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $categories = $stmt->fetchAll();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $name ?></title>

    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="styles/style2.css">

    <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">
    
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
                <input id="price" class="<?php echo $empty_price ?>" value="<?php echo $price?>" name="price" type="text" placeholder="Price">
            </div>
            <div class="ui">
                <?php if(isset($product_id)){?>
                <img height="50" src="./uploads/<?php echo $singleFileName; ?>">
                <?php }else{
                    foreach ($uploadedImages as $image) {
                        echo "<img src='" . htmlspecialchars($image) . "' height='50' alt='Uploaded Image'>";
                    }
                }
                ?>                  
            </div>
            <div class="ui input">
                <?php if(isset($product_id)){ ?>
                <input class="<?= $empty_singleFile?>" height="50" class="<?= $err_image ?>" value="" name="singleFile" id="singleFile" type="file">
                <?php }else {?>
                    <input class="<?php echo $empty_image?>" type="file" name="imagefile">
                    <input type="hidden" name="uploadedImages" value='<?= htmlspecialchars(json_encode($uploadedImages)) ?>'>
                  <?php }  ?>

            </div>
            <div class="images_box ui">
            <?php 
            if(isset($product_id)){
            $query = "SELECT p.name_ FROM product_property pp
                    JOIN property p ON pp.property_id = p.id
                    WHERE pp.product_id = :product_id AND p.type_ = 'gallery'";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
            $stmt->execute();
            $galleryImages = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($galleryImages as $image) {?> 
            <img  height="50" class="images"  src="./uploads/<?= $image['name_'] ?>">
            <?php }}else {
                    if (!empty($uploadedImages2)) {
                        foreach ($uploadedImages2 as $image) {
                            echo "<img src='" . htmlspecialchars($image) . "' height='50' alt='Uploaded Image'>";
                        }
                    }
                 }?>
            </div>
            <div class="ui input">
            <?php if(isset($product_id)){ ?>
                <input  class="<?= $err_multiple_images?>" value="" name="multipleFiles[]" id="multipleFiles" multiple type="file">
                <?php }else {?>
                  <input class="<?php echo $err_image2?>" type="file" name="imagefiles[]" multiple>
                   <input type="hidden" name="uploadedImages2" value='<?= htmlspecialchars(json_encode($uploadedImages2)) ?>'>
                    <?php }  ?>
            </div>
        <div class="box_prperty">
            <div class="select-group">
            <div class="checkbox-group_flex">
             <p class="property_name">Category</p>
             <p>:</p>
            </div>
        <select name="categories[]" multiple class="select_property">
            <?php if($categories) {
                foreach ($categories as $category){
                    // Check if the category is selected
                    $selected_category = in_array($category['id'], $selected_categories) && empty($productId) ? 'selected' : '';
            ?>
            <option value="<?= htmlspecialchars($category['id']) ?>" <?= $selected_category ?>>
                <?= htmlspecialchars($category['name_']) ?>
            </option>
            <?php }} ?>
        </select>
    </div>
 
    <div class="select-group">
        <div class="checkbox-group_flex">
            <p class="property_name">Tags</p>
            <p>:</p>
        </div>
        <select name="tags[]" multiple class="select_property">
            <?php if($tags) {
                foreach ($tags as $tag){
                    // Check if the tag is selected
                    $selected_tag = in_array($tag['id'], $selected_tags) && empty($productId) ? 'selected' : '';
            ?>
            <option value="<?= htmlspecialchars($tag['id']) ?>" <?= $selected_tag ?>>
                <?= htmlspecialchars($tag['name_']) ?>
            </option>
            <?php }} ?>
        </select>
    </div>
</div>

        
        </div>
            <div class="button_edit_add">
                <a class="ui button" href="index.php">
                    Back
                </a>
                <button  type="submit" name="add" class="ui button">
                    <?php echo $name_button?>
                </button>
            </div>
        </div>
    </form>
    <script src="script.js"></script>
</body>
</html>