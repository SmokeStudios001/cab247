<?php
// admin-panel/reset-password.php
session_start();

// Only allow this from localhost for security
$whitelist = array('127.0.0.1', '::1');
if (!in_array($_SERVER['REMOTE_ADDR'], $whitelist)) {
    die("Access denied. This script can only be run from localhost.");
}

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "taxi_booking";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process password reset
$message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $new_password = trim($_POST['new_password']);
    
    if (!empty($username) && !empty($new_password)) {
        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update the password in the database
        $sql = "UPDATE admin_users SET password = ? WHERE username = ?";
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("ss", $hashed_password, $username);
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $message = "Password for user '$username' has been reset successfully!";
                } else {
                    $message = "No user found with that username.";
                }
            } else {
                $message = "Error updating password: " . $conn->error;
            }
            $stmt->close();
        } else {
            $message = "Database error: " . $conn->error;
        }
    } else {
        $message = "Please fill in all fields.";
    }
}

// Check if admin user exists
$admin_exists = false;
$check_sql = "SELECT username FROM admin_users WHERE username = 'admin'";
$result = $conn->query($check_sql);
if ($result && $result->num_rows > 0) {
    $admin_exists = true;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Admin Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        .container {
            max-width: 500px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            background: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background: #45a049;
        }
        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .success {
            background: #dff0d8;
            color: #3c763d;
            border: 1px solid #d6e9c6;
        }
        .error {
            background: #f2dede;
            color: #a94442;
            border: 1px solid #ebccd1;
        }
        .info {
            background: #d9edf7;
            color: #31708f;
            border: 1px solid #bce8f1;
            padding: 15px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Reset Admin Password</h1>
        
        <?php if (!empty($message)): ?>
            <div class="message success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <div class="info">
            <p><strong>Note:</strong> This tool resets the admin password in the database.</p>
            <p>Default username is usually <strong>admin</strong></p>
        </div>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" value="admin" required>
            </div>
            
                        <div class="form-group">
                <label for="new_password">New Password:</label>
                <input type="password" id="new_password" name="new_password" value="password123" required>
            </div>
            
            <button type="submit">Reset Password</button>
        </form>
        
        <?php if (!$admin_exists): ?>
            <div class="message error" style="margin-top: 20px;">
                <strong>Warning:</strong> No admin user found in the database. 
                <p>Please run the database setup first by visiting the <a href="login.php">login page</a>.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>