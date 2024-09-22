<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>add do</title>
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
                });
           </script>';
    exit();  // Stop the script to prevent unauthorized access
}

// Function to validate input data
function validateInput($data) {
    $errors = array();
    if (empty($data['barcode'])) {
        $errors[] = 'Barcode is required';
    }
    // Add more validation rules as needed
    return $errors;
}

// Function to handle file upload
function handleFileUpload($file) {
    if (isset($file) && $file['size'] > 0) {  // Ensure file is uploaded
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (in_array($file['type'], $allowedTypes)) {
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
        } else {
            return array('error' => 'Invalid image format. Only JPG, PNG, and GIF are allowed');
        }
    }
    return array('error' => 'No image uploaded');
}

// Function to add/update product
function addUpdateProduct($data, $imageFileName) {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check if the product already exists
    $stmt = $conn->prepare("SELECT stock FROM products WHERE barcode = ?");
    $stmt->bind_param("s", $data['barcode']);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Product exists, update the stock
        $stmt->bind_result($currentStock);
        $stmt->fetch();
        $stmt->close();
        echo "<script>console.log('Product exists, updating the stock');</script>";

        // Calculate new stock
        $newStock = $currentStock + $data['stock'];

        // Update stock and optionally the image
        if ($imageFileName) {
            // If image is provided, update stock and image
            $stmt = $conn->prepare("UPDATE products SET stock = ?, image = ? WHERE barcode = ?");
            $stmt->bind_param("iss", $newStock, $imageFileName, $data['barcode']);
        } else {
            // If no image, only update stock
            $stmt = $conn->prepare("UPDATE products SET stock = ? WHERE barcode = ?");
            $stmt->bind_param("is", $newStock, $data['barcode']);
        }

        if ($stmt->execute()) {
            return array('message' => 'Stock updated successfully!');
        } else {
            return array('error' => 'Error: Could not update stock!');
        }
    } else {
        // Product doesn't exist, insert new product
        echo "<script>console.log('Product does not exist, inserting new product');</script>";
        $stmt->close();

        // If image is provided, insert it along with other product details
        if ($imageFileName) {
            $stmt = $conn->prepare("INSERT INTO products (barcode, name, description, manufacture, warehouse_id, created_by_id, location, stock, image, cost_price, sale_price) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssiisisss", $data['barcode'], $data['name'], $data['description'], $data['manufacture'], $data['warehouse'], $_SESSION['user_id'], $data['location'], $data['stock'], $imageFileName, $data['cost_price'], $data['sale_price']);
        } else {
            // If no image is provided, insert null for the image field
            $stmt = $conn->prepare("INSERT INTO products (barcode, name, description, manufacture, warehouse_id, created_by_id, location, stock, image, cost_price, sale_price) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NULL, ?, ?)");
            $stmt->bind_param("ssssiisiss", $data['barcode'], $data['name'], $data['description'], $data['manufacture'], $data['warehouse'], $_SESSION['user_id'], $data['location'], $data['stock'], $data['cost_price'], $data['sale_price']);
        }

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
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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

    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo '<script>
                    Swal.fire({
                        title: "Oops",
                        text: "' . $error . '",
                        icon: "error"
                    });
                  </script>';
        }
    } else {
        // Handle file upload
        $fileUploadResult = handleFileUpload($_FILES['image']);
        if (isset($fileUploadResult['error']) && $fileUploadResult['error'] != 'No image uploaded') {
            echo '<script>
                    Swal.fire({
                        title: "Oops",
                        text: "' . $fileUploadResult['error'] . '",
                        icon: "error"
                    });
                  </script>';
        } else {
            $imageFileName = isset($fileUploadResult['imageFileName']) ? $fileUploadResult['imageFileName'] : null;
            $result = addUpdateProduct($data, $imageFileName);
            if (isset($result['error'])) {
                echo '<script>
                        Swal.fire({
                            title: "Oops",
                            text: "' . $result['error'] . '",
                            icon: "error"
                        });
                      </script>';
            } else {
                echo '<script>
                        Swal.fire({
                            title: "Success",
                            text: "' . $result['message'] . '",
                            icon: "success"
                        });
                      </script>';
            }
        }
    }
}
?>

</body>
</html>