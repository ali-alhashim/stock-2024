<?php 

session_start();
?>




<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Warehouse Page</title>
    <link href="../../static/bootstrap/css/bootstrap.min.css" rel="stylesheet"/>
  </head>
    <body>

    <?php
      require("../base/main_menu.php");
    ?>
        <div class="container   my-1">

             
              <a href="add.php" class="btn btn-success my-5">Add</a>
            
            <table class="table table-bordered">
                <thead>
                    <th>#</th>
                    <th>Name</th>
                    <th>Location</th>
                    <th><input type="checkbox" class="form-check-input" value="all"/></th>
                </thead>
                <tbody>
                  <?php
                    include '../base/config.php';
                    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
                    $stmt = $conn->prepare("SELECT id, name, location  FROM warehouse");
                    $stmt->execute();
                    $result = $stmt->get_result();
                    while ($row = $result->fetch_assoc()) 
                    {     
                      echo '<tr>
                                 <td>'.$row["id"].'</td>
                                 <td>'.$row["name"].'</td>
                                 <td>'.$row["location"].'</td>
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
            let currentPage = document.getElementById("warehouses_btn");
            currentPage.classList.add("active");
        </script>
    </body>
</html>