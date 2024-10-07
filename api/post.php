<?php

ini_set('session.hash_function', 'sha256');
ini_set('session.sid_length', 64);  // 256 characters long
  
session_start();
require('../admin/base/config.php');
require('../admin/base/logs_func.php');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);


// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}



if($_SERVER['REQUEST_METHOD'] == 'POST')
{
  $function = trim($_POST['function']);

  if($function =="login")
  {
     login($_POST, $conn);
  }
  elseif($function =="addProduct")
  {
    addProduct($_POST, $conn);
  }
}

function validateUser($username, $password, $conn) {

  if (empty($username) || empty($password)) {
    return false; // Invalid credentials
  }


   // Prepare the SQL statement to prevent SQL injection
   $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
   $stmt->bind_param('s', $username);
   $stmt->execute();
   $result = $stmt->get_result();

   // Check if the username exists
   if ($result->num_rows == 1) 
   {
       // Fetch the user data
       $user = $result->fetch_assoc();
      
       // Verify the password
       if (password_verify($password, $user['password'])) 
       {
           // Password is correct, create session
           $_SESSION['user_id']    = $user['id'];
           $_SESSION['username']   = $user['username'];
           $_SESSION['role']       = $user['role'];
           $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Generates a secure token
           
           
           action_log($user['id'], "Login ".$user['username']." From  Device Mobile android", $conn);
           
           return true;
           
       } 
    }



} 





function login($data, $conn)
{
  // $data is a FormUrlEncoded sent by kotlin or swift with
  // username, password, function:login
  // device => ios or android
  // return http response {token:'sisstion', message:'', status:'', username:''}
  // status => success or invalied username or password
   // Extract and sanitize form data
   $username = isset($data['username']) ? trim($data['username']) : '';
   $password = isset($data['password']) ? trim($data['password']) : '';
   $device   = isset($data['device'])   ? trim($data['device']) : '';

   // Initialize response data
   $response = [
    'token' => '',
    'message' => '',
    'status' => '',
    'username' => $username
];

if (empty($username) || empty($password) || empty($device)) {
  $response['message'] = 'Missing required fields';
  $response['status'] = 'error';
  http_response_code(400); // Bad Request
  echo json_encode($response);
  return;
}


// Authenticate user
if (validateUser($username, $password, $conn)) {
 
  $response['token'] = $_SESSION['csrf_token'];
  $response['message'] = 'Login successful';
  $response['status'] = 'success';
} else {
  $response['message'] = 'Invalid username or password';
  $response['status'] = 'error';
  http_response_code(401); // Unauthorized
}

header('Content-Type: application/json');
echo json_encode($response);



} // end login








//--------------------Add product
function addProduct($data, $conn)
{
    $username = isset($data['username']) ? trim($data['username']) : '';
    $device   = isset($data['device'])   ? trim($data['device']) : '';
    $token    = isset($data['token'])    ? trim($data['token']) : '';
    $newStock = isset($data['newStock']) ? intval($data['newStock']) : 0;
    $barcode  = isset($data['barcode'])  ? trim($data['barcode']) : '';
    $image    = isset($_FILES['image'])  ? $_FILES['image'] : null;

    // Initialize response data
    $response = [
        'message' => '',
        'status' => '',
    ];

    // Check if the token is valid
    if ($token === $_SESSION['csrf_token']) {

        // Check if barcode and other necessary fields are provided
        if (empty($barcode) || empty($newStock) || empty($username)) {
            $response['message'] = "400 Bad Request. Missing required fields.";
            $response['status'] = '400';
            echo json_encode($response);
            return;
        }

        // Handle file upload
        $uploadDir = __DIR__ . '/../static/img/uploads/';
        $imagePath = null;
        
        if ($image && $image['error'] == 0) {
            $imageName = basename($image['name']);
            $targetFile = $uploadDir . $imageName;
            $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

            // Validate image file types (you can restrict it to jpeg/png/gif)
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($imageFileType, $allowedTypes)) {
                // Move the file to the uploads directory
                if (move_uploaded_file($image['tmp_name'], $targetFile)) {
                    $imagePath = '/static/img/uploads/' . $imageName; // Store relative path for the DB
                } else {
                    $response['message'] = "500 Internal Server Error. Failed to upload image.";
                    $response['status'] = '500';
                    echo json_encode($response);
                    return;
                }
            } else {
                $response['message'] = "400 Bad Request. Invalid image type.";
                $response['status'] = '400';
                echo json_encode($response);
                return;
            }
        }

        // Check if the product already exists
        $stmt = $conn->prepare("SELECT stock FROM products WHERE barcode = ?");
        $stmt->bind_param("s", $barcode);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Product exists, update the stock
            $stmt->bind_result($currentStock);
            $stmt->fetch();
            $stmt->close();

            // Calculate new stock
            $theNewStock = $currentStock + $newStock;

            // Update the product stock
            $updateStmt = $conn->prepare("UPDATE products SET stock = ? WHERE barcode = ?");
            $updateStmt->bind_param("is", $theNewStock, $barcode);
            if ($updateStmt->execute()) {
                $response["message"] = "Product stock updated successfully.";
                $response["status"] = "200";
            } else {
                $response["message"] = "500 Internal Server Error. Failed to update stock.";
                $response["status"] = "500";
            }
            $updateStmt->close();
        } else {
            // Product does not exist, insert a new row
            $insertStmt = $conn->prepare("INSERT INTO products (barcode, stock, image, created_by_id) VALUES (?, ?, ?, ?)");
            $createdById = $_SESSION['user_id'];
            $insertStmt->bind_param("siss", $barcode, $newStock, $imagePath, $createdById);

            if ($insertStmt->execute()) {
                $response["message"] = "Product added successfully.";
                $response["status"] = "200";
            } else {
                $response["message"] = "500 Internal Server Error. Failed to insert new product.";
                $response["status"] = "500";
            }
            $insertStmt->close();
        }

    } else {
        // Invalid token
        $response["message"] = "403 Forbidden. Invalid token, login required.";
        $response["status"] = "403";
    }

    // Return response as JSON
    header('Content-Type: application/json');
    echo json_encode($response);
}



?>