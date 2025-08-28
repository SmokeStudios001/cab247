<?php
include 'includes/auth.php';
// admin-panel/settings.php
$page_title = "Settings";
include 'includes/header.php';


// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'setting_') === 0) {
            $setting_key = substr($key, 8);
            $setting_value = trim($value);
            
            $sql = "UPDATE settings SET setting_value = ? WHERE setting_key = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $setting_value, $setting_key);
            $stmt->execute();
        }
    }
    
    header("Location: settings.php?success=Settings updated successfully");
    exit;
}

// Get all settings
$settings = $conn->query("SELECT * FROM settings ORDER BY setting_key");
?>

<div class="content">
    <h1>System Settings</h1>
    
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success"><?php echo $_GET['success']; ?></div>
    <?php endif; ?>
    
    <div class="form-section">
        <form method="POST">
            <table class="settings-table">
                <thead>
                    <tr>
                        <th>Setting</th>
                        <th>Value</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($setting = $settings->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <strong><?php echo ucfirst(str_replace('_', ' ', $setting['setting_key'])); ?></strong>
                        </td>
                        <td>
                            <input type="text" name="setting_<?php echo $setting['setting_key']; ?>" 
                                   value="<?php echo htmlspecialchars($setting['setting_value']); ?>" 
                                   class="form-control">
                        </td>
                        <td>
                            <?php echo $setting['description']; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            
            <button type="submit" class="btn btn-primary">Save Settings</button>
        </form>
    </div>
</div>

<style>
.settings-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
}

.settings-table th,
.settings-table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #eee;
}

.settings-table th {
    background-color: #f8f9fa;
    font-weight: 600;
}

.settings-table tr:hover {
    background-color: #f8f9fa;
}

.form-control {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}
</style>

<?php include 'includes/footer.php'; ?>