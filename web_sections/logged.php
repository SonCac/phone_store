<?php
// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "phone_store";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    exit("Connection failed (PDO): " . $e->getMessage());
}
$userId = $_SESSION['user_id'] ?? 0;

$stmt = $pdo->prepare("SELECT name, email, date_of_birth, profile_image, address, password_hash FROM users WHERE user_id = :user_id");
$stmt->execute(['user_id' => $userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC) ?: [
    'name' => '',
    'email' => '',
    'date_of_birth' => '',
    'profile_image' => 'iphone_15.jpg',
    'address' => '',
    'password_hash' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save-changes'])) {
        $newUsername = $_POST['username'] ?? '';

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE name = :name AND user_id != :user_id");
        $stmt->execute(['name' => $newUsername, 'user_id' => $userId]);
        $usernameExists = $stmt->fetchColumn();

        if ($usernameExists) {
            echo '<div class="alert alert-warning" style="margin-top: 4rem; margin-bottom: 4rem;">Username already exists. Please choose a different one.</div>';
        } else {
            $uploadDir = 'profile_images/';
            $oldProfileImage = $user['profile_image'];

            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                if ($oldProfileImage != 'default.jpg' && file_exists($oldProfileImage)) {
                    unlink($oldProfileImage);
                }

                $fileExtension = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
                $newFileName = $newUsername . '.' . $fileExtension;

                if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadDir . $newFileName)) {
                    $profileImagePath = $uploadDir . $newFileName;
                    $stmt = $pdo->prepare("UPDATE users SET name = :name, date_of_birth = :dob, address = :address, profile_image = :profile_image WHERE user_id = :user_id");
                    $stmt->execute([
                        'name' => $newUsername,
                        'dob' => $_POST['dob'] ?? $user['date_of_birth'],
                        'address' => $_POST['address'] ?? $user['address'],
                        'profile_image' => $profileImagePath,
                        'user_id' => $userId
                    ]);
                    $_SESSION['profile_image'] = $profileImagePath;
                } else {
                    echo "Error uploading file.";
                }
            } else {
                $stmt = $pdo->prepare("UPDATE users SET name = :name, date_of_birth = :dob, address = :address WHERE user_id = :user_id");
                $stmt->execute([
                    'name' => $newUsername,
                    'dob' => $_POST['dob'] ?? $user['date_of_birth'],
                    'address' => $_POST['address'] ?? $user['address'],
                    'user_id' => $userId
                ]);
            }
            
            echo "<script>window.location.href = window.location.href;</script>";
            exit();
        }
    }

    if (isset($_POST['change-password'])) {
        $currentPassword = $_POST['current-password'] ?? '';
        $newPassword = $_POST['new-password'] ?? '';
        $retypePassword = $_POST['retype-password'] ?? '';

        if (strlen($newPassword) < 8 || !preg_match('/[A-Za-z]/', $newPassword) || !preg_match('/[0-9]/', $newPassword)) {
            echo '<div class="alert alert-warning">New password must be at least 8 characters long and include both letters and numbers.</div>';
        } elseif ($newPassword !== $retypePassword) {
            echo '<div class="alert alert-warning">New passwords do not match. Please try again.</div>';
        } else {
            $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE user_id = :user_id");
            $stmt->execute(['user_id' => $userId]);
            $storedPasswordHash = $stmt->fetchColumn();

            if (password_verify($currentPassword, $storedPasswordHash)) {
                $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password_hash = :password WHERE user_id = :user_id");
                $stmt->execute(['password' => $newPasswordHash, 'user_id' => $userId]);

                echo '<div class="alert alert-success">Password changed successfully!</div>';
            } else {
                echo '<div class="alert alert-warning">Current password is incorrect.</div>';
            }
        }
    }

    if (isset($_POST['logout'])) {
        session_unset();
        session_destroy();
        echo "<script>window.location.href = window.location.href;</script>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="bootstrap.min.css">
    <link rel="stylesheet" href="styles/main_styles.css">
    <style>
        body, html {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f2f2f2;
            font-family: Arial, sans-serif;
        }

        .container {
            width: 400px;
            padding: 40px;
            background-color: #ffffff;
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            text-align: center;
            margin: 100px auto;
        }

        .container h5 {
            font-weight: 600;
            margin-top: 30px;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            font-weight: 500;
        }

        .btn-block {
            width: 100%;
            font-weight: 600;
        }

        .text-center img {
            margin-bottom: 20px;
            border-radius: 50%;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="text-center mb-4">
        <img src="<?= htmlspecialchars($user['profile_image']); ?>" alt="Profile Image" class="rounded-circle" style="width: 150px; height: 150px;">
    </div>

    <form action="" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($user['name']); ?>" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']); ?>" readonly>
        </div>
        <div class="form-group">
            <label for="dob">Date of Birth</label>
            <input type="date" class="form-control" id="dob" name="dob" value="<?= htmlspecialchars($user['date_of_birth']); ?>">
        </div>
        <div class="form-group">
            <label for="address">Address</label>
            <input type="text" class="form-control" id="address" name="address" value="<?= htmlspecialchars($user['address']); ?>">
        </div>
        <div class="form-group">
            <label for="profile-image">Select an image (JPG, PNG)</label>
            <input type="file" class="form-control-file" id="profile-image" name="profile_image" accept=".jpg, .jpeg, .png">
        </div>
        <button type="submit" class="btn btn-primary btn-block" name="save-changes">Save Changes</button>
    </form>

    <h5 class="mt-4">Change Password</h5>
    <form action="" method="POST">
        <div class="form-group">
            <label for="current-password">Current Password</label>
            <input type="password" class="form-control" id="current-password" name="current-password" placeholder="Enter current password">
        </div>
        <div class="form-group">
            <label for="new-password">New Password</label>
            <input type="password" class="form-control" id="new-password" name="new-password" placeholder="Enter new password">
        </div>
        <div class="form-group">
            <label for="retype-password">Retype New Password</label>
            <input type="password" class="form-control" id="retype-password" name="retype-password" placeholder="Retype new password">
        </div>
        <button type="submit" class="btn btn-warning btn-block" name="change-password" onclick="setPasswordRequired()">Change Password</button>
    </form>

    <form action="" method="POST" class="mt-3">
        <button type="submit" class="btn btn-danger btn-block" name="logout">Sign Out</button>
    </form>
</div>

<script>
function setPasswordRequired() {
    document.getElementById('current-password').setAttribute('required', 'required');
    document.getElementById('new-password').setAttribute('required', 'required');
    document.getElementById('retype-password').setAttribute('required', 'required');
}
</script>

</body>
</html>
