<?php
include 'koneksi.php';

// --- LOGIKA LOGIN SINGKAT (Untuk Demo) ---
if (isset($_GET['login'])) {
    $username = $_POST['username'];
    // Di produksi, gunakan password_verify()
    $query = $conn->query("SELECT * FROM users WHERE username = '$username'");
    $user = $query->fetch_assoc();
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nama'] = $user['nama_lengkap'];
        $_SESSION['role'] = $user['role'];
    }
    header("Location: index.php");
    exit;
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

// Ambil data untuk dashboard
$role = $_SESSION['role'] ?? 'guest';
$user_id = $_SESSION['user_id'] ?? 0;

// Query Data
$products = $conn->query("SELECT * FROM products LIMIT 6");
$orders = $conn->query("SELECT o.*, u.nama_lengkap as customer_name FROM orders o JOIN users u ON o.customer_id = u.id");
$invoices = $conn->query("SELECT i.*, o.total_harga, u.nama_lengkap FROM invoices i JOIN orders o ON i.order_id = o.id JOIN users u ON o.customer_id = u.id");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PT. Gaya Fashion - B2B Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-gray-50 text-gray-800">

    <!-- LOGIN SCREEN (Jika belum login) -->
    <?php if ($role == 'guest'): ?>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-600 to-purple-700">
        <div class="bg-white p-8 rounded-xl shadow-2xl w-96">
            <h2 class="text-2xl font-bold text-center mb-6 text-indigo-600">Masuk Sistem</h2>
            <form action="?login=1" method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Username</label>
                    <select name="username" class="w-full mt-1 p-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="admin">Admin</option>
                        <option value="sales1">Sales</option>
                        <option value="finance1">Finance</option>
                        <option value="gudang1">Gudang</option>
                        <option value="toko_abc">Customer (Toko ABC)</option>
                    </select>
                </div>
                <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded-lg hover:bg-indigo-700 transition">Login</button>
                <p class="text-xs text-center text-gray-400 mt-4">Password: password123 (diabaikan untuk demo)</p>
            </form>
        </div>
    </div>

    <!-- DASHBOARD (Jika sudah login) -->
    <?php else: ?>
    <div class="flex h-screen overflow-hidden">
        
        <!-- SIDEBAR -->
        <aside class="w-64 bg-white border-r border-gray-200 hidden md:flex flex-col">
            <div class="p-6 border-b">
                <h1 class="text-xl font-bold text-indigo-600">GAYA FASHION</h1>
                <p class="text-xs text-gray-500">Welcome, <?= $_SESSION['nama']; ?></p>
            </div>
            <nav class="flex-1 p-4 space-y-2">
                <a href="#" class="flex items-center px-4 py-2 text-gray-700 bg-gray-100 rounded-lg font-medium">
                    <span>🏠</span>&nbsp; Dashboard
                </a>
                
                <!-- Menu Berdasarkan Role -->
                <?php if (in_array($role, ['admin', 'sales', 'customer'])): ?>
                <a href="#products" class="flex items-center px-4 py-2 text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 rounded-lg transition">
                    <span>👕</span>&nbsp; Katalog Produk
                </a>
                <?php endif; ?>

                <?php if (in_array($role, ['admin', 'sales', 'finance'])): ?>
                <a href="#orders" class="flex items-center px-4 py-2 text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 rounded-lg transition">
                    <span>📦</span>&nbsp; Data Pesanan
                </a>
                <?php endif; ?>

                <?php if (in_array($role, ['admin', 'finance', 'customer'])): ?>
                <a href="#invoice" class="flex items-center px-4 py-2 text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 rounded-lg transition">
                    <span>🧾</span>&nbsp; Invoice
                </a>
                <?php endif; ?>
            </nav>
            <div class="p-4 border-t">
                <a href="?logout=1" class="block text-center text-sm text-red-500 hover:text-red-700">Logout</a>
            </div>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="flex-1 overflow-y-auto p-8">
            <header class="flex justify-between items-center mb-8">
                <h2 class="text-3xl font-bold text-gray-800">
                    <?php 
                        if($role == 'admin') echo "Overview Perusahaan";
                        elseif($role == 'sales') echo "Area Penjualan";
                        elseif($role == 'finance') echo "Manajemen Keuangan";
                        elseif($role == 'gudang') echo "Manajemen Stok";
                        else echo "Katalog Partner";
                    ?>
                </h2>
                <div class="bg-white px-4 py-2 rounded-full shadow-sm border text-sm font-semibold text-indigo-600 uppercase tracking-wide">
                    Role: <?= $role; ?>
                </div>
            </header>

            <!-- KATALOG PRODUK (Untuk Sales & Customer) -->
            <?php if (in_array($role, ['admin', 'sales', 'customer'])): ?>
            <section id="products" class="mb-12">
                <h3 class="text-xl font-semibold mb-4">Koleksi Terbaru</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <?php while($p = $products->fetch_assoc()): ?>
                    <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition overflow-hidden border border-gray-100">
                        <div class="h-48 bg-gray-200 w-full object-cover">
                            <img src="<?= $p['gambar']; ?>" class="w-full h-full object-cover">
                        </div>
                        <div class="p-4">
                            <h4 class="font-bold text-lg"><?= $p['nama_produk']; ?></h4>
                            <p class="text-gray-500 text-sm mb-2">Stok: <?= $p['stok']; ?></p>
                            <div class="flex justify-between items-center mt-4">
                                <span class="text-indigo-600 font-bold">Rp <?= number_format($p['harga'], 0, ',', '.'); ?></span>
                                <?php if($role == 'customer'): ?>
                                <button class="bg-indigo-600 text-white px-3 py-1 rounded text-sm hover:bg-indigo-700">Beli</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </section>
            <?php endif; ?>

            <!-- TABEL INVOICE (Menu Utama Request) -->
            <?php if (in_array($role, ['admin', 'finance', 'customer'])): ?>
            <section id="invoice" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-semibold">Daftar Invoice</h3>
                    <button onclick="window.location.href='cetak_invoice.php'" class="text-sm bg-gray-800 text-white px-4 py-2 rounded hover:bg-gray-900">+ Buat Invoice Baru</button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-gray-500 text-sm border-b">
                                <th class="py-3 px-4">No. Invoice</th>
                                <th class="py-3 px-4">Customer</th>
                                <th class="py-3 px-4">Total Tagihan</th>
                                <th class="py-3 px-4">Jatuh Tempo</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm">
                            <?php while($inv = $invoices->fetch_assoc()): ?>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="py-3 px-4 font-medium"><?= $inv['nomor_invoice']; ?></td>
                                <td class="py-3 px-4"><?= $inv['nama_lengkap']; ?></td>
                                <td class="py-3 px-4">Rp <?= number_format($inv['total_harga'], 0, ',', '.'); ?></td>
                                <td class="py-3 px-4"><?= $inv['tanggal_jatuh_tempo']; ?></td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-1 rounded text-xs font-semibold 
                                        <?= $inv['status_bayar'] == 'lunas' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                                        <?= strtoupper($inv['status_bayar']); ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <a href="invoice_view.php?code=<?= $inv['nomor_invoice']; ?>" class="text-indigo-600 hover:underline">Lihat</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </section>
            <?php endif; ?>

        </main>
    </div>
    <?php endif; ?>
</body>
</html>