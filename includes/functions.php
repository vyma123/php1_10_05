<?php 

declare(strict_types=1);

function isValidInput($input){
    return preg_match('/^[\p{L}0-9 .,–\-]+$/u', $input);
}

function isValidNumberWithDotInput($input) {
    return preg_match('/^[0-9.]+$/', $input);
}

function add_property(object $pdo, string $type_, string $name) {
    $query = "INSERT INTO property (type_, name_ ) VALUES (:type_, :name_);";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":type_", $type_);
    $stmt->bindParam(":name_", $name);
    $stmt->execute();
}

function deleteProductPropertyByType($pdo, $product_id, $type) {
    $query = "DELETE pp FROM product_property pp
              JOIN property p ON pp.property_id = p.id
              WHERE pp.product_id = :product_id AND p.type_ = :type";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':product_id', $product_id);
    $stmt->bindParam(':type', $type);
    $stmt->execute();
}

function get_property(object $pdo, string $name_, string $type_) {
    $query = "SELECT name_ FROM property WHERE type_ = :type_ AND name_ = :name_;";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":name_", $name_);
    $stmt->bindParam(":type_", $type_);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result;
}



function set_message(array $new_items, array $existing_items, string $new_message, string $exist_message) {
    if (!empty($new_items)) {
        return $new_message;
    }
    if (!empty($existing_items)) {
        return $exist_message;
    }
    return '';
}

function select_all_products(object $pdo)  {
    $query = "SELECT * FROM products";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $pdo=null;
    $stmt=null;
    return $results;
}

function uploadFileds($files){
    highlight_string("<?php ". var_export($files, true). ";?>");
    if($files['files']['name'][0] == ""){
        return "Please select at least one file";
    }

}

function numbers_only($value)
{
    return preg_match('/^([0-9].*)$/', $value);
}

function handleUpload($file, $target_dir) {
    global $overallUploadOk, $err_image; 

    if (isset($file) && $file["error"] == 0) {
        $target_file = $target_dir . basename($file["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        $check = getimagesize($file["tmp_name"]);
        if ($check !== false) {
            echo '';
        } else {
            echo "File is not an image.<br>";
            $err_image = 'empty_field';
            $overallUploadOk = 0;
            return false;
        }

        if ($file["size"] > 500000) {
            echo "Sorry, file is too large.<br>";
            $err_image = 'empty_field';
            $overallUploadOk = 0;
            return false;
        }

        if (!in_array($imageFileType, ["jpg", "jpeg", "png", "gif"])) {
            echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.<br>";
            $err_image = 'empty_field';
            $overallUploadOk = 0;
            return false;
        }

        // Nếu tất cả các kiểm tra đều OK, trả về đường dẫn tệp đã tải lên
        return $target_file;

    } else {
        $overallUploadOk = 0;
        return false;
    }
}

function handleMultipleUploads($files, $target_dir) {
    global $overallUploadOk2, $err_image2; 

    $uploaded_files = []; 
    $failed_files = [];   

    foreach ($files["name"] as $key => $name) {
        if ($files["error"][$key] == 0) {
            $target_file = $target_dir . basename($name);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            $check = getimagesize($files["tmp_name"][$key]);
            if ($check === false) {
                $failed_files[] = $name . " (not an image)";
                $err_image2 = 'empty_field';
                $overallUploadOk2 = 0;
                continue;
            }

            if ($files["size"][$key] > 1000000) {
                $failed_files[] = $name . " (file too large)";
                $err_image2 = 'empty_field';
                $overallUploadOk2 = 0;
                continue;
            }

            if (!in_array($imageFileType, ["jpg", "jpeg", "png", "gif"])) {
                $failed_files[] = $name . " (invalid file format)";
                $err_image2 = 'empty_field';
                $overallUploadOk2 = 0;
                continue;
            }
        } else {
            $overallUploadOk2 = 0;
        }
    }

    if (!empty($uploaded_files)) {
        echo "The following files have been uploaded: " . implode(", ", $uploaded_files) . "<br>";
    }

    if (!empty($failed_files)) {
        echo "The following files could not be uploaded: " . implode(", ", $failed_files) . "<br>";
    }

    return $uploaded_files; // Return an array of successfully uploaded file paths
}


function getRecordCount($pdo, $searchTermLike, $category = null, $tag = null, $date_from = null, 
                        $date_to = null, $price_from = null, $price_to = null) {
    $query = "SELECT COUNT(DISTINCT products.id) FROM products";
    $conditions = ["product_name LIKE :search_term"];
    $params = [':search_term' => $searchTermLike];
    
    if ($category) {
        $query .= " JOIN product_property pp1 ON products.id = pp1.product_id AND pp1.property_id = :category";
        $params[':category'] = $category;
    }
    if ($tag) {
        $query .= " JOIN product_property pp2 ON products.id = pp2.product_id AND pp2.property_id = :tag";
        $params[':tag'] = $tag;
    }

    if ($date_from && $date_to) {
        $conditions[] = "date BETWEEN :date_from AND :date_to";
        $params[':date_from'] = $date_from;
        $params[':date_to'] = $date_to;
    }

    if ($price_from && $price_to) {
        $conditions[] = "price BETWEEN :price_from AND :price_to";
        $params[':price_from'] = $price_from;
        $params[':price_to'] = $price_to;
    }

    if (!empty($conditions)) {
        $query .= " WHERE " . implode(" AND ", $conditions);
    }

    $stmt = $pdo->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $stmt->execute();
    return $stmt->fetchColumn();
}

// function getSortedProducts($pdo, $searchTermLike, $category = null, $tag = null, $date_from = null, 
//                            $date_to = null, $price_from = null, $price_to = null) {
//     $query = "SELECT DISTINCT products.* FROM products";
//     $conditions = ["product_name LIKE :search_term"];
//     $params = [':search_term' => $searchTermLike];

//     if ($category) {
//         $query .= " JOIN product_property pp1 ON products.id = pp1.product_id AND pp1.property_id = :category";
//         $params[':category'] = $category;
//     }
//     if ($tag) {
//         $query .= " JOIN product_property pp2 ON products.id = pp2.product_id AND pp2.property_id = :tag";
//         $params[':tag'] = $tag;
//     }

//     if ($date_from && $date_to) {
//         $conditions[] = "date BETWEEN :date_from AND :date_to";
//         $params[':date_from'] = $date_from;
//         $params[':date_to'] = $date_to;
//     }

//     if ($price_from && $price_to) {
//         $conditions[] = "price BETWEEN :price_from AND :price_to";
//         $params[':price_from'] = $price_from;
//         $params[':price_to'] = $price_to;
//     }

//     if (!empty($conditions)) {
//         $query .= " WHERE " . implode(" AND ", $conditions);
//     }

//     // Order by the creation date in descending order to get the most recently created products first
//     $query .= " ORDER BY products.date DESC"; // or products.date DESC, depending on your column name

//     $stmt = $pdo->prepare($query);
//     foreach ($params as $key => $value) {
//         $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
//     }
//     $stmt->execute();
    
//     // Fetch all products sorted by creation date
//     return $stmt->fetchAll(PDO::FETCH_ASSOC);
// }
