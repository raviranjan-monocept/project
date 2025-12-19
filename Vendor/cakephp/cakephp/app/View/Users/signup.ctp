<?php
/**
 * Signup View
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Care Health Insurance</title>
    <?php echo $this->Html->css('auth'); ?>
</head>
<body>
    <div class="auth-container">
        <!-- Header -->
        <?php echo $this->element('header'); ?>

        <!-- Signup Form Card -->
        <div class="auth-card signup-card">
            <div class="auth-content">
                <h1 class="auth-title">Sign Up</h1>

                <!-- Error Alert -->
                <?php if ($this->Session->check('Message.flash')): ?>
                    <div class="alert alert-error">
                        <span class="alert-icon">🔔</span>
                        <div class="alert-text">
                            <?php echo $this->Session->flash(); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Signup Form -->
                <?php echo $this->Form->create('User', array('url' => array('action' => 'signup'), 'id' => 'signupForm')); ?>
                
                    <!-- User Type Selection -->
                    <div class="form-group">
                        <label class="field-label">User type</label>
                        <div class="radio-group">
                            <label class="radio-label">
                                <input type="radio" name="data[User][user_type]" value="user" checked="checked" class="user-type-radio" id="userTypeUser">
                                User
                            </label>
                            <label class="radio-label">
                                <input type="radio" name="data[User][user_type]" value="admin" class="user-type-radio" id="userTypeAdmin">
                                Admin
                            </label>
                        </div>
                    </div>

                    <!-- Row 1: Full Name & Username -->
                    <div class="form-row-2col">
                        <div class="form-group">
                            <label for="fullName" class="field-label">Full Name</label>
                            <?php echo $this->Form->input('full_name', array(
                                'type' => 'text',
                                'label' => false,
                                'class' => 'form-control',
                                'div' => false,
                                'id' => 'fullName'
                            )); ?>
                        </div>
                        <div class="form-group">
                            <label for="userName" class="field-label">User Name</label>
                            <?php echo $this->Form->input('username', array(
                                'type' => 'text',
                                'label' => false,
                                'placeholder' => 'User Name',
                                'class' => 'form-control',
                                'div' => false,
                                'id' => 'userName'
                            )); ?>
                        </div>
                    </div>

                    <!-- Row 2: Email & Confirm Email -->
                    <div class="form-row-2col">
                        <div class="form-group">
                            <label for="emailAddress" class="field-label">Email Address</label>
                            <?php echo $this->Form->input('email', array(
                                'type' => 'email',
                                'label' => false,
                                'placeholder' => 'email address',
                                'class' => 'form-control',
                                'div' => false,
                                'id' => 'emailAddress'
                            )); ?>
                        </div>
                        <div class="form-group">
                            <label for="confirmEmail" class="field-label">Confirm Email</label>
                            <?php echo $this->Form->input('confirm_email', array(
                                'type' => 'email',
                                'label' => false,
                                'placeholder' => 'confirm email',
                                'class' => 'form-control',
                                'div' => false,
                                'id' => 'confirmEmail'
                            )); ?>
                        </div>
                    </div>

                    <!-- Row 3: Password & Confirm Password -->
                    <div class="form-row-2col">
                        <div class="form-group">
                            <label for="password" class="field-label">Password</label>
                            <?php echo $this->Form->input('password', array(
                                'type' => 'password',
                                'label' => false,
                                'placeholder' => 'Password',
                                'class' => 'form-control',
                                'div' => false,
                                'id' => 'password'
                            )); ?>
                        </div>
                        <div class="form-group">
                            <label for="confirmPassword" class="field-label">Confirm Password</label>
                            <?php echo $this->Form->input('confirm_password', array(
                                'type' => 'password',
                                'label' => false,
                                'placeholder' => 'Confirm Password',
                                'class' => 'form-control',
                                'div' => false,
                                'id' => 'confirmPassword'
                            )); ?>
                        </div>
                    </div>

                    <!-- Access Code Field (Hidden by default, shows for Admin) -->
                    <div class="form-group" id="signupAccessCodeField" style="display: none;">
                        <label for="signupAccessCode" class="field-label">Enter code</label>
                        <?php echo $this->Form->input('access_code', array(
                            'type' => 'text',
                            'label' => false,
                            'placeholder' => 'Enter code',
                            'class' => 'form-control',
                            'div' => false,
                            'id' => 'signupAccessCode'
                        )); ?>
                    </div>

                    <!-- Submit Button -->
                    <div class="form-actions">
                        <?php echo $this->Form->button('SIGN UP', array(
                            'type' => 'submit',
                            'class' => 'btn btn-primary'
                        )); ?>
                    </div>

                <?php echo $this->Form->end(); ?>
            </div>
        </div>
    </div>

    <?php echo $this->Html->script('https://code.jquery.com/jquery-3.6.0.min.js'); ?>
    <?php echo $this->Html->script('auth'); ?>
</body>
</html>