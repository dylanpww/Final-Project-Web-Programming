<?php
include_once("controller.php");

// masukkin product baru
if (isset($_POST['insert_product'])) {
    $name = $_POST['name'];
    $price = (int)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $image = $_FILES['image'];

    // upload fotonya
    $image_name = null;
    if ($image && $image['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . "/img/";
        $image_name = basename($image['name']);
        move_uploaded_file($image['tmp_name'], $upload_dir . $image_name);
    }

    insertProduct($name, $price, $stock, $image_name);
    header("Location: admin.php");
    exit();
}

// update product
if (isset($_POST['update_product'])) {
    $product_id = (int)$_POST['product_id'];
    $price = (int)$_POST['price'];
    $stock = (int)$_POST['stock'];
    updateProduct($product_id, $price, $stock);
    header("Location: admin.php");
    exit();
}

// delete product
if (isset($_POST['delete_product'])) {
    $product_id = (int)$_POST['product_id'];
    deleteProduct($product_id);
    header("Location: admin.php");
    exit();
}

// masukkin product ke $products
$products = getProducts();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="p-6 bg-gray-100 min-h-screen">

    <!-- form update product -->
    <section class="mb-12 bg-white p-6 rounded shadow max-w-lg mx-auto">
        <h2 class="text-xl font-semibold mb-4">Add New Product</h2>
        <form action="admin.php" method="post" enctype="multipart/form-data" class="space-y-4">
            <div>
                <label class="block mb-1 font-medium">Name:</label>
                <input type="text" name="name" required
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" />
            </div>
            <div>
                <label class="block mb-1 font-medium">Price:</label>
                <input type="number" name="price" min="0" required
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" />
            </div>
            <div>
                <label class="block mb-1 font-medium">Stock:</label>
                <input type="number" name="stock" min="0" required
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" />
            </div>
            <div>
                <label class="block mb-1 font-medium">Image:</label>
                <input type="file" name="image" accept="image/*"
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" />
            </div>
            <button type="submit" name="insert_product"
                class="bg-green-600 text-white px-5 py-2 rounded hover:bg-green-700 transition">Add Product</button>
        </form>
    </section>

    <!-- product yang ada sekarang -->
    <section class="max-w-5xl mx-auto bg-white rounded shadow p-6">
        <h2 class="text-2xl font-semibold mb-4">Existing Products</h2>
        <table class="table-auto w-full border-collapse border border-gray-300">
            <thead>
                <tr class="bg-blue-600 text-white">
                    <th class="border border-gray-300 px-4 py-2">Product ID</th>
                    <th class="border border-gray-300 px-4 py-2">Name</th>
                    <th class="border border-gray-300 px-4 py-2">Price</th>
                    <th class="border border-gray-300 px-4 py-2">Stock</th>
                    <th class="border border-gray-300 px-4 py-2">Image</th>
                    <th class="border border-gray-300 px-4 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                    <tr class="text-center border border-gray-300">
                        <td class="border border-gray-300 px-2 py-1"><?= htmlspecialchars($p['product_id']) ?></td>
                        <td class="border border-gray-300 px-2 py-1"><?= htmlspecialchars($p['name']) ?></td>
                        <td class="border border-gray-300 px-2 py-1">Rp <?= number_format($p['price']) ?></td>
                        <td class="border border-gray-300 px-2 py-1"><?= htmlspecialchars($p['stock']) ?></td>
                        <td class="border border-gray-300 px-2 py-1">
                            <img src="img/<?= htmlspecialchars($p['image']) ?>" alt="product image" class="w-16 mx-auto" />
                        </td>
                        <td class="border border-gray-300 px-2 py-1 space-x-2">

                            <!-- bagian update -->
                            <form action="admin.php" method="post" class="inline-block">
                                <input type="hidden" name="product_id" value="<?= $p['product_id'] ?>" />
                                <input type="number" name="price" value="<?= $p['price'] ?>" min="0" required
                                    class="w-20 border border-gray-300 rounded px-2 py-1" />
                                <input type="number" name="stock" value="<?= $p['stock'] ?>" min="0" required
                                    class="w-20 border border-gray-300 rounded px-2 py-1" />
                                <button type="submit" name="update_product"
                                    class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600 transition">
                                    Update
                                </button>
                            </form>

                            <!-- bagian delete -->
                            <form method="post" class="inline-block" onsubmit="return confirm('Delete this product?');">
                                <input type="hidden" name="product_id" value="<?= $p['product_id'] ?>" />
                                <button type="submit" name="delete_product"
                                    class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700 transition">
                                    Delete
                                </button>
                            </form>

                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>

</body>

</html>
