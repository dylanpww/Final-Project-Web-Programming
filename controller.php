<?php
function my_connectDB()
{
    $host = "localhost";
    $user = "root";
    $pwd = "";
    $db = "vending_machine";

    $conn = mysqli_connect($host, $user, $pwd, $db) or die("Error connecting to database");
    return $conn;
}

function my_closeDB($conn)
{
    mysqli_close($conn);
}
function viewCart()
{
    $allData = array();
    $conn = my_connectDB();

    if ($conn != null) {
        // ngambil transaksi yang belom dibayar
        $sql = "SELECT * FROM `transactions` WHERE status = 'unpaid'";
        $result = mysqli_query($conn, $sql) or die(mysqli_error($conn));

        if ($row = mysqli_fetch_assoc($result)) {
            $transaction_id = $row['transaction_id'];

            //  ngambil data data item yang dimasukkan di cart
            $sql_items = "SELECT ti.quantity, ti.subtotal, p.name, p.price 
                            FROM transaction_items ti 
                            JOIN products p ON ti.product_id = p.product_id 
                            WHERE ti.transaction_id = $transaction_id";

            $items_result = mysqli_query($conn, $sql_items) or die(mysqli_error($conn));

            while ($item = mysqli_fetch_assoc($items_result)) {
                $data = [
                    'name' => $item['name'],
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'subtotal' => $item['subtotal']
                ];
                array_push($allData, $data);
            }
        }
    }

    my_closeDB($conn);
    return $allData;
}


function addToCart()
{
    $conn = my_connectDB();
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];

    if ($conn != null) {
        // ngambil data dari products
        $sql_query = "SELECT * FROM `products` WHERE product_id = $product_id";
        $productResult = mysqli_query($conn, $sql_query) or die(mysqli_error($conn));
        $productRow = mysqli_fetch_assoc($productResult);
        $price = $productRow['price'];
        $stock = $productRow['stock'];

        // cek kalo stok ada
        if ($stock <= 0) {
            echo "<script>alert('Barang habis!');</script>";
            return;
        } elseif ($stock < $quantity) {
            echo "<script>alert('barang tidak cukup, anda meminta :  $quantity, ready : $stock');</script>";
            return;
        }

        $subtotal = $price * $quantity;

        // ngambil atau bikin transaksi baru
        $sql_query = "SELECT * FROM `transactions` WHERE status = 'unpaid'";
        $result = mysqli_query($conn, $sql_query) or die(mysqli_error($conn));

        if (mysqli_num_rows($result) > 0) {
            // transaksi yang udah ada
            $transaction = mysqli_fetch_assoc($result);
            $transaction_id = $transaction['transaction_id'];

            // cek kalo udah ada barang yang sama di cart
            $sql_check = "SELECT * FROM transaction_items WHERE transaction_id = $transaction_id AND product_id = $product_id";
            $check_result = mysqli_query($conn, $sql_check) or die(mysqli_error($conn));

            if ($item = mysqli_fetch_assoc($check_result)) {
                $new_quantity = $item['quantity'] + $quantity;

                // error handling kalo barang yang dimau melebihi stock yang ada
                if ($new_quantity > $stock) {
                    echo "<script>alert('Barang tidak cukup!, barang yang di cart: {$item['quantity']}, ready : $stock');</script>";
                    return;
                }

                $new_subtotal = $new_quantity * $price;
                $sql_update = "UPDATE transaction_items 
                                SET quantity = $new_quantity, subtotal = $new_subtotal 
                                WHERE transaction_id = $transaction_id AND product_id = $product_id";
                mysqli_query($conn, $sql_update) or die(mysqli_error($conn));
            } else {
                // nambah barang baru ke cart
                $sql_insert = "INSERT INTO transaction_items(product_id, transaction_id, quantity, subtotal)
                                VALUES ($product_id, $transaction_id, $quantity, $subtotal)";
                mysqli_query($conn, $sql_insert) or die(mysqli_error($conn));
            }

            // update total
            $sql_total = "SELECT SUM(subtotal) AS total FROM transaction_items WHERE transaction_id = $transaction_id";
            $total_result = mysqli_query($conn, $sql_total) or die(mysqli_error($conn));
            $total_row = mysqli_fetch_assoc($total_result);
            $new_total = $total_row['total'] ?? 0;

            $sql_update_total = "UPDATE transactions SET total_amount = $new_total WHERE transaction_id = $transaction_id";
            mysqli_query($conn, $sql_update_total) or die(mysqli_error($conn));

        } else {
            // ga ada transaksi lama, maka akan buat baru
            $sql_insert_transaction = "INSERT INTO transactions (total_amount, status) VALUES ($subtotal, 'unpaid')";
            mysqli_query($conn, $sql_insert_transaction) or die(mysqli_error($conn));
            $transaction_id = mysqli_insert_id($conn);

            $sql_insert_item = "INSERT INTO transaction_items(product_id, transaction_id, quantity, subtotal)
                                VALUES ($product_id, $transaction_id, $quantity, $subtotal)";
            mysqli_query($conn, $sql_insert_item) or die(mysqli_error($conn));
        }
    }

    my_closeDB($conn);
}



function deleteFromCart($productName)
{
    $conn = my_connectDB();

    if ($conn != null) {
        // cari traksaksi yang belom dibayar
        $sql = "SELECT * FROM `transactions` WHERE status = 'unpaid'";
        $result = mysqli_query($conn, $sql) or die(mysqli_error($conn));

        if ($row = mysqli_fetch_assoc($result)) {
            $transaction_id = $row['transaction_id'];

            // ngambil product id dari nama barang
            $escapedName = mysqli_real_escape_string($conn, $productName);
            $sql_product = "SELECT * FROM products WHERE name = '$escapedName' LIMIT 1";
            $product_result = mysqli_query($conn, $sql_product) or die(mysqli_error($conn));

            if ($product = mysqli_fetch_assoc($product_result)) {
                $product_id = $product['product_id'];
                $price = $product['price'];

                // ngambil jumlah barang dari transaksi_items
                $sql_item = "SELECT * FROM transaction_items WHERE transaction_id = $transaction_id AND product_id = $product_id LIMIT 1";
                $item_result = mysqli_query($conn, $sql_item) or die(mysqli_error($conn));

                if ($item = mysqli_fetch_assoc($item_result)) {
                    $quantity = $item['quantity'];

                    if ($quantity > 1) {
                        $new_quantity = $quantity - 1;
                        $new_subtotal = $new_quantity * $price;

                        $sql_update = "UPDATE transaction_items 
                                        SET quantity = $new_quantity, subtotal = $new_subtotal 
                                        WHERE transaction_id = $transaction_id AND product_id = $product_id";
                        mysqli_query($conn, $sql_update) or die(mysqli_error($conn));
                    } else {
                        $sql_delete = "DELETE FROM transaction_items 
                                        WHERE transaction_id = $transaction_id AND product_id = $product_id";
                        mysqli_query($conn, $sql_delete) or die(mysqli_error($conn));
                    }

                    // update total di transaksi
                    $sql_total = "SELECT SUM(subtotal) AS total FROM transaction_items WHERE transaction_id = $transaction_id";
                    $total_result = mysqli_query($conn, $sql_total) or die(mysqli_error($conn));
                    $total_row = mysqli_fetch_assoc($total_result);
                    $new_total = $total_row['total'] ?? 0;

                    $sql_update_total = "UPDATE transactions SET total_amount = $new_total WHERE transaction_id = $transaction_id";
                    mysqli_query($conn, $sql_update_total) or die(mysqli_error($conn));
                }
            }
        }
    }

    my_closeDB($conn);
}

function insertProduct($name, $price, $stock, $image) {
    //masukin product baru
    $conn = my_connectDB();
    $name = $conn->real_escape_string($name);
    $image = $conn->real_escape_string($image);
    $price = (int)$price;
    $stock = (int)$stock;
    $sql = "INSERT INTO products (name, price, stock, image) VALUES ('$name', $price, $stock, '$image')";
    $conn->query($sql);
    $conn->close();
}

function getProducts() {
    //ngambil product berdasarkan id
    $conn = my_connectDB();
    $sql = "SELECT * FROM products ORDER BY product_id ASC";
    $result = $conn->query($sql);
    $products = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
    $conn->close();
    return $products;
}

function updateProduct($product_id, $price, $stock) {
    //updaet product
    $conn = my_connectDB();
    $product_id = (int)$product_id;
    $price = (int)$price;
    $stock = (int)$stock;
    $sql = "UPDATE products SET price = $price, stock = $stock WHERE product_id = $product_id";
    $conn->query($sql);
    $conn->close();
}

function deleteProduct($product_id) {
    $conn = my_connectDB();

    // Hapus data di tabel transaction_items yang berelasi
    $deleteItemsQuery = "DELETE FROM transaction_items WHERE product_id = $product_id";
    $conn->query($deleteItemsQuery);

    // Setelah tidak ada relasi, hapus dari tabel products
    $deleteProductQuery = "DELETE FROM products WHERE product_id = $product_id";
    $conn->query($deleteProductQuery);
}


function checkout()
{
    $conn = my_connectDB();
    $total = 0;

    //ngambil transaksi terakhir yang belom dibayar
    $sql = "SELECT * FROM transactions WHERE status = 'unpaid' ORDER BY transaction_id DESC LIMIT 1";
    $result = mysqli_query($conn, $sql);
    $transaction = mysqli_fetch_assoc($result);

    

    if ($transaction) {
        $transaction_id = $transaction['transaction_id'];
        $total = $transaction['total_amount'];

        // ngambil barang dari transaksi
        $items_sql = "SELECT product_id, quantity FROM transaction_items WHERE transaction_id = $transaction_id";
        $items_result = mysqli_query($conn, $items_sql);

        while ($item = mysqli_fetch_assoc($items_result)) {
            $product_id = $item['product_id'];
            $quantity = $item['quantity'];

            // ngurangin stok kalo stok cukup
            $update_stock_sql = "UPDATE products 
                                    SET stock = stock - $quantity 
                                    WHERE product_id = $product_id AND stock >= $quantity";
            mysqli_query($conn, $update_stock_sql);
        }

        // update transaksi jadi telah dibayar (selesai)
        $update = "UPDATE transactions SET status = 'paid' WHERE transaction_id = $transaction_id";
        mysqli_query($conn, $update);
    }

    my_closeDB($conn);

    // balik ke awal
    header("Location: index.php");
    exit;
}

?>