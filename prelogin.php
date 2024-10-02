<?php

if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: attendence.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="login-container">
        <h1>Login</h1>
        <form action="login.php" method="post">
            <label for="section">Section:</label>
            <select id="section" name="section" required>
                <option class="tx1" value="a1">A1</option>
                <option class="tx1" value="a2">A2</option>
                <option class="tx1" value="b1">B1</option>
                <option class="tx1" value="b2">B2</option>
                <!-- Add more sections as needed -->
            </select>

            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Login</button>
        </form>
<br><br>
       <a href="passwordforgot.php"> <button>Password Forgot</button></a>

        
    </div>
</body>
</html>
