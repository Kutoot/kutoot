<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Kutoot OTP</title>
</head>
<body style="margin:0;padding:0;background-color:#ffffff;font-family:Arial,Helvetica,sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#ffffff;">
    <tr>
        <td align="center" style="padding:40px 0;">

            <!-- Main wrapper -->
            <table width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;width:100%;border-radius:12px;overflow:hidden;box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                
                <!-- Dark Header -->
                <tr>
                    <td align="center" style="background-color:#2a1d15;padding:40px 32px 32px 32px;">
                        <table cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td align="center" style="padding-bottom:16px;">
                                    <img
                                        src="{{ $message->embed(public_path('images/kutoot-full-logo.png')) }}"
                                        alt="Kutoot"
                                        height="32"
                                        style="display:block;border:0;outline:none;"
                                    >
                                </td>
                            </tr>
                            <tr>
                                <td align="center">
                                    <h1 style="margin:0;font-size:24px;color:#ffffff;font-family:Arial,Helvetica,sans-serif;font-weight:bold;">Verify Your Account</h1>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- Content Area -->
                <tr>
                    <td style="padding:40px 32px;background-color:#ffffff;">
                        
                        <!-- Greeting -->
                        <p style="margin:0 0 20px 0;font-size:16px;color:#333333;line-height:1.5;">Hello,</p>
                        
                        <!-- Intro -->
                        <p style="margin:0 0 32px 0;font-size:16px;color:#333333;line-height:1.5;">
                            We received a request to verify your account. Please use the 
                            <span style="background-color:#fef08a;padding:2px 4px;">One-Time Password (OTP)</span> 
                            below to complete your verification process.
                        </p>

                        <!-- Gradient OTP Block -->
                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td align="center" style="background: linear-gradient(135deg, #f48024 0%, #d33232 100%); border-radius:12px; padding:40px 20px;">
                                    <p style="margin:0 0-16px 0;font-size:12px;font-weight:bold;color:#ffffff;letter-spacing:1px;text-transform:uppercase;">YOUR OTP CODE</p>
                                    <p style="margin:16px 0 0 0;font-size:48px;font-weight:bold;color:#ffffff;letter-spacing:12px;font-family:Arial,Helvetica,sans-serif;">{{ $otp }}</p>
                                </td>
                            </tr>
                        </table>

                        <!-- Spacer -->
                        <div style="height:32px;">&nbsp;</div>

                        <!-- Important Notice Block -->
                        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#fff7ed;border-left:4px solid #f97316;border-radius:4px;">
                            <tr>
                                <td style="padding:16px 20px;">
                                    <p style="margin:0;font-size:14px;color:#333333;line-height:1.5;">
                                        <span style="margin-right:8px;">⏱️</span>
                                        <strong>Important:</strong> This code will expire in 10 minutes for security reasons.
                                    </p>
                                </td>
                            </tr>
                        </table>

                        <!-- Spacer -->
                        <div style="height:24px;">&nbsp;</div>

                        <!-- Security Tips Block -->
                        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#fffbeb;border-radius:8px;">
                            <tr>
                                <td style="padding:24px 24px;">
                                    <p style="margin:0 0 12px 0;font-size:16px;font-weight:bold;color:#1a1a1a;">🔒 Security Tips</p>
                                    <p style="margin:0;font-size:14px;color:#666666;line-height:1.6;">
                                        Never share this code with anyone. Our team will never ask for your OTP. If you didn't request this code, please ignore this email or contact our support team immediately.
                                    </p>
                                </td>
                            </tr>
                        </table>

                    </td>
                </tr>

                <!-- Dark Footer -->
                <tr>
                    <td align="center" style="background-color:#2a1d15;padding:32px;color:#ffffff;font-size:13px;line-height:1.8;font-family:Arial,Helvetica,sans-serif;">
                        <p style="margin:0;">&copy; 2026 Kutoot. All rights reserved.</p>
                        <p style="margin:4px 0;">Need help? <a href="mailto:it@kutoot.com" style="color:#60a5fa;text-decoration:none;">Contact Support</a></p>
                        <p style="margin:16px 0 0 0;color:#9ca3af;font-size:12px;">This is an automated message, please do not reply to this email.</p>
                    </td>
                </tr>

            </table>
            <!-- /Main wrapper -->

        </td>
    </tr>
</table>

</body>
</html>
