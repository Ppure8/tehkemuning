<?php
include 'koneksi.php';
$aksi = $_GET['aksi'] ?? '';

if ($aksi == 'simpan') {
    $data = json_decode(file_get_contents('php://input'), true);
    $nama = $data['nama'] ?? '';
    $no_rumah = $data['no_rumah'] ?? '';
    $no_hp = $data['no_hp'] ?? '';
    $catatan = $data['catatan'] ?? ''; // <-- Menangkap data catatan dari pembeli
    $items = $data['items'] ?? [];

    $total = 0;
    foreach ($items as $item) {
        $total += $item['harga'] * $item['jumlah'];
    }

    // Simpan ke tabel pesanan (termasuk kolom catatan)
    $stmt = $db->prepare("INSERT INTO pesanan (nama_pembeli, no_rumah, no_hp, catatan, total, status) VALUES (?, ?, ?, ?, ?, 'Pending')");
    $stmt->execute([$nama, $no_rumah, $no_hp, $catatan, $total]);
    $pesanan_id = $db->lastInsertId();

    // Simpan detail item
    $stmtDetail = $db->prepare("INSERT INTO detail_pesanan (pesanan_id, nama_menu, jumlah, subtotal) VALUES (?, ?, ?, ?)");
    foreach ($items as $nama_menu => $val) {
        $subtotal = $val['harga'] * $val['jumlah'];
        $stmtDetail->execute([$pesanan_id, $nama_menu, $val['jumlah'], $subtotal]);
    }

    echo json_encode(['status' => 'sukses']);
} 
elseif ($aksi == 'ambil_pesanan') {
    $stmt = $db->query("SELECT * FROM pesanan ORDER BY id DESC LIMIT 20");
    $pesanan = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($pesanan as &$p) {
        $stmtDetail = $db->prepare("SELECT * FROM detail_pesanan WHERE pesanan_id = ?");
        $stmtDetail->execute([$p['id']]);
        $p['detail'] = $stmtDetail->fetchAll(PDO::FETCH_ASSOC);
        
        // Memastikan kolom catatan disertakan dalam respons JSON ke admin.php
        $p['catatan'] = $p['catatan'] ?? '';
    }

    header('Content-Type: application/json');
    echo json_encode($pesanan);
} 
elseif ($aksi == 'update_status') {
    $id = $_GET['id'] ?? 0;
    $status = $_GET['status'] ?? '';
    $stmt = $db->prepare("UPDATE pesanan SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);
    echo json_encode(['status' => 'sukses']);
}
?>