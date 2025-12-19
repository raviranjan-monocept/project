<?php
/**
 * Header Element
 */
?>
<header class="auth-header">
    <div class="header-content">
        <!-- Logo -->
        <div class="header-logo">
            <?php echo $this->Html->image('careLogo.png', array('alt' => 'Care Health Insurance', 'class' => 'logo-img')); ?>
        </div>

        <!-- Right Side: Role Dropdown & Sign Up Button -->
        <div class="header-right">
            <!-- Login As Label & Dropdown -->
            <div class="role-selector">
                <span class="login-as-label">Login As:</span>
                <div class="dropdown">
                    <button class="dropdown-toggle" id="roleDropdown" type="button">
                        <span class="user-icon">👤</span>
                        <span class="role-text" id="selectedRole">USER</span>
                        <span class="dropdown-arrow">▾</span>
                    </button>
                    <div class="dropdown-menu" id="roleDropdownMenu">
                        <a href="#" class="dropdown-item" data-role="user">USER</a>
                        <a href="#" class="dropdown-item" data-role="guest">GUEST</a>
                        <a href="#" class="dropdown-item" data-role="admin">ADMIN</a>
                        <a href="#" class="dropdown-item" data-role="super_user">SUPER USER</a>
                    </div>
                </div>
            </div>

            <!-- Sign Up Button -->
            <?php if ($this->request->params['action'] !== 'signup'): ?>
                <?php echo $this->Html->link('Sign Up', array('action' => 'signup'), array('class' => 'btn btn-signup')); ?>
            <?php endif; ?>
        </div>
    </div>
</header>