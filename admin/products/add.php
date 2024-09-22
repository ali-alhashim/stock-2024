
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Product Page</title>
    <link href="../../static/bootstrap/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="../../static/sweetalert2/sweetalert2.min.css"rel="stylesheet"/>
    <script src="../../static/sweetalert2/sweetalert2.all.min.js"></script>
</head>
<body>

<?php
// Configuration
require_once '../base/config.php';

// Security
session_start();
if (!isset($_SESSION['role']) || ($_SESSION['role'] != "superadmin" && $_SESSION['role'] != "admin")) {
  
    

    echo '<script>
    Swal.fire({
        title: "Oops",
        text: "You are not authorized to add Product!",
        icon: "error"
    }).then(() => {
        window.location.href = "../../index.php";
    });
  </script>';

    
   exit();  // Stop the script to prevent unauthorized access
}
?>

<?php require("../base/main_menu.php"); ?>

<div class="container my-1">
    <form action="add_do.php" method="POST" class="my-5" enctype="multipart/form-data">
        <table class="table table-bordered">
            <tbody>
                <tr>
                    <td>Barcode</td>
                    <td><input type="text" name="barcode" placeholder="Barcode" class="form-control form-control-lg" id="barcode" required/></td>
                </tr>
                <tr>
                    <td>Name</td>
                    <td><input type="text" name="name" placeholder="Product Name" class="form-control form-control-lg" id="product_name" /></td>
                </tr>
                <tr>
                    <td>Description</td>
                    <td><input type="text" name="description" placeholder="Product Description" class="form-control form-control-lg" id="description" /></td>
                </tr>
                <tr>
                    <td>Manufacture</td>
                    <td><input type="text" name="manufacture" placeholder="Product Manufacture" class="form-control form-control-lg" id="manufacture" /></td>
                </tr>
                <tr>
                    <td>Warehouse</td>
                    <td>
                        <select name="warehouse" class="form-select" required>
                            <?php
                            include '../base/config.php';
                            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
                            $stmt = $conn->prepare ("SELECT id, name FROM warehouse");
                            $stmt->execute();
                            $result = $stmt->get_result();
                            while ($row = $result->fetch_assoc()) {
                                echo '<option value="' . $row["id"] . '">' . $row["name"] . '</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Location</td>
                    <td><input type="text" name="location" placeholder="Location shelf name & level number" class="form-control form-control-lg" /></td>
                </tr>
                <tr>
                    <td>IN / OUT</td>
                    <td>
                        <select name="movement_type" class="form-select" required>
                            <option value="in">In</option>
                            <option value="out">Out</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Stock</td>
                    <td><input type="number" name="stock" class="form-control" value="1" required/></td>
                </tr>
                <tr>
                    <td>Image</td>
                    <td><input type="file" name="image" class="form-control" /></td>
                </tr>
                <tr>
                    <td>Cost Price</td>
                    <td><input type="text" name="cost_price" class="form-control" /></td>
                </tr>
                <tr>
                    <td>Sale Price</td>
                    <td><input type="text" name="sale_price" class="form-control" /></td>
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