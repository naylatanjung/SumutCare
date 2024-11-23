<?php
require __DIR__ . '/../vendor/autoload.php';
$config = require __DIR__ . '/../config/settings.php';

$fusekiEndpoint = $config['fusekiEndpoint'];
$id = isset($_GET['id']) ? urldecode($_GET['id']) : null;
$query = isset($_GET['query']) ? htmlspecialchars($_GET['query']) : 'default-query'; // Gunakan default jika kosong

if (!$id || !filter_var($id, FILTER_VALIDATE_URL)) {
    echo "<p>Data tidak ditemukan atau ID tidak valid.</p>";
    echo '<a href="search.php" class="btn btn-secondary">Kembali</a>';
    exit;
}

// Query SPARQL untuk mendapatkan detail berdasarkan kategori
$sparql = "
    PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
    PREFIX hospital: <http://example.org/hospital#>
    PREFIX clinic: <http://example.org/clinic#>
    PREFIX privatePractice: <http://example.org/privatePractice#>

    SELECT ?nama ?alamat ?jenis ?kelas ?kepemilikan ?direktur ?provinsi ?kota ?jKlinik ?jPerawatan ?kategori ?type ?latitude ?longitude WHERE {
        {
            <$id> a hospital:Hospital ;
                  rdfs:label ?nama ;
                  hospital:alamat ?alamat ;
                  hospital:jenis ?jenis ;
                  hospital:kelas ?kelas ;
                  hospital:kepemilikan ?kepemilikan ;
                  hospital:direktur ?direktur ;
                  hospital:provinsi ?provinsi ;
                  hospital:kab_kota ?kota ;
                  hospital:latitude ?latitude ;
                  hospital:longitude ?longitude .
            BIND('Hospital' AS ?type)
        }
        UNION
        {
            <$id> a clinic:Clinic ;
                  rdfs:label ?nama ;
                  clinic:alamat ?alamat ;
                  clinic:jenis_klinik ?jKlinik ;
                  clinic:jenis_perawatan ?jPerawatan ;
                  clinic:provinsi ?provinsi ;
                  clinic:kab_kota ?kota ;
                  clinic:latitude ?latitude ;
                  clinic:longitude ?longitude .
            BIND('Clinic' AS ?type)
        }
        UNION
        {
            <$id> a privatePractice:Privatepractice ;
                  rdfs:label ?nama ;
                  privatePractice:alamat ?alamat ;
                  privatePractice:kategori ?kategori ;
                  privatePractice:provinsi ?provinsi ;
                  privatePractice:kab_kota ?kota ;
                  privatePractice:latitude ?latitude ;
                  privatePractice:longitude ?longitude .
            BIND('Private Practice' AS ?type)
        }
    }
";

// Menggunakan cURL untuk mengirim permintaan ke Fuseki
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $fusekiEndpoint . '?query=' . urlencode($sparql));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/sparql-results+json']);

$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
$result = $data['results']['bindings'][0] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Fasilitas Kesehatan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        h2 {
            font-weight: bold;
            color: #005f63;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        #map {
            height: 100%;
            width: 100%;
            border-radius: 10px;
        }
        .btn-secondary {
            background-color: #6c757d;
            border: none;
            transition: background-color 0.3s ease;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        .map-container {
            height: 400px;
        }
        .navbar {
            background-color: #007b83;
        }
        .navbar-brand {
            color: #fff;
        }
        .footer {
            background-color: #007b83;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <?php if ($result): ?>
            <div class="text-center mb-4">
                <h2><?= htmlspecialchars($result['nama']['value']) ?></h2>
                <p class="text-muted">Informasi detail tentang fasilitas kesehatan</p>
            </div>
            <div class="card p-4">
                <div class="row">
                    <!-- Kolom Kiri: Peta -->
                    <div class="col-md-6 map-container">
                        <div id="map"></div>
                    </div>
                    <!-- Kolom Kanan: Detail -->
                    <div class="col-md-6">
                        <p><strong>Alamat:</strong> <?= htmlspecialchars($result['alamat']['value']) ?></p>
                        <?php if ($result['type']['value'] === 'Hospital'): ?>
                            <p><strong>Jenis:</strong> <?= htmlspecialchars($result['jenis']['value']) ?></p>
                            <p><strong>Kelas:</strong> <?= htmlspecialchars($result['kelas']['value']) ?></p>
                            <p><strong>Kepemilikan:</strong> <?= htmlspecialchars($result['kepemilikan']['value']) ?></p>
                            <p><strong>Direktur:</strong> <?= htmlspecialchars($result['direktur']['value']) ?></p>
                        <?php elseif ($result['type']['value'] === 'Clinic'): ?>
                            <p><strong>Jenis Klinik:</strong> <?= htmlspecialchars($result['jKlinik']['value']) ?></p>
                            <p><strong>Jenis Perawatan:</strong> <?= htmlspecialchars($result['jPerawatan']['value']) ?></p>
                        <?php elseif ($result['type']['value'] === 'Private Practice'): ?>
                            <p><strong>Kategori:</strong> <?= htmlspecialchars($result['kategori']['value']) ?></p>
                        <?php endif; ?>
                        <p><strong>Provinsi:</strong> <?= htmlspecialchars($result['provinsi']['value']) ?></p>
                        <p><strong>Kab/Kota:</strong> <?= htmlspecialchars($result['kota']['value']) ?></p>
                    </div>
                </div>
            </div>
            <div class="text-center mt-4">
            <a href="/index.php?page=search.php&query=<?= urlencode($query) ?>" class="btn btn-secondary">Kembali</a>
            </div>
            <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
            <script>
                const latitude = <?= json_encode($result['latitude']['value']) ?>;
                const longitude = <?= json_encode($result['longitude']['value']) ?>;
                const map = L.map('map').setView([latitude, longitude], 13);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors'
                }).addTo(map);
                L.marker([latitude, longitude]).addTo(map)
                    .bindPopup('<strong><?= htmlspecialchars($result['nama']['value']) ?></strong><br><?= htmlspecialchars($result['alamat']['value']) ?>')
                    .openPopup();
            </script>
        <?php else: ?>
            <div class="text-center">
                <p class="text-danger">Data tidak ditemukan.</p>
                <a href="index.php?page=search.php&query=<?= urlencode($query) ?>" class="btn btn-secondary btn-kembali">Kembali</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
