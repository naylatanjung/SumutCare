<?php
require __DIR__ . '/../vendor/autoload.php';
$config = require __DIR__ . '/../config/settings.php';

$fusekiEndpoint = $config['fusekiEndpoint'];
$query = isset($_GET['query']) ? htmlspecialchars($_GET['query']) : '';
$selectedCity = isset($_GET['city']) ? htmlspecialchars($_GET['city']) : '';

// Data distinct kabupaten/kota
$cities = [
    'Asahan', 'Batu Bara', 'Dairi', 'Deli Serdang', 'Humbang Hasundutan',
    'KAB. ASAHAN', 'KAB. BATU BARA', 'KAB. DAIRI', 'KAB. DELI SERDANG', 
    'KAB. HUMBANG HASUNDUTAN', 'KAB. KARO', 'KAB. LABUHANBATU', 
    'KAB. LABUHANBATU SELATAN', 'KAB. LABUHANBATU UTARA', 'KAB. LANGKAT',
    'KAB. MANDAILING NATAL', 'KAB. NIAS', 'KAB. NIAS BARAT', 'KAB. NIAS SELATAN',
    'KAB. NIAS UTARA', 'KAB. PADANG LAWAS', 'KAB. PADANG LAWAS UTARA',
    'KAB. PAKPAK BHARAT', 'KAB. SAMOSIR', 'KAB. SERDANG BEDAGAI', 
    'KAB. SIMALUNGUN', 'KAB. TAPANULI SELATAN', 'KAB. TAPANULI TENGAH', 
    'KAB. TAPANULI UTARA', 'KAB. TOBA SAMOSIR', 'KOTA BINJAI', 'KOTA GUNUNGSITOLI', 
    'KOTA MEDAN', 'KOTA PADANG SIDEMPUAN', 'KOTA PEMATANGSIANTAR', 
    'KOTA SIBOLGA', 'KOTA TANJUNG BALAI', 'KOTA TEBING TINGGI', 'Karo', 
    'Kota Binjai', 'Kota Gunungsitoli', 'Kota Medan', 'Kota Padang Sidempuan', 
    'Kota Pematangsiantar', 'Kota Sibolga', 'Kota Tanjung Balai', 
    'Kota Tebing Tinggi', 'Labuhanbatu', 'Labuhanbatu Selatan', 
    'Labuhanbatu Utara', 'Langkat', 'Mandailing Natal', 'Nias', 
    'Nias Barat', 'Nias Selatan', 'Padang Lawas', 'Padang Lawas Utara', 
    'Pakpak Bharat', 'Samosir', 'Serdang Bedagai', 'Simalungun', 
    'Tapanuli Selatan', 'Tapanuli Tengah', 'Tapanuli Utara', 'Toba Samosir'
];

// SPARQL query
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
        " . ($selectedCity ? "FILTER (STR(?city) = '$selectedCity')" : "") . "
        FILTER (REGEX(?name, '$query', 'i') || REGEX(?city, '$query', 'i') || REGEX(?type, '$query', 'i'))
    }
    ORDER BY ?name
";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $fusekiEndpoint . '?query=' . urlencode($sparql));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/sparql-results+json']);

$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
$results = $data['results']['bindings'] ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fasilitas Kesehatan - Berdasarkan Kota</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Pencarian Berdasarkan Kabupaten/Kota</h2>

        <form method="GET" class="mb-4 text-center">
            <input type="text" name="query" class="form-control mb-3 w-50 mx-auto" placeholder="Cari berdasarkan nama atau kategori..." value="<?= $query ?>">
            <select name="city" class="form-select mb-3 w-50 mx-auto">
                <option value="">Semua Kabupaten/Kota</option>
                <?php foreach ($cities as $city): ?>
                    <option value="<?= htmlspecialchars($city) ?>" <?= $selectedCity === $city ? 'selected' : '' ?>>
                        <?= htmlspecialchars($city) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary">Cari</button>
        </form>

        <?php if ($results): ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Fasilitas</th>
                        <th>Kategori</th>
                        <th>Kabupaten/Kota</th>
                        <th>Detail</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $index => $item): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($item['name']['value']) ?></td>
                            <td><?= htmlspecialchars($item['type']['value']) ?></td>
                            <td><?= htmlspecialchars($item['city']['value']) ?></td>
                            <td><a href="index.php?page=single.php&id=<?= urlencode($item['facility']['value']) ?>&query=<?= urlencode($query) ?>" class="btn btn-sm btn-primary">Lihat Detail</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-center">Tidak ada hasil ditemukan untuk pencarian ini.</p>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function () {
            // Inisialisasi DataTable
            const table = $('.table').DataTable({
                columnDefs: [
                    {
                        targets: 0, // Kolom nomor
                        orderable: false, // Kolom nomor tidak bisa diurutkan
                        searchable: false, // Kolom nomor tidak bisa dicari
                    },
                ],
                drawCallback: function () {
                    // Update nomor baris berdasarkan urutan saat ini
                    const api = this.api();
                    api.column(0, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
                        cell.innerHTML = i + 1; // Isi ulang nomor sesuai urutan
                    });
                },
            });

            // Filter kategori dan kota
            $('form').on('submit', function (e) {
                e.preventDefault(); // Hentikan reload halaman
                const query = $('input[name="query"]').val().toLowerCase();
                const city = $('select[name="city"]').val().toLowerCase();

                // Filter berdasarkan input query dan kota
                table.search(query).draw();
                table.column(3).search(city).draw();
            });
        });
    </script>
</body>
</html>
