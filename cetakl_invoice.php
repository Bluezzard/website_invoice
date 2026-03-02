<?php
include 'koneksi.php';
session_start();

// Jika bukan admin/sales, redirect
if (!in_array($_SESSION['role'], ['admin', 'sales'])) {
    echo "<script>alert('Akses Ditolak!'); window.location='index.php';</script>";
    exit;
}

$pesan = "";

// Proses Simpan Pesanan
if (isset($_POST['simpan'])) {
    $customer_id = $_POST['customer_id'];
    $sales_id = $_SESSION['user_id'];
    $produk_id = $_POST['produk_id'];
    $qty = (int)$_POST['qty'];
    
    // Ambil harga produk
    $harga_produk = $conn->query("SELECT harga, nama_produk, stok FROM products WHERE id = $produk_id")->fetch_assoc();
    $total_harga = $harga_produk['harga'] * $qty;

    // Cek Stok
    if ($harga_produk['stok'] < $qty) {
        $pesan = "<div class='bg-red-100 text-red-700 p-3 rounded mb-4'>Stok tidak cukup!</div>";
    } else {
        // 1. Insert ke tabel orders
        $conn->query("INSERT INTO orders (customer_id, sales_id, total_harga, status) VALUES ($customer_id, $sales_id, $total_harga, 'pending')");
        $order_id = $conn->insert_id;

        // 2. Kurangi Stok Gudang
        $conn->query("UPDATE products SET stok = stok - $qty WHERE id = $produk_id");

        // 3. Generate Nomor Invoice (Format: INV-TAHUN-BULAN-ID)
        $no_invoice = "INV-" . date("Ymd") . "-" . $order_id;
        $tgl_jatuh_tempo = date("Y-m-d", strtotime("+7 days"));

        // 4. Insert ke tabel invoices
        $conn->query("INSERT INTO invoices (order_id, nomor_invoice, tanggal_jatuh_tempo, status_bayar) VALUES ($order_id, '$no_invoice', '$tgl_jatuh_tempo', 'belum_bayar')");

        // Redirect ke halaman invoice view
        header("Location: invoice_view.php?code=$no_invoice");
        exit;
    }
}

// Ambil data untuk dropdown
$customers = $conn->query("SELECT * FROM users WHERE role = 'customer'");
$products = $conn->query("SELECT * FROM products WHERE stok > 0");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Buat Pesanan Baru - Gaya Fashion</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">

    <div class="w-full max-w-lg bg-white p-8 rounded-xl shadow-lg">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Buat Pesanan Baru</h2>
            <a href="index.php" class="text-gray-500 hover:text-gray-700">&times;</a>
        </div>

        <?= $pesan; ?>

        <form action="" method="POST" class="space-y-4">
            
            <!-- Pilih Customer -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Customer</label>
                <select name="customer_id" required class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">-- Pilih Customer --</option>
                    <?php while($c = $customers->fetch_assoc()): ?>
                    <option value="<?= $c['id']; ?>"><?= $c['nama_lengkap']; ?> (<?= $c['username']; ?>)</option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Pilih Produk -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Produk</label>
                <select name="produk_id" id="produk_id" required class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="">-- Pilih Produk --</option>
                    <?php while($p = $products->fetch_assoc()): ?>
                    <option value="<?= $p['id']; ?>" data-harga="<?= $p['harga']; ?>" data-stok="<?= $p['stok']; ?>">
                        <?= $p['nama_produk']; ?> - Stok: <?= $p['stok']; ?> - Rp <?= number_format($p['harga'],0,',','.'); ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Jumlah Quantity -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah (Qty)</label>
                <input type="number" name="qty" id="qty" min="1" required class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                <p class="text-xs text-gray-500 mt-1">Maksimal: <span id="max-stok">0</span></p>
            </div>

            <!-- Total Harga (Otomatis) -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-gray-600 text-sm">Total Harga:</p>
                <p class="text-2xl font-bold text-indigo-600" id="total-harga">Rp 0</p>
            </div>

            <button type="submit" name="simpan" class="w-full bg-indigo-600 text-white py-3 rounded-lg font-semibold hover:bg-indigo-700 transition">
                Generate Invoice & Pesan
            </button>
        </form>
    </div>

    <script>
        // Script Kalkulasi Otomatis
        const produkSelect = document.getElementById('produk_id');
        const qtyInput = document.getElementById('qty');
        const totalHarga = document.getElementById('total-harga');
        const maxStokSpan = document.getElementById('max-stok');

        produkSelect.addEventListener('change', function() {
            const option = this.options[this.selectedIndex];
            const harga = option.getAttribute('data-harga');
            const stok = option.getAttribute('data-stok');
            
            maxStokSpan.innerText = stok;
            qtyInput.max = stok;
            hitungTotal();
        });

        qtyInput.addEventListener('input', hitungTotal);

        function hitungTotal() {
            const option = produkSelect.options[produkSelect.selectedIndex];
            const harga = option ? option.getAttribute('data-harga') : 0;
            const qty = qtyInput.value || 0;
            const total = harga * qty;
            
            totalHarga.innerText = 'Rp ' + total.toLocaleString('id-ID');
        }
    </script>
</body>
</html>