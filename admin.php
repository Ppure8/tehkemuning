<?php
session_start();
if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    header('Location: login.php');
    exit;
}
include 'koneksi.php';

// Ambil tab aktif (pesanan, history, laporan, produk)
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'pesanan';

// ================== PROSES MASTER BARANG (TAMBAH / EDIT / HAPUS) ==================
$pesanProduk = '';

if ($tab == 'produk' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi_produk'])) {
    $namaProduk = trim($_POST['nama'] ?? '');
    $kategoriProduk = trim($_POST['kategori'] ?? '');
    $hargaProduk = (int) ($_POST['harga'] ?? 0);
    $ikonProduk = trim($_POST['ikon'] ?? '');
    $idProduk = (int) ($_POST['id'] ?? 0);
    $namaFileBaru = null;

    // Upload foto produk (opsional)
    if (!empty($_FILES['gambar']['name']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $folderUpload = __DIR__ . '/uploads_produk/';
        if (!is_dir($folderUpload)) {
            mkdir($folderUpload, 0755, true);
        }
        $ekstensiDiizinkan = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ekstensi = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));

        if (in_array($ekstensi, $ekstensiDiizinkan) && $_FILES['gambar']['size'] <= 2 * 1024 * 1024) {
            $namaFileBaru = 'produk_' . uniqid() . '.' . $ekstensi;
            move_uploaded_file($_FILES['gambar']['tmp_name'], $folderUpload . $namaFileBaru);
        } else {
            $pesanProduk = 'Foto gagal diupload: pastikan format jpg/jpeg/png/gif/webp dan ukuran maksimal 2MB. Produk tetap disimpan tanpa foto baru.';
        }
    }

    if ($namaProduk === '') {
        $pesanProduk = 'Nama produk wajib diisi.';
    } else {
        try {
            if ($_POST['aksi_produk'] === 'tambah') {
                $stmtP = $pdo->prepare("INSERT INTO produk (nama, kategori, harga, ikon, gambar) VALUES (?, ?, ?, ?, ?)");
                $stmtP->execute([$namaProduk, $kategoriProduk, $hargaProduk, $ikonProduk, $namaFileBaru]);
                header('Location: admin.php?tab=produk&sukses=tambah');
                exit;

            } elseif ($_POST['aksi_produk'] === 'edit' && $idProduk > 0) {
                $qLama = $pdo->prepare("SELECT gambar FROM produk WHERE id = ?");
                $qLama->execute([$idProduk]);
                $fileLama = $qLama->fetchColumn();

                if (!empty($_POST['hapus_gambar'])) {
                    if ($fileLama && file_exists(__DIR__ . '/uploads_produk/' . $fileLama)) {
                        unlink(__DIR__ . '/uploads_produk/' . $fileLama);
                    }
                    $gambarAkhir = null;
                } elseif ($namaFileBaru) {
                    if ($fileLama && file_exists(__DIR__ . '/uploads_produk/' . $fileLama)) {
                        unlink(__DIR__ . '/uploads_produk/' . $fileLama);
                    }
                    $gambarAkhir = $namaFileBaru;
                } else {
                    $gambarAkhir = $fileLama;
                }

                $stmtP = $pdo->prepare("UPDATE produk SET nama=?, kategori=?, harga=?, ikon=?, gambar=? WHERE id=?");
                $stmtP->execute([$namaProduk, $kategoriProduk, $hargaProduk, $ikonProduk, $gambarAkhir, $idProduk]);
                header('Location: admin.php?tab=produk&sukses=edit');
                exit;
            }
        } catch (\Throwable $e) {
            $pesanProduk = 'Gagal menyimpan produk: ' . $e->getMessage();
        }
    }
}

if ($tab == 'produk' && isset($_GET['hapus'])) {
    $idHapus = (int) $_GET['hapus'];
    try {
        $qLama = $pdo->prepare("SELECT gambar FROM produk WHERE id = ?");
        $qLama->execute([$idHapus]);
        $fileLama = $qLama->fetchColumn();
        if ($fileLama && file_exists(__DIR__ . '/uploads_produk/' . $fileLama)) {
            unlink(__DIR__ . '/uploads_produk/' . $fileLama);
        }
        $pdo->prepare("DELETE FROM produk WHERE id = ?")->execute([$idHapus]);
        header('Location: admin.php?tab=produk&sukses=hapus');
        exit;
    } catch (\Throwable $e) {
        $pesanProduk = 'Gagal menghapus produk: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Es Teh Kemuning</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-900 text-slate-100 pb-24">

    <!-- Header Admin -->
    <header class="bg-slate-800 p-4 border-b border-slate-700 sticky top-0 z-20 flex justify-between items-center shadow-md">
        <div>
            <span class="text-xs bg-amber-500/20 text-amber-400 px-2.5 py-1 rounded-full font-semibold">Admin Es Teh Kemuning 👨‍🍳</span>
            <h1 class="text-base font-bold mt-1">Panel Kelola Toko</h1>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="aktifkanAudio()" id="btn-audio" class="bg-amber-500 hover:bg-amber-600 text-slate-950 text-xs font-bold px-3 py-2 rounded-xl shadow transition animate-bounce cursor-pointer">
                🔊 Aktifkan Alarm
            </button>
            <a href="logout.php" onclick="sessionStorage.clear()" class="bg-red-500/20 text-red-400 border border-red-500/30 text-xs font-bold px-3 py-2 rounded-xl">Keluar</a>
        </div>
    </header>

    <!-- Navigasi Tab Menu Admin -->
    <nav class="bg-slate-800/80 backdrop-blur px-4 py-2 border-b border-slate-700 flex gap-2 overflow-x-auto text-xs font-semibold">
        <a href="admin.php?tab=pesanan" class="px-4 py-2 rounded-xl whitespace-nowrap transition <?= $tab=='pesanan' ? 'bg-amber-500 text-slate-950 font-bold shadow' : 'text-slate-300 hover:bg-slate-700' ?>">🍳 Pesanan Masuk</a>
        <a href="admin.php?tab=history" class="px-4 py-2 rounded-xl whitespace-nowrap transition <?= $tab=='history' ? 'bg-amber-500 text-slate-950 font-bold shadow' : 'text-slate-300 hover:bg-slate-700' ?>">📦 Riwayat / History</a>
        <a href="admin.php?tab=produk" class="px-4 py-2 rounded-xl whitespace-nowrap transition <?= $tab=='produk' ? 'bg-amber-500 text-slate-950 font-bold shadow' : 'text-slate-300 hover:bg-slate-700' ?>">🧃 Master Barang</a>
        <a href="admin.php?tab=laporan" class="px-4 py-2 rounded-xl whitespace-nowrap transition <?= $tab=='laporan' ? 'bg-amber-500 text-slate-950 font-bold shadow' : 'text-slate-300 hover:bg-slate-700' ?>">📊 Laporan & Statistik</a>
    </nav>

    <main class="p-4 max-w-md mx-auto">
        <?php if ($tab == 'pesanan'): ?>
            <!-- TAB 1: PESANAN MASUK (Pending & Diproses) -->
            <div class="space-y-4" id="list-pesanan">
                <!-- Dimuat otomatis via AJAX -->
            </div>

        <?php elseif ($tab == 'history'): ?>
            <!-- TAB 2: RIWAYAT / HISTORY PESANAN SELESAI -->
            <div class="space-y-4">
                <div class="flex items-center justify-between mb-1">
                    <h2 class="text-sm font-bold text-white">Riwayat Pesanan Selesai</h2>
                    <span class="text-[10px] text-slate-500">20 terakhir</span>
                </div>
                <?php
                $selesaiList = [];

                if (!isset($pdo) || !($pdo instanceof PDO)) {
                    echo '<div class="bg-red-500/10 border border-red-500/30 text-red-300 text-xs p-3 rounded-xl mb-3">Koneksi database ($pdo) tidak tersedia.</div>';
                } else {
                    try {
                        $stmt = $pdo->query("SELECT * FROM pesanan WHERE status = 'Selesai' ORDER BY id DESC LIMIT 20");
                        $selesaiList = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    } catch (\Throwable $e) {
                        echo '<div class="bg-red-500/10 border border-red-500/30 text-red-300 text-xs p-3 rounded-xl mb-3">Gagal mengambil data riwayat: ' . htmlspecialchars($e->getMessage()) . '</div>';
                    }
                }

                function labelTanggalHistory($waktu) {
                    if (empty($waktu)) return 'Tanggal Tidak Diketahui';
                    $ts = strtotime($waktu);
                    if ($ts === false) return 'Tanggal Tidak Diketahui';
                    $tglData = date('Y-m-d', $ts);
                    if ($tglData === date('Y-m-d')) return 'Hari Ini';
                    if ($tglData === date('Y-m-d', strtotime('-1 day'))) return 'Kemarin';
                    $hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                    $bulan = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                    return $hari[date('w', $ts)] . ', ' . date('j', $ts) . ' ' . $bulan[(int) date('n', $ts)] . ' ' . date('Y', $ts);
                }

                $warnaAvatar = ['bg-amber-500', 'bg-emerald-500', 'bg-sky-500', 'bg-rose-500', 'bg-violet-500', 'bg-teal-500'];

                if (empty($selesaiList)) {
                    echo '<div class="text-center py-20 text-slate-500"><p class="text-4xl mb-3">📦</p><p class="font-semibold text-slate-400">Belum ada riwayat pesanan selesai</p><p class="text-xs mt-1">Pesanan yang sudah diselesaikan akan muncul di sini.</p></div>';
                } else {
                    $grouped = [];
                    foreach ($selesaiList as $p) {
                        $grouped[labelTanggalHistory($p['waktu'] ?? null)][] = $p;
                    }
                    ?>
                    <!-- Ringkasan -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-gradient-to-br from-emerald-500/15 to-emerald-500/5 border border-emerald-500/20 p-3.5 rounded-2xl">
                            <p class="text-[10px] text-emerald-300/80 font-semibold">📦 Ditampilkan</p>
                            <p class="text-xl font-extrabold text-white mt-1"><?= count($selesaiList) ?> <span class="text-xs font-semibold text-slate-400">pesanan</span></p>
                        </div>
                        <div class="bg-gradient-to-br from-amber-500/15 to-amber-500/5 border border-amber-500/20 p-3.5 rounded-2xl">
                            <p class="text-[10px] text-amber-300/80 font-semibold">💰 Total Nilai</p>
                            <p class="text-xl font-extrabold text-white mt-1">Rp <?= number_format(array_sum(array_column($selesaiList, 'total')), 0, ',', '.') ?></p>
                        </div>
                    </div>

                    <!-- Pencarian -->
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-sm pointer-events-none">🔍</span>
                        <input type="text" id="cariHistory" oninput="filterHistory()" placeholder="Cari nama pelanggan / no. rumah..." class="w-full bg-slate-800 border border-slate-700 rounded-xl pl-9 pr-3 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-amber-500 transition">
                    </div>

                    <div id="wadah-history" class="space-y-5">
                        <?php foreach ($grouped as $labelGrup => $itemGrup): ?>
                            <div data-grup>
                                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2 px-1"><?= htmlspecialchars($labelGrup) ?></p>
                                <div class="space-y-3">
                                    <?php foreach ($itemGrup as $p):
                                        $details = [];
                                        try {
                                            $detailStmt = $pdo->prepare("SELECT * FROM detail_pesanan WHERE pesanan_id = ?");
                                            $detailStmt->execute([$p['id']]);
                                            $details = $detailStmt->fetchAll(PDO::FETCH_ASSOC);
                                        } catch (\Throwable $e) {}
                                        $inisial = strtoupper(substr(trim($p['nama_pembeli'] ?? '?'), 0, 1) ?: '?');
                                        $warna = $warnaAvatar[($p['id'] ?? 0) % count($warnaAvatar)];
                                        $namaCari = strtolower(($p['nama_pembeli'] ?? '') . ' ' . ($p['no_rumah'] ?? ''));
                                    ?>
                                    <div class="kartu-history border border-slate-700 bg-slate-800/40 hover:bg-slate-800/70 hover:border-slate-600 p-4 rounded-2xl transition-all" data-nama="<?= htmlspecialchars($namaCari) ?>">
                                        <div class="flex items-start gap-3">
                                            <div class="w-10 h-10 rounded-full <?= $warna ?> flex items-center justify-center font-extrabold text-slate-950 text-sm shrink-0">
                                                <?= htmlspecialchars($inisial) ?>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex justify-between items-start gap-2">
                                                    <div class="min-w-0">
                                                        <h3 class="font-bold text-sm text-white truncate"><?= htmlspecialchars($p['nama_pembeli'] ?? '-') ?></h3>
                                                        <p class="text-[11px] text-emerald-400 font-semibold mt-0.5 truncate">
                                                            🏡 <?= htmlspecialchars($p['no_rumah'] ?? '-') ?>
                                                            <?php if (!empty($p['no_hp'])): ?>
                                                                · <a href="tel:<?= htmlspecialchars($p['no_hp']) ?>" class="text-sky-400">📞 <?= htmlspecialchars($p['no_hp']) ?></a>
                                                            <?php endif; ?>
                                                        </p>
                                                    </div>
                                                    <span class="text-[10px] font-bold text-slate-500 shrink-0"><?= htmlspecialchars(date('H:i', strtotime($p['waktu'] ?? 'now'))) ?></span>
                                                </div>

                                                <div class="flex flex-wrap gap-1.5 mt-2.5">
                                                    <?php foreach ($details as $d): ?>
                                                        <span class="bg-slate-900/60 border border-slate-700/60 text-[10px] text-slate-300 px-2 py-1 rounded-lg">
                                                            <span class="text-amber-400 font-bold"><?= $d['jumlah'] ?? 0 ?>×</span> <?= htmlspecialchars($d['nama_menu'] ?? '-') ?>
                                                        </span>
                                                    <?php endforeach; ?>
                                                </div>

                                                <?php if (!empty($p['catatan'])): ?>
                                                    <div class="bg-amber-500/10 border border-amber-500/20 text-amber-300 p-2.5 rounded-xl text-xs mt-2.5">
                                                        <span class="font-bold">📝 Catatan:</span> <?= htmlspecialchars($p['catatan']) ?>
                                                    </div>
                                                <?php endif; ?>

                                                <div class="flex justify-between items-center mt-3 pt-2.5 border-t border-slate-700/50">
                                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300">Selesai ✓</span>
                                                    <span class="text-sm font-extrabold text-amber-400">Rp <?= number_format($p['total'] ?? 0, 0, ',', '.') ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <p id="pesan-kosong-cari" class="hidden text-center py-10 text-slate-500 text-xs">🔍 Tidak ada riwayat yang cocok dengan pencarian.</p>
                <?php } ?>
            </div>

        <?php elseif ($tab == 'laporan'): ?>
            <!-- TAB 3: LAPORAN PENJUALAN & MENU TERLARIS -->
            <div class="space-y-6">
                <div class="bg-slate-800 p-4 rounded-2xl border border-slate-700">
                    <h2 class="text-xs font-bold text-amber-400 mb-3">📅 Laporan Penjualan Per Tanggal</h2>
                    <form method="GET" class="flex gap-2">
                        <input type="hidden" name="tab" value="laporan">
                        <input type="date" name="tanggal" value="<?= isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d') ?>" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white">
                        <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-slate-950 px-4 py-2 rounded-xl text-xs font-bold">Cek</button>
                    </form>

                    <?php
                    $pilihTanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');
                    
                    // Query Omzet & Total Pesanan Selesai berdasarkan Tanggal
                    $qOmzet = $pdo->prepare("SELECT SUM(total) as omzet, COUNT(*) as jml FROM pesanan WHERE status = 'Selesai' AND DATE(waktu) = ?");
                    $qOmzet->execute([$pilihTanggal]);
                    $resOmzet = $qOmzet->fetch(PDO::FETCH_ASSOC);
                    ?>
                    <div class="mt-4 pt-3 border-t border-slate-700 grid grid-cols-2 gap-3 text-center">
                        <div class="bg-slate-900/60 p-3 rounded-xl">
                            <p class="text-[10px] text-slate-400">Total Transaksi Selesai</p>
                            <p class="text-base font-bold text-emerald-400 mt-1"><?= $resOmzet['jml'] ?? 0 ?> Pesanan</p>
                        </div>
                        <div class="bg-slate-900/60 p-3 rounded-xl">
                            <p class="text-[10px] text-slate-400">Total Omzet</p>
                            <p class="text-base font-bold text-amber-400 mt-1">Rp <?= number_format($resOmzet['omzet'] ?? 0, 0, ',', '.') ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-slate-800 p-4 rounded-2xl border border-slate-700">
                    <h2 class="text-xs font-bold text-amber-400 mb-3">🏆 Menu Paling Laris (Tanggal <?= date('d/m/Y', strtotime($pilihTanggal)) ?>)</h2>
                    <div class="space-y-2 text-xs">
                        <?php
                        // Query Menu Terlaris berdasarkan Tanggal via Relasi Tabel Pesanan yang Selesai
                        $qLaris = $pdo->prepare("SELECT d.nama_menu, SUM(d.jumlah) as total_terjual FROM detail_pesanan d JOIN pesanan p ON d.pesanan_id = p.id WHERE p.status = 'Selesai' AND DATE(p.waktu) = ? GROUP BY d.nama_menu ORDER BY total_terjual DESC LIMIT 5");
                        $qLaris->execute([$pilihTanggal]);
                        $menuLaris = $qLaris->fetchAll(PDO::FETCH_ASSOC);
                        
                        if (empty($menuLaris)) {
                            echo '<p class="text-slate-500 text-center py-3">Belum ada data penjualan menu pada tanggal ini.</p>';
                        } else {
                            foreach ($menuLaris as $ml):
                        ?>
                        <div class="flex justify-between items-center bg-slate-900/50 p-2.5 rounded-xl">
                            <span class="text-white font-medium">🥤 <?= htmlspecialchars($ml['nama_menu']) ?></span>
                            <span class="bg-amber-500/20 text-amber-400 px-2 py-1 rounded-lg font-bold"><?= $ml['total_terjual'] ?> Terjual</span>
                        </div>
                        <?php 
                            endforeach;
                        } 
                        ?>
                    </div>
                </div>

                <div class="bg-slate-800 p-4 rounded-2xl border border-slate-700">
                    <h2 class="text-xs font-bold text-amber-400 mb-3">👑 Pelanggan Sering Beli (Tanggal <?= date('d/m/Y', strtotime($pilihTanggal)) ?>)</h2>
                    <div class="space-y-2 text-xs">
                        <?php
                        // Query Pelanggan Loyal berdasarkan Tanggal
                        $qPelanggan = $pdo->prepare("SELECT nama_pembeli, no_rumah, COUNT(*) as total_pesanan FROM pesanan WHERE status = 'Selesai' AND DATE(waktu) = ? GROUP BY nama_pembeli, no_rumah ORDER BY total_pesanan DESC LIMIT 5");
                        $qPelanggan->execute([$pilihTanggal]);
                        $pelangganSetia = $qPelanggan->fetchAll(PDO::FETCH_ASSOC);

                        if (empty($pelangganSetia)) {
                            echo '<p class="text-slate-500 text-center py-3">Belum ada data pelanggan pada tanggal ini.</p>';
                        } else {
                            foreach ($pelangganSetia as $ps):
                        ?>
                        <div class="flex justify-between items-center bg-slate-900/50 p-2.5 rounded-xl">
                            <div>
                                <p class="text-white font-bold"><?= htmlspecialchars($ps['nama_pembeli']) ?></p>
                                <p class="text-[10px] text-emerald-400">🏡 <?= htmlspecialchars($ps['no_rumah']) ?></p>
                            </div>
                            <span class="bg-emerald-500/20 text-emerald-300 px-2 py-1 rounded-lg font-bold"><?= $ps['total_pesanan'] ?> Kali Pesan</span>
                        </div>
                        <?php 
                            endforeach;
                        } 
                        ?>
                    </div>
                </div>
            </div>
        <?php elseif ($tab == 'produk'): ?>
            <!-- TAB 4: MASTER BARANG (Kelola Menu Jualan) -->
            <div class="space-y-5">
                <?php
                $editProduk = null;
                if (isset($_GET['edit'])) {
                    try {
                        $qEdit = $pdo->prepare("SELECT * FROM produk WHERE id = ?");
                        $qEdit->execute([(int) $_GET['edit']]);
                        $editProduk = $qEdit->fetch(PDO::FETCH_ASSOC);
                    } catch (\Throwable $e) {}
                }
                ?>

                <?php if (!empty($pesanProduk)): ?>
                    <div class="bg-red-500/10 border border-red-500/30 text-red-300 text-xs p-3 rounded-xl"><?= htmlspecialchars($pesanProduk) ?></div>
                <?php endif; ?>

                <?php if (isset($_GET['sukses'])): ?>
                    <?php
                    $pesanSukses = [
                        'tambah' => '✅ Produk baru berhasil ditambahkan.',
                        'edit' => '✅ Produk berhasil diperbarui.',
                        'hapus' => '✅ Produk berhasil dihapus.',
                    ];
                    ?>
                    <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs p-3 rounded-xl"><?= htmlspecialchars($pesanSukses[$_GET['sukses']] ?? 'Berhasil.') ?></div>
                <?php endif; ?>

                <!-- Form Tambah / Edit Produk -->
                <div class="bg-slate-800 p-4 rounded-2xl border border-slate-700">
                    <h2 class="text-sm font-bold text-white mb-3">
                        <?= $editProduk ? '✏️ Edit Produk: ' . htmlspecialchars($editProduk['nama']) : '➕ Tambah Produk Baru' ?>
                    </h2>
                    <form method="POST" enctype="multipart/form-data" class="space-y-3">
                        <input type="hidden" name="aksi_produk" value="<?= $editProduk ? 'edit' : 'tambah' ?>">
                        <?php if ($editProduk): ?><input type="hidden" name="id" value="<?= (int) $editProduk['id'] ?>"><?php endif; ?>

                        <div>
                            <label class="text-[11px] text-slate-400 font-semibold">Nama Produk</label>
                            <input type="text" name="nama" required value="<?= htmlspecialchars($editProduk['nama'] ?? '') ?>" placeholder="mis. Es Teh Leci" class="w-full mt-1 bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-amber-500">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-[11px] text-slate-400 font-semibold">Kategori</label>
                                <input type="text" name="kategori" list="daftarKategori" required value="<?= htmlspecialchars($editProduk['kategori'] ?? '') ?>" placeholder="Minuman" class="w-full mt-1 bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-amber-500">
                                <datalist id="daftarKategori">
                                    <option value="Minuman">
                                    <option value="Cemilan">
                                </datalist>
                            </div>
                            <div>
                                <label class="text-[11px] text-slate-400 font-semibold">Harga (Rp)</label>
                                <input type="number" name="harga" min="0" required value="<?= (int) ($editProduk['harga'] ?? 0) ?>" placeholder="5000" class="w-full mt-1 bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-amber-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-[11px] text-slate-400 font-semibold">Ikon Emoji</label>
                                <input type="text" name="ikon" maxlength="4" value="<?= htmlspecialchars($editProduk['ikon'] ?? '') ?>" placeholder="🍵" class="w-full mt-1 bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-amber-500">
                            </div>
                            <div>
                                <label class="text-[11px] text-slate-400 font-semibold">Foto Produk</label>
                                <input type="file" name="gambar" accept="image/*" class="w-full mt-1 text-[10px] text-slate-400 file:bg-amber-500 file:text-slate-950 file:font-bold file:border-0 file:rounded-lg file:px-2.5 file:py-1.5 file:mr-2 file:text-[11px]">
                            </div>
                        </div>

                        <?php if ($editProduk && !empty($editProduk['gambar'])): ?>
                            <div class="flex items-center gap-2 bg-slate-900/60 p-2 rounded-xl">
                                <img src="uploads_produk/<?= htmlspecialchars($editProduk['gambar']) ?>" class="w-10 h-10 rounded-lg object-cover">
                                <label class="flex items-center gap-1.5 text-[11px] text-red-300"><input type="checkbox" name="hapus_gambar" value="1"> Hapus foto ini</label>
                            </div>
                        <?php endif; ?>

                        <div class="flex gap-2 pt-1">
                            <button type="submit" class="flex-1 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-xs px-4 py-2.5 rounded-xl shadow">
                                <?= $editProduk ? '💾 Simpan Perubahan' : '➕ Tambah Produk' ?>
                            </button>
                            <?php if ($editProduk): ?>
                                <a href="admin.php?tab=produk" class="bg-slate-700 hover:bg-slate-600 text-slate-200 font-bold text-xs px-4 py-2.5 rounded-xl text-center">Batal</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <!-- Daftar Produk -->
                <?php
                $daftarProduk = [];
                try {
                    $qProduk = $pdo->query("SELECT * FROM produk ORDER BY kategori, nama");
                    $daftarProduk = $qProduk->fetchAll(PDO::FETCH_ASSOC);
                } catch (\Throwable $e) {}

                $produkPerKategori = [];
                foreach ($daftarProduk as $prd) {
                    $produkPerKategori[$prd['kategori'] ?: 'Lainnya'][] = $prd;
                }
                ?>

                <?php if (empty($daftarProduk)): ?>
                    <div class="text-center py-16 text-slate-500">
                        <p class="text-4xl mb-3">🧃</p>
                        <p class="font-semibold text-slate-400">Belum ada produk</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($produkPerKategori as $namaKategori => $listProduk): ?>
                        <div>
                            <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2 px-1"><?= htmlspecialchars($namaKategori) ?></p>
                            <div class="space-y-2">
                                <?php foreach ($listProduk as $prd): ?>
                                    <div class="flex items-center gap-3 bg-slate-800/40 border border-slate-700 p-3 rounded-2xl">
                                        <?php if (!empty($prd['gambar'])): ?>
                                            <img src="uploads_produk/<?= htmlspecialchars($prd['gambar']) ?>" class="w-11 h-11 rounded-xl object-cover shrink-0">
                                        <?php else: ?>
                                            <div class="w-11 h-11 rounded-xl bg-slate-900/60 flex items-center justify-center text-xl shrink-0"><?= htmlspecialchars($prd['ikon'] ?: '🍽️') ?></div>
                                        <?php endif; ?>

                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-bold text-white truncate"><?= htmlspecialchars($prd['nama']) ?></p>
                                            <p class="text-xs text-amber-400 font-semibold">Rp <?= number_format($prd['harga'] ?? 0, 0, ',', '.') ?></p>
                                        </div>

                                        <div class="flex gap-1.5 shrink-0">
                                            <a href="admin.php?tab=produk&edit=<?= (int) $prd['id'] ?>" class="bg-sky-500/20 text-sky-300 text-[10px] font-bold px-2.5 py-1.5 rounded-lg">✏️ Edit</a>
                                            <button type="button" onclick="bukaModalHapus(<?= (int) $prd['id'] ?>, '<?= htmlspecialchars($prd['nama'], ENT_QUOTES) ?>')" class="bg-red-500/20 text-red-300 text-[10px] font-bold px-2.5 py-1.5 rounded-lg hover:bg-red-500/30 transition">🗑️ Hapus</button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </main>

    <!-- Modal Konfirmasi Hapus Kustom -->
    <div id="modalHapus" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-slate-800 border border-slate-700 rounded-3xl max-w-xs w-full p-6 text-center shadow-2xl transform scale-95 transition-transform duration-200" id="modalHapusKonten">
            <div class="w-12 h-12 bg-red-500/20 text-red-400 rounded-full flex items-center justify-center text-xl mx-auto mb-3">
                ⚠️
            </div>
            <h3 class="text-base font-bold text-white mb-1">Hapus Produk?</h3>
            <p class="text-xs text-slate-400 mb-5" id="teksNamaProduk">Produk yang dihapus tidak dapat dikembalikan.</p>
            
            <div class="flex gap-2">
                <button type="button" onclick="tutupModalHapus()" class="flex-1 bg-slate-700 hover:bg-slate-600 text-slate-200 text-xs font-bold py-2.5 rounded-xl transition">Batal</button>
                <a id="btnKonfirmasiHapus" href="#" class="flex-1 bg-red-500 hover:bg-red-600 text-white text-xs font-bold py-2.5 rounded-xl transition shadow-lg shadow-red-500/20 flex items-center justify-center">Ya, Hapus</a>
            </div>
        </div>
    </div>

    <!-- SCRIPT GLOBAL AUDIO ALARM (Dikeluarkan agar bisa diakses dari semua tab) -->
    <script>
        let audioCtx = null;

        function aktifkanAudio() {
            audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            mainkanAlarmKeras();
            const btnAudio = document.getElementById('btn-audio');
            if (btnAudio) btnAudio.style.display = 'none';
            alert('✅ Berhasil! Alarm nyaring siap.');
        }

        function mainkanAlarmKeras() {
            if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            let now = audioCtx.currentTime;
            for (let i = 0; i < 3; i++) {
                let osc = audioCtx.createOscillator();
                let gain = audioCtx.createGain();
                osc.type = 'square';
                osc.frequency.setValueAtTime(950, now + (i * 0.2));
                osc.frequency.setValueAtTime(1400, now + (i * 0.2) + 0.1);
                gain.gain.setValueAtTime(1.0, now + (i * 0.2));
                gain.gain.exponentialRampToValueAtTime(0.01, now + (i * 0.2) + 0.15);
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                osc.start(now + (i * 0.2));
                osc.stop(now + (i * 0.2) + 0.15);
            }
        }
    </script>

    <?php if ($tab == 'pesanan'): ?>
    <script>
        let lastPendingCount = -1;

        function ambilPesanan() {
            fetch('api.php?aksi=ambil_pesanan')
            .then(res => res.json())
            .then(data => {
                let container = document.getElementById('list-pesanan');
                let html = '';
                let activeList = data.filter(p => p.status === 'Pending' || p.status === 'Diproses');
                let pendingList = data.filter(p => p.status === 'Pending');
                let pendingCount = pendingList.length;

                if (lastPendingCount !== -1 && pendingCount > lastPendingCount) {
                    mainkanAlarmKeras();
                }
                lastPendingCount = pendingCount;

                if (activeList.length === 0) {
                    container.innerHTML = `<div class="text-center py-20 text-slate-500"><p class="text-3xl mb-2">🍃</p> Belum ada pesanan aktif.</div>`;
                    return;
                }

                activeList.forEach(p => {
                    let borderCol = p.status === 'Pending' ? 'border-amber-500 bg-slate-800 shadow-lg shadow-amber-500/20' : 'border-slate-700 bg-slate-800/40';
                    
                    let catatanHtml = p.catatan ? `
                        <div class="bg-amber-500/10 border border-amber-500/20 text-amber-300 p-2.5 rounded-xl text-xs mt-3">
                            <span class="font-bold">📝 Catatan:</span> ${p.catatan}
                        </div>` : '';

                    html += `
                    <div class="border ${borderCol} p-4 rounded-2xl relative transition-all">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <h3 class="font-bold text-base text-white">${p.nama_pembeli}</h3>
                                <p class="text-xs text-emerald-400 font-semibold mt-0.5">🏡 ${p.no_rumah}</p>
                            </div>
                            <span class="text-[10px] font-bold px-2.5 py-1 rounded-full ${p.status === 'Pending' ? 'bg-amber-500 text-slate-950 animate-pulse' : 'bg-emerald-500/20 text-emerald-300'}">${p.status}</span>
                        </div>
                        
                        <div class="bg-slate-900/50 p-2.5 rounded-xl my-3 text-xs space-y-1 text-slate-300">
                            ${p.detail.map(item => `<div class="flex justify-between"><span>${item.jumlah}x${item.nama_menu}</span> <span class="font-semibold">Rp ${item.subtotal.toLocaleString('id-ID')}</span></div>`).join('')}
                        </div>

                        ${catatanHtml}

                        <div class="flex justify-between items-center mt-3 pt-3 border-t border-slate-700/60">
                            <span class="text-xs font-bold text-slate-400">Total: <span class="text-white">Rp ${p.total.toLocaleString('id-ID')}</span></span>
                            <div class="space-x-2">
                                ${p.status === 'Pending' ? `<button onclick="ubahStatus(${p.id}, 'Diproses')" class="bg-amber-500 hover:bg-amber-600 text-slate-950 px-3 py-1.5 rounded-xl text-xs font-bold shadow cursor-pointer">Proses 🍳</button>` : ''}
                                ${p.status === 'Diproses' ? `<button onclick="ubahStatus(${p.id}, 'Selesai')" class="bg-emerald-500 hover:bg-emerald-600 text-slate-950 px-3 py-1.5 rounded-xl text-xs font-bold shadow cursor-pointer">Selesai ✓</button>` : ''}
                            </div>
                        </div>
                    </div>`;
                });
                container.innerHTML = html;
            });
        }

        function ubahStatus(id, statusBaru) {
            fetch(`api.php?aksi=update_status&id=${id}&status=${statusBaru}`)
            .then(() => ambilPesanan());
        }

        setInterval(ambilPesanan, 3000);
        ambilPesanan();
    </script>
    <?php endif; ?>

    <?php if ($tab == 'history'): ?>
    <script>
        function filterHistory() {
            const kotakCari = document.getElementById('cariHistory');
            if (!kotakCari) return;
            const kataKunci = kotakCari.value.toLowerCase().trim();
            const semuaGrup = document.querySelectorAll('[data-grup]');
            let adaYangCocok = false;

            semuaGrup.forEach(grup => {
                let grupPunyaCocok = false;
                grup.querySelectorAll('.kartu-history').forEach(kartu => {
                    const cocok = kartu.dataset.nama.includes(kataKunci);
                    kartu.style.display = cocok ? '' : 'none';
                    if (cocok) { grupPunyaCocok = true; adaYangCocok = true; }
                });
                grup.style.display = grupPunyaCocok ? '' : 'none';
            });

            const pesanKosong = document.getElementById('pesan-kosong-cari');
            if (pesanKosong) pesanKosong.classList.toggle('hidden', adaYangCocok || kataKunci === '');
        }
    </script>
    <?php endif; ?>

    <script>
        function bukaModalHapus(id, namaProduk) {
            const modal = document.getElementById('modalHapus');
            const konten = document.getElementById('modalHapusKonten');
            const btnHapus = document.getElementById('btnKonfirmasiHapus');
            const teks = document.getElementById('teksNamaProduk');

            teks.innerHTML = `Hapus produk <b class="text-white">"${namaProduk}"</b> dari daftar?`;
            btnHapus.href = `admin.php?tab=produk&hapus=${id}`;

            modal.classList.remove('hidden');
            setTimeout(() => konten.classList.remove('scale-95'), 10);
        }

        function tutupModalHapus() {
            const modal = document.getElementById('modalHapus');
            const konten = document.getElementById('modalHapusKonten');
            
            konten.classList.add('scale-95');
            setTimeout(() => modal.classList.add('hidden'), 150);
        }
    </script>
</body>
</html>