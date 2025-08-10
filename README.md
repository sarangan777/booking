# VanGo - Van Booking System

A modern, responsive van booking system built with HTML, CSS, JavaScript, and PHP. Features a beautiful user interface with real-time price calculation, booking management, and admin panel.

## 🚐 Features

### User Interface
- **Modern Design**: Clean, responsive design with gradient backgrounds and smooth animations
- **Interactive Booking Form**: Real-time price calculation and form validation
- **Van Type Selection**: Multiple van options with different pricing
- **Date/Time Selection**: Easy pickup and return date/time selection
- **Price Summary**: Live calculation of total booking cost
- **Success Modal**: Beautiful confirmation modal with booking details

### Backend Features
- **Database Management**: MySQL database with automatic table creation
- **Booking Processing**: Secure form handling and data validation
- **Email Notifications**: Automated confirmation emails (configurable)
- **Admin Panel**: Complete booking management system
- **API Endpoints**: RESTful API for availability checking and data retrieval

### Admin Panel
- **Dashboard Statistics**: Overview of bookings, revenue, and status
- **Booking Management**: View, confirm, and cancel bookings
- **Search & Filter**: Find bookings by customer, status, or reference
- **Real-time Updates**: Auto-refresh functionality

## 📁 File Structure

```
booking/
├── index.html          # Main booking interface
├── styles.css          # Modern CSS styling
├── script.js           # Interactive JavaScript functionality
├── database.php        # Database connection and functions
├── booking.php         # Booking processing and API endpoints
├── admin.php           # Admin panel for booking management
└── README.md           # This file
```

## 🛠️ Installation & Setup

### Prerequisites
- XAMPP, WAMP, or similar local server environment
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web browser with JavaScript enabled

### Step 1: Server Setup
1. Install XAMPP (or similar) on your system
2. Start Apache and MySQL services
3. Navigate to your web server directory (e.g., `htdocs` for XAMPP)

### Step 2: Project Setup
1. Clone or download this project to your web server directory
2. Create a new MySQL database named `van_booking`
3. Update database credentials in `database.php` if needed:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'van_booking');
define('DB_USER', 'root');        // Your MySQL username
define('DB_PASS', '');            // Your MySQL password
```

### Step 3: Database Initialization
1. Open your web browser
2. Navigate to `http://localhost/booking/` (or your project URL)
3. The system will automatically create the required database tables
4. Check the browser console for any database connection errors

### Step 4: Admin Access
1. Navigate to `http://localhost/booking/admin.php`
2. Login with default credentials:
   - Username: `admin`
   - Password: `admin123`
3. Change these credentials in production

## 🎨 Customization

### Van Types and Pricing
Edit the van pricing in `database.php`:

```php
$vanPrices = [
    'passenger' => 80,    // Passenger van daily rate
    'cargo' => 60,        // Cargo van daily rate
    'luxury' => 120,      // Luxury van daily rate
    'minibus' => 150      // Minibus daily rate
];
```

### Styling
- Modify `styles.css` to change colors, fonts, and layout
- Update the gradient colors in the CSS variables
- Customize the booking form layout and styling

### Email Configuration
To enable email notifications, uncomment the mail function in `booking.php`:

```php
// Send email (uncomment when email is configured)
mail($to, $subject, $message, implode("\r\n", $headers));
```

## 🔧 Configuration

### Database Settings
- **Host**: Usually `localhost`
- **Database Name**: `van_booking`
- **Username**: Your MySQL username (default: `root`)
- **Password**: Your MySQL password

### Email Settings
Configure your email server settings in `booking.php` for booking confirmations.

### Security
- Change default admin credentials
- Implement proper authentication for admin panel
- Add CSRF protection for forms
- Use HTTPS in production

## 📱 Usage

### Making a Booking
1. Open `index.html` in a web browser
2. Fill out the booking form with customer details
3. Select van type, pickup/return dates and times
4. View real-time price calculation
5. Submit the booking
6. Receive confirmation modal with booking details

### Admin Management
1. Access admin panel at `admin.php`
2. Login with admin credentials
3. View all bookings and statistics
4. Confirm or cancel pending bookings
5. Search and filter bookings as needed

## 🔌 API Endpoints

### Check Van Availability
```
GET /booking.php?action=check_availability&van_type=passenger&pickup_date=2024-01-15&return_date=2024-01-17
```

### Get Van Types
```
GET /booking.php?action=get_van_types
```

### Get Booking by Reference
```
GET /booking.php?action=get_booking&reference=VB1234567890
```

## 🎯 Features in Detail

### Real-time Price Calculation
- Automatically calculates total price based on van type and rental duration
- Updates price display as user changes selections
- Handles date validation and minimum rental periods

### Form Validation
- Client-side validation for immediate feedback
- Server-side validation for security
- Email format validation
- Date range validation
- Required field checking

### Responsive Design
- Mobile-friendly interface
- Adaptive layout for different screen sizes
- Touch-friendly form elements
- Optimized for tablets and smartphones

### Booking Management
- Unique booking references
- Status tracking (pending, confirmed, cancelled)
- Customer information storage
- Special requests handling
- Pickup and destination tracking

## 🚀 Future Enhancements

- **Payment Integration**: Add online payment processing
- **Calendar View**: Visual calendar for availability
- **Customer Portal**: Allow customers to manage their bookings
- **Driver Management**: Track drivers and assignments
- **Maintenance Tracking**: Vehicle maintenance scheduling
- **Reports**: Generate booking and revenue reports
- **Multi-language Support**: Internationalization
- **SMS Notifications**: Text message confirmations

## 🐛 Troubleshooting

### Common Issues

**Database Connection Error**
- Check MySQL service is running
- Verify database credentials in `database.php`
- Ensure database `van_booking` exists

**Form Not Submitting**
- Check PHP error logs
- Verify file permissions
- Ensure all required fields are filled

**Admin Panel Not Loading**
- Check session configuration
- Verify admin credentials
- Check PHP error logs

**Email Not Sending**
- Configure email server settings
- Check spam folder
- Verify email function is uncommented

### Error Logs
Check your server's error logs for detailed error messages:
- XAMPP: `xampp/apache/logs/error.log`
- WAMP: `wamp/logs/apache_error.log`

## 📄 License

This project is open source and available under the MIT License.

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📞 Support

For support or questions:
- Email: info@vango.com
- Phone: +1 (555) 123-4567
- Address: 123 Van Street, City, State 12345

---

**VanGo** - Your trusted partner for van rental services! 🚐✨ 