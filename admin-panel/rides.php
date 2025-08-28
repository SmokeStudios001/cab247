<?php
include 'includes/auth.php';
// admin-panel/rides.php

// Function to get settings from database
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

// Get currency symbol
$currency = getSetting($conn, 'currency');
if (empty($currency)) {
    $currency = '$'; // Default to dollar sign
}

$page_title = "Rides Management";
include 'includes/header.php';

// Get all rides with passenger and driver information
$rides = $conn->query("
    SELECT r.*, p.name as passenger_name, d.name as driver_name, ct.name as car_type_name
    FROM rides r 
    LEFT JOIN passengers p ON r.passenger_id = p.id 
    LEFT JOIN drivers d ON r.driver_id = d.id 
    LEFT JOIN car_types ct ON r.car_type_id = ct.id 
    ORDER BY r.requested_at DESC
");

// Handle ride status updates
if (isset($_GET['update_status']) && isset($_GET['status'])) {
    $rideId = intval($_GET['update_status']);
    $status = $conn->real_escape_string($_GET['status']);
    
    $sql = "UPDATE rides SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $rideId);
    
    if ($stmt->execute()) {
        header("Location: rides.php?success=Ride status updated successfully");
        exit;
    } else {
        $error = "Error updating ride status: " . $conn->error;
    }
}

// Handle ride deletion
if (isset($_GET['delete_ride'])) {
    $rideId = intval($_GET['delete_ride']);
    
    $sql = "DELETE FROM rides WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $rideId);
    
    if ($stmt->execute()) {
        header("Location: rides.php?success=Ride deleted successfully");
        exit;
    } else {
        $error = "Error deleting ride: " . $conn->error;
    }
}
?>

<div class="content">
    <h1>Rides Management</h1>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success"><?php echo $_GET['success']; ?></div>
    <?php endif; ?>
    
    <div class="table-section">
        <h2>All Rides</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Passenger</th>
                    <th>Driver</th>
                    <th>Car Type</th>
                    <th>Pickup</th>
                    <th>Destination</th>
                    <th>Distance</th>
                    <th>Fare</th>
                    <th>Status</th>
                    <th>Requested At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($ride = $rides->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $ride['id']; ?></td>
                    <td><?php echo htmlspecialchars($ride['passenger_name']); ?></td>
                    <td><?php echo htmlspecialchars($ride['driver_name'] ?: 'Not assigned'); ?></td>
                    <td><?php echo htmlspecialchars($ride['car_type_name']); ?></td>
                    <td><?php echo htmlspecialchars(substr($ride['pickup_address'], 0, 30) . '...'); ?></td>
                    <td><?php echo htmlspecialchars(substr($ride['destination_address'], 0, 30) . '...'); ?></td>
                    <td><?php echo $ride['distance'] ? $ride['distance'] . ' km' : 'N/A'; ?></td>
                    <td><?php echo $currency . number_format($ride['fare'], 2); ?></td>
                    <td>
                        <select class="status-select" data-id="<?php echo $ride['id']; ?>" data-original-value="<?php echo $ride['status']; ?>">
                            <option value="pending" <?php echo $ride['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="accepted" <?php echo $ride['status'] == 'accepted' ? 'selected' : ''; ?>>Accepted</option>
                            <option value="driver_arrived" <?php echo $ride['status'] == 'driver_arrived' ? 'selected' : ''; ?>>Driver Arrived</option>
                            <option value="started" <?php echo $ride['status'] == 'started' ? 'selected' : ''; ?>>Started</option>
                            <option value="completed" <?php echo $ride['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="cancelled" <?php echo $ride['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </td>
                    <td><?php echo date('M j, Y g:i A', strtotime($ride['requested_at'])); ?></td>
                    <td>
                        <a href="#" class="btn btn-sm btn-info btn-view-details" data-id="<?php echo $ride['id']; ?>">View Details</a>
                        <a href="rides.php?delete_ride=<?php echo $ride['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this ride?');">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="detailsModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Ride Details</h2>
        <div id="rideDetails"></div>
    </div>
</div>

<script>
// Status selection handling
document.querySelectorAll('.status-select').forEach(select => {
    select.addEventListener('change', function() {
        const rideId = this.getAttribute('data-id');
        const status = this.value;
        
        if (confirm('Are you sure you want to change the ride status to ' + status + '?')) {
            window.location.href = `rides.php?update_status=${rideId}&status=${status}`;
        } else {
            // Reset to original value if cancelled
            this.value = this.getAttribute('data-original-value');
        }
    });
});

// View details modal
document.querySelectorAll('.btn-view-details').forEach(button => {
    button.addEventListener('click', function(e) {
        e.preventDefault();
        const rideId = this.getAttribute('data-id');
        
        fetch(`get-ride-details.php?id=${rideId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const ride = data.data;
                    const detailsHtml = `
                        <h4>Ride Details #${ride.id}</h4>
                        <div class="detail-row">
                            <strong>Passenger:</strong> ${ride.passenger_name} (${ride.passenger_phone})
                        </div>
                        <div class="detail-row">
                            <strong>Driver:</strong> ${ride.driver_name ? `${ride.driver_name} (${ride.driver_phone})` : 'Not assigned'}
                        </div>
                        <div class="detail-row">
                            <strong>Car Type:</strong> ${ride.car_type_name}
                        </div>
                        <div class="detail-row">
                            <strong>Pickup:</strong> ${ride.pickup_address}
                        </div>
                        <div class="detail-row">
                            <strong>Destination:</strong> ${ride.destination_address}
                        </div>
                        <div class="detail-row">
                            <strong>Fare:</strong> ${ride.currency}${ride.fare}
                        </div>
                        <div class="detail-row">
                            <strong>Distance:</strong> ${ride.distance} km
                        </div>
                        <div class="detail-row">
                            <strong>Duration:</strong> ${ride.duration} mins
                        </div>
                        <div class="detail-row">
                            <strong>Status:</strong> <span class="status-badge status-${ride.status}">${ride.status}</span>
                        </div>
                    `;
                    
                    document.getElementById('rideDetails').innerHTML = detailsHtml;
                    document.getElementById('detailsModal').style.display = 'block';
                } else {
                    alert('Error loading ride details');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error loading ride details');
            });
    });
});

// Close modal when clicking on X
document.querySelector('.close').addEventListener('click', function() {
    document.getElementById('detailsModal').style.display = 'none';
});

// Close modal when clicking outside
window.addEventListener('click', function(event) {
    if (event.target == document.getElementById('detailsModal')) {
        document.getElementById('detailsModal').style.display = 'none';
    }
});
</script>

<?php include 'includes/footer.php'; ?>