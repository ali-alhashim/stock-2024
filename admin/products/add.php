<?php
// Configuration
require_once '../base/config.php';

// Security
session_start();
if (!isset($_SESSION['role']) || ($_SESSION['role'] != "superadmin" && $_SESSION['role'] != "admin")) {
    echo "<div class='alert alert-danger text-center'>You are not authorized to add users!</div>";
    exit();  // Stop the script to prevent unauthorized access
}

// Function to validate input data
function validateInput($data) 
{
    $errors = array();
    if (empty($data['barcode'])) {
        $errors[] = 'Barcode is required';
    }
  
  
    // Add more validation rules as needed
    return $errors;
}

// Function to handle file upload
function handleFileUpload($file) 
{
    if (isset($file) &&  $file['error'] === UPLOAD_ERR_OK)
    {
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (in_array($file['type'], $allowedTypes)) 
        {
            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/static/img/uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $imageFileName = uniqid('img_', true) . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
            $destPath = $uploadDir . $imageFileName;
            if (!move_uploaded_file($file['tmp_name'], $destPath)) {
                return array('error' => 'Error moving the uploaded file');
            }
            return array('imageFileName' => $imageFileName);
        } 
        else 
        {
            return array('error' => 'Invalid image format. Only JPG, PNG, and GIF are allowed');
        }
    }
    else 
    {
        return array('error' => 'No image uploaded');
    }
   
}

// Function to add/update product
function addUpdateProduct($data, $imageFileName) 
{
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $stmt = $conn->prepare("SELECT id FROM products WHERE barcode = ?");
    $stmt->bind_param("s", $data['barcode']);
    $stmt->execute();
    $stmt->store_result();


    if ($stmt->num_rows > 0) 
    {
        // Update existing product
        $stmt->close();
        $stmt = $conn->prepare("UPDATE products SET stock = stock + ? WHERE barcode = ?");
        $stmt->bind_param("is", $data['stock'], $data['barcode']);
        if ($stmt->execute()) {
            return array('message' => 'Stock updated successfully!');
        } else {
            return array('error' => 'Error: Could not update stock!');
        }
    } 
    else 
    {
        // Insert new product
        $stmt->close();
        $stmt = $conn->prepare("INSERT INTO products (barcode, name, description, manufacture, warehouse_id, created_by_id, location, stock, image, cost_price, sale_price) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssiisisss", $data['barcode'], $data['name'], $data['description'], $data['manufacture'], $data['warehouse'], $_SESSION['user_id'], $data['location'], $data['stock'], $imageFileName, $data['cost_price'], $data['sale_price']);
        if ($stmt->execute()) {
            return array('message' => 'Product added successfully!');
        } else {
            return array('error' => 'Error: Could not add product!');
        }
    }
    $stmt->close();
    $conn->close();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') 
{
    $data = array(
        'barcode' => trim($_POST['barcode']),
        'name' => trim($_POST['name']),
        'description' => trim($_POST['description']),
        'manufacture' => trim($_POST['manufacture']),
        'warehouse' => trim($_POST['warehouse']),
        'location' => trim($_POST['location']),
        'stock' => (int) trim($_POST['stock']),
        'cost_price' => (float) trim($_POST['cost_price']),
        'sale_price' => (float) trim($_POST['sale_price']),
    );

    $errors = validateInput($data);
    if (!empty($errors)) 
    {
        foreach ($errors as $error) 
        {
            echo "<div class='alert alert-danger text-center'>$error</div>";
        }
    } 
    else 
    {
            $fileUploadResult = handleFileUpload($_FILES['image']);

            if (isset($fileUploadResult['error'])) 
            {
                    if ($fileUploadResult['error'] == 'No image uploaded') {
                        // Handle the case where no image is uploaded
                        $imageFileName = null;
                    } 
                    else 
                    {
                        echo "<div class='alert alert-danger text-center'>" . $fileUploadResult['error'] . "</div>";
                    }
            } 
            else 
            {
                $imageFileName = $fileUploadResult['imageFileName'];
                $result = addUpdateProduct($data, $imageFileName);
                if (isset($result['error'])) 
                {
                    echo "<div class='alert alert-danger text-center'>" . $result['error'] . "</div>";
                } 
                else 
                {
                    echo "<div class='alert alert-success text-center'>" . $result['message'] . "</div>";
                }
            }

    }
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

<?php require("../base/main_menu.php"); ?>

<div class="container my-1">
    <form method="POST" class="my-5" enctype="multipart/form-data">
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