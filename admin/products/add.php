<?php 
session_start();

// Restrict access to superadmin or admin users only
if($_SESSION['role'] != "superadmin" && $_SESSION['role'] != "admin")
{
    // If the user is not an admin or superadmin, redirect or deny access
    echo "<div class='alert alert-danger text-center'>You are not authorized to add users!</div>";
    exit();  // Stop the script to prevent unauthorized access
}

?>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') 
{
    // Sanitize and receive input
    $username     = trim($_POST['username']);
    $password     = trim($_POST['password']);
    $role         = trim($_POST['role']);

    // check if the name of the username exist if not insert to DB
    include '../base/config.php';
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) 
    {
        // If username exists, display a message
        echo "<div class='alert alert-danger text-center'>username with this name already exists!</div>";
    } 
    else 
    {
        // If not, insert the new user into the database
        $stmt->close();
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);  // Secure password hashing
        $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username,  $hashed_password, $role);

        if ($stmt->execute()) 
        {
            echo "<div class='alert alert-success text-center'>user added successfully!</div>";
        } 
        else 
        {
            echo "<div class='alert alert-danger text-center'>Error: Could not add user!</div>";
        }
    }

    $stmt->close();
    $conn->close();
}
?>



<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Product Page</title>
    <link href="../../static/bootstrap/css/bootstrap.min.css" rel="stylesheet"/>
  </head>
    <body>

    <?php
      require("../base/main_menu.php");
    ?>
        <div class="container   my-1">

             
             
            <form method="POST" class="my-5" enctype="multipart/form-data">
            <table class="table table-bordered">
              
                <tbody>
                    <tr>
                        <td>Barcode</td>
                        <td><input type="text" name="barcode" placeholder="Barcode" class="form-control form-control-lg" id="barcode"/>
                    </tr>
                    <tr>
                        <td>Name</td>
                        <td><input type="text" name="name" placeholder="Product Name" class="form-control form-control-lg" id="product_name"/>
                    </tr>
                    <tr>
                        <td>Description</td>
                        <td><input type="text" name="description" placeholder="Product Description" class="form-control form-control-lg" id="description"/>
                    </tr>

                    <tr>
                        <td>Warehouse</td>
                        <td>
                            <select name="warehouse" class="form-select">
                                <?php
                                   include '../base/config.php';
                                   $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
                                   $stmt = $conn->prepare("SELECT id, name  FROM warehouse");
                                   $stmt->execute();
                                   $result = $stmt->get_result();
                                   while ($row = $result->fetch_assoc()) 
                                    {  
                                        echo '<option value="'.$row["id"].'"> '.$row["name"].'</option>';
                                    }   
                                ?>
                           </select>
                        </td>
                    </tr>

                    <tr>
                        <td>Location</td>
                        <td><input type="text" name="location" placeholder="Location shelf name & level number" class="form-control form-control-lg" id="barcode"/>
                    </tr>


                    <tr>
                        <td>IN / OUT</td>
                        <td>
                            <select name="movement_type" class="form-select">
                                <option>in</option>
                                <option>out</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>Stock</td>
                        <td>
                            <input type="number" name="stock" class="form-control" value="1" data-bs-toggle="tooltip" data-bs-placement="bottom" title="quantity of the item"/>
                        </td>
                    </tr>
                    <tr>
                        <td>Image</td>
                        <td>
                            <input type="file" name="image" class="form-control"/>
                        </td>
                    </tr>
                    <tr>
                        <td>Cost price</td>
                        <td>
                            <input type="text" name="cost_price" class="form-control"/>
                        </td>
                    </tr>
                    <tr>
                        <td>Sale price</td>
                        <td>
                            <input type="text" name="sale_price" class="form-control"/>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" class="text-center"><input type="submit" value="Save" class="btn btn-success"/></td>
                       
                    </tr>
                </tbody>
            </table>
            </form>

        </div>

        <script src="../../static/bootstrap/js/bootstrap.bundle.min.js"></script>
        <script>
            let currentPage = document.getElementById("products_btn");
            currentPage.classList.add("active");
        </script>
    </body>
</html>