<?php
// Thông tin kết nối cơ sở dữ liệu
$servername = "localhost";
$username = "root"; // Thường là 'root' với XAMPP
$password = ""; // Mật khẩu mặc định thường là rỗng
$dbname = "phone_store";

// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Lấy dữ liệu từ form
$user = $_POST['username'];
$email = $_POST['email'];
$pass = password_hash($_POST['password'], PASSWORD_DEFAULT); // Mã hóa mật khẩu

// Câu lệnh SQL để chèn dữ liệu vào bảng customers
$sql = "INSERT INTO customers (username, email, password) VALUES ('$user', '$email', '$pass')";

if ($conn->query($sql) === TRUE) {
    echo "Registration successful!";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

// Đóng kết nối
$conn->close();
?>
