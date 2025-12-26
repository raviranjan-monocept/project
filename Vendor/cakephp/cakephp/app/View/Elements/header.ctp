<?php
/**
 * Header Element
 */
?>
<header class="auth-header">
    <div class="header-content">
        <!-- Logo -->
        <div class="header-logo">
            <?php 
            // ✅ FIXED: Changed 'signup' to 'register'
            if ($this->request->params['action'] === 'register') {
                echo $this->Html->link(
                    $this->Html->image('careLogo.png', array('alt' => 'Care Health Insurance', 'class' => 'logo-img')),
                    array('action' => 'login'),
                    array('escape' => false, 'class' => 'logo-link')
                );
            } else {
                echo $this->Html->image('careLogo.png', array('alt' => 'Care Health Insurance', 'class' => 'logo-img'));
            } ?>
        </div>

        <!-- Right Side: Role Dropdown & Sign Up Button -->
        <div class="header-right">
            <?php 
            $currentAction = $this->request->params['action'];
            if ($currentAction === 'login'): ?>
                <!-- Login As dropdown only shows on login page -->
                <div class="role-selector">
                    <span class="login-as-label">Login As:</span>
                    <div class="dropdown">
                        <?php
                        $initialRole = isset($selectedRole) ? $selectedRole : 'user';
                        $initialRoleDisplay = strtoupper(str_replace('_', ' ', $initialRole));
                        ?>
                        <button class="dropdown-toggle" id="roleDropdown" type="button">
                            <span class="user-icon">👤</span>
                            <span class="role-text" id="selectedRole"><?php echo h($initialRoleDisplay); ?></span>
                            <span class="dropdown-arrow">▾</span>
                        </button>
                        <div class="dropdown-menu" id="roleDropdownMenu">
                            <a href="#" class="dropdown-item" data-role="user">USER</a>
                            <a href="#" class="dropdown-item" data-role="admin">ADMIN</a>
                            <a href="#" class="dropdown-item" data-role="super_user">SUPER USER</a>
                        </div>
                    </div>
                </div>
                
                <!-- Sign Up button on login page -->
                <?php 
                // ✅ FIXED: Changed 'signup' to 'register'
                echo $this->Html->link('Sign Up', array('action' => 'register'), array('class' => 'btn btn-signup')); 
                ?>
                
            <?php 
            // ✅ FIXED: Changed 'signup' to 'register'
            elseif ($currentAction === 'register'): ?>
                <!-- Only Back to Login button on register page (no dropdown) -->
                <?php echo $this->Html->link('Back to Login', array('action' => 'login'), array('class' => 'btn btn-signup')); ?>
                
            <?php else: ?>
                <!-- Default for other pages (if any) -->
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
                            <a href="#" class="dropdown-item" data-role="admin">ADMIN</a>
                            <a href="#" class="dropdown-item" data-role="super_user">SUPER USER</a>
                        </div>
                    </div>
                </div>
                <?php 
                // ✅ FIXED: Changed 'signup' to 'register'
                echo $this->Html->link('Register', array('controller' => 'users', 'action' => 'register'), array('class' => 'btn btn-signup')); 
                ?>
            <?php endif; ?>
        </div>
    </div>
</header>

<?php if ($this->request->params['action'] === 'login'): ?>
<script>
$(document).ready(function() {
    // Initialize header using server-provided role when available
    var currentRole = '<?php echo h($initialRole); ?>';

    // Update header dropdown to match current role
    $('#selectedRole').text(currentRole.toUpperCase().replace('_', ' '));

    // Set active class on dropdown items
    $('.dropdown-item').removeClass('active');
    $('.dropdown-item[data-role="' + currentRole + '"]').addClass('active');
    
    // Handle dropdown item click
    $('.dropdown-item').click(function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var selectedRole = $(this).data('role');
        var roleDisplay = selectedRole.toUpperCase().replace('_', ' ');
        
        // Update dropdown text
        $('#selectedRole').text(roleDisplay);
        
        // Update active class
        $('.dropdown-item').removeClass('active');
        $(this).addClass('active');
        
        // Hide dropdown menu
        $('#roleDropdownMenu').hide();
        
        // Update hidden field in form (if exists)
        if ($('#userRole').length) {
            $('#userRole').val(selectedRole);
        }
        
        // Update title on login page
        if ($('#loginTitle').length) {
            var titleText = 'Login As ' + selectedRole.charAt(0).toUpperCase() + 
                           selectedRole.slice(1).replace('_', ' ');
            $('#loginTitle').text(titleText);
        }
        
        // Show/hide access code field
        if (selectedRole === 'admin' || selectedRole === 'super_user') {
            if ($('#accessCodeField').length) {
                $('#accessCodeField').show();
            }
        } else {
            if ($('#accessCodeField').length) {
                $('#accessCodeField').hide();
            }
        }
        
        // Trigger custom event for other components
        $(document).trigger('roleChanged', [selectedRole]);
    });
    
    // Toggle dropdown menu
    $('#roleDropdown').click(function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var isVisible = $('#roleDropdownMenu').is(':visible');
        if (isVisible) {
            $('#roleDropdownMenu').hide();
        } else {
            // Close other dropdowns if any
            $('.dropdown-menu').hide();
            $('#roleDropdownMenu').show();
        }
    });
    
    // Close dropdown when clicking outside
    $(document).click(function() {
        $('#roleDropdownMenu').hide();
    });
    
    // Prevent dropdown from closing when clicking inside
    $('.dropdown-menu').click(function(e) {
        e.stopPropagation();
    });
});
</script>
<?php endif; ?>
