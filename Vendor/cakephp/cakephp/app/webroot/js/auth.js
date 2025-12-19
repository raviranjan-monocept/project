/**
 * Care Health Insurance - Authentication JavaScript
 */

$(document).ready(function() {
    
    // ========================================
    // Role Dropdown Functionality
    // ========================================
    
    let currentRole = 'user'; // Default role
    
    // Toggle dropdown menu
    $('#roleDropdown').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $('#roleDropdownMenu').toggleClass('show');
    });
    
    // Close dropdown when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.dropdown').length) {
            $('#roleDropdownMenu').removeClass('show');
        }
    });
    
    // Handle role selection
    $('.dropdown-item').on('click', function(e) {
        e.preventDefault();
        
        const role = $(this).data('role');
        const roleText = $(this).text();
        
        // Update dropdown display
        $('#selectedRole').text(roleText);
        currentRole = role;
        
        // Update hidden role field
        $('#userRole').val(role);
        
        // Update login title dynamically
        updateLoginTitle(role);
        
        // Show/hide access code field based on role
        toggleAccessCodeField(role);
        
        // Close dropdown
        $('#roleDropdownMenu').removeClass('show');
        
        // Clear any previous errors
        clearErrors();
    });
    
    // ========================================
    // Login Page - Dynamic Title Update
    // ========================================
    
    function updateLoginTitle(role) {
        let titleText = 'Login As ';
        
        switch(role) {
            case 'user':
                titleText += 'User';
                break;
            case 'guest':
                titleText += 'Guest';
                break;
            case 'admin':
                titleText += 'Admin';
                break;
            case 'super_user':
                titleText += 'Super user';
                break;
            default:
                titleText += 'User';
        }
        
        $('#loginTitle').text(titleText);
    }
    
    // ========================================
    // Access Code Field Toggle
    // ========================================
    
    function toggleAccessCodeField(role) {
        const accessCodeField = $('#accessCodeField');
        const accessCodeInput = $('#accessCode');
        
        if (role === 'admin' || role === 'super_user') {
            // Show access code field
            accessCodeField.slideDown(300);
            accessCodeInput.prop('required', true);
        } else {
            // Hide access code field
            accessCodeField.slideUp(300);
            accessCodeInput.prop('required', false);
            accessCodeInput.val(''); // Clear the field
        }
    }
    
    // ========================================
    // Signup Page - User Type Toggle
    // ========================================
    
    $('.user-type-radio').on('change', function() {
        const userType = $('input[name="data[User][user_type]"]:checked').val();
        const signupAccessCodeField = $('#signupAccessCodeField');
        const signupAccessCodeInput = $('#signupAccessCode');
        
        if (userType === 'admin') {
            // Show access code field for admin
            signupAccessCodeField.slideDown(300);
            signupAccessCodeInput.prop('required', true);
        } else {
            // Hide access code field for user
            signupAccessCodeField.slideUp(300);
            signupAccessCodeInput.prop('required', false);
            signupAccessCodeInput.val(''); // Clear the field
        }
    });
    
    // ========================================
    // Form Validation
    // ========================================
    
    // Login Form Validation
    $('#loginForm').on('submit', function(e) {
        const email = $('#email').val().trim();
        const password = $('input[name="data[User][password]"]').val();
        const role = $('#userRole').val();
        const accessCode = $('#accessCode').val();
        
        // Clear previous errors
        clearErrors();
        
        let hasError = false;
        
        // Validate email
        if (!email) {
            showError('Please enter valid Email ID');
            hasError = true;
        } else if (!isValidEmail(email)) {
            showError('Please enter valid Email ID');
            hasError = true;
        }
        
        // Validate password
        if (!password) {
            showError('Password is required');
            hasError = true;
        }
        
        // Validate access code for admin/super_user
        if ((role === 'admin' || role === 'super_user') && !accessCode) {
            showError('Access code is required for ' + role.replace('_', ' '));
            hasError = true;
        }
        
        if (hasError) {
            e.preventDefault();
            return false;
        }
    });
    
    // Signup Form Validation
    $('#signupForm').on('submit', function(e) {
        const fullName = $('#fullName').val().trim();
        const userName = $('#userName').val().trim();
        const email = $('#emailAddress').val().trim();
        const confirmEmail = $('#confirmEmail').val().trim();
        const password = $('#password').val();
        const confirmPassword = $('#confirmPassword').val();
        const userType = $('input[name="data[User][user_type]"]:checked').val();
        const accessCode = $('#signupAccessCode').val();
        
        // Clear previous errors
        clearErrors();
        
        let hasError = false;
        let errorMessage = '';
        
        // Validate full name
        if (!fullName) {
            errorMessage = 'Full name is required';
            hasError = true;
        }
        
        // Validate username
        if (!userName) {
            errorMessage = 'Username is required';
            hasError = true;
        }
        
        // Validate email
        if (!email) {
            errorMessage = 'Email address is required';
            hasError = true;
        } else if (!isValidEmail(email)) {
            errorMessage = 'Please enter a valid email address';
            hasError = true;
        }
        
        // Validate confirm email
        if (!confirmEmail) {
            errorMessage = 'Please confirm your email address';
            hasError = true;
        } else if (email !== confirmEmail) {
            errorMessage = 'Email addresses do not match';
            hasError = true;
        }
        
        // Validate password
        if (!password) {
            errorMessage = 'Password is required';
            hasError = true;
        } else if (password.length < 6) {
            errorMessage = 'Password must be at least 6 characters long';
            hasError = true;
        }
        
        // Validate confirm password
        if (!confirmPassword) {
            errorMessage = 'Please confirm your password';
            hasError = true;
        } else if (password !== confirmPassword) {
            errorMessage = 'Passwords do not match';
            hasError = true;
        }
        
        // Validate access code for admin
        if (userType === 'admin' && !accessCode) {
            errorMessage = 'Access code is required for Admin registration';
            hasError = true;
        }
        
        if (hasError) {
            showSignupError(errorMessage);
            e.preventDefault();
            return false;
        }
    });
    
    // ========================================
    // Helper Functions
    // ========================================
    
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    function showError(message) {
        $('#sideError').show();
        $('#sideErrorText').text(message);
        
        // Optionally scroll to error
        $('html, body').animate({
            scrollTop: $('#sideError').offset().top - 100
        }, 300);
    }
    
    function showSignupError(message) {
        // Create alert if it doesn't exist
        if ($('.alert').length === 0) {
            const alertHtml = `
                <div class="alert alert-error">
                    <span class="alert-icon">🔔</span>
                    <div class="alert-text">${message}</div>
                </div>
            `;
            $('.auth-title').after(alertHtml);
        } else {
            $('.alert-text').text(message);
        }
        
        // Scroll to alert
        $('html, body').animate({
            scrollTop: $('.alert').offset().top - 100
        }, 300);
    }
    
    function clearErrors() {
        $('#sideError').hide();
        $('#sideErrorText').text('');
        $('.form-control').removeClass('error');
        $('.error-message').remove();
    }
    
    // ========================================
    // Real-time Field Validation (Optional)
    // ========================================
    
    // Email validation on blur
    $('#email, #emailAddress').on('blur', function() {
        const email = $(this).val().trim();
        if (email && !isValidEmail(email)) {
            $(this).addClass('error');
            if ($(this).next('.error-message').length === 0) {
                $(this).after('<span class="error-message">Please enter a valid email address</span>');
            }
        } else {
            $(this).removeClass('error');
            $(this).next('.error-message').remove();
        }
    });
    
    // Confirm email validation
    $('#confirmEmail').on('blur', function() {
        const email = $('#emailAddress').val().trim();
        const confirmEmail = $(this).val().trim();
        
        if (confirmEmail && email !== confirmEmail) {
            $(this).addClass('error');
            if ($(this).next('.error-message').length === 0) {
                $(this).after('<span class="error-message">Email addresses do not match</span>');
            }
        } else {
            $(this).removeClass('error');
            $(this).next('.error-message').remove();
        }
    });
    
    // Confirm password validation
    $('#confirmPassword').on('blur', function() {
        const password = $('#password').val();
        const confirmPassword = $(this).val();
        
        if (confirmPassword && password !== confirmPassword) {
            $(this).addClass('error');
            if ($(this).next('.error-message').length === 0) {
                $(this).after('<span class="error-message">Passwords do not match</span>');
            }
        } else {
            $(this).removeClass('error');
            $(this).next('.error-message').remove();
        }
    });
    
    // ========================================
    // Initialize on Page Load
    // ========================================
    
    // Set default role on login page
    if ($('#loginTitle').length) {
        updateLoginTitle('user');
        toggleAccessCodeField('user');
    }
    
    // Set default user type on signup page
    if ($('#signupForm').length) {
        $('input[name="data[User][user_type]"][value="user"]').prop('checked', true);
    }
    
});