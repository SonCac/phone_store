
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['signup'])) {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $retypePassword = $_POST['retype-password'];
        $dob = $_POST['dob'];

        if (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
            echo '<div class="alert alert-warning" style="margin: 4rem 0;">Password must be at least 8 characters long and include both letters and numbers.</div>';
        } elseif ($password !== $retypePassword) {
            echo '<div class="alert alert-warning" style="margin: 4rem 0;">Passwords do not match.</div>';
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("SELECT user_id FROM users WHERE name = :username OR email = :email");
            $stmt->execute(['username' => $username, 'email' => $email]);

            if ($stmt->rowCount() > 0) {
                echo '<div class="alert alert-warning" style="margin: 4rem 0;">User already exists.</div>';
            } else {
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, date_of_birth) VALUES (:name, :email, :password_hash, :dob)");
                $success = $stmt->execute(['name' => $username, 'email' => $email, 'password_hash' => $passwordHash, 'dob' => $dob]);
                echo $success
                    ? '<div class="alert alert-success" style="margin: 4rem 0;">Registration successful!</div>'
                    : '<div class="alert alert-danger" style="margin: 4rem 0;">Registration failed. Please try again.</div>';
            }
        }
    }

    if (isset($_POST['login'])) {
        $loginInput = trim($_POST['login-username-email']);
        $stmt = $pdo->prepare("SELECT user_id, name, password_hash, profile_image, email FROM users WHERE name = :username OR email = :email");
        $stmt->execute(['username' => $loginInput, 'email' => $loginInput]);
        $user = $stmt->fetch();

        if ($user && password_verify($_POST['login-password'], $user['password_hash'])) {
            session_start();
            $_SESSION = array_merge($_SESSION, [
                'user_id' => $user['user_id'],
                'email' => $user['email'],
                'profile_image' => $user['profile_image']
            ]);
            echo "<script>window.location.href = window.location.href;</script>";
            exit();
        } else {
            echo '<div class="alert alert-danger" style="margin: 4rem 0;">Invalid username or password.</div>';
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-5 col-sm-10">
        <h2>Sign Up</h2>
        <form method="POST">
            <div class="form-group">
                <label for="signup-username">Username</label>
                <input type="text" class="form-control" id="signup-username" name="username" placeholder="Enter username" required>
            </div>
            <div class="form-group">
                <label for="signup-email">Email address</label>
                <input type="email" class="form-control" id="signup-email" name="email" placeholder="Enter email" required>
            </div>
            <div class="form-group">
                <label for="signup-password">Password</label>
                <input type="password" class="form-control" id="signup-password" name="password" placeholder="Password" required>
            </div>
            <div class="form-group">
                <label for="signup-retype-password">Retype Password</label>
                <input type="password" class="form-control" id="signup-retype-password" name="retype-password" placeholder="Retype password" required>
            </div>
            <div class="form-group">
                <label for="signup-dob">Date of Birth</label>
                <input type="date" class="form-control" id="signup-dob" name="dob" required>
            </div>
            <button type="submit" name="signup" class="btn btn-primary w-100">Sign Up</button>
        </form>
    </div>

    <div class="col-md-1 d-none d-md-flex align-items-center justify-content-center">
        <div class="border-end" style="height: 100%;"></div>
    </div>

    <div class="col-md-5 col-sm-10 mt-4 mt-md-0">
        <h2 class="mt-2 mt-md-0">Login</h2>
        <form method="POST">
            <div class="form-group">
                <label for="login-username-email">Username/Email</label>
                <input type="text" class="form-control" id="login-username-email" name="login-username-email" placeholder="Enter username or email" required>
            </div>
            <div class="form-group">
                <label for="login-password">Password</label>
                <input type="password" class="form-control" id="login-password" name="login-password" placeholder="Password" required>
            </div>
            <button type="submit" name="login" class="btn btn-primary w-100">Login</button>
        </form>
    </div>
</div>
