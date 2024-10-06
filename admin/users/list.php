
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Users Page</title>
    <link href="../../static/bootstrap/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="../../static/sweetalert2/sweetalert2.min.css" rel="stylesheet"/>
    <script src="../../static/sweetalert2/sweetalert2.all.min.js"></script>
  </head>
    <body>

    <?php 

session_start();
// Restrict access to superadmin or admin users only
if($_SESSION['role'] != "superadmin" && $_SESSION['role'] != "admin")
{
    // If the user is not an admin or superadmin, redirect or deny access
  

    echo '<script>
    Swal.fire({
        title: "Oops",
        text: "You are not authorized to view users!",
        icon: "error"
    }).then(() => {
        window.location.href = "../../index.php";
    });
  </script>';

    exit();  // Stop the script to prevent unauthorized access
}
?>


    <?php
      require("../base/main_menu.php");
    ?>
        <div class="container   my-1">

             <div class="row">
                <div class="col">
                  <a href="add.php" class="btn btn-success my-5">Add</a>
                </div>
                <div class="col text-end">
                  <a href="logs.php" class="btn btn-success my-5">Users Logs</a>
                </div>
              </div>
            
            <table class="table table-bordered">
                <thead>
                    <th>#</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Active</th>
                    <th>MF2</th>
                    <th><input type="checkbox" class="form-check-input" value="all"/></th>
                </thead>
                <tbody>
                  <?php
                    include '../base/config.php';
                    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
                    $stmt = $conn->prepare("SELECT id, username, role, is_active,mf2_code  FROM users");
                    $stmt->execute();
                    $result = $stmt->get_result();
                    while ($row = $result->fetch_assoc()) 
                    {     
                      echo '<tr>
                                 <td>'.$row["id"].'</td>
                                 <td>'.$row["username"].'</td>
                                 <td>'.$row["role"].'</td>
                                 <td>'.$row["is_active"].'</td>
                                 <td>'.$row["mf2_code"].'</td>
                                 <td><input type="checkbox" class="form-check-input" value="'.$row["id"].'"/></td>
                           </tr>
                      ';
                    }
                  ?>
                </tbody>
            </table>

        </div>

        <script src="../../static/bootstrap/js/bootstrap.bundle.min.js"></script>
        <script>
            let currentPage = document.getElementById("users_btn");
            currentPage.classList.add("active");
        </script>
    </body>
</html>