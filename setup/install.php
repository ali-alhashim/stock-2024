<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install</title>
    <link href="../static/bootstrap/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="../static/sweetalert2/sweetalert2.min.css"rel="stylesheet"/>
    <script src="../static/sweetalert2/sweetalert2.all.min.js"></script>
</head>
<body>
    <div class="container">
<?php

session_start();
if(file_exists('../admin/base/config.php'))
  {
    require_once '../admin/base/config.php';
    
    if (!isset($_SESSION['role']) || ($_SESSION['role'] != "superadmin"))
    {
      echo '<script>
                      Swal.fire({
                          title: "Oops",
                          text: "You are not authorized to view Install page contact superadmin user!",
                          icon: "error"
                      }).then(() => {
                          window.location.href = "../index.php";
                      });
              </script>';
  
      exit();  // Stop the script to prevent unauthorized access
    }
  }

  if ($_SERVER['REQUEST_METHOD'] == 'POST') 
  {
  
      if(isset($_SESSION["csrf_token"]) && $_POST["csrf_token"] == $_SESSION["csrf_token"])
      {



            // First, receive form post data 
            $db_user        = $_POST["db_user"];
            $db_pass        = $_POST["db_pass"];
            $db_name        = $_POST["db_name"];
            $db_host        = $_POST["db_host"];
            $db_port        = $_POST["db_port"];
            $sys_admin      = $_POST["sys_admin"];
            $sys_admin_pass = $_POST["sys_admin_pass"];

            // Create connection
            $conn = new mysqli($db_host, $db_user, $db_pass, '', $db_port);

            // Check connection
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }
            echo "Connected successfully<br>";

            // Create database
            $sql = "CREATE DATABASE IF NOT EXISTS `$db_name`";
            if ($conn->query($sql) === TRUE) {
                echo "Database '$db_name' created or already exists<br>";
            } else {
                die("Error creating database: " . $conn->error);
            }

            // Select the database
            $conn->select_db($db_name);

            // Create tables and handle errors
            function createTable($conn, $sql, $tableName) {
                if ($conn->query($sql) === TRUE) {
                    echo "Table '$tableName' created successfully<br>";
                } else {
                    echo "Error creating '$tableName' table: " . $conn->error . "<br>";
                }
            }

            // Create 'warehouse' table
            $warehouse_table_sql = "CREATE TABLE IF NOT EXISTS warehouse (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                location VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            createTable($conn, $warehouse_table_sql, 'warehouse');

            // Create 'users' table
            $user_table_sql = "CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                role ENUM('admin', 'user', 'superadmin') NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                is_active BOOLEAN NOT NULL DEFAULT true,
                mf2_code VARCHAR(255) NULL
            )";
            createTable($conn, $user_table_sql, 'users');

            // Create 'user_logs' table
            $user_logs_table_sql = "CREATE TABLE IF NOT EXISTS user_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                action VARCHAR(600) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id)
            )";
            createTable($conn, $user_logs_table_sql, 'user_logs');

            // Create ip_blacklist table
            $ip_blacklist_table_sql = "CREATE TABLE IF NOT EXISTS ip_blacklist (
                id INT AUTO_INCREMENT PRIMARY KEY,
                ip_address VARCHAR(255) NOT NULL,
                is_banned BOOLEAN NOT NULL DEFAULT true,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            createTable($conn, $ip_blacklist_table_sql, 'ip_blacklist');



            // Create 'product_category' table
            $product_category_table_sql = "CREATE TABLE IF NOT EXISTS product_category (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL
            )";
            createTable($conn, $product_category_table_sql, 'product_category');

            // Create 'products' table
            $product_table_sql = "CREATE TABLE IF NOT EXISTS products (
                id INT AUTO_INCREMENT PRIMARY KEY,
                barcode VARCHAR(255) NULL,
                name VARCHAR(255) NULL,
                description VARCHAR(500) NULL,
                manufacture VARCHAR(255) NULL,
                cost_price DECIMAL(10, 2) NULL,
                sale_price DECIMAL(10, 2) NULL,
                stock INT NULL,
                location VARCHAR(255) NULL,
                image VARCHAR(600) NULL,
                warehouse_id INT NOT NULL,
                created_by_id INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                category_id INT NULL,
                FOREIGN KEY (category_id) REFERENCES product_category(id),
                FOREIGN KEY (warehouse_id) REFERENCES warehouse(id),
                FOREIGN KEY (created_by_id) REFERENCES users(id)
            )";
            createTable($conn, $product_table_sql, 'products');

            // Create 'product_movements' table
            $product_movement_table_sql = "CREATE TABLE IF NOT EXISTS product_movements (
                id INT AUTO_INCREMENT PRIMARY KEY,
                product_id INT NOT NULL,
                user_id INT NOT NULL,
                movement_type ENUM('in', 'out') NOT NULL,
                quantity INT NOT NULL,
                reference VARCHAR(255) NULL,
                movement_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (product_id) REFERENCES products(id),
                FOREIGN KEY (user_id) REFERENCES users(id)
            )";
            createTable($conn, $product_movement_table_sql, 'product_movements');

            // Insert admin user using prepared statements to avoid SQL injection
            $hashed_password = password_hash($sys_admin_pass, PASSWORD_BCRYPT); // Hash the admin password
            $insert_admin_sql = $conn->prepare("INSERT INTO users (username, password, role) 
                                VALUES (?, ?, 'superadmin')
                                ON DUPLICATE KEY UPDATE username=username");
            $insert_admin_sql->bind_param("ss", $sys_admin, $hashed_password);

            if ($insert_admin_sql->execute()) {
                echo "Admin user '$sys_admin' created successfully<br>";
            } else {
                echo "Error creating admin user: " . $insert_admin_sql->error . "<br>";
            }

            // Insert default warehouse
            $default_warehouse_sql = "INSERT INTO warehouse (name, location) 
                                    VALUES ('Default Warehouse', 'Virtual Location')
                                    ON DUPLICATE KEY UPDATE name=name";
            if ($conn->query($default_warehouse_sql) === TRUE) {
                echo "Default warehouse created successfully<br>";
            } else {
                echo "Error creating default warehouse: " . $conn->error . "<br>";
            }

            // Create config file
            $config_content = "<?php\n";
            $config_content .= "define('DB_HOST', '$db_host');\n";
            $config_content .= "define('DB_USER', '$db_user');\n";
            $config_content .= "define('DB_PASS', '$db_pass');\n";
            $config_content .= "define('DB_NAME', '$db_name');\n";
            $config_content .= "define('DB_PORT', '$db_port');\n";
            $config_content .= "?>";

            $file_path = __DIR__ . "../../admin/base/config.php";
            if (file_put_contents($file_path, $config_content)) {
                echo "Configuration file 'config.php' created successfully.<br>";
                echo "<a href='../admin/dashboard/index.php'>Dashboard</a>";
            } 
            else 
            {
                echo "Error creating 'config.php' file";
            }
        }
        else
        {
            echo "Error no CSRF Token";
        }
  }
  else
  {
    echo "only post request";
  }
?>
</div>
</body>
</html>