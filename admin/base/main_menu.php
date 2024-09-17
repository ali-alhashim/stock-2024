<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: ../../index.php");
    exit();
}
?>

<nav class="navbar navbar-expand-lg bg-body-tertiary border-bottom">
  <div class="container-fluid">
    <a class="navbar-brand" href="../dashboard/index.php">Home</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">

        <li class="nav-item">
          <a class="btn btn-outline-primary mx-2" aria-current="page" href="#">Welcome : <?=$_SESSION['username']?> | <?=$_SESSION['role']?></a>
        </li>

        <?php 
        if($_SESSION['role'] == "superadmin")
        {
           echo ' <li class="nav-item">
                   <a class="btn btn-outline-primary mx-2" href="..\users\list.php">Users</a>
                </li>';
        }
        ?>

                <li class="nav-item">
                   <a class="btn btn-outline-primary mx-2" href="..\warehouses\list.php">Warehouses</a>
                </li>


                <li class="nav-item">
                   <a class="btn btn-outline-primary mx-2" href="..\products\list.php">Products</a>
                </li>
      
      
      </ul>
      

      <a href="../logout.php" class="nav-item mx-5 btn btn-outline-danger">Logout</a>
    </div>
  </div>
</nav>