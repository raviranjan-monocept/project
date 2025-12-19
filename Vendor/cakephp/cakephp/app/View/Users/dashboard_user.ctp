<?php
/**
 * Dashboard Views for All Roles
 * Place these files in View/Users/
 */

// ========================================
// dashboard_user.ctp
// ========================================
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Care Health Insurance</title>
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
                    <span>Welcome, <?php echo h($user['full_name']); ?> (User)</span>
                    <?php echo $this->Html->link('Logout', array('action' => 'logout'), array('class' => 'btn btn-signup')); ?>
                </div>
            </div>
        </header>
        
        <div class="auth-card" style="margin-top: 50px;">
            <div class="auth-content">
                <h1 class="auth-title">User Dashboard</h1>
                <p>Welcome to your dashboard, <?php echo h($user['full_name']); ?>!</p>
                <p>You are logged in as a <strong>User</strong>.</p>
                
                <div style="margin-top: 30px;">
                    <h3>Your Profile:</h3>
                    <ul>
                        <li><strong>Name:</strong> <?php echo h($user['full_name']); ?></li>
                        <li><strong>Username:</strong> <?php echo h($user['username']); ?></li>
                        <li><strong>Email:</strong> <?php echo h($user['email']); ?></li>
                        <li><strong>Role:</strong> User</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</body>
</html>