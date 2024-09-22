
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
                            <select name="role" class="select-control">
                                <option>user</option>
                                <option>admin</option>
                                <option>superadmin</option>
                           </select>
                        </td>
                    </tr>

                    <tr>
                        <td>password</td>
                        <td>
                            <input type="password" name="password" placeholder="password" class="form-control form-control-lg" required/>
                        </td>

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
            let currentPage = document.getElementById("users_btn");
            currentPage.classList.add("active");
        </script>
    </body>
</html>