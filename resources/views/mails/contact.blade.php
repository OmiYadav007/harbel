<!DOCTYPE html>
<html>
<head>
    <title>New Contact Form Submission</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
        }
        .container {
            width: 80%;
            margin: 0 auto;
        }
        .header {
            background-color: #f8f8f8;
            padding: 10px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }
        .content {
            padding: 20px;
        }
        .footer {
            background-color: #f8f8f8;
            padding: 10px;
            text-align: center;
            border-top: 1px solid #ddd;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>New Contact Form Submission</h2>
        </div>
        <div>
            <p>Hey Herbal Health Wellness admin, you have received a new contact form submission.</p>
            <p>Here are the details:</p>
        </div>
        <div class="content">
            <p><strong>Name:</strong> {{ $data['name'] }}</p>
            <p><strong>Mobile:</strong> {{ $data['mobile'] }}</p>
        </div>
        <div class="footer">
            <p>This email was sent from your website's contact form.</p>
        </div>
    </div>
</body>
</html>