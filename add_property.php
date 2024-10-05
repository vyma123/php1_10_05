<?php
require_once 'includes/db.inc.php';
require_once 'includes/functions.php';

$tag =$category = $added_cate =  $added_tag = $exist_cate = $exist_tag ='';


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $category = test_input($_POST["category"]);
    $tag = test_input($_POST["tag"]);


    
        if(empty($category) && empty($tag) ){
            $cateempty_field = 'empty_field';
            $tagempty_field = 'empty_field';
        }else if(get_property($pdo, $category) && get_property($pdo, $tag)){
            $cateempty_field = 'empty_field';
            $tagempty_field = 'empty_field';
        }
        else if(get_property($pdo, $category) ){
            $cateempty_field = 'empty_field';
        }else if(get_property($pdo, $tag)){
            $tagempty_field = 'empty_field';
        }

        $exist_cate = get_property($pdo, $category) ? 'category already exists' : '';
        $exist_tag = get_property($pdo, $tag) ? 'tag already exists' : '';

        
        if (!get_property($pdo, $category) && !empty($category) && empty($tag) && isValidInput($category) ) {
            $type_ = 'category';
            add_property($pdo, $type_, $category);
            $added_cate = 'added category: '.$category. '';
            $category = '';
        }

        if (!get_property($pdo, $tag) && !empty($tag) && empty($category) && isValidInput($tag) ) {
            $type_ = 'tag';
            add_property($pdo, $type_, $tag);
            $added_tag = 'added tag: '.$tag. '';
            $tag = '';
        }

        if (!get_property($pdo, $tag) && !get_property($pdo, $category) && isValidInput($tag) && isValidInput($category)) {
            $type_1 = 'category';
            $type_2 = 'tag';

            add_property($pdo, $type_1, $category);
            add_property($pdo, $type_2, $tag);

            $added_cate = 'added category: '.$category. '';
            $added_tag = 'added tag: '.$tag. '';

            $category = '';
            $tag = '';
        }

        
        if( !empty($category) && !isValidInput($category)){
            $cateempty_field = 'empty_field';
        }
        if( !empty($tag) && !isValidInput($tag)){
            $tagempty_field = 'empty_field';
        }
    
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Property</title>

    <link rel="stylesheet" href="style.css">
    <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">

    <!-- link semantic ui -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.5.0/semantic.min.css" integrity="sha512-KXol4x3sVoO+8ZsWPFI/r5KBVB/ssCGB5tsv2nVOKwLg33wTFP3fmnXa47FdSVIshVTgsYk/1734xSk9aFIa4A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body>
    <br>
    <h1 class="add_property">Add Property</h1>
    <form action="" method="post">
        <div class="container_property">
            <?php echo $added_cate;
                  echo $exist_cate;?>
            <br>
            <?php echo $added_tag;
                  echo $exist_tag;?>
            <div class="ui input">
                <input class="<?= $cateempty_field ?>" value="<?= $category?>" name="category" type="text" placeholder="Category...">
            </div>
            <div class="ui input">
                <input class="<?= $tagempty_field ?>" value="<?= $tag?>" name="tag" type="text" placeholder="Tag...">
            </div>
            <div>
                <a class="ui button" href="index.php">
                    Back
                </a>
                <button name="add" class="ui button">
                    Add
                </button>
            </div>
        </div>
    </form>
</body>
</html>
