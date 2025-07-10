<?php
require_once("controller.php");

if (isset($_POST['delete_item'])) {
    deleteFromCart($_POST['delete_item']);
}
$cart = viewCart(); 

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>View Cart</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 p-6">

    <h1 class="text-2xl font-bold text-center text-blue-700 mb-6">Your Cart</h1>

    <?php if (empty($cart)): ?>
        <p class="text-center text-gray-500">Your cart is empty.</p>
    <?php else: ?>
        <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-md p-6">
            <table class="w-full table-auto">
                <thead>
                    <tr class="text-left border-b">
                        <th class="pb-2">Product</th>
                        <th class="pb-2">Price</th>
                        <th class="pb-2">Quantity</th>
                        <th class="pb-2">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart as $item): ?>
                        <tr class="border-b">
                            <td class="py-2"><?= ($item['name']) ?></td>
                            <td class="py-2">Rp. <?= number_format($item['price']) ?></td>
                            <td class="py-2 pl-7"><?= $item['quantity'] ?></td>
                            <td class="py-2">Rp. <?= number_format($item['subtotal']) ?></td>
                            <td class="py-2">
                                <form method="post" action="transaction_items.php">
                                    <input type="hidden" name="delete_item" value="<?= $item['name'] ?>">
                                    <button type="submit" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition">Remove</button>
                                </form>

                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
    
    <div class="flex justify-center gap-4 mt-6">
    <form action="index.php" method="post">
        <button class="bg-blue-500 text-white py-2 px-6 rounded hover:bg-blue-600 transition">Order lagi</button>
    </form>
    <form action="transaction.php" method="post">
        <button class="bg-blue-500 text-white py-2 px-6 rounded hover:bg-blue-600 transition">Bayar</button>
    </form>
</div>

</body>

</html>