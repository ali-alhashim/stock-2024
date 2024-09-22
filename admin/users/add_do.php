<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>add user do</title>
    <link href="../../static/sweetalert2/sweetalert2.min.css"rel="stylesheet"/>
    <script src="../../static/sweetalert2/sweetalert2.all.min.js"></script>
</head>
<body>
<?php 
require_once '../base/config.php';
session_start();

if (!isset($_SESSION['role']) || ($_SESSION['role'] != "superadmin" && $_SESSION['role'] != "admin")) {
    echo '<script>
        Swal.fire({
            title: "Oops",
            text: "You are not authorized to add users!",
            icon: "error"
        }).then(() => {
            window.location.href = "../../index.php";
        });
      </script>';
    exit();  // Stop the script to prevent unauthorized access
}

?>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') 
{
    if(isset($_SESSION["csrf_token"]) && $_POST["csrf_token"] == $_SESSION["csrf_token"])
    {
             // Sanitize and receive input
            $username     = trim($_POST['username']);
            $password     = trim($_POST['password']);
            $role         = trim($_POST['role']);

            // check if the name of the username exist if not insert to DB
        
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
   
}
?>

</body>
</html>