<?php
include_once("controller.php");

$conn = my_connectDB();

if (isset($_POST['finish_payment'])) {
    checkout(); // your modular function to handle stock updates & mark transaction as paid
}

if (isset($_POST['cancel_transaction'])) {
    // Cancel logic: delete latest unpaid transaction and its items
    $cancel_sql = "SELECT transaction_id FROM transactions WHERE status = 'unpaid' ORDER BY transaction_id DESC LIMIT 1";
    $cancel_result = mysqli_query($conn, $cancel_sql);
    if ($cancel_result && mysqli_num_rows($cancel_result) > 0) {
        $cancel_data = mysqli_fetch_assoc($cancel_result);
        $transaction_id = $cancel_data['transaction_id'];

        // Delete related items
        mysqli_query($conn, "DELETE FROM transaction_items WHERE transaction_id = $transaction_id");

        // Delete the transaction
        mysqli_query($conn, "DELETE FROM transactions WHERE transaction_id = $transaction_id");
    }

    my_closeDB($conn);
    header("Location: index.php");
    exit;
}

$total = 0;

// Fetch latest unpaid transaction
$sql = "SELECT * FROM transactions WHERE status = 'unpaid' ORDER BY transaction_id DESC LIMIT 1";
$result = mysqli_query($conn, $sql);
$transaction = mysqli_fetch_assoc($result);

if ($transaction) {
    $total = $transaction['total_amount'];
}

my_closeDB($conn);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Checkout</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen p-6">
    <div class="max-w-xl mx-auto bg-white p-6 rounded-xl shadow-md text-center">
        <div class="text-center my-6">
            <img src="img/logo.png" alt="Logo" class="w-100 h-100 mx-auto">
        </div>

        <h1 class="text-2xl font-bold mb-4">Checkout</h1>

        <p class="text-lg mb-2">Total Price:</p>
        <p class="text-3xl font-bold text-green-600 mb-6">Rp. <?= number_format($total) ?></p>

        <p class="mb-2 text-gray-600">Scan the QR code below to pay:</p>
        <img src="img/qr.png" alt="QR Code" class="mx-auto mb-6 w-48 h-48">

        <form method="post" onsubmit="return showThankYou()">
            <div class="flex justify-center gap-4">
                <button name="finish_payment" type="submit"
                    class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600 transition">
                    Finish Payment
                </button>
                <button name="cancel_transaction" type="submit"
                    class="bg-red-500 text-white px-6 py-2 rounded hover:bg-red-600 transition">
                    Cancel Transaction
                </button>
            </div>
        </form>
    </div>

    <script>
        function showThankYou() {
            alert('Terima kasih!');
            return true;
        }
    </script>
</body>

</html>
