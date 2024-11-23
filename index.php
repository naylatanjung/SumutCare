<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SumutCare - Fasilitas Kesehatan Sumatera Utara</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <header class="header">
        <nav class="navbar navbar-expand-lg navbar-dark">
            <div class="container">
                <a class="navbar-brand" href="index.php?page=pages/home.php">SumutCare</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="/index.php?page=home.php">Beranda</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Cari Fasilitas</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Tentang</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Kontak</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <main>
        <?php
        // Memastikan file yang di-include aman
        $page = $_GET['page'] ?? 'pages/home.php';
        $safe_page = basename($page); // Ambil nama file tanpa folder
        $path = __DIR__ . "/pages/$safe_page"; // Tentukan path lengkap

        if (file_exists($path)) {
            include $path;
        } else {
            echo "<p>Halaman tidak ditemukan.</p>";
        }
        ?>
    </main>

    <footer class="footer text-white py-3">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p>&copy; 2024 SumutCare. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p>Kelompok X</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>