
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
      session_start();
      require("../base/main_menu.php");
    ?>
        <div class="container   my-1">

             
             
            <form method="POST" class="my-5" action="add_do.php">
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
            <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>"/>
            </form>

        </div>

        <script src="../../static/bootstrap/js/bootstrap.bundle.min.js"></script>
        <script>
            let currentPage = document.getElementById("warehouses_btn");
            currentPage.classList.add("active");
        </script>
    </body>
</html>