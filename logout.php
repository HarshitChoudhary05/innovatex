<?php
session_start(); // Start the session

// Destroy all session data
$_SESSION = array(); // Clear all session variables

if (ini_get("session.use_cookies")) {
    // If the session was started with cookies, delete the session cookie
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Redirect to the login page
header("Location: prelogin.php");
exit();
?>
