<?php

require_once 'includes/db.inc.php';
require_once 'includes/functions.php';

$tag =$category = $added_cate =  $added_tag = $exist_cate = $exist_tag = $cat_err = $tag_err ='';
$new_categories = $exist_categories = $new_tags = $exist_tags =  [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $category = test_input($_POST["category"]);
    $tag = test_input($_POST["tag"]);


    $categories = explode(',', $category);
    $tags = explode(',', $tag);
    $categories = array_map('trim', $categories);
    $tags = array_map('trim', $tags);
    $type_1 = 'category';
    $type_2 = 'tag';
    
   
        if(empty($category) && empty($tag) ){
            $cateempty_field = 'empty_field';
            $tagempty_field = 'empty_field';
        }else if(get_property($pdo, $category, $type_1) && get_property($pdo, $tag,$type_2)){
            $cateempty_field = 'empty_field';
            $tagempty_field = 'empty_field';
        }
        else if(get_property($pdo, $category,$type_1) ){
            $cateempty_field = 'empty_field';
        }else if(get_property($pdo, $tag,$type_2)){
            $tagempty_field = 'empty_field';
        }

        $exist_cate = get_property($pdo, $category,$type_1) ? 'category already exists' : '';
        $exist_tag = get_property($pdo, $tag,$type_2) ? 'tag already exists' : ''; 

        if(!isValidInput($category) && isValidInput($tag)){
           $cat_err =  $category;
           $tag_err =  $tag;

        }

        if(!isValidInput($tag) && isValidInput($category) ){
            $tag_err =  $tag;
           $cat_err =  $category;
         }

         if(!isValidInput($tag) && !isValidInput($category)){
            $tag_err =  $tag;
            $cat_err =  $category;
         }

        if(isValidInput($category)){
            foreach($categories as $category){
                $category = trim($category);
                if(!empty($category) && empty($tag)){
                    if (!get_property($pdo, $category,$type_1)) {
                        add_property($pdo, $type_1, $category);
                        $new_categories[] = $category;
                        $cat_err = '';
                    }else{
                        $exist_categories[] = $category;
                    }
                }
            }
        }

        if(isValidInput($tag)){
            foreach($tags as $tag){
                $tag = trim($tag);
                if(!empty($tag) && empty($category)){
                    if (!get_property($pdo, $tag,$type_2) ) {
                        add_property($pdo, $type_2, $tag);
                        $new_tags[] = $tag;
                        $tag_err = '';
                    }else{
                        $exist_tags[] = $tag;

                    }
                }
            }
        }

       

        if (isValidInput($tag) && isValidInput($category)) {
            foreach ($categories as $category) {
                $category = trim($category);
                array_map(function($tag) use ($pdo, $category, &$new_categories, &$new_tags, &$exist_categories, &$exist_tags) {
                    $type_1 = 'category';
                    $tag = trim($tag);
                    if (!empty($tag) && !empty($category)) {
                        $type_2 = 'tag';
                        if(!get_property($pdo, $category,$type_1)){
                            add_property($pdo, $type_1, $category);
                            $new_categories[] = $category;

                        }else {
                            $exist_categories[] = $category;

                        }
                        if(!get_property($pdo, $tag,$type_2)){
                            add_property($pdo, $type_2, $tag);
                            $new_tags[] = $tag;
                        }else {
                            $exist_tags[] = $tag;

                        }
                 
                    
                    }
                }, $tags); 
            }
        }

        

        
        if( !empty($category) && !isValidInput($category)){
            $cateempty_field = 'empty_field';
        }
        if( !empty($tag) && !isValidInput($tag)){
            $tagempty_field = 'empty_field';
        }

    
        $added_cate = set_message($new_categories, $exist_categories, 'added categories', 'Category already exists');
        $added_tag = set_message($new_tags, $exist_tags, 'added tags', 'tag already exists');
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
            <div class="ui input flex_property">
                <label for="category">Category: </label>
                <input id="category" class="<?= $cateempty_field ?>" value="<?= $cat_err?>" name="category" type="text" placeholder="Category1, Category2, ...">
            </div>
            <div class="ui input flex_property">
               <label for="tag">Tag: </label>
                <input id="tag" class="<?= $tagempty_field ?>" value="<?= $tag_err?>" name="tag" type="text" placeholder="Tag1, Tag2, ...">
            </div>
            <div class="button_property">
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
