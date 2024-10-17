<?php 
require_once "includes/db.inc.php";
require_once "includes/functions.php";



// get all products 
$results = select_all_products($pdo);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="style.css">
    <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">




    <!-- link semantic ui -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.5.0/semantic.min.css" integrity="sha512-KXol4x3sVoO+8ZsWPFI/r5KBVB/ssCGB5tsv2nVOKwLg33wTFP3fmnXa47FdSVIshVTgsYk/1734xSk9aFIa4A==" crossorigin="anonymous" referrerpolicy="no-referrer" />



    <title>PHP1</title>
</head>

<body>
    <section class="container">
        <h1>PHP1</h1>
        <div class="product_header">
            <div class="product_header_top">
                <div>
                    <a href="edit_add.php" class="ui primary button">
                        Add product
                    </button>
                    <a href="add_property.php">
                        <button  class="ui button">
                            Add property
                        </button>
                    </a>
                    <button class="ui button">
                        Sync from VillaTheme
                    </button>
                </div>
                <div class="ui icon input">
                    <input type="text" placeholder="Search product...">
                    <i class="inverted circular search link icon"></i>
                </div>
            </div>
            <div class="product_header_bottom">
                <select class="ui dropdown">
                    <option value="0">Date</option>
                    <option value="1">Product name</option>
                    <option value="2">Price</option>
                </select>
                <select class="ui dropdown">
                    <option value="0">ASC</option>
                    <option value="1">DESC</option>
                </select>

                <select class="ui dropdown">
                    <option value="0">Category</option>
                    <option value="1">category1</option>
                </select>
                <select class="ui dropdown">
                    <option value="0">Select Tag</option>
                    <option value="1">tag1</option>
                </select>
                <div class="ui input">
                    <input type="date" id="date_from" name="date_from">
                </div>
                <div class="ui input">
                    <input type="date" id="date_to" name="date_to">
                </div>
                <div class="ui input">
                    <input type="text" id="price_from" name="price_from" placeholder="price from">
                </div>
                <div class="ui input">
                    <input type="text" id="price_to" name="price_to" placeholder="price to">
                </div>
                <button class="ui button">
                    Filter
                </button>
            </div>
        </div>

        <!-- table -->
    <table class="ui compact celled table">
  <thead>
    <tr>
      <th>Date</th>
      <th>Product name</th>
      <th>SKU</th>
      <th>Price</th>
      <th>Feature Image</th>
      <th>Gallery</th>
      <th>Categories</th>
      <th>Tags</th>
      <th>Action</th>

    </tr>
  </thead>
  <tbody>
      <?php foreach ($results as $row){
        $product_id = $row['id'];
          ?>
          <tr>
      <td><?php echo htmlspecialchars($row['date'])?></td>
      <td><?php echo htmlspecialchars($row['product_name'])?></td>
      <td><?php echo htmlspecialchars($row['sku'])?></td>
      <td><?php echo htmlspecialchars($row['price'])?></td>
      <td>
          <img width="100" src="./uploads/<?php echo $row['featured_image']; ?>">

      </td>
      <td>sfds.jpg,dsfsd.png</td>
      <td>category1</td>
      <td>tag1</td>
      <td>
        <a href="edit_add.php?product_id=<?php echo $product_id  ?>">Edit</a>
        <a href="delete.php">Delete</a>

      </td>
    </tr>

      <?php }?>
  </tbody>
</table>
    </section>



</body>

</html>