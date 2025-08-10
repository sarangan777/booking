<?php
/**
 * VanGo Database Setup Script
 * Creates database, tables, and inserts sample data
 */

// Include database functions
require_once 'database.php';

// Set headers
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VanGo - Database Setup</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .setup-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .setup-step {
            margin: 20px 0;
            padding: 20px;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            background: #f8f9fa;
        }
        .setup-step.success {
            border-color: #28a745;
            background: #d4edda;
        }
        .setup-step.error {
            border-color: #dc3545;
            background: #f8d7da;
        }
        .setup-step h3 {
            margin: 0 0 10px 0;
            color: #333;
        }
        .setup-step p {
            margin: 5px 0;
            color: #666;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        .progress-bar {
            width: 100%;
            height: 20px;
            background: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
            margin: 20px 0;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            width: 0%;
            transition: width 0.3s ease;
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <div style="text-align: center; margin-bottom: 30px;">
            <i class="fas fa-van-shuttle" style="font-size: 48px; color: #667eea; margin-bottom: 20px;"></i>
            <h1>VanGo Database Setup</h1>
            <p>Setting up the database for your van booking system</p>
        </div>

        <div class="progress-bar">
            <div class="progress-fill" id="progressBar"></div>
        </div>

        <div id="setupSteps">
            <!-- Setup steps will be populated here -->
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <button class="btn" onclick="runSetup()" id="setupBtn">
                <i class="fas fa-database"></i> Run Setup
            </button>
            <a href="index.php" class="btn" style="margin-left: 10px; background: #6c757d;">
                <i class="fas fa-home"></i> Go to Home
            </a>
        </div>
    </div>

    <script>
        const setupSteps = [
            { id: 'database', name: 'Create Database', description: 'Creating the main database' },
            { id: 'tables', name: 'Create Tables', description: 'Setting up all required tables' },
            { id: 'sample_data', name: 'Insert Sample Data', description: 'Adding sample vans and admin user' },
            { id: 'settings', name: 'System Settings', description: 'Configuring system settings' },
            { id: 'complete', name: 'Setup Complete', description: 'Database setup finished successfully' }
        ];

        function updateProgress(step) {
            const progress = ((step + 1) / setupSteps.length) * 100;
            document.getElementById('progressBar').style.width = progress + '%';
        }

        function updateStep(stepIndex, status, message) {
            const step = setupSteps[stepIndex];
            const stepElement = document.getElementById(step.id);
            
            if (stepElement) {
                stepElement.className = `setup-step ${status}`;
                stepElement.innerHTML = `
                    <h3><i class="fas fa-${status === 'success' ? 'check-circle' : status === 'error' ? 'exclamation-circle' : 'spinner fa-spin'}"></i> ${step.name}</h3>
                    <p>${message || step.description}</p>
                `;
            }
        }

        function createStepElements() {
            const container = document.getElementById('setupSteps');
            setupSteps.forEach((step, index) => {
                const stepElement = document.createElement('div');
                stepElement.id = step.id;
                stepElement.className = 'setup-step';
                stepElement.innerHTML = `
                    <h3><i class="fas fa-circle"></i> ${step.name}</h3>
                    <p>${step.description}</p>
                `;
                container.appendChild(stepElement);
            });
        }

        async function runSetup() {
            const btn = document.getElementById('setupBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Running Setup...';

            createStepElements();

            try {
                // Step 1: Create Database
                updateStep(0, 'pending', 'Creating database...');
                const dbResult = await fetch('setup-database.php', { method: 'POST' });
                const dbData = await dbResult.json();
                
                if (dbData.success) {
                    updateStep(0, 'success', 'Database created successfully');
                    updateProgress(0);
                } else {
                    updateStep(0, 'error', dbData.message);
                    throw new Error(dbData.message);
                }

                // Step 2: Create Tables
                updateStep(1, 'pending', 'Creating tables...');
                const tablesResult = await fetch('setup-tables.php', { method: 'POST' });
                const tablesData = await tablesResult.json();
                
                if (tablesData.success) {
                    updateStep(1, 'success', 'Tables created successfully');
                    updateProgress(1);
                } else {
                    updateStep(1, 'error', tablesData.message);
                    throw new Error(tablesData.message);
                }

                // Step 3: Insert Sample Data
                updateStep(2, 'pending', 'Inserting sample data...');
                const sampleResult = await fetch('setup-sample-data.php', { method: 'POST' });
                const sampleData = await sampleResult.json();
                
                if (sampleData.success) {
                    updateStep(2, 'success', 'Sample data inserted successfully');
                    updateProgress(2);
                } else {
                    updateStep(2, 'error', sampleData.message);
                    throw new Error(sampleData.message);
                }

                // Step 4: System Settings
                updateStep(3, 'pending', 'Configuring system settings...');
                const settingsResult = await fetch('setup-settings.php', { method: 'POST' });
                const settingsData = await settingsResult.json();
                
                if (settingsData.success) {
                    updateStep(3, 'success', 'System settings configured');
                    updateProgress(3);
                } else {
                    updateStep(3, 'error', settingsData.message);
                    throw new Error(settingsData.message);
                }

                // Step 5: Complete
                updateStep(4, 'success', 'Setup completed successfully! You can now use the system.');
                updateProgress(4);

                btn.innerHTML = '<i class="fas fa-check"></i> Setup Complete';
                btn.style.background = '#28a745';

            } catch (error) {
                console.error('Setup error:', error);
                btn.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Setup Failed';
                btn.style.background = '#dc3545';
                btn.disabled = false;
            }
        }

        // Auto-run setup if requested
        if (window.location.search.includes('auto=1')) {
            setTimeout(runSetup, 1000);
        }
    </script>
</body>
</html> 