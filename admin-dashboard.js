// VanGo Admin Dashboard JavaScript
document.addEventListener('DOMContentLoaded', function() {
    initializeAdminDashboard();
    loadDashboardData();
});

let currentDeleteId = null;
let currentDeleteType = null;

function initializeAdminDashboard() {
    // Initialize sidebar navigation
    const sidebarItems = document.querySelectorAll('.sidebar-nav .nav-item');
    sidebarItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove active class from all items
            sidebarItems.forEach(i => i.classList.remove('active'));
            
            // Add active class to clicked item
            this.classList.add('active');
            
            // Get tab name
            const tab = this.getAttribute('data-tab');
            
            // Show corresponding tab content
            showTab(tab);
        });
    });

    // Initialize sidebar toggle
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            const sidebar = document.querySelector('.admin-sidebar');
            sidebar.classList.toggle('collapsed');
        });
    }

    // Initialize form submissions
    setupFormHandlers();
}

function setupFormHandlers() {
    // Add van form
    const addVanForm = document.getElementById('addVanForm');
    if (addVanForm) {
        addVanForm.addEventListener('submit', function(e) {
            e.preventDefault();
            saveVan();
        });
    }

    // Edit van form
    const editVanForm = document.getElementById('editVanForm');
    if (editVanForm) {
        editVanForm.addEventListener('submit', function(e) {
            e.preventDefault();
            updateVan();
        });
    }
}

function showTab(tabName) {
    // Hide all tab contents
    const tabContents = document.querySelectorAll('.tab-content');
    tabContents.forEach(tab => tab.classList.remove('active'));
    
    // Show selected tab
    const selectedTab = document.getElementById(tabName);
    if (selectedTab) {
        selectedTab.classList.add('active');
    }
    
    // Update page title
    const pageTitle = document.getElementById('page-title');
    if (pageTitle) {
        pageTitle.textContent = tabName.charAt(0).toUpperCase() + tabName.slice(1);
    }
    
    // Load tab-specific data
    switch (tabName) {
        case 'dashboard':
            loadDashboardData();
            break;
        case 'bookings':
            loadBookings();
            break;
        case 'vans':
            loadVans();
            break;
        case 'customers':
            loadCustomers();
            break;
        case 'reports':
            loadReports();
            break;
    }
}

function loadDashboardData() {
    // Load dashboard statistics
    fetch('get-dashboard-stats.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateDashboardStats(data.stats);
                updateRecentBookings(data.recent_bookings);
                updateVanAvailability(data.van_availability);
            } else {
                console.error('Failed to load dashboard data:', data.message);
                // Set default values if API fails
                setDefaultDashboardStats();
            }
        })
        .catch(error => {
            console.error('Error loading dashboard data:', error);
            setDefaultDashboardStats();
        });
}

function setDefaultDashboardStats() {
    document.getElementById('total-bookings').textContent = '0';
    document.getElementById('total-vans').textContent = '0';
    document.getElementById('total-customers').textContent = '0';
    document.getElementById('total-revenue').textContent = '$0';
}

function updateDashboardStats(stats) {
    document.getElementById('total-bookings').textContent = stats.total_bookings || '0';
    document.getElementById('total-vans').textContent = stats.total_vans || '0';
    document.getElementById('total-customers').textContent = stats.total_customers || '0';
    document.getElementById('total-revenue').textContent = '$' + (stats.total_revenue || '0');
}

function updateRecentBookings(bookings) {
    const container = document.getElementById('recent-bookings');
    if (!container) return;
    
    if (!bookings || bookings.length === 0) {
        container.innerHTML = '<p>No recent bookings found.</p>';
        return;
    }
    
    let html = '<div class="recent-bookings-list">';
    bookings.forEach(booking => {
        html += `
            <div class="booking-item">
                <div class="booking-info">
                    <strong>${booking.booking_id}</strong>
                    <span>${booking.customer_name}</span>
                </div>
                <div class="booking-status status-${booking.status}">
                    ${booking.status}
                </div>
            </div>
        `;
    });
    html += '</div>';
    container.innerHTML = html;
}

function updateVanAvailability(availability) {
    const container = document.getElementById('van-availability');
    if (!container) return;
    
    if (!availability || availability.length === 0) {
        container.innerHTML = '<p>No van availability data found.</p>';
        return;
    }
    
    let html = '<div class="van-availability-list">';
    availability.forEach(van => {
        html += `
            <div class="van-item">
                <div class="van-info">
                    <strong>${van.name}</strong>
                    <span>${van.type}</span>
                </div>
                <div class="van-status status-${van.status}">
                    ${van.status}
                </div>
            </div>
        `;
    });
    html += '</div>';
    container.innerHTML = html;
}

function loadBookings() {
    fetch('get-bookings.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateBookingsTable(data.bookings);
            } else {
                console.error('Failed to load bookings:', data.message);
            }
        })
        .catch(error => {
            console.error('Error loading bookings:', error);
        });
}

function updateBookingsTable(bookings) {
    const tbody = document.getElementById('bookings-tbody');
    if (!tbody) return;
    
    if (!bookings || bookings.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8">No bookings found.</td></tr>';
        return;
    }
    
    let html = '';
    bookings.forEach(booking => {
        html += `
            <tr>
                <td>${booking.booking_id}</td>
                <td>
                    <strong>${booking.contact_name}</strong><br>
                    <small>${booking.contact_email}</small>
                </td>
                <td>${booking.van_type}</td>
                <td>${booking.pickup_date}</td>
                <td>${booking.return_date || 'Same day'}</td>
                <td>$${booking.total_amount}</td>
                <td><span class="status-badge status-${booking.status}">${booking.status}</span></td>
                <td>
                    <button class="btn btn-primary" onclick="viewBooking('${booking.booking_id}')">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-success" onclick="confirmBooking('${booking.booking_id}')">
                        <i class="fas fa-check"></i>
                    </button>
                </td>
            </tr>
        `;
    });
    tbody.innerHTML = html;
}

function loadVans() {
    fetch('manage-vans.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateVansGrid(data.vans);
            } else {
                console.error('Failed to load vans:', data.message);
                showNotification('Failed to load vans: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error loading vans:', error);
            showNotification('Error loading vans. Please try again.', 'error');
        });
}

function updateVansGrid(vans) {
    const grid = document.getElementById('vans-grid');
    if (!grid) return;
    
    if (!vans || vans.length === 0) {
        grid.innerHTML = '<p>No vans found. <button class="add-btn" onclick="openAddVanModal()">Add your first van</button></p>';
        return;
    }
    
    let html = '';
    vans.forEach(van => {
        const features = van.features ? JSON.parse(van.features) : [];
        const featuresHtml = features.map(feature => `<span class="feature-tag">${feature}</span>`).join('');
        
        html += `
            <div class="van-card">
                <div class="van-header">
                    <h3>${van.name}</h3>
                    <span class="van-status status-${van.status}">${van.status}</span>
                </div>
                <div class="van-details">
                    <p><strong>Type:</strong> ${van.type}</p>
                    <p><strong>Model:</strong> ${van.model || 'N/A'}</p>
                    <p><strong>Registration:</strong> ${van.registration_number || 'N/A'}</p>
                    <p><strong>Seats:</strong> ${van.seats}</p>
                    <p><strong>Daily Rate:</strong> $${van.daily_rate}</p>
                </div>
                <div class="van-features">
                    ${featuresHtml}
                </div>
                <div class="van-actions">
                    <button class="btn btn-primary" onclick="editVan('${van.van_id}')">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button class="btn btn-danger" onclick="deleteVan('${van.van_id}')">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </div>
            </div>
        `;
    });
    grid.innerHTML = html;
}

function loadCustomers() {
    fetch('get-customers.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateCustomersTable(data.customers);
            } else {
                console.error('Failed to load customers:', data.message);
            }
        })
        .catch(error => {
            console.error('Error loading customers:', error);
        });
}

function updateCustomersTable(customers) {
    const tbody = document.getElementById('customers-tbody');
    if (!tbody) return;
    
    if (!customers || customers.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7">No customers found.</td></tr>';
        return;
    }
    
    let html = '';
    customers.forEach(customer => {
        html += `
            <tr>
                <td>${customer.first_name} ${customer.last_name}</td>
                <td>${customer.email}</td>
                <td>${customer.phone}</td>
                <td>${customer.total_bookings || 0}</td>
                <td>$${customer.total_spent || '0.00'}</td>
                <td>${customer.last_booking || 'Never'}</td>
                <td>
                    <button class="btn btn-primary" onclick="viewCustomer('${customer.user_id}')">
                        <i class="fas fa-eye"></i>
                    </button>
                </td>
            </tr>
        `;
    });
    tbody.innerHTML = html;
}

function loadReports() {
    // Load reports data
    console.log('Loading reports...');
}

// Van Management Functions
function openAddVanModal() {
    const modal = document.getElementById('addVanModal');
    if (modal) {
        modal.style.display = 'block';
        // Reset form
        document.getElementById('addVanForm').reset();
    }
}

function closeAddVanModal() {
    const modal = document.getElementById('addVanModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

function saveVan() {
    const form = document.getElementById('addVanForm');
    const formData = new FormData(form);
    
    // Validate required fields
    const requiredFields = ['vanName', 'vanType', 'vanModel', 'registrationNumber', 'vanSeats', 'vanDailyRate'];
    let isValid = true;
    
    requiredFields.forEach(field => {
        const input = document.getElementById(field);
        if (!input || !input.value.trim()) {
            isValid = false;
            showFieldError(input, 'This field is required');
        }
    });
    
    if (!isValid) {
        showNotification('Please fill in all required fields', 'error');
        return;
    }
    
    // Prepare van data
    const vanData = {
        name: formData.get('vanName'),
        type: formData.get('vanType'),
        model: formData.get('vanModel'),
        registration_number: formData.get('registrationNumber'),
        year: formData.get('vanYear') || new Date().getFullYear(),
        seats: parseInt(formData.get('vanSeats')),
        capacity: parseInt(formData.get('vanCapacity')) || parseInt(formData.get('vanSeats')),
        daily_rate: parseFloat(formData.get('vanDailyRate')),
        hourly_rate: parseFloat(formData.get('vanDailyRate')) / 8,
        description: formData.get('vanDescription') || '',
        features: formData.get('vanFeatures') ? formData.get('vanFeatures').split(',').map(f => f.trim()) : [],
        status: formData.get('vanStatus') || 'available',
        location: formData.get('vanLocation') || 'Main Hub'
    };
    
    // Show loading state
    const saveBtn = document.querySelector('#addVanModal .save-btn');
    const originalText = saveBtn.innerHTML;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    saveBtn.disabled = true;
    
    // Send to backend
    fetch('manage-vans.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(vanData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Van added successfully!', 'success');
            closeAddVanModal();
            loadVans(); // Reload vans list
        } else {
            showNotification('Failed to add van: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error saving van:', error);
        showNotification('Error saving van. Please try again.', 'error');
    })
    .finally(() => {
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
    });
}

function editVan(vanId) {
    // Fetch van details
    fetch(`manage-vans.php?id=${vanId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populateEditForm(data.van);
                openEditVanModal();
            } else {
                showNotification('Failed to load van details: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error loading van details:', error);
            showNotification('Error loading van details. Please try again.', 'error');
        });
}

function populateEditForm(van) {
    document.getElementById('editVanId').value = van.van_id;
    document.getElementById('editVanName').value = van.name;
    document.getElementById('editVanType').value = van.type;
    document.getElementById('editVanModel').value = van.model || '';
    document.getElementById('editRegistrationNumber').value = van.registration_number || '';
    document.getElementById('editVanYear').value = van.year || '';
    document.getElementById('editVanSeats').value = van.seats;
    document.getElementById('editVanCapacity').value = van.capacity;
    document.getElementById('editVanDailyRate').value = van.daily_rate;
    document.getElementById('editVanDescription').value = van.description || '';
    document.getElementById('editVanLocation').value = van.location || '';
    document.getElementById('editVanStatus').value = van.status;
    
    // Handle features
    const features = van.features ? JSON.parse(van.features) : [];
    document.getElementById('editVanFeatures').value = features.join(', ');
}

function openEditVanModal() {
    const modal = document.getElementById('editVanModal');
    if (modal) {
        modal.style.display = 'block';
    }
}

function closeEditVanModal() {
    const modal = document.getElementById('editVanModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

function updateVan() {
    const form = document.getElementById('editVanForm');
    const formData = new FormData(form);
    const vanId = formData.get('vanId');
    
    // Validate required fields
    const requiredFields = ['vanName', 'vanType', 'vanModel', 'registrationNumber', 'vanSeats', 'vanDailyRate'];
    let isValid = true;
    
    requiredFields.forEach(field => {
        const input = document.getElementById('edit' + field.charAt(0).toUpperCase() + field.slice(1));
        if (!input || !input.value.trim()) {
            isValid = false;
            showFieldError(input, 'This field is required');
        }
    });
    
    if (!isValid) {
        showNotification('Please fill in all required fields', 'error');
        return;
    }
    
    // Prepare van data
    const vanData = {
        name: formData.get('vanName'),
        type: formData.get('vanType'),
        model: formData.get('vanModel'),
        registration_number: formData.get('registrationNumber'),
        year: formData.get('vanYear') || new Date().getFullYear(),
        seats: parseInt(formData.get('vanSeats')),
        capacity: parseInt(formData.get('vanCapacity')) || parseInt(formData.get('vanSeats')),
        daily_rate: parseFloat(formData.get('vanDailyRate')),
        hourly_rate: parseFloat(formData.get('vanDailyRate')) / 8,
        description: formData.get('vanDescription') || '',
        features: formData.get('vanFeatures') ? formData.get('vanFeatures').split(',').map(f => f.trim()) : [],
        status: formData.get('vanStatus') || 'available',
        location: formData.get('vanLocation') || 'Main Hub'
    };
    
    // Show loading state
    const saveBtn = document.querySelector('#editVanModal .save-btn');
    const originalText = saveBtn.innerHTML;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
    saveBtn.disabled = true;
    
    // Send to backend
    fetch(`manage-vans.php?id=${vanId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(vanData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Van updated successfully!', 'success');
            closeEditVanModal();
            loadVans(); // Reload vans list
        } else {
            showNotification('Failed to update van: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error updating van:', error);
        showNotification('Error updating van. Please try again.', 'error');
    })
    .finally(() => {
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
    });
}

function deleteVan(vanId) {
    currentDeleteId = vanId;
    currentDeleteType = 'van';
    
    const modal = document.getElementById('deleteModal');
    if (modal) {
        modal.style.display = 'block';
    }
}

function confirmDelete() {
    if (!currentDeleteId || !currentDeleteType) {
        return;
    }
    
    if (currentDeleteType === 'van') {
        // Show loading state
        const deleteBtn = document.querySelector('#deleteModal .delete-btn');
        const originalText = deleteBtn.innerHTML;
        deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
        deleteBtn.disabled = true;
        
        fetch(`manage-vans.php?id=${currentDeleteId}`, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Van deleted successfully!', 'success');
                closeDeleteModal();
                loadVans(); // Reload vans list
            } else {
                showNotification('Failed to delete van: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error deleting van:', error);
            showNotification('Error deleting van. Please try again.', 'error');
        })
        .finally(() => {
            deleteBtn.innerHTML = originalText;
            deleteBtn.disabled = false;
        });
    }
}

function closeDeleteModal() {
    const modal = document.getElementById('deleteModal');
    if (modal) {
        modal.style.display = 'none';
    }
    currentDeleteId = null;
    currentDeleteType = null;
}

function viewBooking(bookingId) {
    showNotification(`Viewing booking: ${bookingId}`, 'info');
}

function confirmBooking(bookingId) {
    showNotification(`Confirming booking: ${bookingId}`, 'info');
}

function viewCustomer(customerId) {
    showNotification(`Viewing customer: ${customerId}`, 'info');
}

// Utility Functions
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

function showFieldError(field, message) {
    // Remove existing error
    const existingError = field.parentNode.querySelector('.field-error');
    if (existingError) {
        existingError.remove();
    }
    
    // Add new error
    const errorDiv = document.createElement('div');
    errorDiv.className = 'field-error';
    errorDiv.textContent = message;
    errorDiv.style.cssText = `
        color: #dc3545;
        font-size: 0.875rem;
        margin-top: 0.25rem;
    `;
    
    field.parentNode.appendChild(errorDiv);
    
    // Remove error on input
    field.addEventListener('input', function() {
        const error = this.parentNode.querySelector('.field-error');
        if (error) {
            error.remove();
        }
    }, { once: true });
}

// Close modals when clicking outside
window.addEventListener('click', function(event) {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
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
    
    .van-card {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }
    
    .van-card:hover {
        transform: translateY(-5px);
        border-color: #667eea;
        box-shadow: 0 10px 25px rgba(102, 126, 234, 0.15);
    }
    
    .van-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid #e9ecef;
    }
    
    .van-header h3 {
        margin: 0;
        color: #333;
        font-size: 1.3rem;
    }
    
    .van-status {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .status-available {
        background: #d4edda;
        color: #155724;
    }
    
    .status-maintenance {
        background: #fff3cd;
        color: #856404;
    }
    
    .status-inactive {
        background: #f8d7da;
        color: #721c24;
    }
    
    .van-details {
        margin-bottom: 20px;
    }
    
    .van-details p {
        margin: 8px 0;
        color: #666;
        font-size: 0.95rem;
    }
    
    .van-features {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 20px;
    }
    
    .feature-tag {
        background: rgba(102, 126, 234, 0.1);
        color: #667eea;
        padding: 4px 10px;
        border-radius: 15px;
        font-size: 0.8rem;
        font-weight: 500;
    }
    
    .van-actions {
        display: flex;
        gap: 10px;
    }
    
    .btn {
        padding: 8px 16px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 0.9rem;
        font-weight: 500;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    
    .btn-primary {
        background: #667eea;
        color: white;
    }
    
    .btn-primary:hover {
        background: #5a6fd8;
        transform: translateY(-2px);
    }
    
    .btn-danger {
        background: #dc3545;
        color: white;
    }
    
    .btn-danger:hover {
        background: #c82333;
        transform: translateY(-2px);
    }
    
    .btn-success {
        background: #28a745;
        color: white;
    }
    
    .btn-success:hover {
        background: #218838;
        transform: translateY(-2px);
    }
    
    .status-badge {
        padding: 4px 10px;
        border-radius: 15px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .status-pending {
        background: #fff3cd;
        color: #856404;
    }
    
    .status-confirmed {
        background: #d4edda;
        color: #155724;
    }
    
    .status-cancelled {
        background: #f8d7da;
        color: #721c24;
    }
    
    .recent-bookings-list,
    .van-availability-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    
    .booking-item,
    .van-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px;
        background: #f8f9fa;
        border-radius: 8px;
        border-left: 4px solid #667eea;
    }
    
    .booking-info,
    .van-info {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    
    .booking-info strong,
    .van-info strong {
        color: #333;
        font-size: 0.95rem;
    }
    
    .booking-info span,
    .van-info span {
        color: #666;
        font-size: 0.85rem;
    }
`;
document.head.appendChild(style);