<?php
// admin-panel/drivers.php
include 'includes/auth.php';
$page_title = "Drivers";
include 'includes/header.php';

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_driver'])) {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $license_number = trim($_POST['license_number']);
        $car_model = trim($_POST['car_model']);
        $car_number = trim($_POST['car_number']);
        $car_type_id = isset($_POST['car_type_id']) ? intval($_POST['car_type_id']) : 0;
        
        // Check if email already exists
        $check_email = $conn->prepare("SELECT id FROM drivers WHERE email = ?");
        $check_email->bind_param("s", $email);
        $check_email->execute();
        
        if ($check_email->get_result()->num_rows > 0) {
            $error = "Email already exists";
        } else {
            // Default password
            $password = password_hash('password123', PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO drivers (name, email, phone, password, license_number, car_model, car_number, car_type_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssi", $name, $email, $phone, $password, $license_number, $car_model, $car_number, $car_type_id);
            
            if ($stmt->execute()) {
                header("Location: drivers.php?success=Driver added successfully");
                exit;
            } else {
                $error = "Error adding driver: " . $conn->error;
            }
        }
    } elseif (isset($_POST['update_driver'])) {
        $id = intval($_POST['id']);
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $license_number = trim($_POST['license_number']);
        $car_model = trim($_POST['car_model']);
        $car_number = trim($_POST['car_number']);
        $car_type_id = isset($_POST['car_type_id']) ? intval($_POST['car_type_id']) : 0;

        $sql = "UPDATE drivers SET name=?, email=?, phone=?, license_number=?, car_model=?, car_number=?, car_type_id=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssii", $name, $email, $phone, $license_number, $car_model, $car_number, $car_type_id, $id);
        
        if ($stmt->execute()) {
            header("Location: drivers.php?success=Driver updated successfully");
            exit;
        } else {
            $error = "Error updating driver: " . $conn->error;
        }
    } elseif (isset($_POST['delete_driver'])) {
        $id = intval($_POST['id']);
        
        $sql = "DELETE FROM drivers WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            header("Location: drivers.php?success=Driver deleted successfully");
            exit;
        } else {
            $error = "Error deleting driver: " . $conn->error;
        }
    }
}

// Handle status updates
if (isset($_GET['update_status']) && isset($_GET['status'])) {
    $driverId = intval($_GET['update_status']);
    $status = $conn->real_escape_string($_GET['status']);

    $sql = "UPDATE drivers SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $driverId);
    
    if ($stmt->execute()) {
        header("Location: drivers.php?success=Driver status updated successfully");
        exit;
    } else {
        $error = "Error updating driver status: " . $conn->error;
    }
}

// Handle verification toggling
if (isset($_GET['toggle_verification'])) {
    $driverId = intval($_GET['toggle_verification']);

    // Get current verification status
    $current_status_sql = "SELECT is_verified FROM drivers WHERE id = ?";
    $current_status_stmt = $conn->prepare($current_status_sql);
    $current_status_stmt->bind_param("i", $driverId);
    $current_status_stmt->execute();
    $current_status_result = $current_status_stmt->get_result();
    
    if ($current_status_result->num_rows > 0) {
        $current_status = $current_status_result->fetch_assoc()['is_verified'];
        
        // Toggle status
        $new_status = $current_status == 1 ? 0 : 1;

        $sql = "UPDATE drivers SET is_verified = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $new_status, $driverId);
        
        if ($stmt->execute()) {
            header("Location: drivers.php?success=Driver verification updated successfully");
            exit;
        } else {
            $error = "Error updating driver verification: " . $conn->error;
        }
    } else {
        $error = "Driver not found";
    }
}

// Get all drivers with error handling
$drivers_result = $conn->query("SELECT d.*, ct.name as car_type_name FROM drivers d LEFT JOIN car_types ct ON d.car_type_id = ct.id ORDER BY d.name");

if ($drivers_result === false) {
    // If the join fails, try a simpler query without the join
    $drivers_result = $conn->query("SELECT * FROM drivers ORDER BY name");
    $simple_query = true;
} else {
    $simple_query = false;
}

// Get car types for dropdown
$car_types = $conn->query("SELECT id, name FROM car_types");
?>

<div class="content">
    <h1>Drivers</h1>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success"><?php echo $_GET['success']; ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger"><?php echo $_GET['error']; ?></div>
    <?php endif; ?>
    
    <div class="table-section">
        <h2>Existing Drivers</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Car</th>
                    <?php if (!$simple_query): ?>
                    <th>Car Type</th>
                    <?php endif; ?>
                    <th>Status</th>
                    <th>Verified</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($drivers_result && $drivers_result->num_rows > 0): ?>
                    <?php while ($driver = $drivers_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $driver['id']; ?></td>
                        <td><?php echo htmlspecialchars($driver['name']); ?></td>
                        <td><?php echo htmlspecialchars($driver['email']); ?></td>
                        <td><?php echo htmlspecialchars($driver['phone']); ?></td>
                        <td><?php echo htmlspecialchars($driver['car_model'] . ' (' . $driver['car_number'] . ')'); ?></td>
                        <?php if (!$simple_query): ?>
                        <td><?php echo htmlspecialchars($driver['car_type_name']); ?></td>
                        <?php endif; ?>
                        <td>
                            <select class="status-select" data-id="<?php echo $driver['id']; ?>" data-original-value="<?php echo $driver['status']; ?>">
                                <option value="online" <?php echo $driver['status'] == 'online' ? 'selected' : ''; ?>>Online</option>
                                <option value="offline" <?php echo $driver['status'] == 'offline' ? 'selected' : ''; ?>>Offline</option>
                                <option value="on_ride" <?php echo $driver['status'] == 'on_ride' ? 'selected' : ''; ?>>On Ride</option>
                            </select>
                        </td>
                        <td>
                            <label class="switch">
                                <input type="checkbox" class="verification-toggle" data-id="<?php echo $driver['id']; ?>" <?php echo $driver['is_verified'] ? 'checked' : ''; ?>>
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td>
                            <a href="#" class="btn btn-sm btn-info btn-edit"
                               data-id="<?php echo $driver['id']; ?>"
                               data-name="<?php echo $driver['name']; ?>"
                               data-email="<?php echo $driver['email']; ?>"
                               data-phone="<?php echo $driver['phone']; ?>"
                               data-license_number="<?php echo $driver['license_number']; ?>"
                               data-car_model="<?php echo $driver['car_model']; ?>"
                               data-car_number="<?php echo $driver['car_number']; ?>"
                               data-car_type_id="<?php echo $driver['car_type_id']; ?>">Edit</a>
                            <form method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this driver?');">
                                <input type="hidden" name="delete_driver" value="1">
                                <input type="hidden" name="id" value="<?php echo $driver['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="<?php echo $simple_query ? 8 : 9; ?>" style="text-align: center;">No drivers found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
</table>
    </div>
    
    <div class="form-section">
        <h2>Add New Driver</h2>
        <form method="POST">
            <input type="hidden" name="add_driver" value="1">
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="text" id="phone" name="phone" required>
            </div>
            <div class="form-group">
                <label for="license_number">License Number</label>
                <input type="text" id="license_number" name="license_number" required>
            </div>
            <div class="form-group">
                <label for="car_model">Car Model</label>
                <input type="text" id="car_model" name="car_model" required>
            </div>
            <div class="form-group">
                <label for="car_number">Car Number</label>
                <input type="text" id="car_number" name="car_number" required>
            </div>
            <div class="form-group">
                <label for="car_type_id">Car Type</label>
                <select id="car_type_id" name="car_type_id" required>
                    <option value="">Select Car Type</option>
                    <?php if ($car_types && $car_types->num_rows > 0): ?>
                        <?php while ($type = $car_types->fetch_assoc()): ?>
                        <option value="<?php echo $type['id']; ?>"><?php echo $type['name']; ?></option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Add Driver</button>
        </form>
    </div>
</div>

<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Edit Driver</h2>
        <form method="POST">
            <input type="hidden" name="update_driver" value="1">
            <input type="hidden" id="edit_id" name="id">
            <div class="form-group">
                <label for="edit_name">Name</label>
                <input type="text" id="edit_name" name="name" required>
            </div>
            <div class="form-group">
                <label for="edit_email">Email</label>
                <input type="email" id="edit_email" name="email" required>
            </div>
            <div class="form-group">
                <label for="edit_phone">Phone</label>
                <input type="text" id="edit_phone" name="phone" required>
            </div>
            <div class="form-group">
                <label for="edit_license_number">License Number</label>
                <input type="text" id="edit_license_number" name="license_number" required>
            </div>
            <div class="form-group">
                <label for="edit_car_model">Car Model</label>
                <input type="text" id="edit_car_model" name="car_model" required>
            </div>
            <div class="form-group">
                <label for="edit_car_number">Car Number</label>
                <input type="text" id="edit_car_number" name="car_number" required>
            </div>
            <div class="form-group">
                <label for="edit_car_type_id">Car Type</label>
                <select id="edit_car_type_id" name="car_type_id" required>
                    <option value="">Select Car Type</option>
                    <?php
                    // Re-fetch car types for the modal
                    $car_types_modal = $conn->query("SELECT id, name FROM car_types");
                    if ($car_types_modal && $car_types_modal->num_rows > 0):
                        while ($type = $car_types_modal->fetch_assoc()): ?>
                        <option value="<?php echo $type['id']; ?>"><?php echo $type['name']; ?></option>
                        <?php endwhile;
                    endif; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update Driver</button>
        </form>
    </div>
</div>

<script>
// Modal functionality for drivers
document.querySelectorAll('.btn-edit').forEach(button => {
    button.addEventListener('click', function(e) {
        e.preventDefault();
        
        document.getElementById('edit_id').value = this.getAttribute('data-id');
        document.getElementById('edit_name').value = this.getAttribute('data-name');
        document.getElementById('edit_email').value = this.getAttribute('data-email');
        document.getElementById('edit_phone').value = this.getAttribute('data-phone');
        document.getElementById('edit_license_number').value = this.getAttribute('data-license_number');
        document.getElementById('edit_car_model').value = this.getAttribute('data-car_model');
        document.getElementById('edit_car_number').value = this.getAttribute('data-car_number');
        document.getElementById('edit_car_type_id').value = this.getAttribute('data-car_type_id');
        
        document.getElementById('editModal').style.display = 'block';
    });
});

// Status selection handling
document.querySelectorAll('.status-select').forEach(select => {
    select.addEventListener('change', function() {
        const driverId = this.getAttribute('data-id');
        const status = this.value;
        
        if (confirm('Are you sure you want to change the driver status to ' + status + '?')) {
            window.location.href = `drivers.php?update_status=${driverId}&status=${status}`;
        } else {
            // Reset to original value if cancelled
            this.value = this.getAttribute('data-original-value');
        }
    });
});

// Verification toggle handling
document.querySelectorAll('.verification-toggle').forEach(toggle => {
    toggle.addEventListener('change', function() {
        const driverId = this.getAttribute('data-id');
        const isVerified = this.checked;
        const status = isVerified ? 'verify' : 'unverify';
        
        if (confirm('Are you sure you want to ' + status + ' this driver?')) {
            window.location.href = `drivers.php?toggle_verification=${driverId}`;
        } else {
            // Reset to original state if cancelled
            this.checked = !this.checked;
        }
    });
});

// Close modal when clicking on X
document.querySelector('.close').addEventListener('click', function() {
    document.getElementById('editModal').style.display = 'none';
});

// Close modal when clicking outside
window.addEventListener('click', function(event) {
    if (event.target == document.getElementById('editModal')) {
        document.getElementById('editModal').style.display = 'none';
    }
});
</script>

<?php include 'includes/footer.php'; ?>