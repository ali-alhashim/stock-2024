<?php 
session_start();
?>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') 
{
    // Sanitize and receive input
    $wh_name     = trim($_POST['wh_name']);
    $wh_location = trim($_POST['wh_location']);

    // check if the name of the warehouse exist if not insert to DB
    include '../base/config.php';
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    $stmt = $conn->prepare("SELECT id FROM warehouse WHERE name = ?");
    $stmt->bind_param("s", $wh_name);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) 
    {
        // If warehouse name exists, display a message
        echo "<div class='alert alert-danger text-center'>Warehouse with this name already exists!</div>";
    } 
    else 
    {
        // If not, insert the new warehouse into the database
        $stmt->close();
        $stmt = $conn->prepare("INSERT INTO warehouse (name, location) VALUES (?, ?)");
        $stmt->bind_param("ss", $wh_name, $wh_location);

        if ($stmt->execute()) 
        {
            echo "<div class='alert alert-success text-center'>Warehouse added successfully!</div>";
        } 
        else 
        {
            echo "<div class='alert alert-danger text-center'>Error: Could not add warehouse!</div>";
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
    <title>Add Warehouse Page</title>
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
                        <td>Name</td>
                        <td><input type="text" name="wh_name" placeholder="warehouse name" class="form-control form-control-lg" required/>
                    </tr>
                    <tr>
                        <td>Location</td>
                        <td><input type="text" name="wh_location" placeholder="warehouse location" class="form-control form-control-lg" required/>
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