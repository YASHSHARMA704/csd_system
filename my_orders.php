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

$user_id = $_SESSION['user_id']; // Assuming user_id is stored in session
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="all.min.css">
    <style>
        body {
            background-color: #f0f2f5; /* Light background color */
        }

        .section-title {
            margin-top: 20px;
            color: #343a40; /* Dark color for heading */
        }

        .table-container {
            margin-top: 20px;
            background-color: #ffffff; /* Background color for table */
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .no-orders {
            text-align: center;
            font-size: 1.2rem;
            color: #6c757d;
            margin-top: 20px;
        }

        .total-price {
            font-weight: bold;
        }
    </style>
</head>
<body>

    <!-- navbar -->
    <?php include 'navbar.php'; ?>

    <div class="container">
        <h2 class="section-title">Current Orders</h2>
        <div class="table-container">
            <?php
            $query = "SELECT * FROM orders WHERE user_id = $user_id AND status = 1";
            $result = mysqli_query($conn, $query);

            if (mysqli_num_rows($result) == 0) {
                echo "<div class='no-orders'>No current orders.</div>";
            } else {
                $serial_number = 1;
                while ($order = mysqli_fetch_assoc($result)) {
                    $order_id = $order['order_id'];
                    echo "<h4>Order ID: $order_id</h4>";
                    echo "<table class='table table-bordered'>";
                    echo "<thead>";
                    echo "<tr>";
                    echo "<th>Sno.</th>";
                    echo "<th>Item Name</th>";
                    echo "<th>Quantity</th>";
                    echo "<th>Price</th>";
                    echo "</tr>";
                    echo "</thead>";
                    echo "<tbody>";

                    $item_query = "SELECT * FROM order_details WHERE order_id = $order_id";
                    $item_result = mysqli_query($conn, $item_query);
                    $total_price = 0;

                    while ($item = mysqli_fetch_assoc($item_result)) {
                        $item_name = $item['item_name'];
                        $quantity = $item['quantity'];
                        $price = $item['price'];
                        $total_price += $price * $quantity;

                        echo "<tr>";
                        echo "<td>$serial_number</td>";
                        echo "<td>$item_name</td>";
                        echo "<td>$quantity</td>";
                        echo "<td>" . number_format($price, 2) . "</td>";
                        echo "</tr>";

                        $serial_number++;
                    }

                    echo "<tr>";
                    echo "<td colspan='3' class='text-right total-price'>Total Price</td>";
                    echo "<td class='total-price'>" . number_format($total_price, 2) . "</td>";
                    echo "</tr>";

                    echo "</tbody>";
                    echo "</table>";
                }
            }
            ?>
        </div>
    </div>

    <!-- jQuery and Bootstrap JS -->
    <script src="jquery-3.3.1.slim.min.js"></script>
    <script src="popper.min.js"></script>
    <script src="bootstrap.min.js"></script>
</body>
</html>
