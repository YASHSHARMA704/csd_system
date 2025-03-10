<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "csd_system";

session_start();

$conn = mysqli_connect($servername, $username, $password, $database);

if (!$conn) {
    die("Sorry, Connection with database is not built " . mysqli_connect_error());
}

$updateSuccess = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['Add_To_Cart'])) {
        if (isset($_SESSION['cart'])) {
            $item_array_id = array_column($_SESSION['cart'], "itemId");
            if (in_array($_POST['itemId'], $item_array_id)) {
                echo "<script>alert('Item is already added in the cart!')</script>";
                echo "<script>window.location = 'user_dashboard.php'</script>";
            } else {
                $count = count($_SESSION['cart']);
                $_SESSION['cart'][$count] = array(
                    'itemId' => $_POST['itemId'],
                    'name' => $_POST['name'],
                    'category' => $_POST['category'],
                    'description' => $_POST['description'],
                    'price' => $_POST['price'],
                    'selected_quantity' => $_POST['selected_quantity']
                );

                echo "<script>alert('Item is Successfully added in the cart!')</script>";
                echo "<script>window.location = 'user_dashboard.php'</script>";
            }
        } else {
            $_SESSION['cart'][0] = array(
                'itemId' => $_POST['itemId'],
                'name' => $_POST['name'],
                'category' => $_POST['category'],
                'description' => $_POST['description'],
                'price' => $_POST['price'],
                'selected_quantity' => $_POST['selected_quantity']
            );

            echo "<script>alert('Item is Successfully added in the cart!')</script>";
            echo "<script>window.location = 'user_dashboard.php'</script>";
        }
    }

    if (isset($_POST['Remove_Item'])) {
        foreach ($_SESSION['cart'] as $key => $value) {
            if ($value['itemId'] == $_POST['itemId']) {
                unset($_SESSION['cart'][$key]);
                $_SESSION['cart'] = array_values($_SESSION['cart']);
                echo "<script>alert('Item is Successfully Removed from the cart!')</script>";
                echo "<script>window.location = 'cartpage.php'</script>";
            }
        }
    }

    if (isset($_POST['Update_Item'])) {
        $editItemId = $_POST['editItemId'];
        $newQuantity = $_POST['selected_quantity'];

        // Fetch stock_quantity from your database
        $query = "SELECT stock_quantity FROM items WHERE itemId = " . $editItemId;
        $result = mysqli_query($conn, $query);

        if (!$result) {
            die("Error fetching stock quantity: " . mysqli_error($conn));
        }

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $stockQuantity = $row['stock_quantity'];

            // Ensure the new quantity does not exceed stock quantity
            if ($newQuantity > $stockQuantity) {
                $newQuantity = $stockQuantity;
                echo "<script>alert('Selected quantity exceeds available stock. Updated to maximum available.')</script>";
            }

            // Update session cart with new quantity
            foreach ($_SESSION['cart'] as $key => $value) {
                if ($value['itemId'] == $editItemId) {
                    $_SESSION['cart'][$key]['selected_quantity'] = $newQuantity;
                    $updateSuccess = true; // Set update success flag
                    break;
                }
            }
        } else {
            die("Item with ID " . $editItemId . " not found in database.");
        }

        // Redirect back to cart page after update
        header("Location: cartpage.php");
        exit;
    }

    if (isset($_POST['Make_Purchase'])) {
        $total = 0;
        if (isset($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $key => $value) {
                $total += $value['price'] * $value['selected_quantity'];
            }
        }
    
        if ($total == 0) {
            echo "<script>alert('No item in the cart!')</script>";
            echo "<script>window.location = 'cartpage.php'</script>";
        } else {
            // Generate a unique random order_id
            do {
                $order_id = rand(100000, 999999);
                $query = "SELECT * FROM orders WHERE order_id = $order_id";
                $result = mysqli_query($conn, $query);
            } while (mysqli_num_rows($result) > 0);
    
            // Insert into orders table
            $user_id = $_SESSION['user_id']; // Assuming user_id is stored in session
            $query = "INSERT INTO orders (user_id, order_id, status) VALUES ($user_id, $order_id, 1)";
            if (mysqli_query($conn, $query)) {
                // Insert each item in the cart into the order_details table
                foreach ($_SESSION['cart'] as $key => $value) {
                    $item_id = $value['itemId'];
                    $item_name = $value['name'];
                    $quantity = $value['selected_quantity'];
                    $price = $value['price'];
                    
                    $query = "INSERT INTO order_details (order_id, item_id, item_name, quantity, price) 
                              VALUES ($order_id, $item_id, '$item_name', $quantity, $price)";
                    
                    if (!mysqli_query($conn, $query)) {
                        echo "<script>alert('Error: Could not insert order details')</script>";
                    }
                }
                echo "<script>alert('Purchase successful! Order ID: $order_id')</script>";
                unset($_SESSION['cart']); // Clear the cart after successful purchase
                echo "<script>window.location = 'user_dashboard.php'</script>";
            } else {
                echo "<script>alert('Error: Could not make the purchase')</script>";
            }
        }
    }
    

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart Page</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="all.min.css">
    <style>
        body {
            background-color: #f0f2f5; /* Light background color */
        }

        .header-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
        }

        .header-row h1 {
            margin: 0;
            color: #343a40; /* Dark color for heading */
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            transition: background-color 0.3s, border-color 0.3s;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #004085;
        }

        .btn-outline-primary {
            border-color: #007bff;
            color: #007bff;
            transition: background-color 0.3s, color 0.3s;
        }

        .btn-outline-primary:hover {
            background-color: #007bff;
            color: #fff;
        }

        .btn-outline-danger {
            border-color: #dc3545;
            color: #dc3545;
            transition: background-color 0.3s, color 0.3s;
        }

        .btn-outline-danger:hover {
            background-color: #dc3545;
            color: #fff;
        }

        .btn-back {
            margin-right: 10px;
        }

        .btn-print {
            background-color: #28a745;
            border-color: #28a745;
            color: #fff;
        }

        .btn-orders {
            background-color: #28a745; /* Attractive color */
            border-color: #28a745;
            color: #fff;
            transition: background-color 0.3s, border-color 0.3s, transform 0.3s;
        }

        .btn-orders:hover {
            background-color: #218838;
            border-color: #1e7e34;  
            transform: scale(1.05);
        }


        .btn-print:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }

        .card-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center; /* Center cards horizontally */
        }

        .card-container .card {
            flex: 1 1 calc(25% - 20px); /* 4 cards per row with spacing */
            max-width: 320px; /* Slightly increased card width */
            margin-bottom: 20px;
            transition: transform 0.3s, box-shadow 0.3s;
            border: 1px solid #ddd; /* Light border color */
            border-radius: 8px;
            background-color: #fff; /* Card background color */
        }

        .card-container .card:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .card-img-top {
            height: 50%; /* Set a fixed height for the image */
            width: 50%; /* Make the image take full width of the card */
            margin:auto;
            object-fit: cover;
            border-bottom: 1px solid #ddd; /* Border below image */
        }

        .card-body {
            padding: 15px;
        }

        .card-title {
            color: #007bff;
            font-size: 1.1rem;
            margin-bottom: 10px;
        }

        .card-text {
            color: #495057; /* Darker text color */
        }

        .cart-summary {
            margin-top: 30px;
            background-color: #ffffff; /* Background color for summary */
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        @media (max-width: 768px) {
            .card-container .card {
                flex: 1 1 calc(50% - 20px); /* 2 cards per row on small screens */
            }
        }

        @media (max-width: 576px) {
            .card-container .card {
                flex: 1 1 100%; /* 1 card per row on extra small screens */
            }
        }
    </style>
</head>
<body>

    <!-- navbar -->
    <?php include 'navbar.php'; ?>

    <div class="container">
        <div class="header-row">
            <h1>My Cart</h1>
            <div>
                <a href="user_dashboard.php" class="btn btn-secondary btn-back font-weight-bold">&lt; Back</a>
                <button onclick="window.print()" class="btn btn-print font-weight-bold">Print</button>
            </div>
        </div>

        <div class="card-container">
            <?php
            $total = 0;
            if (isset($_SESSION['cart'])) {
                foreach ($_SESSION['cart'] as $key => $value) {
                    $query = "SELECT * FROM items WHERE itemId = " . $value['itemId'];
                    $result = mysqli_query($conn, $query);

                    if (!$result) {
                        die("Error fetching item details: " . mysqli_error($conn));
                    }

                    if (mysqli_num_rows($result) > 0) {
                        $row = mysqli_fetch_assoc($result);
                        $stockQuantity = $row['stock_quantity'];
                        $itemImage = !empty($row['item_image']) ? $row['item_image'] : 'default_image.jpg'; // Default image if not found
                    } else {
                        $stockQuantity = 0;
                        $itemImage = 'default_image.jpg'; // Default image if not found
                    }

                    // Calculate total price
                    $total += $value['price'] * $value['selected_quantity'];

                    echo "<div class='card'>";
                    echo "<img src='items_image/" . htmlspecialchars($itemImage) . "' class='card-img-top' alt='" . htmlspecialchars($value['name']) . "'>";
                    echo "<div class='card-body'>";
                    echo "<h5 class='card-title'>ID: " . htmlspecialchars($value['itemId']) . "</h5>";
                    echo "<p class='card-text'>Name: " . htmlspecialchars($value['name']) . "</p>";
                    echo "<p class='card-text'>Category: " . htmlspecialchars($value['category']) . "</p>";
                    echo "<p class='card-text'>Description: " . htmlspecialchars($value['description']) . "</p>";
                    echo "<p class='card-text'>Price: " . number_format($value['price'], 2) . "</p>";
                    echo "<p class='card-text'>Selected Quantity: " . $value['selected_quantity'] . "</p>";
                    echo "<div class='d-flex justify-content-between'>";
                    echo "<button class='btn btn-outline-primary edit-btn' data-itemid='" . $value['itemId'] . "' data-selectedquantity='" . $value['selected_quantity'] . "' data-stockquantity='" . $stockQuantity . "'>Edit</button>";
                    echo "<form method='POST' class='mb-0'>
                            <input type='hidden' name='itemId' value='" . $value['itemId'] . "'>
                            <button type='submit' name='Remove_Item' class='btn btn-outline-danger btn-sm'>Remove</button>
                            </form>";
                    echo "</div></div></div>";
                }
            } else {
                echo "<div class='card'><div class='card-body'>Your cart is empty</div></div>";
            }
            ?>
        </div>

        <div class="col-lg-3 col-md-6 ml-auto cart-summary">
            <div class="border bg-light rounded p-4">
                <h3>Total:</h3>
                <h5 class='text-right'><?php echo number_format($total, 2) ?></h5>
                <br>
                <form method="POST">
                    <button class="btn btn-primary btn-block" name="Make_Purchase">Make Purchase</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Quantity</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="cartpage.php" method="POST">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="editQuantity">Selected Quantity:</label>
                            <input type="number" class="form-control" id="editQuantity" name="selected_quantity" value="" min="1">
                            <input type="hidden" name="editItemId" id="editItemId" value="">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" name="Update_Item">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- jQuery and Bootstrap JS -->
    <script src="jquery-3.3.1.slim.min.js"></script>
    <script src="popper.min.js"></script>
    <script src="bootstrap.min.js"></script>

    <script>
        $(document).ready(function() {
            <?php if ($updateSuccess): ?>
                // Display success alert using Bootstrap alert
                $('.container').prepend('<div class="alert alert-success alert-dismissible fade show mt-3" role="alert">Item quantity updated successfully!<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
            <?php endif; ?>
            
            $('.edit-btn').on('click', function() {
                var itemId = $(this).data('itemid');
                var selectedQuantity = $(this).data('selectedquantity');
                var stockQuantity = $(this).data('stockquantity');

                $('#editItemId').val(itemId);
                $('#editQuantity').attr('max', stockQuantity); // Set max attribute dynamically
                $('#editQuantity').val(selectedQuantity);
                $('#editModal').modal('show');
            });
        });
    </script>
</body>
</html>
