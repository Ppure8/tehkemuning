<?php
include 'koneksi.php';

$produk = [];
$pesanMuatProduk = '';
try {
    $stmtProduk = $pdo->query("SELECT * FROM produk ORDER BY kategori, nama");
    $produk = $stmtProduk->fetchAll(PDO::FETCH_ASSOC);
} catch (\Throwable $e) {
    $pesanMuatProduk = 'Menu sedang tidak bisa dimuat. Silakan refresh halaman ini.';
}

$daftarKategori = [];
foreach ($produk as $p) {
    $kat = $p['kategori'] ?: 'Lainnya';
    if (!in_array($kat, $daftarKategori, true)) {
        $daftarKategori[] = $kat;
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
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: { 100: '#E7ECDF', 700: '#28493A', 800: '#1F3D2F', 900: '#14261D' },
                        tea: { 100: '#F6E9D6', 500: '#C68A3D', 600: '#B8792E' },
                        paper: '#FAF8F2',
                        ink: { 300: '#B7AF9C', 500: '#6B6456', 900: '#262421' }
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        serif: ['Fraunces', 'serif']
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        #cari-menu::placeholder { color: #B7AF9C; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-paper text-ink-900 pb-24">

    <!-- Toast Notifikasi Custom Bergaya Premium -->
    <div id="toast-notif" class="fixed top-6 left-1/2 -translate-x-1/2 z-[70] hidden transition-all duration-500 transform -translate-y-10 opacity-0 pointer-events-none w-max max-w-[90vw]">
        <div class="bg-brand-900 backdrop-blur-md px-5 py-3.5 rounded-2xl shadow-2xl border border-brand-700 flex items-center gap-3">
            <div id="toast-icon" class="text-2xl shrink-0 bg-white/10 w-10 h-10 flex items-center justify-center rounded-full">🔔</div>
            <div>
                <h4 id="toast-title" class="font-serif font-bold text-sm text-white">Pemberitahuan</h4>
                <p id="toast-message" class="text-xs text-brand-100 mt-0.5">Pesan notifikasi di sini.</p>
            </div>
        </div>
    </div>

    <!-- Header -->
    <header class="bg-brand-800 text-white px-5 pt-5 pb-6 rounded-b-[2rem] shadow-lg sticky top-0 z-20">
        <div class="flex justify-between items-start gap-3">
            <div>
                <span class="inline-flex items-center bg-white/10 px-3 py-1 rounded-full text-[11px] font-medium">📍 Perum Panghegar</span>
                <h1 class="font-serif text-2xl font-semibold mt-2 leading-tight">Es Teh Kemuning</h1>
                <p class="text-xs text-white/60 mt-0.5">Diseduh segar, diantar hangat ke depan pintu</p>
            </div>
            <div class="bg-white/10 p-2 rounded-2xl backdrop-blur-md flex items-center justify-center">
    <img src="logo.png" alt="Logo Es Teh Kemuning" class="w-12 h-12 object-contain">
</div>
        </div>
    </header>

    <!-- Daftar Produk -->
    <main class="p-4 pt-5 max-w-md mx-auto">
        
        <!-- ================= BANNER LIVE TRACKING ================= -->
        <div id="banner-tracking" class="hidden mb-5 bg-brand-100 border border-brand-800/20 p-4 rounded-2xl shadow-sm flex items-center justify-between transition-all">
            <div>
                <p class="text-[10px] text-brand-800 font-bold uppercase tracking-wider mb-0.5">STATUS PESANAN ANDA 🎉</p>
                <p class="text-xs text-ink-500">Atas nama: <span id="track-nama" class="font-bold text-ink-900"></span></p>
                <div class="mt-2 flex items-center gap-2">
                    <span class="relative flex h-3 w-3">
                      <span id="ping-dot" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-tea-500 opacity-75"></span>
                      <span id="solid-dot" class="relative inline-flex rounded-full h-3 w-3 bg-tea-600"></span>
                    </span>
                    <p class="text-sm font-serif font-extrabold text-tea-600" id="track-status">Menunggu Konfirmasi...</p>
                </div>
            </div>
            <button id="btn-tutup-track" onclick="selesaikanTracking()" class="text-[10px] bg-white hover:bg-paper text-ink-500 px-3 py-2 rounded-xl border border-ink-900/10 transition shadow-sm">Tutup</button>
        </div>
        <!-- ======================================================== -->

        <h2 class="font-serif text-lg font-semibold text-ink-900 mb-3">Menu hari ini</h2>

        <?php if (!empty($pesanMuatProduk)): ?>
            <div class="bg-red-50 border border-red-200 text-red-600 text-xs p-3 rounded-xl mb-4"><?= htmlspecialchars($pesanMuatProduk) ?></div>
        <?php elseif (empty($produk)): ?>
            <div class="text-center py-24 text-ink-300">
                <p class="text-4xl mb-3">🧋</p>
                <p class="font-medium text-ink-500">Menu belum tersedia</p>
            </div>
        <?php else: ?>

            <div class="relative mb-3">
                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-ink-300 text-sm pointer-events-none">🔍</span>
                <input type="text" id="cari-menu" placeholder="Cari menu favoritmu..." class="w-full bg-white border border-ink-900/10 rounded-xl pl-9 pr-3 py-2.5 text-xs text-ink-900 focus:outline-none focus:border-brand-800 focus:ring-2 focus:ring-brand-800/10 transition">
            </div>

            <div class="flex gap-2 overflow-x-auto no-scrollbar pb-1 mb-5 -mx-4 px-4">
                <button type="button" class="chip-kategori aktif shrink-0 px-4 py-1.5 rounded-full text-xs font-semibold bg-brand-800 text-white transition" data-kategori="semua">Semua</button>
                <?php foreach ($daftarKategori as$kat): ?>
                    <button type="button" class="chip-kategori shrink-0 px-4 py-1.5 rounded-full text-xs font-semibold bg-white text-ink-500 border border-ink-900/10 transition" data-kategori="<?= htmlspecialchars($kat) ?>"><?= htmlspecialchars($kat) ?></button>
                <?php endforeach; ?>
            </div>

            <div class="grid grid-cols-2 gap-3" id="grid-produk">
                <?php foreach ($produk as $p):$kat = $p['kategori'] ?: 'Lainnya';$fotoProduk = !empty($p['gambar']) ? 'uploads_produk/' . htmlspecialchars($p['gambar']) . '?v=' . time() : '';
                ?>
                <div class="kartu-produk bg-white p-3.5 rounded-2xl border border-ink-900/8 hover:shadow-md hover:-translate-y-0.5 transition-all flex flex-col justify-between"
                     data-id="<?= (int) $p['id'] ?>"
                     data-nama="<?= htmlspecialchars($p['nama']) ?>"
                     data-harga="<?= (int) ($p['harga'] ?? 0) ?>"
                     data-ikon="<?= htmlspecialchars($p['ikon'] ?? '') ?>"
                     data-gambar="<?= htmlspecialchars($p['gambar'] ?? '') ?>"
                     data-kategori="<?= htmlspecialchars($kat) ?>">
                    <div>
                        <!-- Area Gambar yang Bisa Diklik -->
                        <div onclick="bukaDetailProduk(this)" class="w-full h-24 mb-2.5 rounded-xl overflow-hidden bg-brand-100 flex items-center justify-center cursor-pointer relative group">
                            <?php if (!empty($fotoProduk)): ?>
                                <img src="<?= $fotoProduk ?>" alt="<?= htmlspecialchars($p['nama']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition">
                                <div class="absolute inset-0 bg-brand-900/30 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-[10px] font-bold">🔍 Lihat</div>
                            <?php else: ?>
                                <span class="text-3xl group-hover:scale-110 transition"><?= htmlspecialchars($p['ikon'] ?: '🥤') ?></span>
                            <?php endif; ?>
                        </div>
                        <span class="text-[10px] font-semibold text-brand-800 bg-brand-100 px-2 py-0.5 rounded-md"><?= htmlspecialchars($kat) ?></span>
                        <h3 class="font-serif font-semibold text-[15px] text-ink-900 mt-1.5 leading-snug min-h-[2.25rem]"><?= htmlspecialchars($p['nama']) ?></h3>
                    </div>
                    <div class="mt-3 flex items-center justify-between">
                        <span class="text-sm font-bold text-tea-600">Rp <?= number_format($p['harga'] ?? 0, 0, ',', '.') ?></span>
                        <div class="area-aksi"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <p id="menu-kosong-cari" class="hidden text-center py-10 text-ink-300 text-xs">😕 Menu tidak ditemukan.</p>

        <?php endif; ?>
    </main>

    <!-- Floating Keranjang -->
    <div id="cart-bar" class="fixed bottom-4 left-4 right-4 max-w-md mx-auto bg-brand-900 text-white p-4 rounded-2xl shadow-2xl flex items-center justify-between hidden z-30 transition-all">
        <div>
            <p class="text-xs text-white/60"><span id="cart-count">0</span> item dipilih</p>
            <p class="text-base font-serif font-semibold" id="cart-total">Rp 0</p>
        </div>
        <button type="button" onclick="bukaModal()" class="bg-tea-500 hover:bg-tea-600 text-brand-900 px-5 py-2.5 rounded-xl font-bold text-sm shadow transition">Lihat Keranjang</button>
    </div>

    <!-- Modal Detail Gambar / Produk (Popup Besar) -->
    <div id="modal-detail" class="fixed inset-0 bg-ink-900/70 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-sm rounded-3xl shadow-2xl overflow-hidden p-6 relative animate-in fade-in zoom-in duration-200">
            <button type="button" onclick="tutupDetailProduk()" class="absolute top-4 right-4 text-ink-300 hover:text-ink-900 font-bold text-lg bg-ink-900/5 w-8 h-8 rounded-full flex items-center justify-center transition">✕</button>
            <div id="detail-konten" class="text-center pt-2">
                <!-- Konten dinamis dimuat via JS -->
            </div>
        </div>
    </div>

    <!-- Modal Keranjang + Data Pembeli -->
    <div id="modal-checkout" class="fixed inset-0 bg-ink-900/60 backdrop-blur-sm z-50 hidden flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div class="bg-white w-full max-w-md rounded-t-[2rem] sm:rounded-3xl shadow-2xl max-h-[90vh] overflow-y-auto">

            <div id="langkah-keranjang" class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-serif font-semibold text-lg text-ink-900">Keranjang & Pengiriman</h3>
                    <button type="button" onclick="tutupModal()" class="text-ink-300 hover:text-ink-900 font-bold text-lg transition">✕</button>
                </div>

                <div id="daftar-keranjang" class="mb-3"></div>

                <div class="flex justify-between items-center py-3 border-t border-b border-ink-900/8 mb-4">
                    <span class="text-xs font-semibold text-ink-500">Total Pesanan</span>
                    <span id="modal-total" class="text-base font-serif font-bold text-brand-800">Rp 0</span>
                </div>

                <form id="form-order" onsubmit="kirimPesanan(event)">
                    <p class="text-xs font-semibold text-ink-500 mb-2">Detail Pengiriman</p>
                    <div class="space-y-3 mb-4">
                        <div>
                            <label class="text-xs font-medium text-ink-500">Nama Pemesan</label>
                            <input type="text" id="nama" required class="w-full mt-1 bg-ink-900/5 border border-ink-900/10 rounded-xl px-3.5 py-2.5 text-sm text-ink-900 focus:outline-none focus:bg-white focus:border-brand-800 focus:ring-2 focus:ring-brand-800/10 transition" placeholder="Contoh: Bu Siti">
                        </div>
                        <div>
                            <label class="text-xs font-medium text-ink-500">Blok / Nomor Rumah</label>
                            <input type="text" id="no_rumah" required class="w-full mt-1 bg-ink-900/5 border border-ink-900/10 rounded-xl px-3.5 py-2.5 text-sm text-ink-900 focus:outline-none focus:bg-white focus:border-brand-800 focus:ring-2 focus:ring-brand-800/10 transition" placeholder="Contoh: Blok C2 No. 12">
                        </div>
                        <div>
                            <label class="text-xs font-medium text-ink-500">Nomor WhatsApp (opsional)</label>
                            <input type="text" id="no_hp" class="w-full mt-1 bg-ink-900/5 border border-ink-900/10 rounded-xl px-3.5 py-2.5 text-sm text-ink-900 focus:outline-none focus:bg-white focus:border-brand-800 focus:ring-2 focus:ring-brand-800/10 transition" placeholder="08123456xxx">
                        </div>
                        <div>
                            <label class="text-xs font-medium text-ink-500">Catatan / Request Khusus (opsional)</label>
                            <textarea id="catatan" rows="2" class="w-full mt-1 bg-ink-900/5 border border-ink-900/10 rounded-xl px-3.5 py-2 text-sm text-ink-900 focus:outline-none focus:bg-white focus:border-brand-800 focus:ring-2 focus:ring-brand-800/10 transition" placeholder="Contoh: Es sedikit ya kak"></textarea>
                        </div>
                    </div>
                    <button type="submit" id="btn-kirim" class="w-full bg-brand-800 text-white py-3 rounded-xl font-bold text-sm shadow-lg hover:bg-brand-900 transition disabled:opacity-60 disabled:cursor-not-allowed">Kirim Pesanan</button>
                </form>
            </div>

            <div id="langkah-sukses" class="p-8 text-center hidden">
                <div class="w-14 h-14 rounded-full bg-brand-100 text-brand-800 flex items-center justify-center mx-auto mb-4 text-2xl">✓</div>
                <h3 class="font-serif font-semibold text-lg text-ink-900 mb-1">Pesanan terkirim</h3>
                <p class="text-sm text-ink-500 mb-6">Penjual sudah menerima pesananmu. Mohon tunggu di rumah ya.</p>
                <button type="button" onclick="pesanLagi()" class="w-full bg-brand-800 text-white py-3 rounded-xl font-bold text-sm shadow-lg hover:bg-brand-900 transition">Tutup</button>
            </div>

        </div>
    </div>

    <script>
        let cart = {};
        const kartuRenderers = {};
        let intervalTracking;
        let timerAutoTerima = null; // timer 2 menit: auto-konfirmasi "diterima" kalau pembeli tidak klik manual

        // Fungsi Memaparkan Notifikasi Toast Custom
        function tampilkanNotifKustom(judul, pesan, ikon = '🔔') {
            const toast = document.getElementById('toast-notif');
            document.getElementById('toast-title').innerText = judul;
            document.getElementById('toast-message').innerText = pesan;
            document.getElementById('toast-icon').innerText = ikon;

            // Kesan muncul
            toast.classList.remove('hidden');
            setTimeout(() => {
                toast.classList.remove('-translate-y-10', 'opacity-0');
                toast.classList.add('translate-y-0', 'opacity-100');
            }, 10);

            // Hilang secara automatik selepas 5 saat
            setTimeout(() => {
                toast.classList.remove('translate-y-0', 'opacity-100');
                toast.classList.add('-translate-y-10', 'opacity-0');
                setTimeout(() => toast.classList.add('hidden'), 500);
            }, 5000);
        }

        // Cek jika ada pesanan yang sedang dilacak saat halaman dimuat
        window.onload = () => {
            mulaiTracking();
        }

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
                mediaHtml = `<div class="w-full h-64 rounded-2xl overflow-hidden bg-ink-900/5 mb-4 shadow-inner flex items-center justify-center">
                    <img src="uploads_produk/${gambar}" class="w-full h-full object-cover">
                </div>`;
            } else {
                mediaHtml = `<div class="w-full h-48 rounded-2xl bg-brand-100 mb-4 flex items-center justify-center text-6xl shadow-inner">
                    ${ikon || '🥤'}
                </div>`;
            }

            konten.innerHTML = `
                ${mediaHtml}
                <span class="text-xs font-semibold text-brand-800 bg-brand-100 px-2.5 py-1 rounded-md inline-block mb-1">${kategori}</span>
                <h2 class="font-serif text-lg font-semibold text-ink-900 mb-1">${nama}</h2>
                <p class="text-base font-bold text-tea-600 mb-4">Rp ${harga.toLocaleString('id-ID')}</p>
                <button type="button" onclick="tutupDetailProduk()" class="w-full bg-brand-800 text-white py-2.5 rounded-xl font-bold text-xs shadow hover:bg-brand-900 transition">Tutup</button>
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
                    areaAksi.innerHTML = `<button type="button" class="btn-tambah bg-brand-800 hover:bg-brand-900 text-white w-7 h-7 rounded-full flex items-center justify-center text-sm font-bold transition">+</button>`;
                    areaAksi.querySelector('.btn-tambah').addEventListener('click', () => ubahJumlah(1));
                } else {
                    areaAksi.innerHTML = `
                        <div class="flex items-center gap-1 bg-brand-100 rounded-full px-1 py-1">
                            <button type="button" class="btn-kurang w-6 h-6 rounded-full bg-white shadow-sm flex items-center justify-center text-brand-800 font-bold text-xs">−</button>
                            <span class="text-xs font-bold text-brand-800 w-4 text-center">${qty}</span>
                            <button type="button" class="btn-tambah w-6 h-6 rounded-full bg-brand-800 text-white shadow-sm flex items-center justify-center font-bold text-xs">+</button>
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
                wadah.innerHTML = '<p class="text-xs text-ink-300 text-center py-6">Keranjang masih kosong.</p>';
            } else {
                let html = '';
                idList.forEach(id => {
                    const item = cart[id];
                    const subtotal = item.harga * item.jumlah;
                    total += subtotal;
                    html += `
                    <div class="flex items-center gap-2.5 py-2.5 border-b border-ink-900/8 last:border-0">
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold text-ink-900 truncate">${item.nama}</p>
                            <p class="text-[11px] text-ink-300">Rp ${item.harga.toLocaleString('id-ID')}</p>
                        </div>
                        <span class="text-xs font-bold text-tea-600 w-16 text-right">Rp ${subtotal.toLocaleString('id-ID')}</span>
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
            
            // Isi otomatis nama & rumah jika sebelumnya pernah pesan (dari fitur Live Tracking)
            let dataLama = JSON.parse(localStorage.getItem('pelanggan_es_teh'));
            if(dataLama) {
                document.getElementById('nama').value = dataLama.nama || '';
                document.getElementById('no_rumah').value = dataLama.rumah || '';
            }
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
                    c.classList.remove('aktif', 'bg-brand-800', 'text-white');
                    c.classList.add('bg-white', 'text-ink-500', 'border', 'border-ink-900/10');
                });
                chip.classList.add('aktif', 'bg-brand-800', 'text-white');
                chip.classList.remove('bg-white', 'text-ink-500', 'border', 'border-ink-900/10');
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

            let dataInput = {
                nama: document.getElementById('nama').value.trim(),
                no_rumah: document.getElementById('no_rumah').value.trim(),
                no_hp: document.getElementById('no_hp').value.trim(),
                catatan: document.getElementById('catatan').value.trim(),
                items: itemsUntukServer
            };

            fetch('api.php?aksi=simpan', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(dataInput)
            })
            .then(res => res.json())
            .then(response => {
                if (response.status === 'sukses') {
                    document.getElementById('langkah-keranjang').classList.add('hidden');
                    document.getElementById('langkah-sukses').classList.remove('hidden');

                    // Pesanan baru dikirim -> batalkan timer auto-konfirmasi milik pesanan SEBELUMNYA
                    // (kalau ada) supaya tidak salah menembak pesanan yang baru ini.
                    if (timerAutoTerima) { clearTimeout(timerAutoTerima); timerAutoTerima = null; }

                    // Simpan identitas & ID pesanan ke memori HP pembeli untuk Live Tracking
                    localStorage.setItem('pelanggan_es_teh', JSON.stringify({
                        id_pesanan: response.pesanan_id, // <-- ID Pesanan disimpan di sini
                        nama: dataInput.nama,
                        rumah: dataInput.no_rumah,
                        status_terakhir: 'Pending'
                    }));
                    
                    mulaiTracking();
                } else {
                    tombolKirim.disabled = false;
                    tombolKirim.innerText = 'Kirim Pesanan';
                    tampilkanNotifKustom('Gagal', 'Gagal mengirim pesanan.', '❌');
                }
            })
        }

        function pesanLagi() { location.reload(); }

        // ================= FUNGSI LIVE TRACKING =================
        function mulaiTracking() {
            let dataPelanggan = JSON.parse(localStorage.getItem('pelanggan_es_teh'));
            if (dataPelanggan) {
                document.getElementById('banner-tracking').classList.remove('hidden');
                document.getElementById('track-nama').innerText = dataPelanggan.nama;
                
                // Cek status ke server setiap 3 detik
                if(intervalTracking) clearInterval(intervalTracking);
                intervalTracking = setInterval(() => cekStatusDiServer(dataPelanggan), 3000);
                cekStatusDiServer(dataPelanggan);
            }
        }

        function konfirmasiDiterima() {
            // Batalkan timer auto-konfirmasi 2 menit karena pembeli sudah klik manual duluan
            if (timerAutoTerima) { clearTimeout(timerAutoTerima); timerAutoTerima = null; }

            let dataPelanggan = JSON.parse(localStorage.getItem('pelanggan_es_teh'));
            
            // Kirim status 'Diterima' ke database via API agar admin mendeteksi notifikasi
            if (dataPelanggan && dataPelanggan.id_pesanan) {
                fetch(`api.php?aksi=update_status&id=${dataPelanggan.id_pesanan}&status=Diterima`)
                .then(res => res.json())
                .catch(e => console.log(e));
            }

            let successSound = new Audio('https://assets.mixkit.co/active_storage/sfx/1114/1114-preview.mp3');
            successSound.play().catch(e => console.log(e));
            
            tampilkanNotifKustom('Terima Kasih!', 'Pesanan telah diterima. Selamat menikmati!', '🥰');
            selesaikanTracking();
        }

        function cekStatusDiServer(pelanggan) {
            fetch('api.php?aksi=ambil_pesanan')
            .then(res => res.json())
            .then(data => {
                // Cocokkan berdasarkan ID pesanan (paling akurat & tidak mungkin salah).
                // ID ini sudah disimpan sejak pesanan pertama kali dikirim (lihat kirimPesanan()).
                let pesananSaya = null;
                if (pelanggan.id_pesanan) {
                    pesananSaya = data.find(p => String(p.id) === String(pelanggan.id_pesanan));
                }

                // Fallback lama (nama + alamat) hanya dipakai kalau ID belum tersedia,
                // misalnya untuk pelanggan yang sempat menyimpan data sebelum perbaikan ini.
                if (!pesananSaya) {
                    pesananSaya = data.slice().reverse().find(p => p.nama_pembeli.toLowerCase() === pelanggan.nama.toLowerCase() && p.no_rumah.toLowerCase() === pelanggan.rumah.toLowerCase());
                }
                
                let teksStatus = document.getElementById('track-status');
                let dotPing = document.getElementById('ping-dot');
                let dotSolid = document.getElementById('solid-dot');
                let btnTutupTrack = document.getElementById('btn-tutup-track');

                if (pesananSaya && pesananSaya.status !== 'Selesai') {
                    let statusSekarang = pesananSaya.status;

                    if (statusSekarang === 'Diproses') {
                        teksStatus.innerText = 'Sedang Diproses 🍳';
                        teksStatus.className = 'text-sm font-serif font-extrabold text-brand-800';
                        dotPing.className = 'animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-800 opacity-75';
                        dotSolid.className = 'relative inline-flex rounded-full h-3 w-3 bg-brand-800';
                    } else {
                        teksStatus.innerText = 'Menunggu Konfirmasi ⏳';
                        teksStatus.className = 'text-sm font-serif font-extrabold text-tea-600';
                        dotPing.className = 'animate-ping absolute inline-flex h-full w-full rounded-full bg-tea-500 opacity-75';
                        dotSolid.className = 'relative inline-flex rounded-full h-3 w-3 bg-tea-600';
                    }

                    // Tampilan tombol standar saat belum selesai
                    btnTutupTrack.innerText = 'Tutup';
                    btnTutupTrack.className = 'text-[10px] bg-white hover:bg-paper text-ink-500 px-3 py-2 rounded-xl border border-ink-900/10 transition shadow-sm';
                    btnTutupTrack.onclick = selesaikanTracking;
                    
                    // JIKA ADMIN BARU SAJA KLIK PROSES
                    if (statusSekarang === 'Diproses' && pelanggan.status_terakhir !== 'Diproses') {
                        let notifSound = new Audio('https://assets.mixkit.co/active_storage/sfx/933/933-preview.mp3');
                        notifSound.play().catch(e => console.log(e));
                        
                        tampilkanNotifKustom('Pesanan Diproses!', 'Yey! Pesanan sedang dibuat oleh Admin.', '👨‍🍳');
                        
                        pelanggan.status_terakhir = 'Diproses';
                        localStorage.setItem('pelanggan_es_teh', JSON.stringify(pelanggan));
                    }
                } else {
                    // JIKA PESANAN TIDAK ADA (Berarti API telah memindahkannya karena admin klik Selesai)
                    // ATAU JIKA STATUSNYA SELESAI
                    if (pelanggan.status_terakhir === 'Diproses' || pelanggan.status_terakhir === 'Pending' || pelanggan.status_terakhir === 'Selesai') {
                        
                        // Banner pelacakan berubah jadi biru "Sedang Diantar" (TIDAK HILANG)
                        teksStatus.innerText = 'Sedang Diantar 🛵';
                        teksStatus.className = 'text-sm font-serif font-extrabold text-blue-600';
                        dotPing.className = 'animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-500 opacity-75';
                        dotSolid.className = 'relative inline-flex rounded-full h-3 w-3 bg-blue-600';

                        // Mengubah Tombol "Tutup" menjadi "Pesanan Diterima ✓"
                        btnTutupTrack.innerText = 'Pesanan Diterima ✓';
                        btnTutupTrack.className = 'text-xs bg-brand-800 hover:bg-brand-900 text-white font-bold px-4 py-2.5 rounded-xl shadow-lg transition animate-pulse';
                        btnTutupTrack.onclick = konfirmasiDiterima;

                        // Mainkan suara & notifikasi toast HANYA sekali saat transisi pertama kali
                        if (pelanggan.status_terakhir !== 'Selesai') {
                            let notifSound = new Audio('https://assets.mixkit.co/active_storage/sfx/933/933-preview.mp3');
                            notifSound.play().catch(e => console.log(e));
                            
                            tampilkanNotifKustom('Pesanan Diantar!', 'Minuman Anda sedang dalam perjalanan ke rumah. Harap klik konfirmasi jika sudah diterima.', '🛵');
                            
                            pelanggan.status_terakhir = 'Selesai';
                            localStorage.setItem('pelanggan_es_teh', JSON.stringify(pelanggan));

                            // Kalau dalam 2 menit pembeli tidak klik "Pesanan Diterima" secara manual,
                            // anggap otomatis sudah diterima (supaya pesanan tidak menggantung terus).
                            // Simpan ID pesanan yang dituju timer ini secara spesifik, supaya kalau
                            // pembeli keburu pesan lagi (pesanan baru) sebelum 2 menit habis,
                            // timer lama TIDAK salah menembak pesanan yang baru itu.
                            const idPesananUntukTimerIni = pelanggan.id_pesanan;
                            if (timerAutoTerima) clearTimeout(timerAutoTerima);
                            timerAutoTerima = setTimeout(() => {
                                let dataTerkini = JSON.parse(localStorage.getItem('pelanggan_es_teh'));
                                if (dataTerkini && String(dataTerkini.id_pesanan) === String(idPesananUntukTimerIni)) {
                                    konfirmasiDiterima();
                                }
                            }, 2 * 60 * 1000);
                        }
                        
                        // Hentikan request interval ke server, biarkan menunggu pembeli klik terima
                        clearInterval(intervalTracking);
                    } else {
                        // Jika status_terakhir kosong / error tak terduga
                        selesaikanTracking();
                    }
                }
            }).catch(e => console.log(e));
        }

        function selesaikanTracking() {
            clearInterval(intervalTracking);
            if (timerAutoTerima) { clearTimeout(timerAutoTerima); timerAutoTerima = null; }
            localStorage.removeItem('pelanggan_es_teh');
            document.getElementById('banner-tracking').classList.add('hidden');
        }
    </script>
</body>
</html>