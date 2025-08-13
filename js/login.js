/**
 * Login Page JavaScript
 * 0S-CARE - Cancer Patient Care Management System
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize login form
    initializeLoginForm();
    initializeRegisterForm();
    initializeForgotPasswordForm();
    
    // Initialize accessibility features
    initializeAccessibility();
    
    // Demo credentials auto-fill
    initializeDemoCredentials();
});

/**
 * Login Form Initialization
 */
function initializeLoginForm() {
    const loginForm = document.getElementById('loginForm');
    
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            if (!validateEmail(email)) {
                e.preventDefault();
                showAlert('Please enter a valid email address.', 'danger');
                return false;
            }
            
            if (password.length < 1) {
                e.preventDefault();
                showAlert('Please enter your password.', 'danger');
                return false;
            }
            
            // Show loading state
            const submitBtn = loginForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Signing In...';
            submitBtn.disabled = true;
            
            // Re-enable after 5 seconds if form didn't submit
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 5000);
        });
    }
}

/**
 * Register Form Initialization
 */
function initializeRegisterForm() {
    const registerForm = document.getElementById('registerForm');
    
    if (registerForm) {
        // Real-time password validation
        const passwordField = document.getElementById('reg_password');
        const confirmPasswordField = document.getElementById('reg_confirm_password');
        
        passwordField.addEventListener('input', function() {
            validatePasswordStrength(this.value);
        });
        
        confirmPasswordField.addEventListener('input', function() {
            validatePasswordMatch();
        });
        
        // Form submission
        registerForm.addEventListener('submit', function(e) {
            if (!validateRegistrationForm()) {
                e.preventDefault();
                return false;
            }
            
            // Show loading state
            const submitBtn = registerForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Creating Account...';
            submitBtn.disabled = true;
            
            // Re-enable after 10 seconds if form didn't submit
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 10000);
        });
    }
}

/**
 * Forgot Password Form Initialization
 */
function initializeForgotPasswordForm() {
    const forgotForm = document.getElementById('forgotPasswordForm');
    
    if (forgotForm) {
        forgotForm.addEventListener('submit', function(e) {
            const email = document.getElementById('forgot_email').value;
            
            if (!validateEmail(email)) {
                e.preventDefault();
                showAlert('Please enter a valid email address.', 'danger');
                return false;
            }
            
            // Show loading state
            const submitBtn = forgotForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Processing...';
            submitBtn.disabled = true;
            
            // Re-enable after 5 seconds
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 5000);
        });
    }
}

/**
 * Registration Form Validation
 */
function validateRegistrationForm() {
    const form = document.getElementById('registerForm');
    const formData = new FormData(form);
    let isValid = true;
    
    // Check required fields
    const requiredFields = ['first_name', 'last_name', 'username', 'email', 'password', 'confirm_password', 'role'];
    
    requiredFields.forEach(field => {
        const value = formData.get(field);
        if (!value || value.trim() === '') {
            showAlert(`Please fill in the ${field.replace('_', ' ')} field.`, 'danger');
            isValid = false;
            return false;
        }
    });
    
    if (!isValid) return false;
    
    // Validate email format
    if (!validateEmail(formData.get('email'))) {
        showAlert('Please enter a valid email address.', 'danger');
        return false;
    }
    
    // Validate password strength
    if (!validatePasswordStrength(formData.get('password'), false)) {
        showAlert('Password must be at least 8 characters with uppercase, lowercase, and number.', 'danger');
        return false;
    }
    
    // Validate password match
    if (formData.get('password') !== formData.get('confirm_password')) {
        showAlert('Passwords do not match.', 'danger');
        return false;
    }
    
    // Validate phone number if provided
    const phone = formData.get('phone');
    if (phone && !validatePhone(phone)) {
        showAlert('Please enter a valid phone number.', 'danger');
        return false;
    }
    
    // Check terms agreement
    if (!formData.get('terms_agreed')) {
        showAlert('Please agree to the Terms of Service.', 'danger');
        return false;
    }
    
    // Check consent
    if (!formData.get('consent_given')) {
        showAlert('Please provide consent for health information collection.', 'danger');
        return false;
    }
    
    return true;
}

/**
 * Password Strength Validation
 */
function validatePasswordStrength(password, showFeedback = true) {
    const minLength = 8;
    const hasUpperCase = /[A-Z]/.test(password);
    const hasLowerCase = /[a-z]/.test(password);
    const hasNumbers = /\d/.test(password);
    const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(password);
    
    let strength = 0;
    let feedback = [];
    
    if (password.length >= minLength) {
        strength += 1;
    } else {
        feedback.push('At least 8 characters');
    }
    
    if (hasUpperCase) {
        strength += 1;
    } else {
        feedback.push('One uppercase letter');
    }
    
    if (hasLowerCase) {
        strength += 1;
    } else {
        feedback.push('One lowercase letter');
    }
    
    if (hasNumbers) {
        strength += 1;
    } else {
        feedback.push('One number');
    }
    
    if (hasSpecialChar) {
        strength += 1;
    }
    
    if (showFeedback) {
        updatePasswordFeedback(strength, feedback);
    }
    
    return strength >= 4; // Minimum requirements met
}

/**
 * Update Password Feedback Display
 */
function updatePasswordFeedback(strength, feedback) {
    let feedbackElement = document.getElementById('password-feedback');
    
    if (!feedbackElement) {
        feedbackElement = document.createElement('div');
        feedbackElement.id = 'password-feedback';
        feedbackElement.className = 'mt-2';
        document.getElementById('reg_password').parentNode.appendChild(feedbackElement);
    }
    
    const strengthColors = ['danger', 'danger', 'warning', 'info', 'success', 'success'];
    const strengthTexts = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong', 'Very Strong'];
    
    const color = strengthColors[strength] || 'secondary';
    const text = strengthTexts[strength] || 'No Password';
    
    let html = `<div class="progress mb-2" style="height: 5px;">
                    <div class="progress-bar bg-${color}" style="width: ${(strength / 5) * 100}%"></div>
                </div>
                <small class="text-${color}">Strength: ${text}</small>`;
    
    if (feedback.length > 0) {
        html += `<br><small class="text-muted">Missing: ${feedback.join(', ')}</small>`;
    }
    
    feedbackElement.innerHTML = html;
}

/**
 * Password Match Validation
 */
function validatePasswordMatch() {
    const password = document.getElementById('reg_password').value;
    const confirmPassword = document.getElementById('reg_confirm_password').value;
    const confirmField = document.getElementById('reg_confirm_password');
    
    if (confirmPassword.length > 0) {
        if (password === confirmPassword) {
            confirmField.classList.remove('is-invalid');
            confirmField.classList.add('is-valid');
        } else {
            confirmField.classList.remove('is-valid');
            confirmField.classList.add('is-invalid');
        }
    } else {
        confirmField.classList.remove('is-valid', 'is-invalid');
    }
}

/**
 * Email Validation
 */
function validateEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

/**
 * Phone Validation
 */
function validatePhone(phone) {
    const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
    return phoneRegex.test(phone.replace(/[\s\-\(\)]/g, ''));
}

/**
 * Show Alert Messages
 */
function showAlert(message, type = 'info') {
    // Remove existing alerts
    const existingAlerts = document.querySelectorAll('.alert-floating');
    existingAlerts.forEach(alert => alert.remove());
    
    // Create new alert
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show alert-floating`;
    alertDiv.style.cssText = 'position: fixed; top: 20px; left: 50%; transform: translateX(-50%); z-index: 9999; min-width: 300px; max-width: 500px;';
    
    alertDiv.innerHTML = `
        <i class="bi bi-${getAlertIcon(type)} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

/**
 * Get Alert Icon Based on Type
 */
function getAlertIcon(type) {
    const icons = {
        'success': 'check-circle-fill',
        'danger': 'exclamation-triangle-fill',
        'warning': 'exclamation-triangle-fill',
        'info': 'info-circle-fill'
    };
    return icons[type] || 'info-circle-fill';
}

/**
 * Initialize Demo Credentials
 */
function initializeDemoCredentials() {
    // Add click handlers to demo credentials
    const demoCredentials = document.querySelector('.demo-credentials');
    if (demoCredentials) {
        demoCredentials.addEventListener('click', function(e) {
            if (e.target.tagName === 'STRONG') {
                const role = e.target.textContent.toLowerCase();
                if (role.includes('patient')) {
                    fillDemoCredentials('diana@example.com', 'password');
                } else if (role.includes('caregiver')) {
                    fillDemoCredentials('chance@example.com', 'password');
                }
            }
        });
        
        // Make demo credentials clickable
        demoCredentials.style.cursor = 'pointer';
        demoCredentials.title = 'Click on Patient or Caregiver to auto-fill';
    }
}

/**
 * Fill Demo Credentials
 */
function fillDemoCredentials(email, password) {
    document.getElementById('email').value = email;
    document.getElementById('password').value = password;
    
    // Add visual feedback
    showAlert(`Demo credentials filled for ${email}`, 'info');
}

/**
 * Initialize Accessibility Features
 */
function initializeAccessibility() {
    // Add accessibility controls if not already present
    if (!document.querySelector('.accessibility-bar')) {
        const accessibilityBar = document.createElement('div');
        accessibilityBar.className = 'accessibility-bar d-none d-md-block';
        accessibilityBar.innerHTML = `
            <small class="me-2">Accessibility:</small>
            <button type="button" class="btn btn-sm btn-outline-light" onclick="toggleHighContrast()" title="High Contrast">
                <i class="bi bi-circle-half"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-light" onclick="toggleLargeFont()" title="Large Font">
                <i class="bi bi-fonts"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-light" onclick="toggleDyslexiaFont()" title="Dyslexia-Friendly Font">
                <i class="bi bi-type"></i>
            </button>
        `;
        document.body.appendChild(accessibilityBar);
    }
}

/**
 * Accessibility Functions
 */
function toggleHighContrast() {
    document.body.classList.toggle('high-contrast');
    const isEnabled = document.body.classList.contains('high-contrast');
    localStorage.setItem('high-contrast', isEnabled);
    showAlert(`High contrast ${isEnabled ? 'enabled' : 'disabled'}`, 'info');
}

function toggleLargeFont() {
    document.body.classList.toggle('large-font');
    const isEnabled = document.body.classList.contains('large-font');
    localStorage.setItem('large-font', isEnabled);
    showAlert(`Large font ${isEnabled ? 'enabled' : 'disabled'}`, 'info');
}

function toggleDyslexiaFont() {
    document.body.classList.toggle('dyslexia-font');
    const isEnabled = document.body.classList.contains('dyslexia-font');
    localStorage.setItem('dyslexia-font', isEnabled);
    showAlert(`Dyslexia-friendly font ${isEnabled ? 'enabled' : 'disabled'}`, 'info');
}

/**
 * Load Saved Accessibility Preferences
 */
function loadAccessibilityPreferences() {
    if (localStorage.getItem('high-contrast') === 'true') {
        document.body.classList.add('high-contrast');
    }
    if (localStorage.getItem('large-font') === 'true') {
        document.body.classList.add('large-font');
    }
    if (localStorage.getItem('dyslexia-font') === 'true') {
        document.body.classList.add('dyslexia-font');
    }
}

// Load accessibility preferences on page load
document.addEventListener('DOMContentLoaded', loadAccessibilityPreferences);

/**
 * Form Input Enhancement
 */
document.addEventListener('DOMContentLoaded', function() {
    // Add input validation styling
    const inputs = document.querySelectorAll('input, select, textarea');
    
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.hasAttribute('required') && !this.value.trim()) {
                this.classList.add('is-invalid');
            } else if (this.value.trim()) {
                this.classList.remove('is-invalid');
                
                // Specific validation for email fields
                if (this.type === 'email' && validateEmail(this.value)) {
                    this.classList.add('is-valid');
                } else if (this.type === 'email') {
                    this.classList.add('is-invalid');
                } else if (this.type !== 'email') {
                    this.classList.add('is-valid');
                }
            }
        });
        
        input.addEventListener('input', function() {
            this.classList.remove('is-invalid', 'is-valid');
        });
    });
});

/**
 * Keyboard Navigation Enhancement
 */
document.addEventListener('keydown', function(e) {
    // Escape key closes modals
    if (e.key === 'Escape') {
        const openModal = document.querySelector('.modal.show');
        if (openModal) {
            const modalInstance = bootstrap.Modal.getInstance(openModal);
            if (modalInstance) {
                modalInstance.hide();
            }
        }
    }
    
    // Enter key submits forms when focused on submit button
    if (e.key === 'Enter' && e.target.type === 'submit') {
        e.target.click();
    }
});

/**
 * Loading State Management
 */
function showLoading(element, text = 'Loading...') {
    const originalContent = element.innerHTML;
    element.setAttribute('data-original-content', originalContent);
    element.innerHTML = `<i class="bi bi-hourglass-split me-2"></i>${text}`;
    element.disabled = true;
}

function hideLoading(element) {
    const originalContent = element.getAttribute('data-original-content');
    if (originalContent) {
        element.innerHTML = originalContent;
        element.removeAttribute('data-original-content');
    }
    element.disabled = false;
}

/**
 * Network Status Monitoring
 */
window.addEventListener('online', function() {
    showAlert('Connection restored', 'success');
});

window.addEventListener('offline', function() {
    showAlert('Connection lost. Some features may not work.', 'warning');
});

/**
 * Error Handling
 */
window.addEventListener('error', function(e) {
    console.error('JavaScript Error:', e.error);
    // Don't show error alerts to users for JavaScript errors
    // These should be logged server-side
});

/**
 * Page Visibility API for pausing activities
 */
document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        // Page is hidden, pause any ongoing activities
        console.log('Page hidden - pausing activities');
    } else {
        // Page is visible, resume activities
        console.log('Page visible - resuming activities');
    }
});