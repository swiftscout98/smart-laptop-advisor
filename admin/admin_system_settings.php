<?php
/**
 * System Settings Page
 * Module E: User & System Administration
 * Configure system-wide settings and preferences
 */

// Start session and include necessary files
session_start();
require_once 'includes/db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

// ==================== HANDLE FORM SUBMISSION ====================
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    $category = $_POST['category'];
    
    // Begin transaction
    mysqli_begin_transaction($conn);
    
    try {
        foreach ($_POST as $key => $value) {
            if ($key !== 'update_settings' && $key !== 'category') {
                // Update setting
                $update_query = "UPDATE system_settings SET setting_value = ? WHERE setting_key = ?";
                $stmt = mysqli_prepare($conn, $update_query);
                mysqli_stmt_bind_param($stmt, 'ss', $value, $key);
                mysqli_stmt_execute($stmt);
            }
        }
        
        mysqli_commit($conn);
        $success_message = ucfirst($category) . " settings updated successfully!";
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $error_message = "Error updating settings: " . $e->getMessage();
    }
}

// ==================== DATA FETCHING ====================

// Fetch all settings grouped by category
$settings_query = "SELECT 
    setting_key,
    setting_value,
    setting_type,
    category,
    description,
    is_editable
FROM system_settings
WHERE category = 'general'
ORDER BY setting_key";

$settings_result = mysqli_query($conn, $settings_query);
$settings_by_category = [];
while ($row = mysqli_fetch_assoc($settings_result)) {
    $category = $row['category'];
    if (!isset($settings_by_category[$category])) {
        $settings_by_category[$category] = [];
    }
    $settings_by_category[$category][] = $row;
}

// ==================== VIEW LAYER ====================
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Settings - Smart Laptop Advisor</title>
    
    <!-- CSS Files -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="source/assets/css/bootstrap.css">
    <link rel="stylesheet" href="source/assets/vendors/iconly/bold.css">
    <link rel="stylesheet" href="source/assets/vendors/perfect-scrollbar/perfect-scrollbar.css">
    <link rel="stylesheet" href="source/assets/vendors/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" href="source/assets/css/app.css">
    <link rel="shortcut icon" href="source/assets/images/favicon.svg" type="image/x-icon">
    
    <style>
        .settings-sidebar {
            position: sticky;
            top: 20px;
        }
        .settings-sidebar .nav-link {
            color: #495057;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            transition: all 0.3s;
        }
        .settings-sidebar .nav-link:hover {
            background-color: #f8f9fa;
        }
        .settings-sidebar .nav-link.active {
            background-color: #435ebe;
            color: white;
        }
        .settings-section {
            display: none;
        }
        .settings-section.active {
            display: block;
        }
        .setting-item {
            border-bottom: 1px solid #dee2e6;
            padding: 1.5rem 0;
        }
        .setting-item:last-child {
            border-bottom: none;
        }
        .setting-label {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        .setting-description {
            color: #6c757d;
            font-size: 0.875rem;
            margin-bottom: 0.75rem;
        }
        .maintenance-warning {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }
    </style>
</head>

<body>
    <div id="app">
        <?php include 'includes/admin_header.php'; ?>

        <div id="main">
            <header class="mb-3">
                <a href="#" class="burger-btn d-block d-xl-none">
                    <i class="bi bi-justify fs-3"></i>
                </a>
            </header>

            <div class="page-heading">
                <div class="page-title">
                    <div class="row">
                        <div class="col-12 col-md-6 order-md-1 order-last">
                            <h3>System Settings</h3>
                            <p class="text-subtitle text-muted">Configure system-wide settings and preferences</p>
                        </div>
                        <div class="col-12 col-md-6 order-md-2 order-first">
                            <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="admin_dashboard.php">Dashboard</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Settings</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>

                <?php if ($success_message): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-2"></i><?php echo $success_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error_message): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i><?php echo $error_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Settings Layout -->
                <section class="section">
                    <div class="row">
                        <!-- Settings Content -->
                        <div class="col-12">
                            <?php foreach ($settings_by_category as $category => $settings): ?>
                                <div class="settings-section active" id="section-<?php echo $category; ?>">
                                    <div class="card">
                                        <div class="card-header">
                                            <h4 class="card-title mb-0"><?php echo ucfirst($category); ?> Settings</h4>
                                        </div>
                                        <div class="card-body">
                                            <?php if ($category === 'maintenance'): ?>
                                                <div class="maintenance-warning">
                                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                                    <strong>Warning:</strong> Enabling maintenance mode will make the site unavailable to all users except administrators.
                                                </div>
                                            <?php endif; ?>
                                            
                                            <form method="POST" action="">
                                                <input type="hidden" name="category" value="<?php echo $category; ?>">
                                                
                                                <?php foreach ($settings as $setting): ?>
                                                    <div class="setting-item">
                                                        <label class="setting-label">
                                                            <?php echo ucwords(str_replace('_', ' ', str_replace($category . '_', '', $setting['setting_key']))); ?>
                                                        </label>
                                                        
                                                        <?php if ($setting['description']): ?>
                                                            <div class="setting-description">
                                                                <?php echo htmlspecialchars($setting['description']); ?>
                                                            </div>
                                                        <?php endif; ?>
                                                        
                                                        <?php if ($setting['is_editable']): ?>
                                                            <?php if ($setting['setting_type'] === 'boolean'): ?>
                                                                <div class="form-check form-switch">
                                                                    <input class="form-check-input" type="checkbox" 
                                                                           name="<?php echo $setting['setting_key']; ?>" 
                                                                           value="1"
                                                                           <?php echo $setting['setting_value'] == '1' ? 'checked' : ''; ?>>
                                                                    <label class="form-check-label">
                                                                        <?php echo $setting['setting_value'] == '1' ? 'Enabled' : 'Disabled'; ?>
                                                                    </label>
                                                                </div>
                                                            <?php elseif ($setting['setting_type'] === 'text'): ?>
                                                                <textarea class="form-control" 
                                                                          name="<?php echo $setting['setting_key']; ?>" 
                                                                          rows="3"><?php echo htmlspecialchars($setting['setting_value']); ?></textarea>
                                                            <?php elseif ($setting['setting_type'] === 'integer'): ?>
                                                                <input type="number" class="form-control" 
                                                                       name="<?php echo $setting['setting_key']; ?>" 
                                                                       value="<?php echo htmlspecialchars($setting['setting_value']); ?>">
                                                            <?php else: ?>
                                                                <input type="text" class="form-control" 
                                                                       name="<?php echo $setting['setting_key']; ?>" 
                                                                       value="<?php echo htmlspecialchars($setting['setting_value']); ?>">
                                                            <?php endif; ?>
                                                        <?php else: ?>
                                                            <div class="alert alert-light mb-0">
                                                                <i class="bi bi-lock me-2"></i>
                                                                This value is read-only for security reasons.
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endforeach; ?>
                                                
                                                <div class="mt-4">
                                                    <button type="submit" name="update_settings" class="btn btn-primary">
                                                        <i class="bi bi-save me-2"></i>Save <?php echo ucfirst($category); ?> Settings
                                                    </button>
                                                    <button type="button" class="btn btn-outline-secondary" onclick="location.reload()">
                                                        <i class="bi bi-arrow-counterclockwise me-2"></i>Reset
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </section>

                <!-- System Information -->
                <section class="section mt-4">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">System Information</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-sm">
                                        <tr>
                                            <th width="40%">PHP Version</th>
                                            <td><?php echo phpversion(); ?></td>
                                        </tr>
                                        <tr>
                                            <th>MySQL Version</th>
                                            <td><?php echo mysqli_get_server_info($conn); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Server Software</th>
                                            <td><?php echo $_SERVER['SERVER_SOFTWARE']; ?></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-sm">
                                        <tr>
                                            <th width="40%">Server Time</th>
                                            <td><?php echo date('Y-m-d H:i:s'); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Max Upload Size</th>
                                            <td><?php echo ini_get('upload_max_filesize'); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Memory Limit</th>
                                            <td><?php echo ini_get('memory_limit'); ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>


            </div>

            <?php include 'includes/admin_footer.php'; ?>
        </div>
    </div>

    <!-- JavaScript Files -->
    <script src="source/assets/js/bootstrap.js"></script>
    <script src="source/assets/js/app.js"></script>

    <script>
        // Update toggle label text
        document.querySelectorAll('.form-check-input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const label = this.nextElementSibling;
                if (label && label.classList.contains('form-check-label')) {
                    label.textContent = this.checked ? 'Enabled' : 'Disabled';
                }
            });
        });
    </script>
</body>
</html>

<?php
// Close database connection
mysqli_close($conn);
?>
