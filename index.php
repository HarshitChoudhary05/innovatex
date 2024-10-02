<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header("Location: prelogin.php");
    exit();
}

// Redirect to the appropriate page based on the user's role
if ($_SESSION['role'] == 'CR' || $_SESSION['role'] == 'Mentor') {
    header("Location: attendance.php");
    exit();
} else {
    echo "Access denied.";
}
?>
