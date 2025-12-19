<?php
// ========================================
// dashboard_super_user.ctp
// ========================================
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super User Dashboard - Care Health Insurance</title>
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
                    <span>Welcome, <?php echo h($user['full_name']); ?> (Super User)</span>
                    <?php echo $this->Html->link('Logout', array('action' => 'logout'), array('class' => 'btn btn-signup')); ?>
                </div>
            </div>
        </header>
        
        <div class="auth-card" style="margin-top: 50px;">
            <div class="auth-content">
                <h1 class="auth-title">Super User Dashboard</h1>
                <p>Welcome to your super user dashboard, <?php echo h($user['full_name']); ?>!</p>
                <p>You are logged in as a <strong>Super User</strong> with full system access.</p>
                
                <div style="margin-top: 30px;">
                    <h3>Super User Controls:</h3>
                    <ul>
                        <li>Full User Management</li>
                        <li>System Administration</li>
                        <li>Security Settings</li>
                        <li>Database Management</li>
                        <li>Advanced Configuration</li>
                        <li>Audit Logs</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</body>
</html>