<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warehouse Add do</title>
    <link href="../../static/sweetalert2/sweetalert2.min.css"rel="stylesheet"/>
    <script src="../../static/sweetalert2/sweetalert2.all.min.js"></script>
</head>
<body>
<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] == 'POST') 
{

    if(isset($_SESSION["csrf_token"]) && $_POST["csrf_token"] == $_SESSION["csrf_token"])
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
               

                echo '<script>
                            Swal.fire({
                                title: "Success",
                                text: "Warehouse added successfully!",
                                icon: "success"
                            }).then(() => {
                            window.location.href = "list.php";
                            });
                     </script>';

            } 
            else 
            {
                echo "<div class='alert alert-danger text-center'>Error: Could not add warehouse!</div>";
            }
        }

        $stmt->close();
        $conn->close();
    }
    else
    {
        //csrf_token not correct 
        return "csrf_token not correct";
    }
   
}
?> 
</body>
</html>