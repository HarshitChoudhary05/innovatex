<?php
session_start();

if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: prelogin.php");
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "school_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if POST data is set
if (isset($_POST['section']) && isset($_POST['username']) && isset($_POST['password'])) {
    $section = $_POST['section'];
    $user = $_POST['username'];
    $pass = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = ? AND section = ? AND role IN ('CR', 'Mentor')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $user, $section);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        if (password_verify($pass, $row['password'])) {
            $_SESSION['username'] = $user;
            $_SESSION['role'] = $row['role'];
            $_SESSION['section'] = $section;
            header("Location: index.php"); // Redirect to index.php
            exit();
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "No user found.";
    }

    $stmt->close();
} else {
    echo "Please fill in all fields.";
}

$conn->close();
?>
