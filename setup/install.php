<?php

//first receive form post data 

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
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

echo "Connected successfully";

// First, try to create the database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS `$db_name`"; // Backticks to handle reserved words or special characters

if ($conn->query($sql) === TRUE) {
    echo "Database '$db_name' exists or has been created successfully<br>";
} else {
    die("Error creating or accessing database: " . $conn->error);
}


 // Select the database
 $conn->select_db($db_name);









// Create 'warehouse' table
$warehouse_table_sql = "CREATE TABLE IF NOT EXISTS warehouse (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    location VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($warehouse_table_sql) === TRUE) {
    echo "Table 'warehouse' created successfully<br>";
} else {
    echo "Error creating 'warehouse' table: " . $conn->error . "<br>";
}



// Create 'users' table
$user_table_sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user', 'superadmin') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($user_table_sql) === TRUE) {
    echo "Table 'users' created successfully<br>";
} else {
    echo "Error creating 'users' table: " . $conn->error . "<br>";
}



// Create 'products' table
$product_table_sql = "CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description VARCHAR(500)  NULL,
    manufacture VARCHAR(255)  NULL,
    cost_price DECIMAL(10, 2)  NULL,
    sale_price DECIMAL(10, 2)  NULL,
    stock INT  NULL,
    location VARCHAR(255)  NULL,
    warehouse_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (warehouse_id) REFERENCES warehouse(id)
)";
if ($conn->query($product_table_sql) === TRUE) {
    echo "Table 'products' created successfully<br>";
} else {
    echo "Error creating 'products' table: " . $conn->error . "<br>";
}



// Create 'product_movements' table
$product_movement_table_sql = "CREATE TABLE IF NOT EXISTS product_movements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id    INT NOT NULL,
    movement_type ENUM('in', 'out') NOT NULL,
    quantity INT NOT NULL,
    movement_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
)";
if ($conn->query($product_movement_table_sql) === TRUE) {
    echo "Table 'product_movements' created successfully<br>";
} else {
    echo "Error creating 'product_movements' table: " . $conn->error . "<br>";
}


// insert admin user $sys_admin  , $sys_admin_pass 
$hashed_password = password_hash($sys_admin_pass, PASSWORD_BCRYPT); // Hash the admin password
$insert_admin_sql = "INSERT INTO users (username, password, role) 
                     VALUES ('$sys_admin', '$hashed_password', 'superadmin')
                     ON DUPLICATE KEY UPDATE username=username"; // Prevents duplicate admin creation

if ($conn->query($insert_admin_sql) === TRUE) {
    echo "Admin user '$sys_admin' created successfully<br>";
} else {
    echo "Error creating admin user: " . $conn->error . "<br>";
}

// create Virtual warehouse as default warehouse
$default_warehouse_sql = "INSERT INTO warehouse (name, location) 
                          VALUES ('Default Warehouse', 'Virtual Location')
                          ON DUPLICATE KEY UPDATE name=name"; // Prevents creating another default

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
} else {
    echo "Error creating 'config.php' file.<br>";
}
// Close the connection
$conn->close();

?>