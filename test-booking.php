<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Booking API</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-form { max-width: 500px; margin: 0 auto; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input, select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #0056b3; }
        .result { margin-top: 20px; padding: 15px; border-radius: 4px; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }
        .api-selector { margin-bottom: 20px; }
    </style>
</head>
<body>
    <h1>Test Booking API</h1>
    
    <div class="api-selector">
        <label>Select API to test:</label>
        <select id="apiSelect">
            <option value="booking-simple.php">Simple API (booking-simple.php)</option>
            <option value="booking.php">Full API (booking.php)</option>
        </select>
    </div>
    
    <div class="test-form">
        <form id="testForm">
            <div class="form-group">
                <label for="contactName">Name:</label>
                <input type="text" id="contactName" name="contactName" value="John Doe" required>
            </div>
            <div class="form-group">
                <label for="contactEmail">Email:</label>
                <input type="email" id="contactEmail" name="contactEmail" value="test@example.com" required>
            </div>
            <div class="form-group">
                <label for="contactPhone">Phone:</label>
                <input type="tel" id="contactPhone" name="contactPhone" value="+1234567890" required>
            </div>
            <div class="form-group">
                <label for="vanType">Van Type:</label>
                <select id="vanType" name="vanType" required>
                    <option value="passenger">Passenger Van</option>
                    <option value="cargo">Cargo Van</option>
                    <option value="luxury">Luxury Van</option>
                    <option value="minibus">Minibus</option>
                </select>
            </div>
            <div class="form-group">
                <label for="pickupLocation">Pickup Location:</label>
                <input type="text" id="pickupLocation" name="pickupLocation" value="123 Main St" required>
            </div>
            <div class="form-group">
                <label for="dropoffLocation">Dropoff Location:</label>
                <input type="text" id="dropoffLocation" name="dropoffLocation" value="456 Oak Ave" required>
            </div>
            <div class="form-group">
                <label for="pickupDate">Pickup Date:</label>
                <input type="date" id="pickupDate" name="pickupDate" required>
            </div>
            <div class="form-group">
                <label for="pickupTime">Pickup Time:</label>
                <input type="time" id="pickupTime" name="pickupTime" value="10:00" required>
            </div>
            <button type="submit">Test Booking API</button>
        </form>
        <div id="result"></div>
    </div>

    <script>
        // Set default date to tomorrow
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        document.getElementById('pickupDate').value = tomorrow.toISOString().split('T')[0];

        document.getElementById('testForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const selectedApi = document.getElementById('apiSelect').value;
            const formData = new FormData(this);
            const bookingData = {
                contactName: formData.get('contactName'),
                contactEmail: formData.get('contactEmail'),
                contactPhone: formData.get('contactPhone'),
                vanType: formData.get('vanType'),
                pickupLocation: formData.get('pickupLocation'),
                dropoffLocation: formData.get('dropoffLocation'),
                pickupDate: formData.get('pickupDate'),
                pickupTime: formData.get('pickupTime'),
                returnDate: '',
                returnTime: '',
                passengers: '2',
                luggage: '1',
                specialRequests: 'Test booking'
            };

            console.log('Sending data to:', selectedApi);
            console.log('Data:', bookingData);

            fetch(selectedApi, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(bookingData)
            })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                const resultDiv = document.getElementById('result');
                if (data.success) {
                    resultDiv.className = 'result success';
                    resultDiv.innerHTML = `
                        <h3>Success!</h3>
                        <p><strong>Message:</strong> ${data.message}</p>
                        ${data.booking ? `
                            <p><strong>Booking Reference:</strong> ${data.booking.reference}</p>
                            <p><strong>Total Price:</strong> $${data.booking.totalPrice}</p>
                        ` : ''}
                        ${data.received_data ? `<p><strong>Received Data:</strong> ${JSON.stringify(data.received_data)}</p>` : ''}
                    `;
                } else {
                    resultDiv.className = 'result error';
                    resultDiv.innerHTML = `
                        <h3>Error!</h3>
                        <p><strong>Message:</strong> ${data.message}</p>
                        ${data.errors ? `<p><strong>Errors:</strong> ${JSON.stringify(data.errors)}</p>` : ''}
                        ${data.received_method ? `<p><strong>Received Method:</strong> ${data.received_method}</p>` : ''}
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                const resultDiv = document.getElementById('result');
                resultDiv.className = 'result error';
                resultDiv.innerHTML = `
                    <h3>Network Error!</h3>
                    <p>${error.message}</p>
                    <p>This might be a 404 error if the API file is not found.</p>
                    <p>Make sure you're accessing: http://localhost/booking/${selectedApi}</p>
                `;
            });
        });
    </script>
</body>
</html> 