<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
<?php
// Start session
ini_set('session.hash_function', 'sha256');
ini_set('session.sid_length', 64);  // 256 characters long

session_start();


// Include the config file with database credentials
include 'admin/base/config.php';
include 'admin/base/logs_func.php';
// Connect to the database
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

if(is_ip_blacklisted(get_client_ip(), $conn ))
{
    echo "you are blacklisted";
    exit();
}

// Get the client's IP address
function get_client_ip() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]; // Handle multiple IPs
    } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
        return $_SERVER['REMOTE_ADDR'];
    }
    return 'UNKNOWN';
}

// is ip blacklisted
function is_ip_blacklisted($ip, $conn) 
{
    $stmt = $conn->prepare("SELECT COUNT(*) FROM ip_blacklist WHERE ip_address = ? AND is_banned = true");
    $stmt->bind_param('s', $ip);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if($count>0)
    {
        return True;
    }
    else
    {
        return False;
    }
}
// add to blacklist
function blacklist_ip($ip, $conn) 
{
    $stmt = $conn->prepare("INSERT INTO ip_blacklist (ip_address) VALUES (?)");
    $stmt->bind_param('s', $ip);
    $stmt->execute();
    $stmt->close();
}


function get_device_type() {
    $userAgent = $_SERVER['HTTP_USER_AGENT'];

    // Simple mobile detection by checking for common mobile keywords in the User-Agent
    $mobile_agents = array('iPhone', 'Android', 'webOS', 'BlackBerry', 'iPod', 'Opera Mini', 'Windows Phone');

    foreach ($mobile_agents as $device) {
        if (stripos($userAgent, $device) !== false) {
            return 'Mobile';
        }
    }

    return 'Computer';
}




// Track failed login attempts
$failed_login_key = 'failed_login_' . $_SERVER['REMOTE_ADDR']; // Use IP as key or user ID if available
if (!isset($_SESSION[$failed_login_key])) {
    $_SESSION[$failed_login_key] = 0;
}

if ($_SESSION[$failed_login_key] >= 5) 
{
    // send his ip address to black list in database
   
    
    // Get the client's IP address
    $clientIp = get_client_ip();
    blacklist_ip($clientIp, $conn);
    die(' <br> You have been temporarily locked out due to too many failed login attempts. Please try again later. IP:'.$clientIp);
}






// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and receive input
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Check if username and password are provided
    if (empty($username) || empty($password)) {
        die("Please provide both username and password.");
    }

    

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare the SQL statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the username exists
    if ($result->num_rows == 1) {
        // Fetch the user data
        $user = $result->fetch_assoc();
        
        // Verify the password
        if (password_verify($password, $user['password'])) {
            // Password is correct, create session
            $_SESSION['user_id']    = $user['id'];
            $_SESSION['username']   = $user['username'];
            $_SESSION['role']       = $user['role'];
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Generates a secure token
            
            
            action_log($user['id'], "Login ".$user['username']." From ".get_client_ip()." Device ".get_device_type(), $conn);

            // Redirect to a dashboard or home page
            header("Location: admin/dashboard/index.php");
            exit();
        } else {
            // Invalid password
            echo "Invalid username or password.";
            // Increment failed login attempts
            action_log(null, "Invalid username ".$_POST['username']." or password From ".get_client_ip()." Device ".get_device_type(), $conn);
            $_SESSION[$failed_login_key]++;
        }
    } else {
        // Invalid username
        echo "Invalid username or password.";
        action_log(null, "Invalid username ".$_POST['username']." or password From ".get_client_ip()." Device ".get_device_type(), $conn);
        // Increment failed login attempts
        $_SESSION[$failed_login_key]++; 
    }

    // Close the connection
    $stmt->close();
    $conn->close();
}
else
{
    header("Location:index.php");
    exit();
}
?>
</body>
</html>