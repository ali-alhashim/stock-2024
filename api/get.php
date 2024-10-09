<?php

ini_set('session.hash_function', 'sha256');
ini_set('session.sid_length', 64);  // 256 characters long
  
session_start();
require('../admin/base/config.php');
require('../admin/base/logs_func.php');
require('../admin/base/log_to_file.php');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);


// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}



// protect get request with csrf_token check if valied todo
if($_SERVER['REQUEST_METHOD'] == 'GET')
{
  $function = trim($_GET['function']);

  if($function =="warehouses")
  {
    warehouses($conn, $_GET);
  }
  elseif($function =="getProductByBarcode")
  {
    getProductByBarcode($conn, $_GET);
  }
}


function warehouses($conn, $data)
{
    $stmt = $conn->prepare("SELECT id, name  FROM warehouse");
    $stmt->execute();
    $result = $stmt->get_result();

    $warehouses = [];

    // Fetch the result as an associative array
    while ($row = $result->fetch_assoc()) {
        $warehouses[] = $row;
    }

     // Return the result as a JSON response
     header('Content-Type: application/json');
     echo json_encode($warehouses);
 
     $stmt->close();
}



function getProductByBarcode($conn, $data)
{
    // Check if the token is valid
    

  $barcode = isset($data['barcode']) ? trim($data['barcode']) : '';
  $token   = isset($data['token'])    ? trim($data['token']) : '';

  logToFile("getProductByBarcode $barcode, $token | ".$_SESSION['token']);

  if ($token == $_SESSION['token']) {
    $stmt = $conn->prepare("SELECT *  FROM products where barcode =?");
    $stmt->bind_param('s', $barcode);
    $stmt->execute();
    $result = $stmt->get_result();

    $product = [];

    while ($row = $result->fetch_assoc()) 
    {
      $product[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode($product);

    $stmt->close();
  }
  else
  {
    // you have to login first message
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Login First!']);

  }

   


}




$conn->close();
?>