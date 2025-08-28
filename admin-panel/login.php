<?php
// admin-panel/login.php
session_start();

// Define database configuration
$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "taxi_booking";

// Function to execute SQL from file
function executeSQLFile($conn, $file_path) {
    // Check if file exists
    if (!file_exists($file_path)) {
        die("Database file not found: $file_path");
    }
    
    // Read the entire SQL file
    $sql = file_get_contents($file_path);
    
    // Execute multiple queries
    if ($conn->multi_query($sql)) {
        do {
            // Store first result set
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->more_results() && $conn->next_result());
        
        return true;
    } else {
        die("Error executing SQL file: " . $conn->error);
    }
}

// Check if user is already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
    exit;
}

// Try to connect to MySQL server
$conn = new mysqli($servername, $db_username, $db_password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if database exists
$db_exists = false;
$result = $conn->query("SHOW DATABASES LIKE '$dbname'");
if ($result && $result->num_rows > 0) {
    $db_exists = true;
    $conn->select_db($dbname);
}

$database_created = false;
if (!$db_exists) {
    // Database doesn't exist, create it
    $sql = "CREATE DATABASE $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
    if ($conn->query($sql)) {
        $conn->select_db($dbname);
        // Execute SQL from external file
        executeSQLFile($conn, '../database/taxi_booking.sql');
        $database_created = true;
    } else {
        die("Error creating database: " . $conn->error);
    }
} else {
    // Database exists, check if tables exist
    $conn->select_db($dbname);
    $result = $conn->query("SHOW TABLES LIKE 'admin_users'");
    if (!$result || $result->num_rows == 0) {
        // Tables don't exist, execute SQL file
        executeSQLFile($conn, '../database/taxi_booking.sql');
        $database_created = true;
    } else {
        // Check if admin user exists
        $result = $conn->query("SELECT COUNT(*) as count FROM admin_users");
        if ($result) {
            $row = $result->fetch_assoc();
            if ($row['count'] == 0) {
                // Table is empty, execute SQL file
                executeSQLFile($conn, '../database/taxi_booking.sql');
                $database_created = true;
            }
        }
    }
}

// Process login
$error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    // Check credentials
    $sql = "SELECT id, username, password FROM admin_users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $admin = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $admin['password'])) {
                // Regenerate session ID for security
                session_regenerate_id(true);
                
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['last_activity'] = time();
                
                header("Location: dashboard.php");
                exit;
            } else {
                $error = "Invalid password. Please try again.";
            }
        } else {
            $error = "Username not found. Please use the default credentials.";
        }
        $stmt->close();
    } else {
        $error = "Database error. Please try again.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Taxi Booking Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .login-page {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            width: 100%;
            max-width: 400px;
        }
        
        .login-form {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        
        .company-logo {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .company-logo i {
            font-size: 50px;
            color: #667eea;
            background: #f7f9fc;
            padding: 20px;
            border-radius: 50%;
        }
        
        .login-form h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
            font-weight: 600;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #555;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            border-color: #667eea;
            outline: none;
        }
        
        .btn-primary {
            width: 100%;
            padding: 12px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn-primary:hover {
            background: #5a67d8;
        }
        
        .error-message {
            background: #fee;
            color: #c53030;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #c53030;
        }
        
        .success-message {
            background: #dff0d8;
            color: #3c763d;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #3c763d;
        }
        
        .login-note {
            margin-top: 20px;
            padding: 15px;
            background: #f7f9fc;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .login-note code {
            background: #edf2f7;
            padding: 2px 6px;
            border-radius: 3px;
        }
    </style>
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-form">
            <div class="company-logo">
                <i class="fas fa-taxi"></i>
            </div>
            <h1>Taxi Booking Admin</h1>
            
            <?php if ($database_created): ?>
                <div class="success-message">
                    Database setup completed successfully! You can now login with the default credentials.
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required autocomplete="off" value="admin">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required value="password123">
                </div>
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
            <div class="login-note">
                <p><strong>Default Admin Credentials:</strong></p>
                <p>Username: <code>admin</code></p>
                <p>Password: <code>password123</code></p>
            </div>
        </div>
    </div>
</body>
</html>