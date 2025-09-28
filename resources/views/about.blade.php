<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang AKAR - AKAR</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
        }
        .logo {
            font-size: 32px;
            font-weight: 700;
            color: #FF8C00;
            margin-bottom: 10px;
        }
        h1 {
            font-size: 28px;
            margin-bottom: 20px;
        }
        h2 {
            font-size: 22px;
            margin-top: 30px;
            margin-bottom: 15px;
            color: #FF8C00;
        }
        p {
            margin-bottom: 15px;
        }
        ul, ol {
            margin-bottom: 20px;
            padding-left: 20px;
        }
        li {
            margin-bottom: 10px;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 14px;
            color: #666;
        }
        .back-button {
            display: inline-block;
            background-color: #FF8C00;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            margin-top: 30px;
        }
        .back-button:hover {
            background-color: #FF8C00;
        }
        .content {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
        .last-updated {
            font-size: 14px;
            color: #666;
            margin-bottom: 30px;
        }
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        .feature-item {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .feature-title {
            font-weight: 600;
            color: #FF8C00;
            margin-bottom: 10px;
        }
        img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">AKAR</div>
            <h1>Tentang AKAR</h1>
        </div>

        <div class="content">
            <div class="last-updated">
                Terakhir diperbarui: {{ $setting->updated_at->format('d F Y') }}
            </div>

            {!! $setting->description !!}
            
            <div style="text-align: center; margin-top: 40px;">
                <a href="{{ url('/') }}" class="back-button">Kembali ke Beranda</a>
            </div>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} AKAR. Hak Cipta Dilindungi.</p>
        </div>
    </div>
</body>
</html> 