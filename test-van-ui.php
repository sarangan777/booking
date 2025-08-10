<?php
/**
 * Test Van Selection UI
 * Showcases the enhanced van selection interface design
 */

session_start();
require_once 'database.php';

// Set content type
header('Content-Type: text/html; charset=UTF-8');

// Function to get appropriate icon for van type
function getVanIcon($type) {
    $type = strtolower($type);
    switch ($type) {
        case 'passenger':
        case 'people':
            return 'users';
        case 'luxury':
        case 'premium':
            return 'crown';
        case 'cargo':
        case 'freight':
            return 'box';
        case 'minibus':
        case 'bus':
            return 'bus';
        case 'suv':
            return 'car';
        case 'sedan':
            return 'car-side';
        default:
            return 'van-shuttle';
    }
}

// Get sample vans for demonstration
$sampleVans = [
    [
        'van_id' => 'VAN001',
        'type' => 'Economy',
        'seats' => 4,
        'daily_rate' => 80,
        'ac' => true,
        'wifi' => false,
        'gps' => true,
        'status' => 'available'
    ],
    [
        'van_id' => 'VAN002',
        'type' => 'Standard',
        'seats' => 6,
        'daily_rate' => 120,
        'ac' => true,
        'wifi' => true,
        'gps' => true,
        'status' => 'available'
    ],
    [
        'van_id' => 'VAN003',
        'type' => 'Premium',
        'seats' => 8,
        'daily_rate' => 180,
        'ac' => true,
        'wifi' => true,
        'gps' => true,
        'status' => 'available'
    ],
    [
        'van_id' => 'VAN004',
        'type' => 'Luxury',
        'seats' => 6,
        'daily_rate' => 250,
        'ac' => true,
        'wifi' => true,
        'gps' => true,
        'status' => 'available'
    ],
    [
        'van_id' => 'VAN005',
        'type' => 'Minibus',
        'seats' => 12,
        'daily_rate' => 200,
        'ac' => true,
        'wifi' => true,
        'gps' => true,
        'status' => 'available'
    ],
    [
        'van_id' => 'VAN006',
        'type' => 'Cargo',
        'seats' => 2,
        'daily_rate' => 100,
        'ac' => false,
        'wifi' => false,
        'gps' => true,
        'status' => 'available'
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Van Selection UI Demo - VanGo</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .demo-container {
            max-width: 1200px;
            margin: 50px auto;
            padding: 20px;
        }
        
        .demo-header {
            text-align: center;
            margin-bottom: 50px;
        }
        
        .demo-header h1 {
            font-size: 3rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 15px;
        }
        
        .demo-header p {
            font-size: 1.2rem;
            color: #666;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .demo-section {
            margin-bottom: 60px;
        }
        
        .demo-section h2 {
            font-size: 2rem;
            color: #333;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .van-selection {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .van-option {
            border: 2px solid #e9ecef;
            border-radius: 20px;
            padding: 30px 25px;
            text-align: center;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            background: white;
            position: relative;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            animation: fadeInUp 0.6s ease forwards;
            opacity: 0;
            transform: translateY(30px);
        }
        
        .van-option:nth-child(1) { animation-delay: 0.1s; }
        .van-option:nth-child(2) { animation-delay: 0.2s; }
        .van-option:nth-child(3) { animation-delay: 0.3s; }
        .van-option:nth-child(4) { animation-delay: 0.4s; }
        .van-option:nth-child(5) { animation-delay: 0.5s; }
        .van-option:nth-child(6) { animation-delay: 0.6s; }
        
        .van-option::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }
        
        .van-option:hover {
            border-color: #667eea;
            transform: translateY(-8px);
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.15);
        }
        
        .van-option:hover::before {
            transform: scaleX(1);
        }
        
        .van-option.selected {
            border-color: #667eea;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            transform: translateY(-8px);
            box-shadow: 0 20px 50px rgba(102, 126, 234, 0.3);
            animation: selectedPulse 0.3s ease;
        }
        
        .van-option.selected::before {
            transform: scaleX(1);
        }
        
        .van-option.selected::after {
            content: '✓';
            position: absolute;
            top: 15px;
            right: 15px;
            width: 25px;
            height: 25px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: bold;
        }
        
        .van-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2.5rem;
            color: white;
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
            transition: all 0.3s ease;
        }
        
        .van-option.selected .van-icon {
            background: rgba(255, 255, 255, 0.2);
            box-shadow: 0 10px 25px rgba(255, 255, 255, 0.2);
        }
        
        .van-option h4 {
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 8px;
            color: #333;
        }
        
        .van-option.selected h4 {
            color: white;
        }
        
        .van-type {
            font-size: 0.9rem;
            color: #667eea;
            font-weight: 600;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .van-option.selected .van-type {
            color: rgba(255, 255, 255, 0.8);
        }
        
        .van-capacity {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-bottom: 15px;
            font-size: 0.95rem;
            color: #666;
        }
        
        .van-option.selected .van-capacity {
            color: rgba(255, 255, 255, 0.9);
        }
        
        .van-capacity i {
            color: #667eea;
            font-size: 1rem;
        }
        
        .van-option.selected .van-capacity i {
            color: white;
        }
        
        .van-price {
            font-size: 1.8rem;
            font-weight: 800;
            color: #667eea;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }
        
        .van-price .currency {
            font-size: 1.2rem;
            font-weight: 600;
        }
        
        .van-price .period {
            font-size: 0.9rem;
            font-weight: 500;
            opacity: 0.7;
        }
        
        .van-option.selected .van-price {
            color: white;
        }
        
        .van-features {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: center;
            margin-top: 20px;
        }
        
        .feature-badge {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s ease;
        }
        
        .feature-badge:hover {
            background: rgba(102, 126, 234, 0.2);
            transform: translateY(-2px);
        }
        
        .van-option.selected .feature-badge {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }
        
        .van-option.selected .feature-badge:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        .van-popular {
            position: absolute;
            top: 15px;
            left: 15px;
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            color: white;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .van-recommended {
            position: absolute;
            top: 15px;
            left: 15px;
            background: linear-gradient(135deg, #00b894 0%, #00a085 100%);
            color: white;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes selectedPulse {
            0% { transform: translateY(-8px) scale(1); }
            50% { transform: translateY(-8px) scale(1.02); }
            100% { transform: translateY(-8px) scale(1); }
        }
        
        .demo-info {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 30px;
            border-radius: 20px;
            margin-bottom: 40px;
        }
        
        .demo-info h3 {
            color: #333;
            margin-bottom: 15px;
        }
        
        .demo-info ul {
            margin: 0;
            padding-left: 20px;
        }
        
        .demo-info li {
            margin-bottom: 8px;
            color: #666;
        }
        
        .navigation-links {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 40px;
        }
        
        .navigation-links a {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .navigation-links a:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }
        
        @media (max-width: 768px) {
            .van-selection {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .van-option {
                padding: 25px 20px;
            }
            
            .van-icon {
                width: 70px;
                height: 70px;
                font-size: 2rem;
            }
            
            .van-option h4 {
                font-size: 1.3rem;
            }
            
            .van-price {
                font-size: 1.6rem;
            }
        }
    </style>
</head>
<body>
    <div class="demo-container">
        <div class="demo-header">
            <h1>Van Selection UI Demo</h1>
            <p>Experience the enhanced van selection interface with modern design, smooth animations, and intuitive user experience.</p>
        </div>
        
        <div class="demo-section">
            <div class="demo-info">
                <h3>🎨 Design Features</h3>
                <ul>
                    <li><strong>Modern Card Design:</strong> Clean, rounded cards with subtle shadows and gradients</li>
                    <li><strong>Smooth Animations:</strong> Hover effects, selection feedback, and staggered loading animations</li>
                    <li><strong>Visual Hierarchy:</strong> Clear typography, icons, and color coding for easy scanning</li>
                    <li><strong>Feature Badges:</strong> Highlight van amenities like AC, WiFi, and GPS</li>
                    <li><strong>Status Indicators:</strong> Popular and recommended badges for better decision making</li>
                    <li><strong>Responsive Design:</strong> Optimized for all screen sizes and devices</li>
                </ul>
            </div>
            
            <h2>Choose Your Perfect Van</h2>
            <div class="van-selection">
                <?php foreach ($sampleVans as $van): ?>
                    <div class="van-option" 
                         data-van="<?php echo htmlspecialchars($van['van_id']); ?>" 
                         data-price="<?php echo $van['daily_rate']; ?>"
                         data-capacity="<?php echo $van['seats']; ?>"
                         data-type="<?php echo htmlspecialchars($van['type']); ?>">
                        
                        <?php if ($van['daily_rate'] >= 200): ?>
                            <div class="van-popular">Popular</div>
                        <?php elseif ($van['seats'] >= 8): ?>
                            <div class="van-recommended">Recommended</div>
                        <?php endif; ?>
                        
                        <div class="van-icon">
                            <i class="fas fa-<?php echo getVanIcon($van['type']); ?>"></i>
                        </div>
                        
                        <h4><?php echo htmlspecialchars($van['type']); ?> Van</h4>
                        <div class="van-type"><?php echo htmlspecialchars($van['type']); ?> Class</div>
                        
                        <div class="van-capacity">
                            <i class="fas fa-users"></i>
                            <span><?php echo $van['seats']; ?> Passengers</span>
                        </div>
                        
                        <div class="van-price">
                            <span class="currency">$</span>
                            <span class="amount"><?php echo number_format($van['daily_rate']); ?></span>
                            <span class="period">/day</span>
                        </div>
                        
                        <div class="van-features">
                            <?php if ($van['ac']): ?>
                                <span class="feature-badge"><i class="fas fa-snowflake"></i> AC</span>
                            <?php endif; ?>
                            <?php if ($van['wifi']): ?>
                                <span class="feature-badge"><i class="fas fa-wifi"></i> WiFi</span>
                            <?php endif; ?>
                            <?php if ($van['gps']): ?>
                                <span class="feature-badge"><i class="fas fa-map-marker-alt"></i> GPS</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="navigation-links">
            <a href="book-van.php">
                <i class="fas fa-calendar-plus"></i> Book Van Page
            </a>
            <a href="index.php">
                <i class="fas fa-home"></i> Home Page
            </a>
            <a href="vans.html">
                <i class="fas fa-van-shuttle"></i> Our Vans
            </a>
            <a href="auth.html">
                <i class="fas fa-sign-in-alt"></i> Login
            </a>
        </div>
    </div>

    <script>
        // Van selection functionality
        document.addEventListener('DOMContentLoaded', function() {
            const vanOptions = document.querySelectorAll('.van-option');
            
            vanOptions.forEach(option => {
                option.addEventListener('click', function() {
                    // Remove selected class from all options
                    vanOptions.forEach(opt => opt.classList.remove('selected'));
                    
                    // Add selected class to clicked option
                    this.classList.add('selected');
                    
                    // Get van data
                    const vanId = this.getAttribute('data-van');
                    const vanType = this.getAttribute('data-type');
                    const price = this.getAttribute('data-price');
                    const capacity = this.getAttribute('data-capacity');
                    
                    // Show selection feedback
                    console.log(`Selected: ${vanType} Van - $${price}/day - ${capacity} passengers`);
                    
                    // You can add more functionality here like updating a summary panel
                });
            });
        });
    </script>
</body>
</html> 