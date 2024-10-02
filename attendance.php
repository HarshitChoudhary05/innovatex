<?php
session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header("Location: prelogin.php");
    exit();
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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $erp_number = $_POST['erp_number'];
    $status = $_POST['status'];
    $file = $_FILES['file']['name'] ?? null;

    // Fetch student ID by ERP number
    $sql = "SELECT id, name FROM students WHERE erp_number = ? AND section = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $erp_number, $_SESSION['section']);
    $stmt->execute();
    $student_result = $stmt->get_result();

    if ($student_result->num_rows > 0) {
        $student = $student_result->fetch_assoc();
        $student_id = $student['id'];
        $student_name = $student['name'];

        // Handle file upload
        if ($status == 'Leave' && $file) {
            $target_dir = "uploads/";
            $target_file = $target_dir . basename($_FILES["file"]["name"]);
            move_uploaded_file($_FILES["file"]["tmp_name"], $target_file);
        } else {
            $file = null;
        }

        // Delete existing attendance record for the same student and date
        $delete_sql = "DELETE FROM attendance WHERE student_id = ? AND date = CURDATE()";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $student_id);
        $delete_stmt->execute();

        // Insert new attendance record
        $insert_sql = "INSERT INTO attendance (student_id, date, time, status, file) VALUES (?, CURDATE(), CURTIME(), ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("iss", $student_id, $status, $file);
        $insert_stmt->execute();

        echo "<script>alert('Attendance marked for $student_name ($erp_number)');</script>";
    } else {
        echo "<script>alert('No student found with ERP number $erp_number');</script>";
    }
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mark Attendance</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="css/attendence.css">
</head>
<body>
    <div class="attendance-container">
        <h1>Mark Attendance</h1>
        <form action="attendance.php" method="post" enctype="multipart/form-data">
            <label for="erp_number">ERP Number:</label>
            <input type="text" id="erp_number" name="erp_number" required>
            <button type="button" id="fetch_student">Fetch</button>

            <label>Student Name:</label>
            <input type="text" id="student_name" name="student_name" readonly>

            <label>Status:</label>
            <label><input type="radio" name="status" value="Present" required> Present</label>
            <label><input type="radio" name="status" value="Absent" required> Absent</label>
            <label><input type="radio" name="status" value="Leave" required> Leave</label>

            <div id="file-upload" style="display: none;">
                <label for="file">Upload File (Image/PDF):</label>
                <input type="file" id="file" name="file" accept=".jpg,.jpeg,.png,.pdf">
            </div>

            <button type="submit">Submit</button>
        </form>

        <a href="dashboard.php">Dashboard</a><br>

        <a href="logout.php">Logout</a>

        <script>
            document.getElementById('fetch_student').addEventListener('click', function() {
                var erpNumber = document.getElementById('erp_number').value;
                if (erpNumber) {
                    fetch('fetch_student.php?erp=' + erpNumber)
                        .then(response => response.json())
                        .then(data => {
                            if (data.name) {
                                document.getElementById('student_name').value = data.name;
                            } else {
                                document.getElementById('student_name').value = "No student found";
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                        });
                }
            });

            document.querySelectorAll('input[name="status"]').forEach(radio => {
                radio.addEventListener('change', () => {
                    document.getElementById('file-upload').style.display = radio.value === 'Leave' ? 'block' : 'none';
                });
            });

 // Check session validity on page load
 window.onload = function() {
                var sessionValid = <?php echo isset($_SESSION['username']) ? 'true' : 'false'; ?>;
                if (!sessionValid) {
                    alert('Your session has expired. Redirecting to login page.');
                    window.location.href = 'login.html';
                }
            };

        </script>
    </div>
</body>
</html>
