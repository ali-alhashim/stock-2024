<?php
// Start session
ini_set('session.hash_function', 'sha256');
ini_set('session.sid_length', 64);  // 256 characters long

session_start();

// Include the config file with database credentials
include 'admin/base/config.php';

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and receive input
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Check if username and password are provided
    if (empty($username) || empty($password)) {
        die("Please provide both username and password.");
    }

    // Connect to the database
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare the SQL statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the username exists
    if ($result->num_rows == 1) {
        // Fetch the user data
        $user = $result->fetch_assoc();
        
        // Verify the password
        if (password_verify($password, $user['password'])) {
            // Password is correct, create session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Redirect to a dashboard or home page
            header("Location: admin/dashboard/index.php");
            exit();
        } else {
            // Invalid password
            echo "Invalid username or password.";
        }
    } else {
        // Invalid username
        echo "Invalid username or password.";
    }

    // Close the connection
    $stmt->close();
    $conn->close();
}
?>



<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Page</title>
    <link href="static/bootstrap/css/bootstrap.min.css" rel="stylesheet"/>
  </head>
  <body>
<!---->
<section class="vh-100" style="background-color: #2c2c2c;">
    <div class="container py-5 h-100">
      <div class="row d-flex justify-content-center align-items-center h-100">
        <div class="col-12 col-md-8 col-lg-6 col-xl-5">
          <div class="card shadow-2-strong" style="border-radius: 1rem;">
            <div class="card-body p-5 text-center">
  
              <h3 class="mb-5">Login</h3>
             <form  method="POST">
                <div class="form-outline mb-4">
                
                <input type="text" id="Username" name="username" class="form-control form-control-lg" placeholder="Username"/>
                
              </div>
  
              <div class="form-outline mb-4">
               
                <input type="password" id="password" name="password" class="form-control form-control-lg" placeholder="Password"/>
              </div>
  
              <!-- Checkbox -->
              <div class="form-check d-flex justify-content-start mb-4">
                <input class="form-check-input mx-1" type="checkbox" value="" id="form1Example3" />
                <label class="form-check-label" for="form1Example3"> Remember me</label>
              </div>
  
              <button  class="btn btn-primary btn-lg btn-block" type="submit">Login</button>
            </form>
              <hr class="my-4">
              <h5>Welcome to Inventory System</h5>
            
  
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
<!---->    
    <script src="static/bootstrap/js/bootstrap.bundle.min.js"></script>
  </body>
</html>