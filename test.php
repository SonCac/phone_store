<?php
// Database connection parameters
$servername = "127.0.0.1";
$username = "root"; // Adjust this if your MySQL has a different username
$password = ""; // Set your MySQL password if it exists
$dbname = "phone_store";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query to select all items from the 'products' table
$sql = "SELECT * FROM products";
$result = $conn->query($sql);

// HTML to display the products in a table
echo "<table border='1'>";
echo "<tr><th>Product ID</th><th>Name</th><th>Description</th><th>Price</th><th>Image</th><th>Created At</th></tr>";

if ($result->num_rows > 0) {
    // Output data of each row
    while($row = $result->fetch_assoc()) {
        // Convert the Windows file path to a URL path
        $imagePath = str_replace("C:\\xampp\\htdocs", "", $row["image"]);
        echo "<tr>";
        echo "<td>" . $row["product_id"] . "</td>";
        echo "<td>" . (!empty($row["name"]) ? $row["name"] : "N/A") . "</td>";
        echo "<td>" . (!empty($row["description"]) ? $row["description"] : "N/A") . "</td>";
        echo "<td>" . $row["price"] . "</td>";
        echo "<td><img src='" . $imagePath . "' width='100' height='100'></td>";
        echo "<td>" . $row["created_at"] . "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='6'>No products found</td></tr>";
}

echo "</table>";

// Close the database connection
$conn->close();
?>
