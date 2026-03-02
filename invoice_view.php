<?php
include 'koneksi.php';
$invoice_code = $_GET['code'] ?? 'INV-2023-001';

// Query Join untuk mengambil detail invoice
$query = $conn->query("
    SELECT i.*, o.total_harga, u.nama_lengkap, u.alamat, o.tanggal_pesan 
    FROM invoices i 
    JOIN orders o ON i.order_id = o.id 
    JOIN users u ON o.customer_id = u.id 
    WHERE i.nomor_invoice = '$invoice_code'
");
$data = $query->fetch_assoc();

if(!$data) $data = [
    'nomor_invoice' => 'INV-2023-001',
    'nama_lengkap' => 'Toko Baju ABC',
    'alamat' => 'Jl. Sudirman No. 12, Jakarta',
    'total_harga' => 450000,
    'tanggal_pesan' => date('Y-m-d'),
    'tanggal_jatuh_tempo' => date('Y-m-d', strtotime('+14 days')),
    'status_bayar' => 'belum_bayar'
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice <?= $data['nomor_invoice']; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none; }
            body { background: white; }
            .shadow-xl { box-shadow: none; }
        }
    </style>
</head>
<body class="bg-gray-100 py-10">

    <div class="max-w-3xl mx-auto bg-white p-10 rounded-xl shadow-xl">
        
        <!-- Header Invoice -->
        <div class="flex justify-between items-start border-b pb-8 mb-8">
            <div>
                <h1 class="text-4xl font-bold text-gray-800 tracking-tight">INVOICE</h1>
                <p class="text-gray-500 mt-1"><?= $data['nomor_invoice']; ?></p>
            </div>
            <div class="text-right">
                <h2 class="font-bold text-xl text-indigo-600">PT. GAYA FASHION</h2>
                <p class="text-sm text-gray-500">Jl. Mode Indonesia No. 88<br>Jakarta Selatan</p>
            </div>
        </div>

        <!-- Info Pelanggan -->
        <div class="flex justify-between mb-10">
            <div>
                <p class="text-gray-500 text-sm uppercase font-semibold">Tagihan Kepada</p>
                <h3 class="font-bold text-lg"><?= $data['nama_lengkap']; ?></h3>
                <p class="text-gray-600 text-sm w-48"><?= $data['alamat']; ?></p>
            </div>
            <div class="text-right">
                <div class="mb-2">
                    <p class="text-gray-500 text-sm uppercase font-semibold">Tanggal Pesan</p>
                    <p class="font-medium"><?= date('d M Y', strtotime($data['tanggal_pesan'])); ?></p>
                </div>
                <div>
                    <p class="text-gray-500 text-sm uppercase font-semibold">Jatuh Tempo</p>
                    <p class="font-medium text-red-500"><?= date('d M Y', strtotime($data['tanggal_jatuh_tempo'])); ?></p>
                </div>
            </div>
        </div>

        <!-- Tabel Item (Dummy Items) -->
        <table class="w-full mb-8">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Deskripsi</th>
                    <th class="text-center py-3 px-4 text-sm font-semibold text-gray-600">Qty</th>
                    <th class="text-right py-3 px-4 text-sm font-semibold text-gray-600">Harga</th>
                    <th class="text-right py-3 px-4 text-sm font-semibold text-gray-600">Total</th>
                </tr>
            </thead>
            <tbody class="text-sm">
                <tr class="border-b">
                    <td class="py-3 px-4">Kaos Polos Premium - Hitam</td>
                    <td class="py-3 px-4 text-center">10</td>
                    <td class="py-3 px-4 text-right">50.000</td>
                    <td class="py-3 px-4 text-right">500.000</td>
                </tr>
                <tr class="border-b">
                    <td class="py-3 px-4">Kemeja Flannel - Merah</td>
                    <td class="py-3 px-4 text-center">2</td>
                    <td class="py-3 px-4 text-right">150.000</td>
                    <td class="py-3 px-4 text-right">300.000</td>
                </tr>
                <!-- Total -->
                <tr>
                    <td colspan="2" class="py-4 px-4 text-right font-bold text-gray-600">TOTAL</td>
                    <td colspan="2" class="py-4 px-4 text-right font-bold text-2xl text-indigo-600">Rp <?= number_format($data['total_harga'], 0, ',', '.'); ?></td>
                </tr>
            </tbody>
        </table>

        <!-- Footer -->
        <div class="border-t pt-8 text-center text-gray-500 text-sm">
            <p>Terima kasih telah bekerja sama dengan kami.</p>
            <p class="mt-2">Jika ada pertanyaan, silakan hubungi finance@gayafashion.com</p>
        </div>

        <!-- Tombol Cetak -->
        <div class="mt-8 text-center no-print">
            <button onclick="window.print()" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition shadow-lg">
                Cetak / Save as PDF
            </button>
            <a href="index.php" class="ml-4 text-gray-500 hover:text-gray-700">Kembali ke Dashboard</a>
        </div>
    </div>

</body>
</html>