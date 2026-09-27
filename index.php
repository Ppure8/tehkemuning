<?php
session_start();
include 'koneksi.php';

$produkList = [];
try {
    $stmt =$pdo->query("SELECT * FROM produk ORDER BY kategori, nama");
    $produkList =$stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (\Throwable $e) {$error = "Gagal memuat produk: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemesanan - Es Teh Kemuning</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-900 text-slate-100 pb-28">

    <!-- Header Pelanggan -->
    <header class="bg-[#2D4A3E] text-white p-5 rounded-b-[2.5rem] shadow-lg sticky top-0 z-20">
        <div class="flex justify-between items-center">
            <div>
                <span class="text-xs bg-[#4A7062] px-3 py-1 rounded-full font-medium">📍 Perum Panghegar</span>
                <h1 class="text-xl font-bold mt-1">Es Teh Kemuning 🍃</h1>
            </div>
            <div class="bg-white/10 p-2 rounded-2xl backdrop-blur-md flex items-center justify-center">
                <img src="logo.png" alt="Logo Es Teh Kemuning" class="w-12 h-12 object-contain">
            </div>
        </div>
    </header>

    <main class="p-4 max-w-md mx-auto space-y-4">
        <?php if (!empty($error)): ?>
            <div class="bg-red-500/10 border border-red-500/30 text-red-300 text-xs p-3 rounded-xl"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Daftar Menu -->
        <div class="grid grid-cols-2 gap-3" id="daftar-menu">
            <?php if (empty($produkList)): ?>
                <div class="col-span-2 text-center py-16 text-slate-500">
                    <p class="text-3xl mb-2">🍃</p>
                    <p class="text-xs">Belum ada menu yang tersedia.</p>
                </div>
            <?php else: ?>
                <?php foreach ($produkList as$p): ?>
                <div class="bg-slate-800 border border-slate-700/80 rounded-2xl p-3 flex flex-col justify-between shadow-md">
                    <div>
                        <div class="w-full h-28 bg-slate-900/60 rounded-xl overflow-hidden mb-2.5 flex items-center justify-center">
                            <?php if (!empty($p['gambar']) && file_exists(__DIR__ . '/uploads_produk/' .$p['gambar'])): ?>
                                <img src="uploads_produk/<?= htmlspecialchars($p['gambar']) ?>" alt="<?= htmlspecialchars($p['nama']) ?>" class="w-full h-full object-cover">
                            <?php else: ?>
                                <span class="text-3xl"><?= htmlspecialchars($p['ikon'] ?: '🧋') ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <span class="text-[10px] bg-slate-700/60 text-slate-300 px-2 py-0.5 rounded-md font-medium"><?= htmlspecialchars($p['kategori']) ?></span>
                        <h3 class="text-xs font-bold text-white mt-1.5 line-clamp-2"><?= htmlspecialchars($p['nama']) ?></h3>
                    </div>

                    <div class="flex items-center justify-between mt-3 pt-2 border-t border-slate-700/50">
                        <span class="text-xs font-extrabold text-amber-400">Rp <?= number_format($p['harga'], 0, ',', '.') ?></span>
                        <button onclick='tambahItem(<?= json_encode($p) ?>)' class="bg-amber-500 hover:bg-amber-400 text-slate-950 px-2.5 py-1.5 rounded-xl font-bold text-xs shadow transition flex items-center gap-1">
                            <span>➕</span>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <!-- Floating Cart Bar -->
    <div id="cart-bar" class="fixed bottom-0 left-0 right-0 bg-slate-800 border-t border-slate-700 p-4 shadow-2xl hidden z-30">
        <div class="max-w-md mx-auto flex justify-between items-center">
            <div>
                <p class="text-[10px] text-slate-400">Total Keranjang (<span id="cart-count">0</span> item)</p>
                <p class="text-sm font-extrabold text-amber-400" id="cart-total">Rp 0</p>
            </div>
            <button onclick="bukaCheckout()" class="bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-bold px-5 py-2.5 rounded-xl text-xs shadow">
                Lanjut Pesan 🛒
            </button>
        </div>
    </div>

    <!-- Modal Checkout -->
    <div id="modalCheckout" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-slate-800 border border-slate-700 rounded-3xl max-w-sm w-full p-5 space-y-4 shadow-2xl">
            <h3 class="text-sm font-bold text-white">Konfirmasi Pesanan Anda</h3>
            <div id="cart-items-summary" class="max-h-40 overflow-y-auto space-y-2 text-xs text-slate-300">
                <!-- List item keranjang -->
            </div>
            <div class="space-y-2 pt-2 border-t border-slate-700">
                <input type="text" id="namaPembeli" placeholder="Nama Pemesan" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white">
                <input type="text" id="noRumah" placeholder="Nomor Rumah / Blok" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white">
                <textarea id="catatanPesanan" placeholder="Catatan (opsional, misal: Es sedikit)" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white"></textarea>
            </div>
            <div class="flex gap-2 pt-2">
                <button onclick="tutupCheckout()" class="flex-1 bg-slate-700 text-slate-200 py-2.5 rounded-xl text-xs font-bold">Batal</button>
                <button onclick="kirimPesanan()" class="flex-1 bg-amber-500 text-slate-950 py-2.5 rounded-xl text-xs font-bold">Kirim Pesanan 🚀</button>
            </div>
        </div>
    </div>

    <script>
        let keranjang = [];

        function tambahItem(produk) {
            let ada = keranjang.find(item => item.id === produk.id);
            if (ada) {
                ada.jumlah++;
            } else {
                keranjang.push({ ...produk, jumlah: 1 });
            }
            updateCartUI();
        }

        function updateCartUI() {
            let totalCount = keranjang.reduce((sum, item) => sum + item.jumlah, 0);
            let totalPrice = keranjang.reduce((sum, item) => sum + (item.harga * item.jumlah), 0);
            
            document.getElementById('cart-count').innerText = totalCount;
            document.getElementById('cart-total').innerText = 'Rp ' + totalPrice.toLocaleString('id-ID');
            
            let cartBar = document.getElementById('cart-bar');
            if (totalCount > 0) {
                cartBar.classList.remove('hidden');
            } else {
                cartBar.classList.add('hidden');
            }
        }

        function bukaCheckout() {
            let summaryHTML = '';
            let totalPrice = 0;
            keranjang.forEach(item => {
                let subtotal = item.harga * item.jumlah;
                totalPrice += subtotal;
                summaryHTML += `<div class="flex justify-between"><span>${item.jumlah}x ${item.nama}</span> <span class="font-bold">Rp ${subtotal.toLocaleString('id-ID')}</span></div>`;
            });
            document.getElementById('cart-items-summary').innerHTML = summaryHTML;
            document.getElementById('modalCheckout').classList.remove('hidden');
        }

        function tutupCheckout() {
            document.getElementById('modalCheckout').classList.add('hidden');
        }

        function kirimPesanan() {
            let nama = document.getElementById('namaPembeli').value.trim();
            let rumah = document.getElementById('noRumah').value.trim();
            let catatan = document.getElementById('catatanPesanan').value.trim();

            if (!nama || !rumah) {
                alert('Nama dan Nomor Rumah wajib diisi!');
                return;
            }

            let dataKirim = {
                nama_pembeli: nama,
                no_rumah: rumah,
                catatan: catatan,
                items: keranjang
            };

            fetch('api.php?aksi=pesan', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(dataKirim)
            })
            .then(res => res.json())
            .then(data => {
                if (data.sukses) {
                    alert('🎉 Pesanan berhasil dikirim!');
                    keranjang = [];
                    updateCartUI();
                    tutupCheckout();
                } else {
                    alert('Gagal mengirim pesanan: ' + (data.pesan || 'Kesalahan sistem'));
                }
            })
            .catch(err => {
                alert('Terjadi kesalahan jaringan.');
            });
        }
    </script>
</body>
</html>
