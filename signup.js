// VanGo Signup Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    initializeSignupForm();
    setupPasswordStrength();
    setupFormValidation();
});

function initializeSignupForm() {
    const form = document.getElementById('signupForm');
    if (form) {
        form.addEventListener('submit', handleSignup);
    }

    // Set maximum date for date of birth (18 years ago)
    const dateOfBirth = document.getElementById('dateOfBirth');
    if (dateOfBirth) {
        const today = new Date();
        const minAge = new Date(today.getFullYear() - 18, today.getMonth(), today.getDate());
        dateOfBirth.max = minAge.toISOString().split('T')[0];
    }
}

function setupPasswordStrength() {
    const passwordInput = document.getElementById('password');
    if (passwordInput) {
        passwordInput.addEventListener('input', checkPasswordStrength);
    }
}

function setupFormValidation() {
    const confirmPassword = document.getElementById('confirmPassword');
    if (confirmPassword) {
        confirmPassword.addEventListener('input', validatePasswordMatch);
    }

    // Real-time validation for other fields
    const inputs = document.querySelectorAll('#signupForm input, #signupForm textarea');
    inputs.forEach(input => {
        input.addEventListener('blur', validateField);
        input.addEventListener('input', clearFieldError);
    });
}

function validateField(e) {
    const field = e.target;
    const fieldName = field.name;
    const value = field.value.trim();
    
    clearFieldError(e);
    
    let isValid = true;
    let errorMessage = '';
    
    switch (fieldName) {
        case 'firstName':
            if (value.length < 2) {
                isValid = false;
                errorMessage = 'First name must be at least 2 characters long';
            } else if (!/^[a-zA-Z\s]+$/.test(value)) {
                isValid = false;
                errorMessage = 'First name can only contain letters and spaces';
            }
            break;
            
        case 'lastName':
            if (value.length < 2) {
                isValid = false;
                errorMessage = 'Last name must be at least 2 characters long';
            } else if (!/^[a-zA-Z\s]+$/.test(value)) {
                isValid = false;
                errorMessage = 'Last name can only contain letters and spaces';
            }
            break;
            
        case 'email':
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                isValid = false;
                errorMessage = 'Please enter a valid email address';
            }
            break;
            
        case 'phone':
            const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
            if (!phoneRegex.test(value.replace(/[\s\-\(\)]/g, ''))) {
                isValid = false;
                errorMessage = 'Please enter a valid phone number';
            }
            break;
            
        case 'password':
            if (value.length < 8) {
                isValid = false;
                errorMessage = 'Password must be at least 8 characters long';
            } else if (!/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/.test(value)) {
                isValid = false;
                errorMessage = 'Password must contain uppercase, lowercase, and number';
            }
            break;
            
        case 'confirmPassword':
            const password = document.getElementById('password').value;
            if (value !== password) {
                isValid = false;
                errorMessage = 'Passwords do not match';
            }
            break;
            
        case 'terms':
            if (!field.checked) {
                isValid = false;
                errorMessage = 'You must agree to the terms and conditions';
            }
            break;
    }
    
    if (!isValid) {
        showFieldError(field, errorMessage);
    }
    
    return isValid;
}

function validatePasswordMatch() {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    const errorDiv = document.getElementById('confirmPassword-error');
    
    if (confirmPassword && password !== confirmPassword) {
        errorDiv.textContent = 'Passwords do not match';
        errorDiv.style.display = 'block';
        return false;
    } else if (confirmPassword && password === confirmPassword) {
        errorDiv.style.display = 'none';
        return true;
    }
}

function checkPasswordStrength() {
    const password = document.getElementById('password').value;
    const strengthFill = document.getElementById('strength-fill');
    const strengthText = document.getElementById('strength-text');
    
    let strength = 0;
    let strengthLabel = '';
    let strengthColor = '';
    
    // Check length
    if (password.length >= 8) strength += 25;
    if (password.length >= 12) strength += 25;
    
    // Check character types
    if (/[a-z]/.test(password)) strength += 25;
    if (/[A-Z]/.test(password)) strength += 25;
    if (/[0-9]/.test(password)) strength += 25;
    if (/[^A-Za-z0-9]/.test(password)) strength += 25;
    
    // Cap at 100
    strength = Math.min(strength, 100);
    
    // Set strength label and color
    if (strength < 25) {
        strengthLabel = 'Very Weak';
        strengthColor = '#dc3545';
    } else if (strength < 50) {
        strengthLabel = 'Weak';
        strengthColor = '#fd7e14';
    } else if (strength < 75) {
        strengthLabel = 'Medium';
        strengthColor = '#ffc107';
    } else if (strength < 100) {
        strengthLabel = 'Strong';
        strengthColor = '#28a745';
    } else {
        strengthLabel = 'Very Strong';
        strengthColor = '#20c997';
    }
    
    // Update UI
    strengthFill.style.width = strength + '%';
    strengthFill.style.backgroundColor = strengthColor;
    strengthText.textContent = strengthLabel;
    strengthText.style.color = strengthColor;
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

function handleSignup(e) {
    e.preventDefault();
    
    // Validate all fields
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
    
    // Additional validation for password match
    if (!validatePasswordMatch()) {
        isValid = false;
    }
    
    if (!isValid) {
        showNotification('Please fix the errors in the form', 'error');
        return;
    }
    
    // Show loading state
    const submitBtn = document.getElementById('signupBtn');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Account...';
    submitBtn.disabled = true;
    
    // Send form data to PHP backend
    fetch('signup.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccessModal();
            form.reset();
            
            // Clear password strength
            const strengthFill = document.getElementById('strength-fill');
            const strengthText = document.getElementById('strength-text');
            if (strengthFill) strengthFill.style.width = '0%';
            if (strengthText) strengthText.textContent = 'Password strength';
        } else {
            showNotification(data.message || 'Signup failed. Please try again.', 'error');
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

function showSuccessModal() {
    const modal = document.getElementById('successModal');
    modal.style.display = 'block';
}

function redirectToLogin() {
    // Store signup success message in sessionStorage
    sessionStorage.setItem('signupSuccess', 'true');
    window.location.href = 'login.php';
}

function showTerms() {
    const modal = document.getElementById('termsModal');
    modal.style.display = 'block';
}

function closeTerms() {
    const modal = document.getElementById('termsModal');
    modal.style.display = 'none';
}

function showPrivacy() {
    const modal = document.getElementById('privacyModal');
    modal.style.display = 'block';
}

function closePrivacy() {
    const modal = document.getElementById('privacyModal');
    modal.style.display = 'none';
}

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
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
        background: ${type === 'error' ? '#dc3545' : '#667eea'};
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
    
    .password-strength {
        margin-top: 0.5rem;
    }
    
    .strength-bar {
        width: 100%;
        height: 4px;
        background: #e9ecef;
        border-radius: 2px;
        overflow: hidden;
    }
    
    .strength-fill {
        height: 100%;
        width: 0%;
        transition: all 0.3s ease;
    }
    
    .strength-text {
        font-size: 0.75rem;
        color: #6c757d;
        margin-top: 0.25rem;
        display: block;
    }
`;
document.head.appendChild(style); 