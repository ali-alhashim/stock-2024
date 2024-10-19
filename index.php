
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Page</title>
    <link href="static/bootstrap/css/bootstrap.min.css" rel="stylesheet"/>
  </head>
  <body>
<!---->
<section class="vh-100" style="background-color: #2c2c2c;">
    <div class="container py-5 h-100">
      <div class="row d-flex justify-content-center align-items-center h-100">
        <div class="col-12 col-md-8 col-lg-6 col-xl-5">
          <div class="card shadow-2-strong" style="border-radius: 1rem;">
            <div class="card-body p-5 text-center">
  
              <h3 class="mb-5">Login</h3>
             <form  method="POST" action="login.php">
                <div class="form-outline mb-4">
                
                <input type="text" id="Username" name="username" class="form-control form-control-lg" placeholder="Username" required/>
                
              </div>
  
              <div class="form-outline mb-4">
               
                <input type="password" id="password" name="password" class="form-control form-control-lg" placeholder="Password" required/>
              </div>
  
           
  
              <button  class="btn btn-primary btn-lg btn-block" type="submit">Login</button>
             
            </form>
              <hr class="my-4">
              <h5>Welcome to Inventory System</h5>
              <div class="row my-5">
                <div class="col">
                   <a href="android/app/build/outputs/apk/debug/app-debug.apk">
                  <img src="static/img/google.png" width="200"/>
                   </a>

                </div>
                <div class="col">
                  <img src="static/img/apple.png" width="200"/>
                </div>
              </div>
             
            
            
  
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
<!---->    
    <script src="static/bootstrap/js/bootstrap.bundle.min.js"></script>
  </body>
</html>