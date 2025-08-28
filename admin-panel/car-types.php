<?php
// admin-panel/car-types.php
include 'includes/auth.php';
$page_title = "Car Types";
include 'includes/header.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_car_type'])) {
        // Add new car type
        $name = $_POST['name'];
        $base_fare = $_POST['base_fare'];
        $per_km = $_POST['per_km'];
        $per_minute = $_POST['per_minute'];
        $waiting_fee = $_POST['waiting_fee'];
        $status = isset($_POST['status']) ? 1 : 0;
        
        // Check if status column exists
        $check_column = $conn->query("SHOW COLUMNS FROM car_types LIKE 'status'");
        if ($check_column->num_rows > 0) {
            $stmt = $conn->prepare("INSERT INTO car_types (name, base_fare, per_km, per_minute, waiting_fee, status) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sddddi", $name, $base_fare, $per_km, $per_minute, $waiting_fee, $status);
        } else {
            // If status column doesn't exist, insert without it
            $stmt = $conn->prepare("INSERT INTO car_types (name, base_fare, per_km, per_minute, waiting_fee) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sdddd", $name, $base_fare, $per_km, $per_minute, $waiting_fee);
        }
        
        if ($stmt->execute()) {
            $success_msg = "Car type added successfully!";
        } else {
            $error_msg = "Error adding car type: " . $conn->error;
        }
    } elseif (isset($_POST['edit_car_type'])) {
        // Edit existing car type
        $id = $_POST['edit_id'];
        $name = $_POST['edit_name'];
        $base_fare = $_POST['edit_base_fare'];
        $per_km = $_POST['edit_per_km'];
        $per_minute = $_POST['edit_per_minute'];
        $waiting_fee = $_POST['edit_waiting_fee'];
        $status = isset($_POST['edit_status']) ? 1 : 0;
        
        // Check if status column exists
        $check_column = $conn->query("SHOW COLUMNS FROM car_types LIKE 'status'");
        if ($check_column->num_rows > 0) {
            $stmt = $conn->prepare("UPDATE car_types SET name = ?, base_fare = ?, per_km = ?, per_minute = ?, waiting_fee = ?, status = ? WHERE id = ?");
            $stmt->bind_param("sddddii", $name, $base_fare, $per_km, $per_minute, $waiting_fee, $status, $id);
        } else {
            // If status column doesn't exist, update without it
            $stmt = $conn->prepare("UPDATE car_types SET name = ?, base_fare = ?, per_km = ?, per_minute = ?, waiting_fee = ? WHERE id = ?");
            $stmt->bind_param("sddddi", $name, $base_fare, $per_km, $per_minute, $waiting_fee, $id);
        }
        
        if ($stmt->execute()) {
            $success_msg = "Car type updated successfully!";
        } else {
            $error_msg = "Error updating car type: " . $conn->error;
        }
    }
}

// Get all car types
$car_types = $conn->query("SELECT * FROM car_types ORDER BY id DESC");

// Check if status column exists
$status_column_exists = $conn->query("SHOW COLUMNS FROM car_types LIKE 'status'")->num_rows > 0;
?>

<div class="content">
    <h1>Car Types</h1>
    
    <?php if (isset($success_msg)): ?>
        <div class="alert alert-success"><?php echo $success_msg; ?></div>
    <?php endif; ?>
    
    <?php if (isset($error_msg)): ?>
        <div class="alert alert-danger"><?php echo $error_msg; ?></div>
    <?php endif; ?>
    
    <div class="form-section">
        <h2>Add New Car Type</h2>
        <form method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label for="name">Car Type Name</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="base_fare">Base Fare (<?php echo $currency; ?>)</label>
                    <input type="number" id="base_fare" name="base_fare" step="0.01" min="0" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="per_km">Per KM Rate (<?php echo $currency; ?>)</label>
                    <input type="number" id="per_km" name="per_km" step="0.01" min="0" required>
                </div>
                <div class="form-group">
                    <label for="per_minute">Per Minute Rate (<?php echo $currency; ?>)</label>
                    <input type="number" id="per_minute" name="per_minute" step="0.01" min="0" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="waiting_fee">Waiting Fee (<?php echo $currency; ?> per minute)</label>
                    <input type="number" id="waiting_fee" name="waiting_fee" step="0.01" min="0" required>
                </div>
                <?php if ($status_column_exists): ?>
                <div class="form-group">
                    <label for="status">Status</label>
                    <div style="display: flex; align-items: center; margin-top: 8px;">
                        <label class="switch">
                            <input type="checkbox" id="status" name="status" checked>
                            <span class="slider round"></span>
                        </label>
                        <span style="margin-left: 10px;">Active</span>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <button type="submit" name="add_car_type" class="btn btn-primary">Add Car Type</button>
        </form>
    </div>
    
    <div class="list-section">
        <h2>Existing Car Types</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Base Fare</th>
                    <th>Per KM Rate</th>
                    <th>Per Minute Rate</th>
                    <th>Waiting Fee</th>
                    <?php if ($status_column_exists): ?>
                    <th>Status</th>
                    <?php endif; ?>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($car_types && $car_types->num_rows > 0): ?>
                    <?php while ($car_type = $car_types->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $car_type['id']; ?></td>
                            <td><?php echo htmlspecialchars($car_type['name']); ?></td>
                            <td><?php echo $currency . number_format($car_type['base_fare'], 2); ?></td>
                            <td><?php echo $currency . number_format($car_type['per_km'], 2); ?></td>
                            <td><?php echo $currency . number_format($car_type['per_minute'], 2); ?></td>
                            <td><?php echo $currency . number_format($car_type['waiting_fee'], 2); ?></td>
                            <?php if ($status_column_exists): ?>
                            <td>
                                <label class="switch">
                                    <input type="checkbox" class="status-toggle" data-id="<?php echo $car_type['id']; ?>" data-table="car_types" <?php echo isset($car_type['status']) && $car_type['status'] ? 'checked' : ''; ?>>
                                    <span class="slider round"></span>
                                </label>
                            </td>
                            <?php endif; ?>
                            <td>
                                <button class="btn btn-sm btn-primary btn-edit" 
                                        data-id="<?php echo $car_type['id']; ?>"
                                        data-name="<?php echo htmlspecialchars($car_type['name']); ?>"
                                        data-base_fare="<?php echo $car_type['base_fare']; ?>"
                                        data-per_km="<?php echo $car_type['per_km']; ?>"
                                        data-per_minute="<?php echo $car_type['per_minute']; ?>"
                                        data-waiting_fee="<?php echo $car_type['waiting_fee']; ?>"
                                        data-status="<?php echo isset($car_type['status']) ? $car_type['status'] : 1; ?>">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="<?php echo $status_column_exists ? 8 : 7; ?>" style="text-align: center;">No car types found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Edit Car Type</h2>
        <form method="POST">
            <input type="hidden" id="edit_id" name="edit_id">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="edit_name">Car Type Name</label>
                    <input type="text" id="edit_name" name="edit_name" required>
                </div>
                <div class="form-group">
                    <label for="edit_base_fare">Base Fare (<?php echo $currency; ?>)</label>
                    <input type="number" id="edit_base_fare" name="edit_base_fare" step="0.01" min="0" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="edit_per_km">Per KM Rate (<?php echo $currency; ?>)</label>
                    <input type="number" id="edit_per_km" name="edit_per_km" step="0.01" min="0" required>
                </div>
                <div class="form-group">
                    <label for="edit_per_minute">Per Minute Rate (<?php echo $currency; ?>)</label>
                    <input type="number" id="edit_per_minute" name="edit_per_minute" step="0.01" min="0" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="edit_waiting_fee">Waiting Fee (<?php echo $currency; ?> per minute)</label>
                    <input type="number" id="edit_waiting_fee" name="edit_waiting_fee" step="0.01" min="0" required>
                </div>
                <?php if ($status_column_exists): ?>
                <div class="form-group">
                    <label for="edit_status">Status</label>
                    <div style="display: flex; align-items: center; margin-top: 8px;">
                        <label class="switch">
                            <input type="checkbox" id="edit_status" name="edit_status">
                            <span class="slider round"></span>
                        </label>
                        <span style="margin-left: 10px;">Active</span>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <button type="submit" name="edit_car_type" class="btn btn-primary">Update Car Type</button>
        </form>
    </div>
</div>

<?php
include 'includes/footer.php';
?>