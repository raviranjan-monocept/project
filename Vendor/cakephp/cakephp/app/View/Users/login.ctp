<?php
/**
 * Login View
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Care Health Insurance</title>
    <?php echo $this->Html->css('auth'); ?>
</head>
<body>
    <div class="auth-container">
        <?php
        // Determine selected role early so header can initialize correctly
        $selectedRole = isset($this->request->data['User']['role']) ?
            $this->request->data['User']['role'] : 'user';
        ?>
        <!-- Header -->
        <?php echo $this->element('header', array('selectedRole' => $selectedRole)); ?>

        <!-- Login Form Card -->
        <div class="auth-card">
            <div class="auth-content">
                <h1 class="auth-title" id="loginTitle">Login As User</h1>

                <!-- Error Alert -->
                <?php if ($this->Session->check('Message.flash')): ?>
                    <div class="alert alert-error">
                        <span class="alert-icon">🔔</span>
                        <div class="alert-text">
                            <?php echo $this->Session->flash(); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Side Error Message -->
                <div class="side-error" id="sideError" style="display: none;">
                    <strong>Error Message:</strong>
                    <span id="sideErrorText"></span>
                </div>

                <!-- Login Form -->
                
                <?php echo $this->Form->create('User', array('url' => array('action' => 'login'), 'id' => 'loginForm')); ?>
                
                    <!-- Email Field -->
                    <div class="form-group">
                        <label for="email" class="field-label">Email Address</label>
                        <?php echo $this->Form->input('email', array(
                            'type' => 'email',
                            'label' => false,
                            'placeholder' => 'Email Address',
                            'class' => 'form-control',
                            'div' => false,
                            'id' => 'email',
                            'value' => isset($this->request->data['User']['email']) ? 
                                $this->request->data['User']['email'] : ''
                        )); ?>
                    </div>

                    <!-- Password Field -->
                    <div class="form-group">
                        <?php echo $this->Form->input('password', array(
                            'type' => 'password',
                            'label' => false,
                            'placeholder' => 'Password',
                            'class' => 'form-control',
                            'div' => false,
                            'value' => '' // Don't pre-fill password for security
                        )); ?>
                        <p class="helper-text">
                            Do not share your password with anyone. Employees of care insurance will never ask you to disclose this information. Please report any attempt to obtain your password to <a href="mailto:security@careinsurance.com">security@careinsurance.com</a>
                        </p>
                    </div>

                    <!-- Access Code Field (Hidden by default) -->
                    <div class="form-group" id="accessCodeField" style="display: none;">
                        <label for="accessCode" class="field-label">Enter code</label>
                        <?php echo $this->Form->input('access_code', array(
                            'type' => 'text',
                            'label' => false,
                            'placeholder' => 'Enter code',
                            'class' => 'form-control',
                            'div' => false,
                            'id' => 'accessCode',
                            'value' => isset($this->request->data['User']['access_code']) ? 
                                $this->request->data['User']['access_code'] : ''
                        )); ?>
                    </div>

                    <!-- Hidden Role Field - Set from POST data if available -->
                    <?php echo $this->Form->hidden('role', array(
                        'value' => $selectedRole, 
                        'id' => 'userRole'
                    )); ?>

                    <!-- Remember Me & Forgot Password -->
                    <div class="form-row">
                        <div class="form-left">
                            <label class="checkbox-label">
                                <?php 
                                $rememberChecked = isset($this->request->data['User']['remember_me']) ? 
                                    $this->request->data['User']['remember_me'] : false;
                                echo $this->Form->checkbox('remember_me', array(
                                    'id' => 'rememberMe',
                                    'checked' => $rememberChecked
                                )); 
                                ?>
                                Remember Me
                            </label>
                        </div>
                        <div class="form-right">
                            <a href="#" class="link-text">FORGOT YOUR PASSWORD?</a>
                        </div>
                    </div>

                    <!-- Submit Button & Create Account -->
                    <div class="form-row">
                        <div class="form-left">
                            <?php echo $this->Form->button('SIGN IN', array(
                                'type' => 'submit',
                                'class' => 'btn btn-primary'
                            )); ?>
                        </div>
                        <div class="form-right">
                            <?php echo $this->Html->link('CREATE AN ACCOUNT', array('action' => 'signup'), array('class' => 'link-text')); ?>
                        </div>
                    </div>

                <?php echo $this->Form->end(); ?>
            </div>
        </div>
    </div>

    <?php echo $this->Html->script('https://code.jquery.com/jquery-3.6.0.min.js'); ?>
    <?php echo $this->Html->script('auth'); ?>
    
    <script>
    $(document).ready(function() {
        // Function to update form based on selected role
        function updateFormForRole(role) {
            // Update hidden role field
            $('#userRole').val(role);
            
            // Update title
            var titleText = 'Login As ' + role.charAt(0).toUpperCase() + role.slice(1).replace('_', ' ');
            $('#loginTitle').text(titleText);
            
            // Show/hide access code field
            if (role === 'admin' || role === 'super_user') {
                $('#accessCodeField').show();
            } else {
                $('#accessCodeField').hide();
            }
        }
        
        // Get initial role from hidden field
        var currentRole = $('#userRole').val();
        updateFormForRole(currentRole);
        
        // Listen for header dropdown changes
        $(document).on('roleChanged', function(e, role) {
            updateFormForRole(role);
        });
        
        // If there was a POST error, ensure form reflects the selected role
        if ($('#userRole').val()) {
            updateFormForRole($('#userRole').val());
        }
    });
    </script>
</body>
</html>