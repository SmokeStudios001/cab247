<?php
// includes/header.php
// Remove this line if you're using auth.php for session management
// session_start();

// Include authentication first
require_once 'auth.php';

// Then include database connection
require_once 'config.php';

// Check if user is logged in, if not then redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Include functions
require_once 'functions.php';

// Get all settings
$settings = getAllSettings($conn);

// Make common settings available globally
$currency = isset($settings['currency']) ? $settings['currency'] : '$';
$currency_symbol = isset($settings['currency_symbol']) ? $settings['currency_symbol'] : '$';
$company_name = isset($settings['company_name']) ? $settings['company_name'] : 'Taxi Booking';
$min_fare = isset($settings['min_fare']) ? $settings['min_fare'] : '5.00';
$max_wait_time = isset($settings['max_wait_time']) ? $settings['max_wait_time'] : '300';
$search_radius = isset($settings['search_radius']) ? $settings['search_radius'] : '5';

// Set default page title if not defined
if (!isset($page_title)) {
    $page_title = "Admin Panel";
}

// Set default page styles and scripts if not defined
if (!isset($page_styles)) {
    $page_styles = array();
}

if (!isset($page_scripts)) {
    $page_scripts = array();
}

// Add default styles
array_unshift($page_styles, 'css/style.css');
array_unshift($page_styles, 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css');

// Add default scripts
array_unshift($page_scripts, 'js/script.js');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title . ' - ' . $company_name; ?></title>
    
    <!-- Default Styles -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    
    <!-- Page-specific Styles -->
    <?php foreach ($page_styles as $style): ?>
        <?php if (filter_var($style, FILTER_VALIDATE_URL)): ?>
            <link rel="stylesheet" href="<?php echo $style; ?>">
        <?php else: ?>
            <link rel="stylesheet" href="<?php echo $style; ?>">
        <?php endif; ?>
    <?php endforeach; ?>
    
    <style>
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-pending { background-color: #ffc107; color: #000; }
        .status-accepted { background-color: #17a2b8; color: #fff; }
        .status-driver_arrived { background-color: #6610f2; color: #fff; }
        .status-started { background-color: #007bff; color: #fff; }
        .status-completed { background-color: #28a745; color: #fff; }
        .status-cancelled { background-color: #dc3545; color: #fff; }
        
        .status-online { background-color: #28a745; color: #fff; }
        .status-offline { background-color: #6c757d; color: #fff; }
        .status-on_ride { background-color: #007bff; color: #fff; }
        
        /* Switch styles */
        .switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }
        
        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
        }
        
        .slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
        }
        
        input:checked + .slider {
            background-color: #28a745;
        }
        
        input:focus + .slider {
            box-shadow: 0 0 1px #28a745;
        }
        
        input:checked + .slider:before {
            transform: translateX(26px);
        }
        
        .slider.round {
            border-radius: 24px;
        }
        
        .slider.round:before {
            border-radius: 50%;
        }
        
        /* Map legend styles */
        .legend-item {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
        }
        
        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            margin-right: 10px;
            border: 2px solid white;
            box-shadow: 0 0 5px rgba(0,0,0,0.3);
        }
        
        .legend-route {
            width: 30px;
            height: 5px;
            margin-right: 10px;
        }
        
        .taxi-free { background-color: #28a745; }
        .taxi-pob { background-color: #dc3545; }
        .taxi-enroute { background-color: #ffc107; }
        .taxi-about-free { background-color: #17a2b8; }
        .route-orange { background-color: #ff9800; }
        .route-black { background-color: #333; }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2><?php echo $company_name; ?></h2>
                <button class="toggle-sidebar">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="drivers.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'drivers.php' ? 'active' : ''; ?>"><i class="fas fa-id-card"></i> Drivers</a></li>
                    <li><a href="passengers.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'passengers.php' ? 'active' : ''; ?>"><i class="fas fa-user-friends"></i> Passengers</a></li>
                    <li><a href="rides.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'rides.php' ? 'active' : ''; ?>"><i class="fas fa-route"></i> Rides</a></li>
                    <li><a href="car-types.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'car-types.php' ? 'active' : ''; ?>"><i class="fas fa-car"></i> Car Types</a></li>
                    <li><a href="settings.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>"><i class="fas fa-cog"></i> Settings</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <header class="main-header">
                <h1><?php echo $page_title; ?></h1>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?></span>
                </div>
            </header>
            
            <div class="content-wrapper">