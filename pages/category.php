<?php
require __DIR__ . '/../vendor/autoload.php';
$config = require __DIR__ . '/../config/settings.php';

$fusekiEndpoint = $config['fusekiEndpoint'];
$query = isset($_GET['query']) ? htmlspecialchars($_GET['query']) : '';

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
        FILTER (REGEX(?name, '$query', 'i') ||
                REGEX(?city, '$query', 'i') ||
                REGEX(?type, '$query', 'i'))
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
    <title>Fasilitas Kesehatan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Pencarian Berdasarkan Kategori</h2>
        
        <div class="text-center mb-4">
            <select id="categorySelect" class="form-select w-50 mx-auto" aria-label="Pilih Kategori">
                <option value="all">Semua</option>
                <option value="Rumah Sakit">Rumah Sakit</option>
                <option value="Klinik">Klinik</option>
                <option value="Praktik Mandiri">Praktik Mandiri</option>
            </select>
        </div>

        <?php if ($results): ?>
            <table id="healthFacilitiesTable" class="table table-striped table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>Nama Fasilitas</th>
                        <th>Kategori</th>
                        <th>Kota/Kabupaten</th>
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
                            <td>
                                <a href="index.php?page=single.php&id=<?= urlencode($item['facility']['value']) ?>&query=<?= urlencode($query) ?>" class="btn btn-sm btn-primary">Lihat Detail</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <a href="/index.php?page=home.php" class="btn btn-secondary btn-kembali">Kembali</a>
        <?php else: ?>
            <p class="text-center">Tidak ada hasil ditemukan untuk "<strong><?= $query ?></strong>".</p>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function () {
            const table = $('#healthFacilitiesTable').DataTable({
            columnDefs: [
            {
                targets: 0, // Kolom nomor
                orderable: false, // Kolom nomor tidak bisa diurutkan
                searchable: false, // Kolom nomor tidak bisa dicari
            },
            ],
            drawCallback: function () {
            // Iterasi ulang nomor baris saat tabel di-*render*
            const api = this.api();
            api.column(0, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
                cell.innerHTML = i + 1; // Isi kolom nomor berdasarkan urutan yang terlihat
            });
            },
            });

            // Filter dropdown kategori
            $('#categorySelect').on('change', function () {
            const selectedCategory = this.value === 'all' ? '' : this.value; // Jika "Semua", kosongkan filter
            table.column(2).search(selectedCategory).draw(); // Filter data berdasarkan kategori
            });
        });
        
    </script>
</body>
</html>
