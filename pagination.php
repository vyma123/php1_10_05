<html>
<head>
    <title>Pagination</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
            height: 20rem;
        }
        .inline {
            display: inline-block;
            float: right;
            margin: 20px 0px;
        }
        input, button {
            height: 34px;
        }
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
        .pagination a {
            font-weight: bold;
            font-size: 18px;
            color: black;
            padding: 8px 16px;
            text-decoration: none;
            border: 1px solid black;
            margin: 0 2px;
        }
        .pagination a.active {
            background-color: pink;
        }
        .pagination a:hover:not(.active) {
            background-color: skyblue;
        }
    </style>
</head>
<body>
<center>
    <?php
    // Import the file where we defined the connection to Database.
    require_once "includes/db.inc.php";

    $per_page_record = 3;  // Number of entries to show in a page.
    // Look for a GET variable page if not found default is 1.
    $page = isset($_GET["page"]) ? (int)$_GET["page"] : 1;

    $start_from = ($page - 1) * $per_page_record;

    // Fetching products with pagination
    $query = "SELECT * FROM products LIMIT :start_from, :per_page";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':start_from', $start_from, PDO::PARAM_INT);
    $stmt->bindParam(':per_page', $per_page_record, PDO::PARAM_INT);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Counting total records
    $count_query = "SELECT COUNT(*) FROM products";
    $count_stmt = $pdo->prepare($count_query);
    $count_stmt->execute();
    $total_records = $count_stmt->fetchColumn();

    ?>
    <div class="container">
        <br>
        <div>
            <h1>Pagination Simple Example</h1>
            <p>This page demonstrates basic Pagination using PHP and MySQL.</p>
            <table class="table table-striped table-condensed table-bordered">
                <thead>
                    <tr>
                        <th width="10%">ID</th>
                        <th>Name</th>
                        <th>College</th>
                        <th>Score</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($results as $row) {
                    ?>
                        <tr>
                            <td><?php echo $row["product_name"]; ?></td>
                            <td><?php echo $row["sku"]; ?></td>
                            <td><?php echo $row["price"]; ?></td>
                        </tr>
                    <?php
                    }
                    ?>
                </tbody>
            </table>

            <div class="pagination">
                <?php
                echo "</br>";
                // Number of pages required.
                $total_pages = ceil($total_records / $per_page_record);
                $pagLink = "";

                if ($page >= 2) {
                    echo "<a href='pagination.php?page=" . ($page - 1) . "'> Prev </a>";
                }else {
                    echo "<a href='pagination.php?page=" . $page . "'> Prev </a>";

                }

                for ($i = 1; $i <= $total_pages; $i++) {
                    if ($i == $page) {
                        $pagLink .= "<a class='active' href='index1.php?page=" . $i . "'>" . $i . " </a>";
                    } else {
                        $pagLink .= "<a href='index1.php?page=" . $i . "'>" . $i . " </a>";
                    }
                }
                echo $pagLink;

                if ($page < $total_pages) {
                    echo "<a href='pagination.php?page=" . ($page + 1) . "'> Next </a>";
                }else {
                    echo "<a href='pagination.php?page=" . $page . "'> Next </a>";

                }
                ?>
            </div>

            <div class="inline">
                <input id="page" type="number" min="1" max="<?php echo $total_pages ?>" placeholder="<?php echo $page . "/" . $total_pages; ?>" required>
                <button onClick="go2Page();">Go</button>
            </div>
        </div>
    </div>
</center>
<script>
    function go2Page() {
        var page = document.getElementById("page").value;
        page = ((page > <?php echo $total_pages; ?>) ? <?php echo $total_pages; ?> : ((page < 1) ? 1 : page));
        window.location.href = 'pagination.php?page=' + page;
    }
</script>
</body>
</html>
