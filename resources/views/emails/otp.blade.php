<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Kutoot Login OTP</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; background: #f4f4f7; margin: 0; padding: 0; }
        .container { max-width: 480px; margin: 40px auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .header { background: #f59e0b; padding: 24px; text-align: center; }
        .header h1 { color: #fff; margin: 0; font-size: 22px; }
        .body { padding: 32px 24px; text-align: center; }
        .otp-code { font-size: 36px; font-weight: bold; letter-spacing: 8px; color: #1f2937; background: #fef3c7; padding: 16px 32px; border-radius: 8px; display: inline-block; margin: 20px 0; }
        .info { color: #6b7280; font-size: 14px; line-height: 1.6; }
        .footer { background: #f9fafb; padding: 16px 24px; text-align: center; border-top: 1px solid #e5e7eb; }
        .footer p { color: #9ca3af; font-size: 12px; margin: 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Kutoot</h1>
        </div>
        <div class="body">
            <p style="font-size: 16px; color: #374151; margin-bottom: 8px;">Your login OTP is:</p>
            <div class="otp-code">{{ $otp }}</div>
            <p class="info">
                This code is valid for <strong>5 minutes</strong>.<br>
                Use it to securely access your Kutoot account.<br>
                Do not share this code with anyone.
            </p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} Kutoot — Shopping is Winning</p>
        </div>
    </div>
</body>
</html>
