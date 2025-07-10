<?php
include_once("controller.php");
$conn = my_connectDB();
$products = $conn->query("SELECT * FROM products");
if (isset($_POST["add_to_cart"])) {
    addToCart();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Vending Machine</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen p-6 border-[16px] border-blue-300 box-border">

    <div class="text-center my-6">
        <img src="img/logo.png" alt="Logo" class="w-200 h-200 inline-block">
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 max-w-6xl mx-auto">
        <?php while ($row = $products->fetch_assoc()): ?>
            <div class="product-card bg-white rounded-xl shadow-md p-4 text-center transform transition duration-300">

                <img src="img/<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['name']) ?>"
                    class="h-40 w-full object-contain mb-4 rounded">

                <h2 class="text-xl font-semibold text-gray-800"><?= ($row['name']) ?></h2>

                <p class="text-gray-600 mb-2">Rp. <?= number_format($row['price']) ?></p>

                <p class="text-sm text-gray-400 mb-4">Stock: <?= $row['stock'] ?></p>

                <form action="index.php" method="post" class="space-y-2" onsubmit="alert('Item added to cart!')">
                    <input type="hidden" name="product_id" value="<?= $row['product_id'] ?>">
                    <input type="hidden" name="quantity" value="1" required>
                    <button type="submit"
                        class="block w-full bg-green-500 text-white font-semibold py-2 rounded hover:bg-green-600 transition"
                        name="add_to_cart">Add to Cart</button>
                </form>
            </div>
        <?php endwhile; ?>
    </div>

    <form action="transaction_items.php" method="post">
        <div class="text-center mt-8 py-3">
            <button class="inline-block bg-blue-500 text-white py-2 px-6 rounded hover:bg-blue-600 transition"
                name="view_cart" type="submit">
                View Cart
            </button>
        </div>
    </form>

    <script>
        document.querySelectorAll('.product-card').forEach(card => {
            card.addEventListener('mouseenter', () => {
                card.classList.add('scale-105', 'shadow-2xl');
            });

            card.addEventListener('mouseleave', () => {
                card.classList.remove('scale-105', 'shadow-2xl');
            });
        });
    </script>
</body>

</html>