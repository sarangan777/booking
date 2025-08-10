// VanGo Login Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    initializeLoginForm();
    checkSignupSuccess();
});

function initializeLoginForm() {
    const form = document.getElementById('loginForm');
    if (form) {
        form.addEventListener('submit', handleLogin);
    }

    // Real-time validation
    const inputs = document.querySelectorAll('#loginForm input');
    inputs.forEach(input => {
        input.addEventListener('blur', validateField);
        input.addEventListener('input', clearFieldError);
    });
}

function checkSignupSuccess() {
    // Check if user just signed up
    const signupSuccess = sessionStorage.getItem('signupSuccess');
    if (signupSuccess === 'true') {
        showNotification('Account created successfully! Please log in.', 'success');
        sessionStorage.removeItem('signupSuccess');
    }
}

function validateField(e) {
    const field = e.target;
    const fieldName = field.name;
    const value = field.value.trim();
    
    clearFieldError(e);
    
    let isValid = true;
    let errorMessage = '';
    
    switch (fieldName) {
        case 'email':
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                isValid = false;
                errorMessage = 'Please enter a valid email address';
            }
            break;
            
        case 'password':
            if (value.length < 1) {
                isValid = false;
                errorMessage = 'Password is required';
            }
            break;
    }
    
    if (!isValid) {
        showFieldError(field, errorMessage);
    }
    
    return isValid;
}

function showFieldError(field, message) {
    const errorDiv = document.getElementById(field.name + '-error');
    if (errorDiv) {
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
    }
}

function clearFieldError(e) {
    const field = e.target;
    const errorDiv = document.getElementById(field.name + '-error');
    if (errorDiv) {
        errorDiv.style.display = 'none';
    }
}

function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const toggleBtn = field.nextElementSibling;
    const icon = toggleBtn.querySelector('i');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        field.type = 'password';
        icon.className = 'fas fa-eye';
    }
}

function handleLogin(e) {
    e.preventDefault();
    
    // Validate form
    const form = e.target;
    const formData = new FormData(form);
    let isValid = true;
    
    // Validate each field
    for (let [name, value] of formData.entries()) {
        const field = form.querySelector(`[name="${name}"]`);
        if (field && !validateField({ target: field })) {
            isValid = false;
        }
    }
    
    if (!isValid) {
        showNotification('Please fix the errors in the form', 'error');
        return;
    }
    
    // Show loading state
    const submitBtn = document.getElementById('loginBtn');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing In...';
    submitBtn.disabled = true;
    
    // Send form data to PHP backend
    fetch('login.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showLoginSuccess();
            // Redirect to home page after successful login
            setTimeout(() => {
                window.location.href = 'index.php';
            }, 2000);
        } else {
            showNotification(data.message || 'Login failed. Please check your credentials.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred. Please try again.', 'error');
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

function showLoginSuccess() {
    const modal = document.getElementById('loginSuccessModal');
    modal.style.display = 'block';
}

function showForgotPassword() {
    const modal = document.getElementById('forgotPasswordModal');
    modal.style.display = 'block';
}

function closeForgotPassword() {
    const modal = document.getElementById('forgotPasswordModal');
    modal.style.display = 'none';
}

function sendResetEmail() {
    const email = document.getElementById('resetEmail').value;
    
    if (!email) {
        showNotification('Please enter your email address', 'error');
        return;
    }
    
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        showNotification('Please enter a valid email address', 'error');
        return;
    }
    
    // Show loading state
    const btn = document.querySelector('#forgotPasswordModal .auth-button');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
    btn.disabled = true;
    
    // Send to PHP backend
    const formData = new FormData();
    formData.append('email', email);
    
    fetch('forgot-password.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeForgotPassword();
            showNotification('Password reset link sent to your email!', 'success');
        } else {
            showNotification(data.message || 'Failed to send reset email.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred. Please try again.', 'error');
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${type === 'error' ? 'exclamation-circle' : type === 'success' ? 'check-circle' : 'info-circle'}"></i>
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    // Add styles
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'error' ? '#dc3545' : type === 'success' ? '#28a745' : '#667eea'};
        color: white;
        padding: 1rem;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        z-index: 3000;
        animation: slideInRight 0.3s ease;
    `;
    
    notification.querySelector('.notification-content').style.cssText = `
        display: flex;
        align-items: center;
        gap: 0.5rem;
    `;
    
    notification.querySelector('button').style.cssText = `
        background: none;
        border: none;
        color: white;
        cursor: pointer;
        margin-left: 0.5rem;
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

// Social login handlers
document.addEventListener('DOMContentLoaded', function() {
    const googleBtn = document.querySelector('.google-btn');
    const facebookBtn = document.querySelector('.facebook-btn');
    
    if (googleBtn) {
        googleBtn.addEventListener('click', handleGoogleLogin);
    }
    
    if (facebookBtn) {
        facebookBtn.addEventListener('click', handleFacebookLogin);
    }
});

function handleGoogleLogin() {
    showNotification('Google login integration coming soon!', 'info');
}

function handleFacebookLogin() {
    showNotification('Facebook login integration coming soon!', 'info');
}

// Close modals when clicking outside
window.addEventListener('click', function(e) {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });
});

// Close modals with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            modal.style.display = 'none';
        });
    }
});

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    .field-error {
        color: #dc3545;
        font-size: 0.875rem;
        margin-top: 0.25rem;
        display: none;
    }
    
    .auth-links {
        text-align: center;
        margin: 1rem 0;
    }
    
    .auth-links a {
        color: #667eea;
        text-decoration: none;
        font-size: 0.9rem;
    }
    
    .auth-links a:hover {
        text-decoration: underline;
    }
    
    .auth-divider {
        text-align: center;
        margin: 1.5rem 0;
        position: relative;
    }
    
    .auth-divider::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 0;
        right: 0;
        height: 1px;
        background: #e9ecef;
    }
    
    .auth-divider span {
        background: white;
        padding: 0 1rem;
        color: #6c757d;
        font-size: 0.9rem;
    }
    
    .social-login {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        margin-bottom: 1.5rem;
    }
    
    .social-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.75rem;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        background: white;
        color: #333;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 0.9rem;
    }
    
    .social-btn:hover {
        background: #f8f9fa;
        border-color: #667eea;
    }
    
    .google-btn:hover {
        border-color: #db4437;
    }
    
    .facebook-btn:hover {
        border-color: #4267B2;
    }
`;
document.head.appendChild(style); 