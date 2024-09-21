<?php
// Start session

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: ../../index.php");
    exit();
}


?>


<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Page</title>
    <link href="../../static/bootstrap/css/bootstrap.min.css" rel="stylesheet"/>
  </head>
    <body>
    <?php
      require("../base/main_menu.php");
    ?>

    <!--workspace-->
    <div class="container   my-5">

    <div class="row justify-content-md-center">

        <div class="card text-bg-light mb-3 mx-3" style="max-width: 18rem;">
            <div class="card-header">Top 5 Fast Moving Products</div>
                <div class="card-body">
                    <h5 class="card-title">Light card title</h5>
                    <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
                </div>
        </div>


        <div class="card text-bg-light mb-3 mx-3" style="max-width: 18rem;">
            <div class="card-header">Top 5 active users</div>
                <div class="card-body">
                    <h5 class="card-title">Light card title</h5>
                    <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
                </div>
        </div>


        <div class="card text-bg-light mb-3 mx-3" style="max-width: 18rem;">
            <div class="card-header">Top 5 active warehouses</div>
                <div class="card-body">
                    <h5 class="card-title">Light card title</h5>
                    <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
                </div>
        </div>

    </div>




    </div>
    <!--/workspace -->
  
    <script src="../../static/bootstrap/js/bootstrap.bundle.min.js"></script>
    </body>
</html>



