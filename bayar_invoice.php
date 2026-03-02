<?php
include 'koneksi.php';
session_start();

// Cek Role Finance atau Admin
if (!in_array($_SESSION['role'], ['admin', 'finance'])) {
    echo "<script>alert('Hanya Finance yang bisa melakukan pembayaran!'); window.location='index.php';</script>";
    exit;
}

$invoice_code = $_GET['code'] ?? '';

// Proses Update
if (isset($_GET['bayar'])) {
    $conn->query("UPDATE invoices SET status_bayar = 'lunas' WHERE nomor_invoice = '$invoice_code'");
    echo "<script>alert('Pembayaran Berhasil!'); window.location='index.php#invoice';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Konfirmasi Pembayaran</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">

    <div class="bg-white p-8 rounded-xl shadow-lg text-center max-w-md">
        <div class="mb-4 text-green-500 text-6xl">💳</div>
        <h2 class="text-2xl font-bold mb-2">Konfirmasi Pembayaran</h2>
        <p class="text-gray-600 mb-6">Apakah Anda yakin invoice <strong><?= $invoice_code; ?></strong sudah lunas?</p>
        
        <div class="flex gap-4 justify-center">
            <a href="?bayar=1&code=<?= $invoice_code; ?>" class="bg-green-500 text-white px-6 py-2 rounded-lg hover:bg-green-600 font-semibold">
                Ya, Lunas
            </a>
            <a href="index.php#invoice" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400">
                Batal
            </a>
        </div>
    </div>

</body>
</html>