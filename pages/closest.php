<?php
require __DIR__ . '/../vendor/autoload.php';
$config = require __DIR__ . '/../config/settings.php';

$fusekiEndpoint = $config['fusekiEndpoint'];

function haversine($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371; 
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat / 2) * sin($dLat / 2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon / 2) * sin($dLon / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    return $earthRadius * $c;
}

$showTable = false;
$fasilitasKesehatan = [];

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['latitude'], $_GET['longitude'])) {
    $userLatitude = floatval($_GET['latitude']);
    $userLongitude = floatval($_GET['longitude']);
    $maxDistance = isset($_GET['maxDistance']) ? floatval($_GET['maxDistance']) : 1; // Default radius: 1 km

    $sparql = "
        PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
        PREFIX hospital: <http://example.org/hospital#>
        PREFIX clinic: <http://example.org/clinic#>
        PREFIX privatePractice: <http://example.org/privatePractice#>

        SELECT ?facility ?name ?type ?city ?lat ?lon WHERE {
            {
                ?facility a hospital:Hospital ;
                          rdfs:label ?name ;
                          hospital:kab_kota ?city ;
                          hospital:latitude ?lat ;
                          hospital:longitude ?lon . 
                BIND('Rumah Sakit' AS ?type)
            }
            UNION
            {
                ?facility a clinic:Clinic ;
                          rdfs:label ?name ;
                          clinic:kab_kota ?city ;
                          clinic:latitude ?lat ;
                          clinic:longitude ?lon . 
                BIND('Klinik' AS ?type)
            }
            UNION
            {
                ?facility a privatePractice:Privatepractice ;
                          rdfs:label ?name ;
                          privatePractice:kab_kota ?city ;
                          privatePractice:latitude ?lat ;
                          privatePractice:longitude ?lon . 
                BIND('Praktik Mandiri' AS ?type)
            }
        }
    ";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $fusekiEndpoint . '?query=' . urlencode($sparql));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/sparql-results+json']);

    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
    $results = $data['results']['bindings'] ?? [];

    foreach ($results as $result) {
        $lat = floatval($result['lat']['value']);
        $lon = floatval($result['lon']['value']);
        $jarak = haversine($userLatitude, $userLongitude, $lat, $lon);

        // Tambahkan ke array hanya jika jarak <= maxDistance
        if ($jarak <= $maxDistance) {
            $fasilitasKesehatan[] = [
                'nama' => $result['name']['value'],
                'type' => $result['type']['value'],
                'fasilitas' => $result['facility']['value'],
                'latitude' => $lat,
                'longitude' => $lon,
                'jarak' => round($jarak, 2),
            ];
        }
    }

    usort($fasilitasKesehatan, fn($a, $b) => $a['jarak'] <=> $b['jarak']);
    $showTable = true;
} else {
    $userLatitude = null;
    $userLongitude = null;
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SumutCare - Cari Fasilitas Kesehatan</title>
    <link rel="stylesheet" href="https://unpkg.com/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="css/style.css">
    <style>#map { height: 400px; }</style>
</head>
<body>
    <div class="container mt-4">
        <h4 class="text-center">Pilih lokasi Anda di peta untuk mencari fasilitas kesehatan terdekat</h4>
        <form id="locationForm" method="GET" action="index.php">
            <input type="hidden" name="page" value="closest.php">
            <div id="map" class="my-3"></div>
            <input type="hidden" id="latitude" name="latitude" value="<?= htmlspecialchars($userLatitude ?? '') ?>">
            <input type="hidden" id="longitude" name="longitude" value="<?= htmlspecialchars($userLongitude ?? '') ?>">
            <label for="maxDistance">Radius (km):</label>
            <input type="number" id="maxDistance" name="maxDistance" value="<?= htmlspecialchars($_GET['maxDistance'] ?? '1') ?>" step="0.1" required>
            <button type="submit" class="btn btn-primary w-100 mt-2">Cari Fasilitas Terdekat</button>
        </form>

        <?php if ($showTable): ?>
            <h4 class="mt-4">Hasil Pencarian Fasilitas Kesehatan Terdekat</h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Tipe</th>
                        <th>Jarak (km)</th>
                        <th>Detail</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($fasilitasKesehatan as $fasilitas): ?>
                        <tr>
                            <td><?= htmlspecialchars($fasilitas['nama']) ?></td>
                            <td><?= htmlspecialchars($fasilitas['type']) ?></td>
                            <td><?= htmlspecialchars($fasilitas['jarak']) ?> km</td>
                            <td>
                                <a href="index.php?page=single.php&id=<?= urlencode($fasilitas['fasilitas']) ?>&query=<?= urlencode($query) ?>" class="btn btn-sm btn-primary">Lihat Detail</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const map = L.map('map').setView([3.5952, 98.6722], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        let marker = L.marker([3.5952, 98.6722], { draggable: true }).addTo(map);

        marker.on('dragend', function () {
            const position = marker.getLatLng();
            document.getElementById('latitude').value = position.lat.toFixed(6);
            document.getElementById('longitude').value = position.lng.toFixed(6);
        });

        map.on('click', function (e) {
            const { lat, lng } = e.latlng;
            marker.setLatLng([lat, lng]);
            document.getElementById('latitude').value = lat.toFixed(6);
            document.getElementById('longitude').value = lng.toFixed(6);
        });
    </script>
</body>
</html>