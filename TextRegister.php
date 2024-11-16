<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Information</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>

<h2>Customer Information</h2>

<table>
    <tr>
        <th>Customer ID</th>
        <th>Username</th>
        <th>Password</th>
        <th>Email</th>
        <th>Created At</th>
        <th>Phone Number</th>
        <th>Address</th>
    </tr>

    <?php
    // Include the main database connection file
    include 'phone_store.php';

    // Select all customers ordered by created_at in descending order
    $sql = "SELECT * FROM `customers` ORDER BY `created_at` DESC";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Loop through each row in the result set
        while($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['customer_id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['username']) . "</td>";
            echo "<td>" . htmlspecialchars($row['password']) . "</td>";
            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
            echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
            echo "<td>" . htmlspecialchars($row['phone_number']) . "</td>";
            echo "<td>" . htmlspecialchars($row['address']) . "</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='7'>No customers found.</td></tr>";
    }

    // Close the database connection
    $conn->close();
    ?>
</table>

</body>
</html>
