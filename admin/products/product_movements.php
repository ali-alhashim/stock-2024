<?php 

session_start();
?>




<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Products Page</title>
    <link href="../../static/bootstrap/css/bootstrap.min.css" rel="stylesheet"/>
  </head>
    <body>

    <?php
      require("../base/main_menu.php");
    ?>
        <div class="container   my-1">

               <div class="row">
                    <div class="col">
                        <a href="add.php" class="btn btn-success my-5 ">Add</a>
                    </div>
                    <div class="col text-end">
                        <a href="product_movements.php" class="btn btn-secondary my-5 ">Product Movements</a>
                    </div>
                </div>
            
            <table class="table table-bordered ">
                <thead>
                    <th>#</th>
                    <th>Image</th>
                    <th>Product</th>
                    <th> Product Name </th>
                    <th>User</th>
                    <th>Movement type</th>
                    <th>Quantity</th>
                    <th>Date</th>
                    <th><input type="checkbox" class="form-check-input" value="all"/></th>
                </thead>
                <tbody>
                  <?php
                    include '../base/config.php';
                    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
                    $stmt = $conn->prepare("
                                                SELECT PRODUCT_M.*, USERS.username, PRODUCT.barcode, PRODUCT.name, PRODUCT.image
                                                FROM product_movements AS PRODUCT_M
                                                JOIN users AS USERS ON PRODUCT_M.user_id = USERS.id
                                                JOIN products as PRODUCT ON PRODUCT_M.product_id = PRODUCT.id order by PRODUCT_M.id desc;
                                            ");
                    $stmt->execute();
                    $result = $stmt->get_result();
                    while ($row = $result->fetch_assoc()) 
                    {     
                      echo '<tr>
                                 <td>'.$row["id"].'</td>
                                 <td><a href="/static/img/uploads/'.$row["image"].'" target="_blank"><img src="/static/img/uploads/'.$row["image"].'" alt="Product Image" style="max-width:200px"></a></td>
                                 <td>'.$row["barcode"].'</td>
                                  <td>'.$row["name"].'</td>
                                 <td>'.$row["username"].'</td>
                                 <td>'.$row["movement_type"].'</td>
                                 <td>'.$row["quantity"].'</td>
                                 <td>'.$row["movement_date"].'</td>
                                 <td><input type="checkbox" class="form-check-input" value="'.$row["id"].'"/></td>
                           </tr>
                      ';
                    }
                  ?>
                </tbody>
            </table>

        </div>

        <script src="../../static/bootstrap/js/bootstrap.bundle.min.js"></script>
        <script>
            let currentPage = document.getElementById("products_btn");
            currentPage.classList.add("active");
        </script>
    </body>
</html>