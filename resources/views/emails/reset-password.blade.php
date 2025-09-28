<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reset Password Anda</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .container {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #FF8C00;
        }
        .button {
            display: inline-block;
            background-color: #FF8C00;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #666;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">AKAR</div>
            <h2>Reset Password Anda</h2>
        </div>
        
        <p>Halo,</p>
        
        <p>Anda menerima email ini karena kami menerima permintaan reset password untuk akun Anda. Silakan klik tombol di bawah ini untuk melanjutkan proses reset password:</p>
        
        <div style="text-align: center;">
            <a href="{{ $resetUrl }}" class="button">Reset Password</a>
        </div>
        
        <p>Jika Anda tidak meminta reset password, tidak diperlukan tindakan lebih lanjut.</p>
        
        <p>Link reset password ini akan kedaluwarsa dalam 24 jam.</p>
        
        <p>Jika Anda mengalami masalah dengan tombol di atas, salin dan tempel URL berikut ke browser Anda:</p>
        
        <p>{{ $resetUrl }}</p>
        
        <p>Terima kasih,<br>Tim AKAR</p>
        
        <div class="footer">
            <p>Email ini dikirim secara otomatis, mohon jangan membalas email ini.</p>
        </div>
    </div>
</body>
</html> 