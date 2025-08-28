<?php
// includes/functions.php

/**
 * Get a setting value from the database
 * 
 * @param mysqli $conn Database connection
 * @param string $key Setting key
 * @return string Setting value or empty string if not found
 */
function getSetting($conn, $key) {
    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->bind_param("s", $key);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['setting_value'];
    }
    
    return ''; // Return empty string if not found
}

/**
 * Get all settings as an associative array
 * 
 * @param mysqli $conn Database connection
 * @return array Associative array of settings
 */
function getAllSettings($conn) {
    $settings = array();
    $result = $conn->query("SELECT setting_key, setting_value FROM settings");
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    }
    
    return $settings;
}
?>