<?php
require __DIR__ . '/../vendor/autoload.php'; // Memuat EasyRdf dan dependensi lainnya
$config = require __DIR__ . '/../config/settings.php'; // Memuat konfigurasi

$fusekiEndpoint = $config['fusekiEndpoint'];
$query = $_GET['query'] ?? '';

// SPARQL query untuk mengambil data dengan filter pencarian
$sparql = "
    PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
    PREFIX hospital: <http://example.org/hospital#>
    PREFIX clinic: <http://example.org/clinic#>
    PREFIX privatePractice: <http://example.org/privatePractice#>

    SELECT ?facility ?name ?type ?city WHERE {
        {
            ?facility a hospital:Hospital ;
                      rdfs:label ?name ;
                      hospital:kab_kota ?city .
            BIND('Rumah Sakit' AS ?type)
        }
        UNION
        {
            ?facility a clinic:Clinic ;
                      rdfs:label ?name ;
                      clinic:kab_kota ?city .
            BIND('Klinik' AS ?type)
        }
        UNION
        {
            ?facility a privatePractice:Privatepractice ;
                      rdfs:label ?name ;
                      privatePractice:kab_kota ?city .
            BIND('Praktik Mandiri' AS ?type)
        }
        FILTER ( REGEX (?name, '$query', 'i') ||
                     REGEX (?city, '$query', 'i') ||
                     REGEX (?type, '$query', 'i')) .
    }
    ORDER BY ?name
";

// Menggunakan cURL untuk mengirim permintaan ke Fuseki
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $fusekiEndpoint . '?query=' . urlencode($sparql));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/sparql-results+json']);

$response = curl_exec($ch);
curl_close($ch);

// Parsing hasil JSON
$data = json_decode($response, true);
$results = $data['results']['bindings'] ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Pencarian - Fasilitas Kesehatan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center mb-4" style="color: #333;">Hasil Pencarian</h2>
        <?php if ($results): ?>
            <div class="row row-cols-1 row-cols-md-3 g-4">
                <?php foreach ($results as $item): ?>
                    <div class="col">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-header text-center card-header-custom">
                                <?= htmlspecialchars($item['type']['value']) ?>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?= htmlspecialchars($item['name']['value']) ?></h5>
                                <p class="card-text">
                                    <i class="fas fa-map-marker-alt"></i> 
                                    <?= htmlspecialchars($item['city']['value']) ?>
                                </p>
                                <a href="index.php?page=single.php&id=<?= urlencode($item['facility']['value']) ?>&query=<?= urlencode($query) ?>" class="btn mt-auto custom-hover-btn">Lihat Detail</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <a href="/index.php?page=home.php" class="btn btn-secondary btn-kembali">Kembali</a>
        <?php else: ?>
            <div class="text-center mt-4">
                <p class="no-results">
                    Tidak ada hasil ditemukan untuk "<strong><?= htmlspecialchars($query) ?></strong>".
                </p>
                <a href="/index.php?page=home.php" class="btn btn-secondary btn-kembali">Kembali</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
