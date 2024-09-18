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
    <title>Add User Page</title>
    <link href="../../static/bootstrap/css/bootstrap.min.css" rel="stylesheet"/>
  </head>
    <body>

    <?php
      require("../base/main_menu.php");
    ?>
        <div class="container   my-1">

             
             
            <form method="POST" class="my-5">
            <table class="table table-bordered">
              
                <tbody>
                    <tr>
                        <td>username</td>
                        <td><input type="text" name="username" placeholder="username" class="form-control form-control-lg" required/>
                    </tr>
                    <tr>
                        <td>role</td>
                        <td>
                            <select name="role" class="form-control">
                                <option>user</option>
                                <option>admin</option>
                                <option>superadmin</option>
                           </select>
                        </td>
                    </tr>

                    <tr>
                        <td>password</td>
                        <td><input type="password" name="password" placeholder="password" class="form-control form-control-lg" required/>
                    </tr>

                    <tr>
                        <td colspan="2" class="text-center"><input type="submit" value="Save" class="btn btn-success"/></td>
                       
                    </tr>
                </tbody>
            </table>
            </form>

        </div>

        <script src="../../static/bootstrap/js/bootstrap.bundle.min.js"></script>
    </body>
</html>