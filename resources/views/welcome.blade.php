<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Amati Sangkar</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        
        <!-- Bootstrap Icons -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

        <!-- Styles -->
        <style>
            body {
                background-color: #f8fafc;
                font-family: 'Figtree', sans-serif;
                color: #1a202c;
            }
            
            .hero-section {
                background-color: #bf6420;
                color: #ffffff;
                padding: 80px 0;
            }
            
            .feature-card {
                border-radius: 10px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                transition: transform 0.3s ease;
                height: 100%;
            }
            
            .feature-card:hover {
                transform: translateY(-5px);
            }
            
            .feature-icon {
                font-size: 2rem;
                color: #bf6420;
                margin-bottom: 1rem;
            }
            
            .btn-primary {
                background-color: #bf6420;
                border-color: #bf6420;
            }
            
            .btn-primary:hover {
                background-color: #a55518;
                border-color: #a55518;
            }
            
            .btn-outline-light:hover {
                color: #bf6420;
            }
            
            .footer {
                background-color: #1a202c;
                color: #ffffff;
                padding: 2rem 0;
            }
        </style>
    </head>
    <body>
        <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand fw-bold" href="{{ url('/') }}">
                    <span style="color: #bf6420;">AKAR</span>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a href="{{ url('/admin') }}" class="nav-link">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ url('/admin/login') }}" class="nav-link">Login</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <section class="hero-section">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <h1 class="display-4 fw-bold mb-4">Amati Sangkar</h1>
                        <p class="lead mb-4">Solusi digital untuk pendataan dan monitoring satwa di Indonesia</p>
                        <div class="d-flex gap-3">
                            <a href="{{ url('/admin/login') }}" class="btn btn-light">Login</a>
                            <a href="{{ url('/admin') }}" class="btn btn-outline-light">Dashboard</a>
                        </div>
                    </div>
                    <div class="col-lg-6 d-none d-lg-block text-center">
                        <img src="{{ asset('storage/LOGO_AKAR.png') }}" alt="Ilustrasi AKAR" class="img-fluid rounded shadow-lg">
                    </div>
                </div>
            </div>
        </section>

        <section class="py-5">
            <div class="container">
                <div class="text-center mb-5">
                    <h2 class="fw-bold">Fitur Utama Aplikasi Admin Panel</h2>
                    <p class="text-muted">Fitur yang tersedia di Admin Panel</p>
                </div>
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="card feature-card p-4">
                            <div class="text-center">
                                <div class="feature-icon">
                                    <i class="bi bi-card-checklist"></i>
                                </div>
                                <h4>Pemeliharaan & Penangkaran</h4>
                                <p class="text-muted">Pencatatan data hewan yang dipelihara dan dibudidayakan</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card feature-card p-4">
                            <div class="text-center">
                                <div class="feature-icon">
                                    <i class="bi bi-trophy"></i>
                                </div>
                                <h4>Lomba</h4>
                                <p class="text-muted">Pendataan satwa yang diikutsertakan dalam perlombaan</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card feature-card p-4">
                            <div class="text-center">
                                <div class="feature-icon">
                                    <i class="bi bi-geo-alt"></i>
                                </div>
                                <h4>Pemetaan Lokasi</h4>
                                <p class="text-muted">Visualisasi data berdasarkan sebaran lokasi</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card feature-card p-4">
                            <div class="text-center">
                                <div class="feature-icon">
                                    <i class="bi bi-clipboard-data"></i>
                                </div>
                                <h4>Perburuan</h4>
                                <p class="text-muted">Pencatatan data perburuan</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card feature-card p-4">
                            <div class="text-center">
                                <div class="feature-icon">
                                    <i class="bi bi-shop"></i>
                                </div>
                                <h4>Perdagangan</h4>
                                <p class="text-muted">Pendataan perdagangan</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card feature-card p-4">
                            <div class="text-center">
                                <div class="feature-icon">
                                    <i class="bi bi-graph-up"></i>
                                </div>
                                <h4>Statistik & Laporan</h4>
                                <p class="text-muted">Visualisasi data dalam bentuk grafik dan laporan</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-5 bg-light">
            <div class="container">
                <div class="text-center mb-5">
                    <h2 class="fw-bold">Tentang AKAR</h2>
                    <p class="text-muted">Amati Sangkar</p>
                </div>
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <p class="text-center mb-4">
                        Amati Sangkar atau disingkat menjadi AKAR adalah aplikasi untuk membantu sains warga (citizen scientist) dalam pendataan satwa yang ada didalam sangkar dan mengelola catatan saat di lapangan. Sains warga atau citizen scientist adalah siapapun (dengan berbagai macam latar belakang perkerjaan) yang melakukan kegiatan penelitian secara sukarela. AKAR dibangun secara sukarela, swadaya. Tujuan utamanya adalah untuk mengembangkan sains dan konservasi burung di Indonesia.
    Kenapa Amati Sangkar? Karena Indonesia adalah episentrum songbird crisis di Asia Tenggara. Pemanfaatan sumber daya alam, dalam hal ini burung, secara besar-besaran hanya untuk tujuan ekonomi mengakibatkan menurunnya populasi burung di alam. Dengan adanya aplikasi ini, diharapkan bisa menjadi landasan dasar dalam mengelola kekayaan hayati burung di Indonesia. Membangun ekonomi tanpa merusak keseimbangan ekosistem.
                        </p>
                        <p class="text-center">
                            Dengan adanya AKAR, diharapkan dapat meningkatkan kesadaran masyarakat tentang pentingnya konservasi 
                            satwa dan membantu upaya perlindungan satwa di Indonesia.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <footer class="footer">
            <div class="container">
                <div class="row">
                    <div class="col-md-6 mb-4 mb-md-0">
                        <h4 class="fw-bold mb-3">AKAR</h4>
                        <p>Amati Sangkar</p>
                        <p class="mb-0">&copy; {{ date('Y') }} - Hak Cipta Dilindungi</p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <h5 class="mb-3">Kontak</h5>
                        <p><i class="bi bi-envelope me-2"></i> info@akar.id</p>
                        <p><i class="bi bi-telephone me-2"></i> +62 881 0260 41919</p>
                    </div>
                </div>
            </div>
        </footer>

        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
