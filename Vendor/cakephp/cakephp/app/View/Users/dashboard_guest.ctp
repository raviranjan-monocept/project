<?php
// ========================================
// dashboard_guest.ctp
// ========================================
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guest Dashboard - Care Health Insurance</title>
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
                    <span>Welcome, <?php echo h($user['full_name']); ?> (Guest)</span>
                    <?php echo $this->Html->link('Logout', array('action' => 'logout'), array('class' => 'btn btn-signup')); ?>
                </div>
            </div>
        </header>
        
        <div class="auth-card" style="margin-top: 50px;">
            <div class="auth-content">
                <h1 class="auth-title">Guest Dashboard</h1>
                <p>Welcome, <?php echo h($user['full_name']); ?>!</p>
                <p>You are logged in as a <strong>Guest</strong> with limited access.</p>
                
                <div style="margin-top: 30px;">
                    <h3>Guest Access:</h3>
                    <p>As a guest, you have view-only access to certain features.</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>