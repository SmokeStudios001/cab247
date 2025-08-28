<?php
// admin-panel/update-database.php
include 'includes/config.php';

// Add status column to car_types table if it doesn't exist
$check_column = $conn->query("SHOW COLUMNS FROM car_types LIKE 'status'");
if ($check_column->num_rows == 0) {
    $alter_table = $conn->query("ALTER TABLE car_types ADD status TINYINT(1) DEFAULT 1 AFTER waiting_fee");
    if ($alter_table) {
        echo "Successfully added 'status' column to car_types table.<br>";
    } else {
        echo "Error adding column: " . $conn->error . "<br>";
    }
} else {
    echo "'status' column already exists in car_types table.<br>";
}

// Check if other required columns exist
$columns_to_check = ['base_fare', 'per_km', 'per_minute', 'waiting_fee'];
foreach ($columns_to_check as $column) {
    $check = $conn->query("SHOW COLUMNS FROM car_types LIKE '$column'");
    if ($check->num_rows == 0) {
        echo "Column '$column' is missing from car_types table.<br>";
    }
}

echo "Database check complete.";
?>