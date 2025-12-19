<?php
// ========================================
// dashboard_admin.ctp
// ========================================
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Care Health Insurance</title>
    <?php echo $this->Html->css('auth'); ?>
</head>
<body>
    <div class="auth-container">
        <header class="auth-header">
            <div class="header-content">
                <div class="header-logo">
                    <?php echo $this->Html->image('careLogo.png', array('alt' => 'Care Health Insurance')); ?>
                </div>
                <div class="header-right">
                    <span>Welcome, <?php echo h($user['full_name']); ?> (Admin)</span>
                    <?php echo $this->Html->link('Logout', array('action' => 'logout'), array('class' => 'btn btn-signup')); ?>
                </div>
            </div>
        </header>
        
        <div class="auth-card" style="margin-top: 50px;">
            <div class="auth-content">
                <h1 class="auth-title">Admin Dashboard</h1>
                <p>Welcome to your admin dashboard, <?php echo h($user['full_name']); ?>!</p>
                <p>You are logged in as an <strong>Administrator</strong> with elevated privileges.</p>
                
                <div style="margin-top: 30px;">
                    <h3>Admin Controls:</h3>
                    <ul>
                        <li>User Management</li>
                        <li>System Configuration</li>
                        <li>Reports & Analytics</li>
                        <li>Content Management</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
