// VanGo Main JavaScript
document.addEventListener('DOMContentLoaded', function() {
    initializeNavigation();
    initializeBookingForm();
    initializePriceCalculation();
    initializeSmoothScrolling();
});

// Navigation functionality
function initializeNavigation() {
    const hamburger = document.querySelector('.hamburger');
    const navMenu = document.querySelector('.nav-menu');
    
    if (hamburger && navMenu) {
        hamburger.addEventListener('click', function() {
            hamburger.classList.toggle('active');
            navMenu.classList.toggle('active');
        });
        
        // Close mobile menu when clicking on a link
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => {
                hamburger.classList.remove('active');
                navMenu.classList.remove('active');
            });
        });
    }
    
    // Add scroll effect to navbar
    window.addEventListener('scroll', function() {
        const navbar = document.querySelector('.navbar');
        if (navbar) {
            if (window.scrollY > 50) {
                navbar.style.background = 'rgba(255, 255, 255, 0.98)';
                navbar.style.boxShadow = '0 2px 20px rgba(0, 0, 0, 0.1)';
            } else {
                navbar.style.background = 'rgba(255, 255, 255, 0.95)';
                navbar.style.boxShadow = 'none';
            }
        }
    });
}

// Booking form functionality
function initializeBookingForm() {
    const form = document.getElementById('bookingForm');
    if (form) {
        form.addEventListener('submit', handleBookingSubmit);
        
        // Add real-time validation
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('blur', validateField);
            input.addEventListener('input', clearFieldError);
        });
    }
}

// Price calculation
function initializePriceCalculation() {
    const vanTypeSelect = document.getElementById('vanType');
    const passengersSelect = document.getElementById('passengers');
    const estimatedPrice = document.getElementById('estimatedPrice');
    
    if (vanTypeSelect && passengersSelect && estimatedPrice) {
        const updatePrice = () => {
            const vanType = vanTypeSelect.value;
            const passengers = parseInt(passengersSelect.value) || 0;
            
            if (vanType && passengers > 0) {
                const basePrices = {
                    'luxury': 150,
                    'executive': 120,
                    'family': 100,
                    'group': 130
                };
                
                const basePrice = basePrices[vanType] || 100;
                const totalPrice = basePrice + (passengers * 10);
                
                estimatedPrice.textContent = `$${totalPrice}`;
            } else {
                estimatedPrice.textContent = '$0';
            }
        };
        
        vanTypeSelect.addEventListener('change', updatePrice);
        passengersSelect.addEventListener('change', updatePrice);
    }
}

// Smooth scrolling for anchor links
function initializeSmoothScrolling() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

// Form validation
function validateField(e) {
    const field = e.target;
    const fieldName = field.name;
    const value = field.value.trim();
    
    clearFieldError(e);
    
    let isValid = true;
    let errorMessage = '';
    
    switch (fieldName) {
        case 'pickupLocation':
        case 'dropoffLocation':
            if (value.length < 3) {
                isValid = false;
                errorMessage = 'Location must be at least 3 characters long';
            }
            break;
            
        case 'pickupDate':
            const selectedDate = new Date(value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            if (selectedDate < today) {
                isValid = false;
                errorMessage = 'Pickup date cannot be in the past';
            }
            break;
            
        case 'pickupTime':
            if (!value) {
                isValid = false;
                errorMessage = 'Please select a pickup time';
            }
            break;
            
        case 'passengers':
            if (!value || parseInt(value) < 1) {
                isValid = false;
                errorMessage = 'Please select number of passengers';
            }
            break;
            
        case 'vanType':
            if (!value) {
                isValid = false;
                errorMessage = 'Please select a van type';
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
    } else {
        // Create error div if it doesn't exist
        const errorElement = document.createElement('div');
        errorElement.id = field.name + '-error';
        errorElement.className = 'field-error';
        errorElement.textContent = message;
        errorElement.style.cssText = `
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
            display: block;
        `;
        field.parentNode.appendChild(errorElement);
    }
}

function clearFieldError(e) {
    const field = e.target;
    const errorDiv = document.getElementById(field.name + '-error');
    if (errorDiv) {
        errorDiv.style.display = 'none';
    }
}

// Handle booking form submission
function handleBookingSubmit(e) {
    e.preventDefault();
    
    const form = e.target;
    const formData = new FormData(form);
    let isValid = true;
    
    // Validate all fields
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
    const submitBtn = form.querySelector('button[type="submit"]');
    if (submitBtn) {
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        submitBtn.disabled = true;
        
        // Simulate form submission (replace with actual API call)
        setTimeout(() => {
            showNotification('Booking request submitted successfully!', 'success');
            form.reset();
            document.getElementById('estimatedPrice').textContent = '$0';
            
            // Reset button
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }, 2000);
    }
}

// Notification system
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
        max-width: 400px;
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
    
    .booking-form {
        background: white;
        padding: 40px;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        margin-top: 30px;
    }
    
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 20px;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #333;
    }
    
    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        font-size: 1rem;
        transition: border-color 0.3s ease;
    }
    
    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #667eea;
    }
    
    .price-display {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 10px;
        text-align: center;
        margin: 20px 0;
    }
    
    .price-display h3 {
        margin: 0;
        color: #667eea;
        font-size: 1.5rem;
    }
    
    .btn {
        display: inline-block;
        padding: 12px 30px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        text-decoration: none;
        border: none;
        border-radius: 25px;
        cursor: pointer;
        font-size: 1rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
    }
    
    .btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }
    
    .btn-secondary {
        background: #6c757d;
    }
    
    .btn-secondary:hover {
        background: #5a6268;
    }
    
    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }
        
        .booking-form {
            padding: 20px;
        }
    }
`;
document.head.appendChild(style);

function showBookingSuccess(booking) {
    // Populate booking details
    document.getElementById('successBookingId').textContent = booking.booking_id || 'N/A';
    document.getElementById('successVanName').textContent = booking.van_name || 'N/A';
    document.getElementById('successPickupLocation').textContent = booking.pickup_location || 'N/A';
    document.getElementById('successDropoffLocation').textContent = booking.dropoff_location || 'N/A';
    document.getElementById('successPickupDate').textContent = booking.pickup_date || 'N/A';
    document.getElementById('successPickupTime').textContent = booking.pickup_time || 'N/A';
    document.getElementById('successPassengers').textContent = booking.passengers || 'N/A';
    document.getElementById('successTotalAmount').textContent = '$' + (booking.total_amount || '0.00');
    if (document.getElementById('successConductDetails')) {
        document.getElementById('successConductDetails').textContent = booking.conduct_details || 'N/A';
    }
    // Show modal
    document.getElementById('bookingSuccessModal').style.display = 'block';
} 