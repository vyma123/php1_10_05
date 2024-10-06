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


function get_property(object $pdo, string $name_) {
    $query = "SELECT name_ FROM property WHERE name_ = :name_;";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":name_", $name_);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result;

}