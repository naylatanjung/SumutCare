<section class="hero">
    <div class="hero-content text-center">
        <h1 class="hero-title">SumutCare</h1>
        <p class="hero-subtitle">Temukan Fasilitas Kesehatan Terbaik di Sumatera Utara</p>
        <form action="index.php?page=search.php" method="get" class="search-form">
            <input type="hidden" name="page" value="search.php">
            <input type="text" name="query" class="search-input" placeholder="Masukkan kata kunci rumah sakit, klinik, atau spesialis...">
            <button type="submit" class="search-btn">Cari Sekarang</button>
        </form>
    </div>
</section>

<section class="product py-5" id="product">
      <div class="container">
        <h2 class="section-title text-center h3">Fitur</h2>
        <p class="section-paragraf text-center">Pilih Fitur Pencarian yang Anda Inginkan</p>
      
        <div class="row justify-content-center gy-3">
          <div class="col-md-4 col-lg-3">
            <div class="card border-0 rounded">
              <div class="card-body">
                <img src="/assets/img/rumahsakit.jpg" class="w-100 rounded" alt="Rumah Sakit">
                <h5 class="card-title text-center">Kategori</h5>
                <p class="card-text text-center">Lakukan pencarian berdasarkan kategori fasilitas kesehatan</p>
                <a href="/index.php?page=category.php" class="btn btn-sm container">Mulai</a>
              </div>
            </div>
          </div>
        
          <div class="col-md-4 col-lg-3">
            <div class="card border-0 rounded">
              <div class="card-body">
                <img src="/assets/img/klinik.jpg" class="w-100 rounded" alt="Klinik">
                <h5 class="card-title text-center">Kabupaten/Kota</h5>
                <p class="card-text text-center">Lakukan pencarian berdasarkan lokasi kabupaten/kota fasilitas kesehatan</p>
                <a href="/index.php?page=city.php" class="btn btn-sm container">Mulai</a>
              </div>
            </div>
          </div>
        
          <div class="col-md-4 col-lg-3">
            <div class="card border-0 rounded">
              <div class="card-body">
                <img src="/assets/img/praktikmandiri.png" class="w-100 rounded" alt="Praktik Mandiri">
                <h5 class="card-title text-center">Terdekat</h5>
                <p class="card-text text-center">Lakukan pencarian berdasarkan lokasi fasilitas kesehatan terdekat Anda</p>
                <a href="/index.php?page=closest.php" class="btn btn-sm container">Mulai</a>
              </div>
            </div>
          </div>
        
        </div>
      </div>
    </section>