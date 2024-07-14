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
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="bootstrap.min1.css">
    <link rel="stylesheet" href="all.min.css">
    <link rel="stylesheet" href="dataTables.dataTables.min.css">
    <title>User Dashboard</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            color: #333;
            background-color: #f0f4f8; /* Light background color for the body */
            transition: background 0.5s ease-in-out;
        }

        .container {
            margin-top: 20px;
        }

        .header-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            flex-wrap: wrap;
            background-color: #e3f2fd; /* Light blue background for header actions */
            padding: 10px;
            border-radius: 5px;
        }

        .header-actions h2 {
            margin: 0;
            font-weight: bold;
            color: #333;
            transition: color 0.5s ease-in-out;
        }

        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); /* 4 cards per row, responsive */
            gap: 20px 20px; /* Horizontal and vertical gaps between cards */
            background-color: #ffffff; /* White background for the grid */
            padding: 10px;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .card {
            border: 1px solid #ddd;
            border-radius: 5px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: box-shadow 0.3s ease-in-out;
            background-color: #ffffff; /* Card background color */
            display: flex;
            flex-direction: column;
        }

        .card img {
            width: 60%;
            height: 60%; /* Fixed height for the image */
            object-fit: cover; /* Cover the image area */
            margin:auto;
        }

        .card-body {
            padding: 15px;
            background-color: #f9f9f9; /* Light background color for card body */
            flex: 1; /* Grow to take available space */
        }

        .card-title {
            font-size: 1.1em; /* Slightly smaller font size for card titles */
            margin-bottom: 10px;
            color: #333;
            background-color: #e3f2fd; /* Light blue background for card title */
            padding: 5px;
            border-radius: 3px;
        }

        .card-text {
            font-size: 0.85em; /* Slightly smaller font size for card text */
            color: #666;
            background-color: #fafafa; /* Light grey background for card text fields */
            padding: 5px;
            border-radius: 3px;
            margin-bottom: 10px;
        }

        .card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            background-color: #e1f5fe; /* Light blue background color for card footer */
            border-top: 1px solid #ddd;
        }

        .card-footer .btn {
            margin-left: 10px;
            transition: background-color 0.3s ease-in-out, transform 0.3s ease-in-out;
        }

        .card-footer .btn:hover {
            transform: scale(1.05);
        }

        .card-footer .select-quantity {
            display: flex;
            align-items:center;
            justify-content: space-between;
        }

        .select-quantity input {
            width: 60px; /* Reduced width for quantity input */
            margin-right: 10px;
            text-align:center;
        }

        @media (max-width: 900px) {
            .header-actions {
                flex-direction: column;
                align-items: flex-start;
            }

            .card-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); /* Adjusted for smaller screens */
            }
        }

        #add-btn {
            background-color: #ffcc80; /* Light orange button */
            border-color: #ffcc80;
        }

        #add-btn:hover {
            background-color: #ffb74d; /* Darker orange on hover */
        }

        #print-btn {
            background-color: #9575cd; /* Purple button */
            border-color: #9575cd;
        }

        #print-btn:hover {
            background-color: #7e57c2; /* Darker purple on hover */
        }

        #logout-btn {
            background-color: #ef5350; /* Red button */
            border-color: #ef5350;
        }

        #logout-btn:hover {
            background-color: #e53935; /* Darker red on hover */
        }

        .btn-orders {
            background-color: #28a745; /* Attractive color */
            border-color: #28a745;
            color: #fff;
            margin-right: 3px;
            transition: background-color 0.3s, border-color 0.3s, transform 0.3s;
        }

        .btn-orders:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }

    </style>
</head>

<body>

    <!-- navbar -->
    <?php include 'navbar.php'; ?>

    <div class="container">
        <div class="text-center my-4">
            <h2 class="font-weight-bold">User Dashboard</h2>
        </div>
        <div class="header-actions">
            <h2>Available Items</h2>
            <div>
                <?php
                $count = 0;
                if (isset($_SESSION['cart'])) {
                    $count = count($_SESSION['cart']);
                }
                ?>
                <button id="orders-btn" class="btn btn-orders" onclick="window.location.href='my_orders.php';">
                    <i class="fa-solid fa-box"></i> My Orders
                </button>
                <button id="add-btn" class="btn btn-primary" onclick="window.location.href='cartpage.php';"><i
                        class="fa-solid fa-cart-plus"></i> My Cart : <?php echo $count; ?> </button>
                <button id="print-btn" class="btn btn-secondary"><i class="fas fa-print"></i> Print</button>
                <button id="logout-btn" class="btn btn-danger" onclick="window.location.href='logout.php';"><i
                        class="fas fa-sign-out-alt"></i> Logout</button>
            </div>
        </div>

        <div class="card-grid">
            <?php
            $sql = "SELECT * FROM items";
            $result = mysqli_query($conn, $sql);

            while ($row = mysqli_fetch_assoc($result)) {
                ?>
                <div class="card">
                    <img src="<?php echo 'items_image/' . $row['item_image']; ?>" class="item_image1" alt="<?php echo $row['name']; ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $row['name']; ?></h5>
                        <p class="card-text"><strong>ID:</strong> <?php echo $row['itemId']; ?></p>
                        <p class="card-text"><strong>Category:</strong> <?php echo $row['category']; ?></p>
                        <p class="card-text"><strong>Description:</strong> <?php echo $row['description']; ?></p>
                        <p class="card-text"><strong>Price:</strong> $<?php echo number_format($row['price'], 2); ?></p>
                        <p class="card-text"><strong>Stock:</strong> <?php echo $row['stock_quantity']; ?></p>
                    </div>
                    <div class="card-footer">
                        <form action="cartpage.php" method="POST" class="d-flex align-items-center">
                            <input type="hidden" name="itemId" value="<?php echo $row['itemId']; ?>">
                            <input type="hidden" name="name" value="<?php echo $row['name']; ?>">
                            <input type="hidden" name="category" value="<?php echo $row['category']; ?>">
                            <input type="hidden" name="description" value="<?php echo $row['description']; ?>">
                            <input type="hidden" name="price" value="<?php echo $row['price']; ?>">
                            <input type="hidden" name="stock_quantity" value="<?php echo $row['stock_quantity']; ?>">
                            <div class="select-quantity">
                                <input type="number" name="selected_quantity" min="0" max="<?php echo $row['stock_quantity']; ?>" value="0">
                                <button type="submit" name="Add_To_Cart" class="btn btn-outline-primary">Add To Cart</button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>
    </div>

    <!-- Optional JavaScript -->
    <script src="jquery-3.3.1.slim.min.js"></script>
    <script src="popper.min.js"></script>
    <script src="bootstrap.min1.js"></script>
    <script src="dataTables.min.js"></script>

    <script>
        // Print button functionality
        document.getElementById('print-btn').addEventListener('click', function () {
            window.print();
        });
    </script>

</body>

</html>
