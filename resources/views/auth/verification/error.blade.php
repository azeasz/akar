<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Email Gagal - AKAR</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            max-width: 500px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 40px;
            text-align: center;
            margin: 20px;
        }
        .logo {
            font-size: 32px;
            font-weight: 700;
            color: #28a745;
            margin-bottom: 20px;
        }
        .icon {
            font-size: 80px;
            color: #dc3545;
            margin-bottom: 20px;
        }
        h1 {
            color: #dc3545;
            font-size: 24px;
            margin-bottom: 20px;
        }
        p {
            color: #666;
            font-size: 16px;
            margin-bottom: 30px;
        }
        .button {
            display: inline-block;
            background-color: #28a745;
            color: white;
            text-decoration: none;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            transition: background-color 0.3s;
        }
        .button:hover {
            background-color: #218838;
        }
        .button-secondary {
            display: inline-block;
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            transition: background-color 0.3s;
            margin-right: 10px;
        }
        .button-secondary:hover {
            background-color: #5a6268;
        }
        .button-container {
            display: flex;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .footer {
            margin-top: 40px;
            color: #999;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">AKAR</div>
        
        <div class="icon">✕</div>
        
        <h1>Verifikasi Email Gagal</h1>
        
        <p>Maaf, token verifikasi yang Anda gunakan tidak valid atau sudah kadaluarsa. Silakan gunakan link verifikasi yang valid atau minta link verifikasi baru.</p>
        
        <div class="button-container">
            <a href="{{ route('verification.notice') }}" class="button-secondary">Kirim Ulang Link</a>
            <a href="{{ url('/login') }}" class="button">Masuk</a>
        </div>
        
        <div class="footer">
            <p>Jika Anda mengalami masalah, silakan hubungi tim dukungan kami.</p>
        </div>
    </div>
</body>
</html> 