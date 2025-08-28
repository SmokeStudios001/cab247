<?php
// dashboard.php
$page_title = "Dashboard";
require_once 'includes/header.php';

// Get dashboard statistics
$total_drivers = 0;
$total_passengers = 0;
$total_rides = 0;
$total_earnings = 0;

// Get driver count
$result = $conn->query("SELECT COUNT(*) as count FROM drivers");
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $total_drivers = $row['count'];
}

// Get passenger count
$result = $conn->query("SELECT COUNT(*) as count FROM passengers");
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $total_passengers = $row['count'];
}

// Get ride count
$result = $conn->query("SELECT COUNT(*) as count FROM rides");
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $total_rides = $row['count'];
}

// Get total earnings
$result = $conn->query("SELECT SUM(fare) as total FROM rides WHERE status = 'completed'");
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $total_earnings = $row['total'] ? $row['total'] : 0;
}
?>

<div class="dashboard-content">
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-id-card"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $total_drivers; ?></h3>
                <p>Total Drivers</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $total_passengers; ?></h3>
                <p>Total Passengers</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-route"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $total_rides; ?></h3>
                <p>Total Rides</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $currency_symbol . number_format($total_earnings, 2); ?></h3>
                <p>Total Earnings</p>
            </div>
        </div>
    </div>
    
    <div class="recent-activities">
        <h2>Recent Rides</h2>
        <div class="activities-list">
            <?php
            // Get recent rides
            $result = $conn->query("
                SELECT r.*, d.name as driver_name, p.name as passenger_name 
                FROM rides r 
                LEFT JOIN drivers d ON r.driver_id = d.id 
                LEFT JOIN passengers p ON r.passenger_id = p.id 
                ORDER BY r.requested_at DESC 
                LIMIT 5
            ");
            
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '<div class="activity-item">';
                    echo '<div class="activity-info">';
                    echo '<h4>Ride #' . $row['id'] . '</h4>';
                    echo '<p>From: ' . htmlspecialchars($row['pickup_address']) . '</p>';
                    echo '<p>To: ' . htmlspecialchars($row['destination_address']) . '</p>';
                    echo '<p>Driver: ' . htmlspecialchars($row['driver_name'] ?? 'N/A') . '</p>';
                    echo '<p>Passenger: ' . htmlspecialchars($row['passenger_name'] ?? 'N/A') . '</p>';
                    echo '</div>';
                    echo '<div class="activity-status">';
                    echo '<span class="status-badge status-' . $row['status'] . '">' . ucfirst(str_replace('_', ' ', $row['status'])) . '</span>';
                    echo '<p>' . $currency_symbol . number_format($row['fare'], 2) . '</p>';
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo '<p>No recent rides found.</p>';
            }
            ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>