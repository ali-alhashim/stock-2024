<?php 
session_start();
?>




<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Warehouse Page</title>
    <link href="../../static/bootstrap/css/bootstrap.min.css" rel="stylesheet"/>
  </head>
    <body>

    <?php
      require("../base/main_menu.php");
    ?>
        <div class="container   my-5">


            <table class="table table-bordered">
                <thead>
                    <th>#</th>
                    <th>Name</th>
                    <th>Location</th>
                    <th><input type="checkbox" class="form-check-input"/></th>
                </thead>
            </table>

        </div>

        <script src="../../static/bootstrap/js/bootstrap.bundle.min.js"></script>
    </body>
</html>