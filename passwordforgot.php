<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "school_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    
    // Check if username exists
    $user_sql = "SELECT password FROM users WHERE username = ?";
    $user_stmt = $conn->prepare($user_sql);
    $user_stmt->bind_param("s", $username);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();

    if ($user_result->num_rows > 0) {
        $user = $user_result->fetch_assoc();
        $hashed_password = $user['password'];

        // Verify current password
        if (password_verify($current_password, $hashed_password)) {
            // Hash new password and update
            $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_sql = "UPDATE users SET password = ? WHERE username = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ss", $new_hashed_password, $username);
            if ($update_stmt->execute()) {
                echo "Password updated successfully.";
            } else {
                echo "Error updating password.";
            }
            $update_stmt->close();
        } else {
            echo "Current password is incorrect.";
        }
    } else {
        echo "Username not found.";
    }

    $user_stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="css/passwordforget.css">
</head>
<body>
    <h1>Reset Password</h1>
    <form action="" method="POST">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>
        <br>
        <label for="current_password">Current Password:</label>
        <input type="password" id="current_password" name="current_password" required>
        <br>
        <label for="new_password">New Password:</label>
        <input type="password" id="new_password" name="new_password" required>
        <br>
        <button type="submit">Update Password</button>
    </form>
</body>
</html>
