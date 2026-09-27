<?php
include 'koneksi.php';

$produk = [];$pesanMuatProduk = '';
try {
    $stmtProduk =$db->query("SELECT * FROM produk ORDER BY kategori, nama");
    $produk =$stmtProduk->fetchAll(PDO::FETCH_ASSOC);
} catch (\Throwable $e) {$pesanMuatProduk = 'Menu sedang tidak bisa dimuat. Silakan refresh halaman ini.';
}

$daftarKategori = [];
foreach ($produk as$p) {
    $kat =$p['kategori'] ?: 'Lainnya';
    if (!in_array($kat,$daftarKategori, true)) {
        $daftarKategori[] =$kat;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Es Teh Kemuning & Cemilan Perum</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-[#F4F6F0] text-slate-800 pb-24">

    <!-- Header Unik -->
    <header class="bg-[#2D4A3E] text-white p-5 rounded-b-[2.5rem] shadow-lg sticky top-0 z-20">
        <div class="flex justify-between items-center">
            <div>
                <span class="text-xs bg-[#4A7062] px-3 py-1 rounded-full font-medium">📍 Perum Panghegar</span>
                <h1 class="text-xl font-bold mt-1">Es Teh Kemuning 🍃</h1>
            </div>
            <div class="bg-white/10 p-2.5 rounded-2xl backdrop-blur-md flex items-center justify-center">
    <img src="logo.png" alt="Logo Es Teh Kemuning" class="w-8 h-8 object-contain">
</div>
            </div>
        </div>
    </header>

    <!-- Daftar Produk -->
    <main class="p-4 max-w-md mx-auto">
        <h2 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-3">Pilih Menu Favorit</h2>

        <?php if (!empty($pesanMuatProduk)): ?>
            <div class="bg-red-50 border border-red-200 text-red-600 text-xs p-3 rounded-xl mb-4"><?= htmlspecialchars($pesanMuatProduk) ?></div>
        <?php elseif (empty($produk)): ?>
            <div class="text-center py-24 text-slate-400">
                <p class="text-4xl mb-3">🧋</p>
                <p class="font-semibold text-slate-500">Menu belum tersedia</p>
            </div>
        <?php else: ?>

            <div class="relative mb-3">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm pointer-events-none">🔍</span>
                <input type="text" id="cari-menu" placeholder="Cari menu favoritmu..." class="w-full bg-white border border-slate-200 rounded-xl pl-9 pr-3 py-2.5 text-xs focus:outline-none focus:border-[#2D4A3E] transition">
            </div>

            <div class="flex gap-2 overflow-x-auto pb-1 mb-4">
                <button type="button" class="chip-kategori aktif shrink-0 px-4 py-1.5 rounded-full text-xs font-bold bg-[#2D4A3E] text-white transition" data-kategori="semua">Semua</button>
                <?php foreach ($daftarKategori as$kat): ?>
                    <button type="button" class="chip-kategori shrink-0 px-4 py-1.5 rounded-full text-xs font-bold bg-white text-slate-500 border border-slate-200 transition" data-kategori="<?= htmlspecialchars($kat) ?>"><?= htmlspecialchars($kat) ?></button>
                <?php endforeach; ?>
            </div>

            <div class="grid grid-cols-2 gap-3" id="grid-produk">
                <?php foreach ($produk as $p):$kat = $p['kategori'] ?: 'Lainnya';$fotoProduk = !empty($p['gambar']) ? 'uploads_produk/' . htmlspecialchars($p['gambar']) . '?v=' . time() : '';
                ?>
                <div class="kartu-produk bg-white p-3.5 rounded-2xl shadow-sm border border-slate-100 flex flex-col justify-between"
                     data-id="<?= (int) $p['id'] ?>"
                     data-nama="<?= htmlspecialchars($p['nama']) ?>"
                     data-harga="<?= (int) ($p['harga'] ?? 0) ?>"
                     data-ikon="<?= htmlspecialchars($p['ikon'] ?? '') ?>"
                     data-gambar="<?= htmlspecialchars($p['gambar'] ?? '') ?>"
                     data-kategori="<?= htmlspecialchars($kat) ?>">
                    <div>
                        <!-- Area Gambar yang Bisa Diklik -->
                        <div onclick="bukaDetailProduk(this)" class="w-full h-20 mb-2 rounded-xl overflow-hidden bg-[#F4F6F0] flex items-center justify-center cursor-pointer relative group">
                            <?php if (!empty($fotoProduk)): ?>
                                <img src="<?= $fotoProduk ?>" alt="<?= htmlspecialchars($p['nama']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition">
                                <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-[10px] font-bold">🔍 Lihat</div>
                            <?php else: ?>
                                <span class="text-3xl group-hover:scale-110 transition"><?= htmlspecialchars($p['ikon'] ?: '🥤') ?></span>
                            <?php endif; ?>
                        </div>
                        <span class="text-[10px] font-semibold text-[#2D4A3E] bg-[#E8EFEA] px-2 py-0.5 rounded-md"><?= htmlspecialchars($kat) ?></span>
                        <h3 class="font-bold text-sm text-slate-700 mt-1 leading-snug min-h-[2.25rem]"><?= htmlspecialchars($p['nama']) ?></h3>
                    </div>
                    <div class="mt-3 flex items-center justify-between">
                        <span class="text-xs font-bold text-emerald-700">Rp <?= number_format($p['harga'] ?? 0, 0, ',', '.') ?></span>
                        <div class="area-aksi"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <p id="menu-kosong-cari" class="hidden text-center py-10 text-slate-400 text-xs">😕 Menu tidak ditemukan.</p>

        <?php endif; ?>
    </main>

    <!-- Floating Keranjang -->
    <div id="cart-bar" class="fixed bottom-4 left-4 right-4 max-w-md mx-auto bg-[#1E332B] text-white p-4 rounded-2xl shadow-2xl flex items-center justify-between hidden z-30 transition-all">
        <div>
            <p class="text-xs text-slate-300"><span id="cart-count">0</span> Item Dipilih</p>
            <p class="text-base font-bold" id="cart-total">Rp 0</p>
        </div>
        <button type="button" onclick="bukaModal()" class="bg-[#A3B18A] hover:bg-[#8A9A72] text-[#1E332B] px-5 py-2.5 rounded-xl font-bold text-sm shadow">Lihat Keranjang 🛍️</button>
    </div>

    <!-- Modal Detail Gambar / Produk (Popup Besar) -->
    <div id="modal-detail" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-sm rounded-3xl shadow-2xl overflow-hidden p-6 relative animate-in fade-in zoom-in duration-200">
            <button type="button" onclick="tutupDetailProduk()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 font-bold text-lg bg-slate-100 w-8 h-8 rounded-full flex items-center justify-center transition">✕</button>
            <div id="detail-konten" class="text-center pt-2">
                <!-- Konten dinamis dimuat via JS -->
            </div>
        </div>
    </div>

    <!-- Modal Keranjang + Data Pembeli -->
    <div id="modal-checkout" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div class="bg-white w-full max-w-md rounded-t-[2.5rem] sm:rounded-3xl shadow-2xl max-h-[90vh] overflow-y-auto">

            <div id="langkah-keranjang" class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold text-lg text-slate-800">Keranjang & Pengiriman 🛍️</h3>
                    <button type="button" onclick="tutupModal()" class="text-slate-400 font-bold text-lg">✕</button>
                </div>

                <div id="daftar-keranjang" class="mb-3"></div>

                <div class="flex justify-between items-center py-3 border-t border-b border-slate-100 mb-4">
                    <span class="text-xs font-bold text-slate-500">Total Pesanan</span>
                    <span id="modal-total" class="text-base font-extrabold text-[#2D4A3E]">Rp 0</span>
                </div>

                <form id="form-order" onsubmit="kirimPesanan(event)">
                    <p class="text-xs font-bold text-slate-500 mb-2">Detail Pengiriman</p>
                    <div class="space-y-3 mb-4">
                        <div>
                            <label class="text-xs font-semibold text-slate-500">Nama Pemesan</label>
                            <input type="text" id="nama" required class="w-full mt-1 bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:border-[#2D4A3E]" placeholder="Contoh: Bu Siti">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500">Blok / Nomor Rumah</label>
                            <input type="text" id="no_rumah" required class="w-full mt-1 bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:border-[#2D4A3E]" placeholder="Contoh: Blok C2 No. 12">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500">Nomor WhatsApp (Opsional)</label>
                            <input type="text" id="no_hp" class="w-full mt-1 bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:border-[#2D4A3E]" placeholder="08123456xxx">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500">Catatan / Request Khusus (Opsional)</label>
                            <textarea id="catatan" rows="2" class="w-full mt-1 bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-sm focus:outline-none focus:border-[#2D4A3E]" placeholder="Contoh: Es sedikit ya kak"></textarea>
                        </div>
                    </div>
                    <button type="submit" id="btn-kirim" class="w-full bg-[#2D4A3E] text-white py-3 rounded-xl font-bold text-sm shadow-lg hover:bg-[#1E332B] transition">Kirim Pesanan Sekarang ✨</button>
                </form>
            </div>

            <div id="langkah-sukses" class="p-8 text-center hidden">
                <p class="text-5xl mb-3">🎉</p>
                <h3 class="font-bold text-lg text-slate-800 mb-1">Pesanan Terkirim!</h3>
                <p class="text-sm text-slate-500 mb-6">Penjual sudah menerima pesananmu. Mohon tunggu di rumah ya.</p>
                <button type="button" onclick="pesanLagi()" class="w-full bg-[#2D4A3E] text-white py-3 rounded-xl font-bold text-sm shadow-lg">Tutup</button>
            </div>

        </div>
    </div>

    <script>
        let cart = {};
        const kartuRenderers = {};

        // Fungsi Membuka Modal Detail Gambar Produk
        function bukaDetailProduk(el) {
            const kartu = el.closest('.kartu-produk');
            const nama = kartu.dataset.nama;
            const harga = parseInt(kartu.dataset.harga, 10) || 0;
            const ikon = kartu.dataset.ikon || '';
            const gambar = kartu.dataset.gambar || '';
            const kategori = kartu.dataset.kategori;

            const konten = document.getElementById('detail-konten');
            let mediaHtml = '';

            if (gambar) {
                mediaHtml = `<div class="w-full h-64 rounded-2xl overflow-hidden bg-slate-100 mb-4 shadow-inner flex items-center justify-center">
                    <img src="uploads_produk/${gambar}" class="w-full h-full object-cover">
                </div>`;
            } else {
                mediaHtml = `<div class="w-full h-48 rounded-2xl bg-emerald-50 mb-4 flex items-center justify-center text-6xl shadow-inner">
                    ${ikon || '🥤'}
                </div>`;
            }

            konten.innerHTML = `
                ${mediaHtml}
                <span class="text-xs font-semibold text-[#2D4A3E] bg-[#E8EFEA] px-2.5 py-1 rounded-md inline-block mb-1">${kategori}</span>
                <h2 class="text-lg font-bold text-slate-800 mb-1">${nama}</h2>
                <p class="text-base font-extrabold text-emerald-700 mb-4">Rp ${harga.toLocaleString('id-ID')}</p>
                <button type="button" onclick="tutupDetailProduk()" class="w-full bg-[#2D4A3E] text-white py-2.5 rounded-xl font-bold text-xs shadow hover:bg-[#1E332B] transition">Tutup</button>
            `;

            document.getElementById('modal-detail').classList.remove('hidden');
        }

        function tutupDetailProduk() {
            document.getElementById('modal-detail').classList.add('hidden');
        }

        document.querySelectorAll('.kartu-produk').forEach(kartu => {
            const id = kartu.dataset.id;
            const nama = kartu.dataset.nama;
            const harga = parseInt(kartu.dataset.harga, 10) || 0;
            const ikon = kartu.dataset.ikon || '';
            const gambar = kartu.dataset.gambar || '';
            const areaAksi = kartu.querySelector('.area-aksi');

            function ubahJumlah(delta) {
                if (!cart[id]) cart[id] = { nama, harga, ikon, gambar, jumlah: 0 };
                cart[id].jumlah += delta;
                if (cart[id].jumlah <= 0) delete cart[id];
                render();
                updateCartUI();
            }

            function render() {
                const qty = cart[id] ? cart[id].jumlah : 0;
                if (qty === 0) {
                    areaAksi.innerHTML = `<button type="button" class="btn-tambah bg-[#2D4A3E] hover:bg-[#1E332B] text-white w-7 h-7 rounded-full flex items-center justify-center text-sm font-bold transition">+</button>`;
                    areaAksi.querySelector('.btn-tambah').addEventListener('click', () => ubahJumlah(1));
                } else {
                    areaAksi.innerHTML = `
                        <div class="flex items-center gap-1 bg-[#E8EFEA] rounded-full px-1 py-1">
                            <button type="button" class="btn-kurang w-6 h-6 rounded-full bg-white shadow-sm flex items-center justify-center text-[#2D4A3E] font-bold text-xs">−</button>
                            <span class="text-xs font-bold text-[#2D4A3E] w-4 text-center">${qty}</span>
                            <button type="button" class="btn-tambah w-6 h-6 rounded-full bg-[#2D4A3E] text-white shadow-sm flex items-center justify-center font-bold text-xs">+</button>
                        </div>`;
                    areaAksi.querySelector('.btn-tambah').addEventListener('click', () => ubahJumlah(1));
                    areaAksi.querySelector('.btn-kurang').addEventListener('click', () => ubahJumlah(-1));
                }
            }

            kartuRenderers[id] = render;
            render();
        });

        function updateCartUI() {
            let totalItem = 0, totalPrice = 0;
            for (let id in cart) {
                totalItem += cart[id].jumlah;
                totalPrice += cart[id].harga * cart[id].jumlah;
            }
            const cartBar = document.getElementById('cart-bar');
            if (totalItem > 0) {
                cartBar.classList.remove('hidden');
                document.getElementById('cart-count').innerText = totalItem;
                document.getElementById('cart-total').innerText = 'Rp ' + totalPrice.toLocaleString('id-ID');
            } else {
                cartBar.classList.add('hidden');
            }
        }

        function renderKeranjangModal() {
            const wadah = document.getElementById('daftar-keranjang');
            const idList = Object.keys(cart);
            let total = 0;

            if (idList.length === 0) {
                wadah.innerHTML = '<p class="text-xs text-slate-400 text-center py-6">Keranjang masih kosong.</p>';
            } else {
                let html = '';
                idList.forEach(id => {
                    const item = cart[id];
                    const subtotal = item.harga * item.jumlah;
                    total += subtotal;
                    html += `
                    <div class="flex items-center gap-2.5 py-2.5 border-b border-slate-100 last:border-0">
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold text-slate-700 truncate">${item.nama}</p>
                            <p class="text-[11px] text-slate-400">Rp ${item.harga.toLocaleString('id-ID')}</p>
                        </div>
                        <span class="text-xs font-bold text-emerald-700 w-16 text-right">Rp ${subtotal.toLocaleString('id-ID')}</span>
                    </div>`;
                });
                wadah.innerHTML = html;
            }
            document.getElementById('modal-total').innerText = 'Rp ' + total.toLocaleString('id-ID');
        }

        function bukaModal() {
            renderKeranjangModal();
            document.getElementById('langkah-keranjang').classList.remove('hidden');
            document.getElementById('langkah-sukses').classList.add('hidden');
            document.getElementById('modal-checkout').classList.remove('hidden');
        }
        function tutupModal() {
            document.getElementById('modal-checkout').classList.add('hidden');
        }

        function terapkanFilter() {
            const kotakCari = document.getElementById('cari-menu');
            const kataKunci = kotakCari ? kotakCari.value.toLowerCase().trim() : '';
            const chipAktif = document.querySelector('.chip-kategori.aktif');
            const kategoriAktif = chipAktif ? chipAktif.dataset.kategori : 'semua';
            let adaYangTampil = false;

            document.querySelectorAll('.kartu-produk').forEach(kartu => {
                const cocokKategori = kategoriAktif === 'semua' || kartu.dataset.kategori === kategoriAktif;
                const cocokKata = kartu.dataset.nama.toLowerCase().includes(kataKunci);
                const tampil = cocokKategori && cocokKata;
                kartu.style.display = tampil ? '' : 'none';
                if (tampil) adaYangTampil = true;
            });

            const pesanKosong = document.getElementById('menu-kosong-cari');
            if (pesanKosong) pesanKosong.classList.toggle('hidden', adaYangTampil);
        }

        const kotakCariMenu = document.getElementById('cari-menu');
        if (kotakCariMenu) kotakCariMenu.addEventListener('input', terapkanFilter);

        document.querySelectorAll('.chip-kategori').forEach(chip => {
            chip.addEventListener('click', () => {
                document.querySelectorAll('.chip-kategori').forEach(c => {
                    c.classList.remove('aktif', 'bg-[#2D4A3E]', 'text-white');
                    c.classList.add('bg-white', 'text-slate-500', 'border', 'border-slate-200');
                });
                chip.classList.add('aktif', 'bg-[#2D4A3E]', 'text-white');
                chip.classList.remove('bg-white', 'text-slate-500', 'border', 'border-slate-200');
                terapkanFilter();
            });
        });

        function kirimPesanan(e) {
            e.preventDefault();
            if (Object.keys(cart).length === 0) return;

            const tombolKirim = document.getElementById('btn-kirim');
            tombolKirim.disabled = true;
            tombolKirim.innerText = 'Mengirim...';

            let itemsUntukServer = {};
            for (let id in cart) {
                itemsUntukServer[cart[id].nama] = { harga: cart[id].harga, jumlah: cart[id].jumlah };
            }

            let data = {
                nama: document.getElementById('nama').value,
                no_rumah: document.getElementById('no_rumah').value,
                no_hp: document.getElementById('no_hp').value,
                catatan: document.getElementById('catatan').value,
                items: itemsUntukServer
            };

            fetch('api.php?aksi=simpan', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(response => {
                if (response.status === 'sukses') {
                    document.getElementById('langkah-keranjang').classList.add('hidden');
                    document.getElementById('langkah-sukses').classList.remove('hidden');
                } else {
                    tombolKirim.disabled = false;
                    tombolKirim.innerText = 'Kirim Pesanan Sekarang ✨';
                    alert('Gagal mengirim pesanan.');
                }
            })
            .catch(() => {
                tombolKirim.disabled = false;
                tombolKirim.innerText = 'Kirim Pesanan Sekarang ✨';
                alert('Kesalahan jaringan.');
            });
        }

        function pesanLagi() { location.reload(); }
    </script>
</body>
</html>
