<?php 

declare(strict_types=1);

function isValidInput($input){
    return preg_match('/^[a-z0-9 .,\-]+$/i', $input);
}

function add_property(object $pdo, string $type_, string $name) {
    $query = "INSERT INTO property (type_, name_ ) VALUES (:type_, :name_);";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":type_", $type_);
    $stmt->bindParam(":name_", $name);
    $stmt->execute();
}

// function get_property(object $pdo, string $name_) {
//     $query = "SELECT name_ FROM property WHERE name_ = :name_;";
//     $stmt = $pdo->prepare($query);
//     $stmt->bindParam(":name_", $name_);
//     $stmt->execute();
//     $result = $stmt->fetch(PDO::FETCH_ASSOC);
//     return $result;

// }

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