<?php
// Database connection
$servername = "localhost"; // Change this to your database server
$username = "root"; // Change this to your database username
$password = ""; // Change this to your database password
$dbname = "csd_system"; // Change this to your database name

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Initialize variables for alerts
$insert = $delete = $update = false;

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['submit'])) {
        // Add new item
        $itemId = $_POST["itemId"];
        $name = $_POST['name'];
        $category = $_POST['category'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $stock_quantity = $_POST['stock_quantity'];
        $item_image = $_FILES['item_image']['name'];

        $checkQuery = "SELECT * FROM items WHERE itemId = $itemId";
        $checkResult = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($checkResult) > 0) {
            echo "<script>alert('Item ID already exists. Please choose a different Item ID.');</script>";
        } else {
            // Move uploaded image to the server directory
            $target_dir = "items_image/";
            $target_file = $target_dir . basename($_FILES["item_image"]["name"]);
            move_uploaded_file($_FILES["item_image"]["tmp_name"], $target_file);

            $sql = "INSERT INTO items (itemId , name, category, description, price, stock_quantity, item_image) VALUES ('$itemId' , '$name', '$category', '$description', '$price', '$stock_quantity', '$item_image')";
            if (mysqli_query($conn, $sql)) {
                $insert = true;
            }
        }

    } elseif (isset($_POST['update'])) {
        // Update item
        $itemId = $_POST['itemId'];
        $name = $_POST['name'];
        $category = $_POST['category'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $stock_quantity = $_POST['stock_quantity'];

        // Check if new itemId already exists
        $new_itemId = $_POST['new_itemId'];
        if ($new_itemId != $itemId) {
            $checkQuery = "SELECT * FROM items WHERE itemId = $new_itemId";
            $checkResult = mysqli_query($conn, $checkQuery);
            if (mysqli_num_rows($checkResult) > 0) {
                echo "<script>alert('New Item ID already exists. Please choose a different Item ID.');</script>";
                echo "<script>window.location = 'admin_dashboard.php'</script>";
                exit;
            }
        }

        // Update item image if a new image is uploaded
        if (!empty($_FILES['item_image']['name'])) {
            $item_image = $_FILES['item_image']['name'];
            $target_dir = "items_image/";
            $target_file = $target_dir . basename($_FILES["item_image"]["name"]);
            move_uploaded_file($_FILES["item_image"]["tmp_name"], $target_file);
            $sql = "UPDATE items SET itemId='$new_itemId', name='$name', category='$category', description='$description', price='$price', stock_quantity='$stock_quantity', item_image='$item_image' WHERE itemId='$itemId'";
        } else {
            $sql = "UPDATE items SET itemId='$new_itemId', name='$name', category='$category', description='$description', price='$price', stock_quantity='$stock_quantity' WHERE itemId='$itemId'";
        }

        if (mysqli_query($conn, $sql)) {
            $update = true;
        }
    } elseif (isset($_POST['delete'])) {
        // Delete item
        $itemId = $_POST['itemId'];
        $sql = "DELETE FROM items WHERE itemId='$itemId'";
        if (mysqli_query($conn, $sql)) {
            $delete = true;
        }
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="call.min.css">
    <link rel="stylesheet" href="jquery.dataTables.min.css">
    <title>Admin Dashboard</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }

        .container {
            margin-top: 20px;
        }

        .header-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .header-actions h2 {
            margin: 0;
            font-weight: bold;
        }

        .table-container {
            overflow-x: auto;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: white;
            padding: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: box-shadow 0.3s ease-in-out;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f4f4f4;
        }

        @media (max-width: 900px) {
            .header-actions {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <?php include 'navbar.php'; ?>

    <!-- Success Alerts -->
    <?php if ($insert) echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>Item added successfully!<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>"; ?>
    <?php if ($delete) echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>Item deleted successfully!<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>"; ?>
    <?php if ($update) echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>Item updated successfully!<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>"; ?>

    <div class="container">
        <div class="text-center my-4">
            <h2 class="font-weight-bold">Admin Dashboard</h2>
        </div>

        <div class="header-actions mb-3 mt-4">
            <h4>Available Items</h4>
            <div>
                <button id="add-btn" class="btn btn-primary"><i class="fas fa-plus"></i> Add</button>
                <button id="print-btn" class="btn btn-secondary"><i class="fas fa-print"></i> Print</button>
                <button id="logout-btn" class="btn btn-danger" onclick="window.location.href='logout.php';"><i class="fas fa-sign-out-alt"></i> Logout</button>
            </div>
        </div>

        <div class="table-container">
            <table id="myTable">
                <thead>
                    <tr>
                        <th class='text-center'>SNo.</th>
                        <th class='text-center'>Item ID</th>
                        <th class='text-center'>Name</th>
                        <th class='text-center'>Category</th>
                        <th class='text-center'>Description</th>
                        <th class='text-center'>Price</th>
                        <th class='text-center'>Stock Quantity</th>
                        <th class='text-center'>Item Image</th>
                        <th class='text-center'>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT * FROM `items`";
                    $result = mysqli_query($conn, $sql);
                    $sno = 0;
                    while ($row = mysqli_fetch_assoc($result)) {
                        $sno++;
                        echo "<tr>
                            <td class='text-center'>" . $sno . "</td>
                            <td class='text-center'>" . $row['itemId'] . "</td>
                            <td class='text-center'>" . $row['name'] . "</td>
                            <td class='text-center'>" . $row['category'] . "</td>
                            <td class='text-center'>" . $row['description'] . "</td>
                            <td class='text-center'>" . $row['price'] . "</td>
                            <td class='text-center'>" . $row['stock_quantity'] . "</td>
                            <td class='text-center'><img src='items_image/" . $row['item_image'] . "' alt='" . $row['name'] . "' width='50' height='50'></td>
                            <td class='text-center'>
                                <button class='edit btn btn-sm btn-primary' data-itemid='" . $row['itemId'] . "' data-name='" . $row['name'] . "' data-category='" . $row['category'] . "' data-description='" . $row['description'] . "' data-price='" . $row['price'] . "' data-stock_quantity='" . $row['stock_quantity'] . "' data-item_image='" . $row['item_image'] . "'><i class='fas fa-edit'></i> Edit</button>
                                <button class='delete btn btn-sm btn-danger' data-itemid='" . $row['itemId'] . "'><i class='fas fa-trash'></i> Delete</button>
                            </td>
                        </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Modal -->
    <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="admin_dashboard.php" method="post" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addModalLabel">Add Item</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="itemId">Item ID</label>
                            <input type="text" class="form-control" id="itemId" name="itemId" required>
                        </div>
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="category">Category</label>
                            <input type="text" class="form-control" id="category" name="category" required>
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="price">Price</label>
                            <input type="number" class="form-control" id="price" name="price" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label for="stock_quantity">Stock Quantity</label>
                            <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" required>
                        </div>
                        <div class="form-group">
                            <label for="item_image">Item Image</label>
                            <input type="file" class="form-control-file" id="item_image" name="item_image" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" name="submit">Add Item</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="admin_dashboard.php" method="post" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Edit Item</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="itemId">Item ID</label>
                            <input type="text" class="form-control" id="edit-itemId" name="itemId" readonly>
                        </div>
                        <div class="form-group">
                            <label for="new_itemId">New Item ID</label>
                            <input type="text" class="form-control" id="edit-new_itemId" name="new_itemId">
                        </div>
                        <div class="form-group">
                            <label for="edit-name">Name</label>
                            <input type="text" class="form-control" id="edit-name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="edit-category">Category</label>
                            <input type="text" class="form-control" id="edit-category" name="category" required>
                        </div>
                        <div class="form-group">
                            <label for="edit-description">Description</label>
                            <textarea class="form-control" id="edit-description" name="description" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="edit-price">Price</label>
                            <input type="number" class="form-control" id="edit-price" name="price" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label for="edit-stock_quantity">Stock Quantity</label>
                            <input type="number" class="form-control" id="edit-stock_quantity" name="stock_quantity" required>
                        </div>
                        <div class="form-group">
                            <label for="edit-item_image">Item Image</label>
                            <input type="file" class="form-control-file" id="edit-item_image" name="item_image">
                            <small>Leave blank if you don't want to change the image</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" name="update">Update Item</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="admin_dashboard.php" method="post">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteModalLabel">Delete Item</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete this item?</p>
                        <input type="hidden" id="delete-itemId" name="itemId">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger" name="delete">Delete Item</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#myTable').DataTable();

            $('#add-btn').click(function () {
                $('#addModal').modal('show');
            });

            $('.edit').click(function () {
                var itemId = $(this).data('itemid');
                var name = $(this).data('name');
                var category = $(this).data('category');
                var description = $(this).data('description');
                var price = $(this).data('price');
                var stock_quantity = $(this).data('stock_quantity');
                var item_image = $(this).data('item_image');

                $('#edit-itemId').val(itemId);
                $('#edit-new_itemId').val(itemId);
                $('#edit-name').val(name);
                $('#edit-category').val(category);
                $('#edit-description').val(description);
                $('#edit-price').val(price);
                $('#edit-stock_quantity').val(stock_quantity);
                $('#edit-item_image').val('');

                $('#editModal').modal('show');
            });

            $('.delete').click(function () {
                var itemId = $(this).data('itemid');
                $('#delete-itemId').val(itemId);
                $('#deleteModal').modal('show');
            });
        });
    </script>
</body>
</html>
