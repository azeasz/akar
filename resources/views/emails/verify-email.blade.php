<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Verifikasi Email Anda</title>
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
            <h2>Verifikasi Email Anda</h2>
        </div>
        
        <p>Halo {{ $user->name }},</p>
        
        <p>Terima kasih telah mendaftar di aplikasi AKAR. Untuk melengkapi pendaftaran Anda, silakan verifikasi alamat email Anda dengan mengklik tombol di bawah ini:</p>
        
        <div style="text-align: center;">
            <a href="{{ $verificationUrl }}" class="button">Verifikasi Email</a>
        </div>
        
        <p>Jika Anda tidak dapat mengklik tombol di atas, silakan salin dan tempel URL berikut ke browser Anda:</p>
        
        <p>{{ $verificationUrl }}</p>
        
        <p>Jika Anda tidak mendaftar di AKAR, Anda dapat mengabaikan email ini.</p>
        
        <p>Terima kasih,<br>Tim AKAR</p>
        
        <div class="footer">
            <p>Email ini dikirim secara otomatis, mohon jangan membalas email ini.</p>
        </div>
    </div>
</body>
</html> 