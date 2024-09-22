<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Setup Page</title>
    <link href="../static/bootstrap/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="../static/sweetalert2/sweetalert2.min.css"rel="stylesheet"/>
    <script src="../static/sweetalert2/sweetalert2.all.min.js"></script>
  </head>
  <body style="background-color: #2c2c2c;">


  <?php
  session_start();
  //check if ../admin/base/config.php exist 
  // so already installed so to access this page you have to be a superadmin
  if(file_exists('../admin/base/config.php'))
  {
    require_once '../admin/base/config.php';
    
    if (!isset($_SESSION['role']) || ($_SESSION['role'] != "superadmin"))
    {
      echo '<script>
                      Swal.fire({
                          title: "Oops",
                          text: "You are not authorized to view setup page contact superadmin user!",
                          icon: "error"
                      }).then(() => {
                          window.location.href = "../index.php";
                      });
              </script>';
  
      exit();  // Stop the script to prevent unauthorized access
    }

    $setupAlreadyDone = True;


  }
  else
  {
    $setupAlreadyDone = False;
  }
  ?>


<!---->
<section class="vh-100">
    <div class="container py-5 h-100">
      <div class="row d-flex justify-content-center align-items-center h-100">
        <div class="col-12 col-md-8 col-lg-6 col-xl-5">
          <div class="card shadow-2-strong" style="border-radius: 1rem;">
            <div class="card-body p-5 text-center">
  
              <h3 class="mb-5">Setup</h3>
             <form action="install.php" method="POST">
                <div class="form-outline mb-4 row">
                Mysql user:<input type="text" id="Username" name="db_user" class="form-control form-control-lg" placeholder="root" value="root"/>
              </div>
  
              <div class="form-outline mb-4 row">
                Mysql pass:<input type="password" id="password" name="db_pass" class="form-control form-control-lg" placeholder="Password"/>
              </div>


              <div class="form-outline mb-4 row">
                Database Name:<input type="text" id="database" name="db_name" class="form-control form-control-lg" placeholder="stockdb" value="stockdb"/>
              </div>

              <div class="form-outline mb-4 row">
                Database Host:<input type="text" id="db_host" name="db_host" class="form-control form-control-lg" placeholder="localhost" value="localhost"/>
              </div>

              <div class="form-outline mb-4 row">
                Database Port:<input type="text" id="db_port" name="db_port" class="form-control form-control-lg" placeholder="3306" value="3306"/>
              </div>

              <div class="form-outline mb-4 row">
               System admin:<input type="text" id="sys_admin" name="sys_admin" class="form-control form-control-lg" placeholder="admin" value="admin"/>
              </div>

              <div class="form-outline mb-4 row">
               System admin Pass:<input type="password" id="sys_admin_pass" name="sys_admin_pass" class="form-control form-control-lg"/>
              </div>
  
             
  
              <button  class="btn btn-primary btn-lg btn-block" type="submit">Install</button>


            <?php
            if($setupAlreadyDone)
            {
              echo '<input type="hidden" name="csrf_token" value="'.$_SESSION['csrf_token'].'"/>';
            }
            else
            {
              // this first time so generate temp csrf token
              $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
              echo '<input type="hidden" name="csrf_token" value="'.$_SESSION['csrf_token'].'"/>';
            }
            ?>  
            </form>
              <hr class="my-4">
              <h5>Created By Ali Alhashim</h5>
            
  
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
<!---->    
    <script src="../static/bootstrap/js/bootstrap.bundle.min.js"></script>
  </body>
</html>