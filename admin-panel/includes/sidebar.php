<?php
// admin-panel/includes/sidebar.php

// Include database configuration to get company name
include 'config.php';

// Get company name from settings
$company_name = "Taxi Booking System"; // Default value

// Try to get company name from database
$result = $conn->query("SELECT setting_value FROM settings WHERE setting_key = 'company_name'");
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $company_name = $row['setting_value'];
}

$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar">
    <div class="sidebar-header">
        <h2><?php echo htmlspecialchars($company_name); ?></h2>
        <p>Admin Panel</p>
    </div>
    
    <ul class="sidebar-menu">
        <li class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
            <a href="dashboard.php">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li class="<?php echo $current_page == 'drivers.php' ? 'active' : ''; ?>">
            <a href="drivers.php">
                <i class="fas fa-id-card"></i>
                <span>Drivers</span>
            </a>
        </li>
        <li class="<?php echo $current_page == 'passengers.php' ? 'active' : ''; ?>">
            <a href="passengers.php">
                <i class="fas fa-users"></i>
                <span>Passengers</span>
            </a>
        </li>
        <li class="<?php echo $current_page == 'rides.php' ? 'active' : ''; ?>">
            <a href="rides.php">
                <i class="fas fa-route"></i>
                <span>Rides</span>
            </a>
        </li>
        <li class="<?php echo $current_page == 'car-types.php' ? 'active' : ''; ?>">
            <a href="car-types.php">
                <i class="fas fa-car"></i>
                <span>Car Types</span>
            </a>
        </li>
        <li class="<?php echo $current_page == 'settings.php' ? 'active' : ''; ?>">
            <a href="settings.php">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
        </li>
        <li>
            <a href="logout.php">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </li>
    </ul>
</div>