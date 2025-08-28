<?php
include 'includes/auth.php';
// admin-panel/passengers.php
$page_title = "Passengers";
include 'includes/header.php';

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_passenger'])) {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $password = password_hash('password123', PASSWORD_DEFAULT); // Default password
        
        // Check if email already exists
        $check_email = $conn->prepare("SELECT id FROM passengers WHERE email = ?");
        $check_email->bind_param("s", $email);
        $check_email->execute();
        
        if ($check_email->get_result()->num_rows > 0) {
            $error = "Email already exists";
        } else {
            $sql = "INSERT INTO passengers (name, email, phone, password) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $name, $email, $phone, $password);
            
            if ($stmt->execute()) {
                header("Location: passengers.php?success=Passenger added successfully");
                exit;
            } else {
                $error = "Error adding passenger: " . $conn->error;
            }
        }
    } elseif (isset($_POST['update_passenger'])) {
        $id = intval($_POST['id']);
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        
        $sql = "UPDATE passengers SET name=?, email=?, phone=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $name, $email, $phone, $id);
        $stmt->execute();
        
        header("Location: passengers.php?success=Passenger updated successfully");
        exit;
    } elseif (isset($_POST['delete_passenger'])) {
        $id = intval($_POST['id']);
        
        $sql = "DELETE FROM passengers WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        header("Location: passengers.php?success=Passenger deleted successfully");
        exit;
    }
}

// Get all passengers
$passengers = $conn->query("SELECT * FROM passengers ORDER BY name");
?>

<div class="content">
    <h1>Passengers</h1>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success"><?php echo $_GET['success']; ?></div>
    <?php endif; ?>

    <div class="table-section">
        <h2>Existing Passengers</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($passenger = $passengers->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $passenger['id']; ?></td>
                    <td><?php echo htmlspecialchars($passenger['name']); ?></td>
                    <td><?php echo htmlspecialchars($passenger['email']); ?></td>
                    <td><?php echo htmlspecialchars($passenger['phone']); ?></td>
                    <td>
                        <a href="#" class="btn btn-sm btn-info btn-edit"
                           data-id="<?php echo $passenger['id']; ?>"
                           data-name="<?php echo $passenger['name']; ?>"
                           data-email="<?php echo $passenger['email']; ?>"
                           data-phone="<?php echo $passenger['phone']; ?>">Edit</a>
                        <form method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this passenger?');">
                            <input type="hidden" name="delete_passenger" value="1">
                            <input type="hidden" name="id" value="<?php echo $passenger['id']; ?>">
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div class="form-section">
        <h2>Add New Passenger</h2>
        <form method="POST">
            <input type="hidden" name="add_passenger" value="1">
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
            <button type="submit" class="btn btn-primary">Add Passenger</button>
        </form>
    </div>
</div>

<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Edit Passenger</h2>
        <form method="POST">
            <input type="hidden" name="update_passenger" value="1">
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
            <button type="submit" class="btn btn-primary">Update Passenger</button>
        </form>
    </div>
</div>

<script>
// Modal functionality for passengers
document.querySelectorAll('.btn-edit').forEach(button => {
    button.addEventListener('click', function(e) {
        e.preventDefault();
        
        document.getElementById('edit_id').value = this.getAttribute('data-id');
        document.getElementById('edit_name').value = this.getAttribute('data-name');
        document.getElementById('edit_email').value = this.getAttribute('data-email');
        document.getElementById('edit_phone').value = this.getAttribute('data-phone');
        
        document.getElementById('editModal').style.display = 'block';
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