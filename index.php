<?php
// Start session
session_start();

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['user_name'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VanGo - Premium Van Booking Service</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 120px 0 80px;
            position: relative;
            overflow: hidden;
        }
        
        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
        }
        
        .hero-text h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            line-height: 1.2;
        }
        
        .hero-text p {
            font-size: 1.3rem;
            margin-bottom: 30px;
            opacity: 0.9;
            line-height: 1.6;
        }
        
        .hero-buttons {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .hero-image {
            position: relative;
        }
        
        .hero-image img {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        
        .hero-stats {
            background: white;
            padding: 60px 0;
            margin-top: -50px;
            position: relative;
            z-index: 3;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 40px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .stat-card {
            text-align: center;
            padding: 30px 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .stat-label {
            font-size: 1.1rem;
            color: #666;
            font-weight: 500;
        }
        
        .quick-links {
            padding: 100px 0;
            background: #f8f9fa;
        }
        
        .quick-links-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .quick-link-card {
            background: white;
            padding: 40px 30px;
            border-radius: 20px;
            text-align: center;
            text-decoration: none;
            color: inherit;
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .quick-link-card:hover {
            transform: translateY(-10px);
            border-color: #667eea;
            box-shadow: 0 25px 50px rgba(102, 126, 234, 0.2);
        }
        
        .quick-link-card i {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 20px;
        }
        
        .quick-link-card h3 {
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: #333;
        }
        
        .quick-link-card p {
            color: #666;
            line-height: 1.6;
        }
        
        .features {
            padding: 100px 0;
            background: white;
        }
        
        .features h2 {
            text-align: center;
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 60px;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 40px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .feature-card {
            text-align: center;
            padding: 40px 30px;
            border-radius: 15px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            border-color: #667eea;
            box-shadow: 0 20px 40px rgba(102, 126, 234, 0.2);
        }
        
        .feature-card i {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 25px;
        }
        
        .feature-card h3 {
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: #333;
        }
        
        .feature-card p {
            color: #666;
            line-height: 1.6;
        }
        
        .testimonials {
            padding: 100px 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .testimonials h2 {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 60px;
        }
        
        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 40px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .testimonial-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 40px 30px;
            border-radius: 20px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .testimonial-card .stars {
            color: #ffd700;
            font-size: 1.2rem;
            margin-bottom: 20px;
        }
        
        .testimonial-card p {
            font-style: italic;
            margin-bottom: 20px;
            line-height: 1.6;
        }
        
        .testimonial-card .author {
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .booking {
            padding: 100px 0;
            background: #f8f9fa;
        }
        
        .booking h2 {
            text-align: center;
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 60px;
        }
        
        .booking-form {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 50px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
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
            font-weight: 600;
            color: #333;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 15px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .booking-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 40px;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .booking-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }
        
        @media (max-width: 768px) {
            .hero-content {
                grid-template-columns: 1fr;
                gap: 40px;
            }
            
            .hero-text h1 {
                font-size: 2.5rem;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .hero-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <i class="fas fa-van-shuttle"></i>
                <span>VanGo</span>
            </div>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="index.php" class="nav-link active">Home</a>
                </li>
                <li class="nav-item">
                    <a href="vans.html" class="nav-link">Our Vans</a>
                </li>
                <li class="nav-item">
                    <a href="about.html" class="nav-link">About</a>
                </li>
                <li class="nav-item">
                    <a href="contact.html" class="nav-link">Contact</a>
                </li>
                <li class="nav-item">
                    <a href="book-van.php" class="nav-link">Book Now</a>
                </li>
                <?php if ($isLoggedIn): ?>
                    <li class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($userName); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="auth.html" class="dropdown-item">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a></li>
                            <li><a href="auth.html" class="dropdown-item">
                                <i class="fas fa-user-edit"></i> My Profile
                            </a></li>
                            <li><a href="auth.html" class="dropdown-item">
                                <i class="fas fa-calendar-check"></i> My Bookings
                            </a></li>
                            <li><a href="auth.html" class="dropdown-item">
                                <i class="fas fa-heart"></i> Favorites
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a href="logout.php" class="dropdown-item">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a href="auth.html" class="nav-link">Login</a>
                    </li>
                <?php endif; ?>
            </ul>
            <div class="hamburger">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <div class="hero-text">
                <h1>Premium Van Booking Service</h1>
                <p>Experience luxury and comfort with our premium van fleet. Book your ride today and travel in style with professional drivers and top-notch service.</p>
                <div class="hero-buttons">
                    <a href="#booking" class="btn btn-primary">Book Now</a>
                    <a href="vans.html" class="btn btn-secondary">View Fleet</a>
                    <a href="about.html" class="btn btn-outline">Learn More</a>
                </div>
            </div>
            <div class="hero-image">
                <img src="https://images.unsplash.com/photo-1549924231-f129b911e442?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80" alt="Luxury Van">
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="hero-stats">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number">10K+</div>
                <div class="stat-label">Happy Customers</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">50+</div>
                <div class="stat-label">Premium Vans</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">24/7</div>
                <div class="stat-label">Service Available</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">98%</div>
                <div class="stat-label">Satisfaction Rate</div>
            </div>
        </div>
    </section>

    <!-- Quick Links Section -->
    <section class="quick-links">
        <div class="quick-links-grid">
            <a href="vans.html" class="quick-link-card">
                <i class="fas fa-van-shuttle"></i>
                <h3>Our Fleet</h3>
                <p>Browse our premium van collection with detailed specifications and pricing</p>
            </a>
            <a href="book-van.php" class="quick-link-card">
                <i class="fas fa-calendar-plus"></i>
                <h3>Book Now</h3>
                <p>Reserve your van instantly with our easy-to-use booking system</p>
            </a>
            <a href="about.html" class="quick-link-card">
                <i class="fas fa-info-circle"></i>
                <h3>About Us</h3>
                <p>Learn about our mission, values, and commitment to excellence</p>
            </a>
            <a href="contact.html" class="quick-link-card">
                <i class="fas fa-phone"></i>
                <h3>Contact</h3>
                <p>Get in touch with our customer support team for assistance</p>
            </a>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <h2>Why Choose VanGo?</h2>
        <div class="features-grid">
            <div class="feature-card">
                <i class="fas fa-shield-alt"></i>
                <h3>Safe & Secure</h3>
                <p>All our vans are regularly maintained and our drivers are thoroughly vetted for your safety and peace of mind.</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-clock"></i>
                <h3>24/7 Service</h3>
                <p>Book anytime, anywhere. Our service is available round the clock for your convenience and urgent needs.</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-star"></i>
                <h3>Premium Quality</h3>
                <p>Luxury vans with modern amenities to ensure a comfortable and enjoyable journey every time.</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-dollar-sign"></i>
                <h3>Best Prices</h3>
                <p>Competitive pricing with no hidden fees. Get the best value for your money with transparent pricing.</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-user-tie"></i>
                <h3>Professional Drivers</h3>
                <p>Our experienced and licensed drivers ensure a safe, comfortable, and professional journey.</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-leaf"></i>
                <h3>Eco-Friendly</h3>
                <p>We maintain a fleet that meets modern environmental standards and efficiency requirements.</p>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials">
        <h2>What Our Customers Say</h2>
        <div class="testimonials-grid">
            <div class="testimonial-card">
                <div class="stars">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
                <p>"Excellent service! The van was clean, comfortable, and the driver was professional. Highly recommended!"</p>
                <div class="author">- Sarah Johnson</div>
            </div>
            <div class="testimonial-card">
                <div class="stars">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
                <p>"Perfect for our corporate event. The luxury van exceeded our expectations and made our trip memorable."</p>
                <div class="author">- Michael Chen</div>
            </div>
            <div class="testimonial-card">
                <div class="stars">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
                <p>"Reliable, punctual, and affordable. VanGo has become our go-to choice for all transportation needs."</p>
                <div class="author">- Emily Rodriguez</div>
            </div>
        </div>
    </section>

    <!-- Customer Profiles Section -->
    <section class="customer-profiles">
        <div class="container">
            <h2>Meet Our Happy Customers</h2>
            <p class="section-subtitle">Discover why thousands of customers choose VanGo for their transportation needs</p>
            
            <div class="profiles-grid">
                <div class="profile-card">
                    <div class="profile-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3>Sarah Johnson</h3>
                    <p class="profile-title">Business Executive</p>
                    <p class="profile-location">New York, NY</p>
                    <div class="profile-stats">
                        <span><i class="fas fa-calendar"></i> 15+ Bookings</span>
                        <span><i class="fas fa-star"></i> 5.0 Rating</span>
                    </div>
                    <p class="profile-quote">"VanGo has transformed my business travel experience. Professional service every time!"</p>
                </div>

                <div class="profile-card">
                    <div class="profile-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3>Michael Chen</h3>
                    <p class="profile-title">Event Planner</p>
                    <p class="profile-location">Los Angeles, CA</p>
                    <div class="profile-stats">
                        <span><i class="fas fa-calendar"></i> 25+ Bookings</span>
                        <span><i class="fas fa-star"></i> 5.0 Rating</span>
                    </div>
                    <p class="profile-quote">"Perfect for corporate events. Luxury vans that impress our clients!"</p>
                </div>

                <div class="profile-card">
                    <div class="profile-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3>Emily Rodriguez</h3>
                    <p class="profile-title">Family Traveler</p>
                    <p class="profile-location">Miami, FL</p>
                    <div class="profile-stats">
                        <span><i class="fas fa-calendar"></i> 8+ Bookings</span>
                        <span><i class="fas fa-star"></i> 5.0 Rating</span>
                    </div>
                    <p class="profile-quote">"Safe and comfortable for family trips. Kids love the spacious vans!"</p>
                </div>

                <div class="profile-card">
                    <div class="profile-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3>David Thompson</h3>
                    <p class="profile-title">Tour Guide</p>
                    <p class="profile-location">San Francisco, CA</p>
                    <div class="profile-stats">
                        <span><i class="fas fa-calendar"></i> 40+ Bookings</span>
                        <span><i class="fas fa-star"></i> 5.0 Rating</span>
                    </div>
                    <p class="profile-quote">"Reliable service for tour groups. Professional drivers who know the city well."</p>
                </div>

                <div class="profile-card">
                    <div class="profile-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3>Lisa Wang</h3>
                    <p class="profile-title">Wedding Coordinator</p>
                    <p class="profile-location">Chicago, IL</p>
                    <div class="profile-stats">
                        <span><i class="fas fa-calendar"></i> 30+ Bookings</span>
                        <span><i class="fas fa-star"></i> 5.0 Rating</span>
                    </div>
                    <p class="profile-quote">"Elegant vans for wedding transportation. Makes special days even more memorable."</p>
                </div>

                <div class="profile-card">
                    <div class="profile-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3>Robert Martinez</h3>
                    <p class="profile-title">Sports Team Manager</p>
                    <p class="profile-location">Dallas, TX</p>
                    <div class="profile-stats">
                        <span><i class="fas fa-calendar"></i> 50+ Bookings</span>
                        <span><i class="fas fa-star"></i> 5.0 Rating</span>
                    </div>
                    <p class="profile-quote">"Perfect for team transportation. Spacious vans accommodate all our equipment."</p>
                </div>

                <div class="profile-card">
                    <div class="profile-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3>Jennifer Lee</h3>
                    <p class="profile-title">Travel Blogger</p>
                    <p class="profile-location">Seattle, WA</p>
                    <div class="profile-stats">
                        <span><i class="fas fa-calendar"></i> 20+ Bookings</span>
                        <span><i class="fas fa-star"></i> 5.0 Rating</span>
                    </div>
                    <p class="profile-quote">"Luxury travel experience that I love sharing with my followers!"</p>
                </div>

                <div class="profile-card">
                    <div class="profile-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3>James Wilson</h3>
                    <p class="profile-title">Real Estate Agent</p>
                    <p class="profile-location">Phoenix, AZ</p>
                    <div class="profile-stats">
                        <span><i class="fas fa-calendar"></i> 35+ Bookings</span>
                        <span><i class="fas fa-star"></i> 5.0 Rating</span>
                    </div>
                    <p class="profile-quote">"Professional service for client tours. Always on time and presentable."</p>
                </div>

                <div class="profile-card">
                    <div class="profile-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3>Maria Garcia</h3>
                    <p class="profile-title">Restaurant Owner</p>
                    <p class="profile-location">Houston, TX</p>
                    <div class="profile-stats">
                        <span><i class="fas fa-calendar"></i> 12+ Bookings</span>
                        <span><i class="fas fa-star"></i> 5.0 Rating</span>
                    </div>
                    <p class="profile-quote">"Great for catering events. Clean vans that maintain food quality."</p>
                </div>

                <div class="profile-card">
                    <div class="profile-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3>Thomas Brown</h3>
                    <p class="profile-title">Film Producer</p>
                    <p class="profile-location">Atlanta, GA</p>
                    <div class="profile-stats">
                        <span><i class="fas fa-calendar"></i> 45+ Bookings</span>
                        <span><i class="fas fa-star"></i> 5.0 Rating</span>
                    </div>
                    <p class="profile-quote">"Essential for film production. Reliable transportation for cast and crew."</p>
                </div>

                <div class="profile-card">
                    <div class="profile-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3>Amanda Taylor</h3>
                    <p class="profile-title">School Principal</p>
                    <p class="profile-location">Denver, CO</p>
                    <div class="profile-stats">
                        <span><i class="fas fa-calendar"></i> 18+ Bookings</span>
                        <span><i class="fas fa-star"></i> 5.0 Rating</span>
                    </div>
                    <p class="profile-quote">"Safe transportation for school trips. Parents trust VanGo with their children."</p>
                </div>

                <div class="profile-card">
                    <div class="profile-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3>Christopher Davis</h3>
                    <p class="profile-title">Music Band Manager</p>
                    <p class="profile-location">Nashville, TN</p>
                    <div class="profile-stats">
                        <span><i class="fas fa-calendar"></i> 60+ Bookings</span>
                        <span><i class="fas fa-star"></i> 5.0 Rating</span>
                    </div>
                    <p class="profile-quote">"Perfect for band tours. Spacious vans for instruments and equipment."</p>
                </div>

                <div class="profile-card">
                    <div class="profile-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3>Rachel Green</h3>
                    <p class="profile-title">Fashion Designer</p>
                    <p class="profile-location">Las Vegas, NV</p>
                    <div class="profile-stats">
                        <span><i class="fas fa-calendar"></i> 22+ Bookings</span>
                        <span><i class="fas fa-star"></i> 5.0 Rating</span>
                    </div>
                    <p class="profile-quote">"Elegant transportation for fashion shows. Makes a great impression!"</p>
                </div>

                <div class="profile-card">
                    <div class="profile-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3>Kevin Anderson</h3>
                    <p class="profile-title">Tech Startup CEO</p>
                    <p class="profile-location">Austin, TX</p>
                    <div class="profile-stats">
                        <span><i class="fas fa-calendar"></i> 28+ Bookings</span>
                        <span><i class="fas fa-star"></i> 5.0 Rating</span>
                    </div>
                    <p class="profile-quote">"Professional service for investor meetings. Always makes the right impression."</p>
                </div>

                <div class="profile-card">
                    <div class="profile-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3>Sophie Turner</h3>
                    <p class="profile-title">Travel Agent</p>
                    <p class="profile-location">Orlando, FL</p>
                    <div class="profile-stats">
                        <span><i class="fas fa-calendar"></i> 55+ Bookings</span>
                        <span><i class="fas fa-star"></i> 5.0 Rating</span>
                    </div>
                    <p class="profile-quote">"Reliable partner for travel packages. Clients always satisfied with VanGo."</p>
                </div>

                <div class="profile-card">
                    <div class="profile-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3>Daniel Clark</h3>
                    <p class="profile-title">Conference Organizer</p>
                    <p class="profile-location">Boston, MA</p>
                    <div class="profile-stats">
                        <span><i class="fas fa-calendar"></i> 32+ Bookings</span>
                        <span><i class="fas fa-star"></i> 5.0 Rating</span>
                    </div>
                    <p class="profile-quote">"Essential for conference logistics. Handles large groups efficiently."</p>
                </div>

                <div class="profile-card">
                    <div class="profile-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3>Nicole White</h3>
                    <p class="profile-title">Wedding Photographer</p>
                    <p class="profile-location">Portland, OR</p>
                    <div class="profile-stats">
                        <span><i class="fas fa-calendar"></i> 15+ Bookings</span>
                        <span><i class="fas fa-star"></i> 5.0 Rating</span>
                    </div>
                    <p class="profile-quote">"Perfect for wedding photography teams. Clean and professional service."</p>
                </div>

                <div class="profile-card">
                    <div class="profile-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3>Ryan Johnson</h3>
                    <p class="profile-title">Sports Coach</p>
                    <p class="profile-location">Kansas City, MO</p>
                    <div class="profile-stats">
                        <span><i class="fas fa-calendar"></i> 25+ Bookings</span>
                        <span><i class="fas fa-star"></i> 5.0 Rating</span>
                    </div>
                    <p class="profile-quote">"Great for team transportation to games and tournaments."</p>
                </div>

                <div class="profile-card">
                    <div class="profile-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3>Jessica Moore</h3>
                    <p class="profile-title">Corporate Trainer</p>
                    <p class="profile-location">Minneapolis, MN</p>
                    <div class="profile-stats">
                        <span><i class="fas fa-calendar"></i> 20+ Bookings</span>
                        <span><i class="fas fa-star"></i> 5.0 Rating</span>
                    </div>
                    <p class="profile-quote">"Professional service for corporate training events. Always reliable."</p>
                </div>

                <div class="profile-card">
                    <div class="profile-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3>Mark Williams</h3>
                    <p class="profile-title">Construction Manager</p>
                    <p class="profile-location">Detroit, MI</p>
                    <div class="profile-stats">
                        <span><i class="fas fa-calendar"></i> 35+ Bookings</span>
                        <span><i class="fas fa-star"></i> 5.0 Rating</span>
                    </div>
                    <p class="profile-quote">"Reliable transportation for construction crews. Handles equipment well."</p>
                </div>
            </div>
            
            <div class="profiles-cta">
                <a href="customer-profiles.html" class="btn btn-primary">View All Customer Stories</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>VanGo</h3>
                    <p>Premium van booking service for all your transportation needs.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="vans.html">Our Vans</a></li>
                        <li><a href="about.html">About Us</a></li>
                        <li><a href="contact.html">Contact</a></li>
                        <li><a href="book-van.php">Book Now</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Services</h4>
                    <ul>
                        <li><a href="#">Airport Transfer</a></li>
                        <li><a href="#">City Tours</a></li>
                        <li><a href="#">Corporate Travel</a></li>
                        <li><a href="#">Wedding Transportation</a></li>
                        <li><a href="#">Group Travel</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Contact Info</h4>
                    <p><i class="fas fa-phone"></i> +1 (555) 123-4567</p>
                    <p><i class="fas fa-envelope"></i> info@vango.com</p>
                    <p><i class="fas fa-map-marker-alt"></i> 123 Main St, City, State 12345</p>
                    <p><i class="fas fa-clock"></i> 24/7 Service Available</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 VanGo. All rights reserved. | <a href="auth.html">Privacy Policy</a> | <a href="auth.html">Terms of Service</a></p>
            </div>
        </div>
    </footer>

    <script src="script.js"></script>
</body>
</html> 