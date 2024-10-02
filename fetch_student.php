<?php
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

if (isset($_GET['erp'])) {
    $erp_number = $_GET['erp'];

    // Query to fetch student name based on ERP number
    $sql = "SELECT name FROM students WHERE erp_number = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $erp_number);
    $stmt->execute();
    $result = $stmt->get_result();

    $response = array();
    if ($result->num_rows > 0) {
        $student = $result->fetch_assoc();
        $response['name'] = $student['name'];
    } else {
        $response['name'] = null;
    }

    // Return JSON response
    echo json_encode($response);
    $stmt->close();
}

$conn->close();
?>
