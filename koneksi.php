<?php
$dbFile = 'toko.db';
$buatTabel = !file_exists($dbFile);

try {
    $db = new PDO("sqlite:$dbFile");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Alias: banyak file (admin.php, api.php, dll) memanggil koneksi ini
    // lewat nama variabel $pdo, jadi kita samakan supaya keduanya menunjuk
    // ke objek koneksi yang sama.
    $pdo = $db;

    if ($buatTabel) {
        $db->exec("CREATE TABLE produk (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nama TEXT,
            kategori TEXT,
            harga INTEGER,
            ikon TEXT,
            gambar TEXT
        )");

        $db->exec("CREATE TABLE pesanan (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nama_pembeli TEXT,
            no_rumah TEXT,
            no_hp TEXT,
            total INTEGER,
            status TEXT DEFAULT 'Pending',
            waktu DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        $db->exec("CREATE TABLE detail_pesanan (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            pesanan_id INTEGER,
            nama_menu TEXT,
            jumlah INTEGER,
            subtotal INTEGER
        )");

        // Data Dummy Produk Awal
        $db->exec("INSERT INTO produk (nama, kategori, harga, ikon) VALUES 
            ('Es Teh Kemuning Original', 'Minuman', 3000, '🍵'),
            ('Es Teh Lemon Madu', 'Minuman', 5000, '🍋'),
            ('Es Teh Susu Creamy', 'Minuman', 6000, '🥛'),
            ('Tahu Crispy Renyah', 'Cemilan', 5000, '🧆'),
            ('Kentang Goreng Spesial', 'Cemilan', 8000, '🍟'),
            ('Pisang Goreng Keju', 'Cemilan', 7000, '🍌')
        ");
    } else {
        // Migrasi ringan: kalau database lama belum punya kolom 'gambar', tambahkan sekarang.
        // Ini supaya fitur upload foto produk tetap jalan tanpa perlu hapus database lama.
        $kolomProduk = $db->query("PRAGMA table_info(produk)")->fetchAll(PDO::FETCH_ASSOC);
        $adaKolomGambar = false;
        foreach ($kolomProduk as $kolom) {
            if ($kolom['name'] === 'gambar') {
                $adaKolomGambar = true;
                break;
            }
        }
        if (!$adaKolomGambar) {
            $db->exec("ALTER TABLE produk ADD COLUMN gambar TEXT");
        }
    }
} catch (Exception $e) {
    die("Koneksi gagal: " . $e->getMessage());
}
?>