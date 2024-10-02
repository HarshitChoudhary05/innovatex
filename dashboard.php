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

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$section = $_SESSION['section'];

// Filter Logic
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'current_week';
$date_condition = "";

// Calculate the start and end of the current week (Monday to Sunday)
$start_of_week = date('Y-m-d', strtotime('monday this week'));
$end_of_week = date('Y-m-d', strtotime('sunday this week'));

if ($filter === 'current_week') {
    $date_condition = "AND attendance.date BETWEEN '$start_of_week' AND '$end_of_week'";
} elseif ($filter === 'month') {
    $month = isset($_GET['month']) ? $_GET['month'] : date('m');
    $date_condition = "AND MONTH(attendance.date) = " . $month;
} elseif ($filter === 'year') {
    $year = isset($_GET['year']) ? $_GET['year'] : date('Y');
    $date_condition = "AND YEAR(attendance.date) = " . $year;
} elseif ($filter === 'date_range') {
    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : $start_of_week;
    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : $end_of_week;
    $date_condition = "AND attendance.date BETWEEN '$start_date' AND '$end_date'";
}

// Fetch all students in the section
$students_sql = "SELECT id, name, erp_number FROM students WHERE section = ?";
$students_stmt = $conn->prepare($students_sql);
$students_stmt->bind_param("s", $section);
$students_stmt->execute();
$students_result = $students_stmt->get_result();
$students = [];
while ($row = $students_result->fetch_assoc()) {
    $students[] = $row;
}

// Fetch filtered attendance records for the section
$attendance_sql = "SELECT students.name AS student_name, students.erp_number, attendance.date, attendance.status, attendance.file 
                    FROM attendance 
                    JOIN students ON attendance.student_id = students.id 
                    WHERE students.section = ? 
                    $date_condition
                    ORDER BY attendance.date DESC";
$attendance_stmt = $conn->prepare($attendance_sql);
$attendance_stmt->bind_param("s", $section);
$attendance_stmt->execute();
$attendance_result = $attendance_stmt->get_result();

// Organize attendance data
$attendance_data = [];
$dates = [];
while ($row = $attendance_result->fetch_assoc()) {
    $erp_number = $row['erp_number'];
    $date = $row['date'];
    $attendance_data[$erp_number][$date] = $row;
    $dates[$date] = date('l, d M Y', strtotime($date));
}

$students_stmt->close();
$attendance_stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .dashboard-container {
            width: 100%;
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #f4f4f4;
            position: sticky;
            top: 0;
            z-index: 1;
        }
        td a {
            color: #007bff;
            text-decoration: none;
        }
        td a:hover {
            text-decoration: underline;
        }
        .responsive-table {
            display: block;
            width: 100%;
            overflow-x: auto;
            white-space: nowrap;
        }
        .dashboard-container {
            margin: 0 auto;
        }
        .filter-container {
            margin-bottom: 20px;
        }
        .filter-container form {
            display: flex;
            gap: 10px;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <h1>Attendance Dashboard</h1>
        <div class="filter-container">
            <form method="GET">
                <label for="filter">Filter By:</label>
                <select name="filter" id="filter" onchange="this.form.submit()">
                    <option value="current_week" <?php if ($filter === 'current_week') echo 'selected'; ?>>Current Week</option>
                    <option value="month" <?php if ($filter === 'month') echo 'selected'; ?>>Month</option>
                    <option value="year" <?php if ($filter === 'year') echo 'selected'; ?>>Year</option>
                    <option value="date_range" <?php if ($filter === 'date_range') echo 'selected'; ?>>Custom Date Range</option>
                </select>

                <input type="date" name="start_date" id="start_date" value="<?php echo isset($start_date) ? $start_date : $start_of_week; ?>" style="display: <?php echo ($filter === 'date_range') ? 'block' : 'none'; ?>" onchange="this.form.submit()">
                <input type="date" name="end_date" id="end_date" value="<?php echo isset($end_date) ? $end_date : $end_of_week; ?>" style="display: <?php echo ($filter === 'date_range') ? 'block' : 'none'; ?>" onchange="this.form.submit()">

                <select name="month" id="month" style="display: <?php echo ($filter === 'month') ? 'block' : 'none'; ?>" onchange="this.form.submit()">
                    <option value="1" <?php if (isset($month) && $month == 1) echo 'selected'; ?>>January</option>
                    <option value="2" <?php if (isset($month) && $month == 2) echo 'selected'; ?>>February</option>
                    <option value="3" <?php if (isset($month) && $month == 3) echo 'selected'; ?>>March</option>
                    <option value="4" <?php if (isset($month) && $month == 4) echo 'selected'; ?>>April</option>
                    <option value="5" <?php if (isset($month) && $month == 5) echo 'selected'; ?>>May</option>
                    <option value="6" <?php if (isset($month) && $month == 6) echo 'selected'; ?>>June</option>
                    <option value="7" <?php if (isset($month) && $month == 7) echo 'selected'; ?>>July</option>
                    <option value="8" <?php if (isset($month) && $month == 8) echo 'selected'; ?>>August</option>
                    <option value="9" <?php if (isset($month) && $month == 9) echo 'selected'; ?>>September</option>
                    <option value="10" <?php if (isset($month) && $month == 10) echo 'selected'; ?>>October</option>
                    <option value="11" <?php if (isset($month) && $month == 11) echo 'selected'; ?>>November</option>
                    <option value="12" <?php if (isset($month) && $month == 12) echo 'selected'; ?>>December</option>
                    <!-- Add all months -->
                </select>

                <select name="year" id="year" style="display: <?php echo ($filter === 'year') ? 'block' : 'none'; ?>" onchange="this.form.submit()">
                    <option value="2024" <?php if (isset($year) && $year == 2024) echo 'selected'; ?>>2024</option>
                    <!-- Add other years if needed -->
                </select>
            </form>
        </div>

        <div class="responsive-table">
            <table>
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <?php foreach ($dates as $date => $formatted_date) { ?>
                            <th><?php echo htmlspecialchars($formatted_date); ?></th>
                        <?php } ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($student['name'] . " (" . $student['erp_number'] . ")"); ?></td>
                            <?php foreach ($dates as $date => $formatted_date) {
                                $status = isset($attendance_data[$student['erp_number']][$date]) ? $attendance_data[$student['erp_number']][$date]['status'] : 'Not Marked';
                                $file = isset($attendance_data[$student['erp_number']][$date]) ? $attendance_data[$student['erp_number']][$date]['file'] : '';
                                ?>
                                <td>
                                    <?php echo htmlspecialchars($status); ?>
                                    <?php if ($status == 'Leave' && $file) { ?>
                                        <br><a href="uploads/<?php echo htmlspecialchars($file); ?>" target="_blank">View File</a>
                                    <?php } ?>
                                </td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.getElementById('filter').addEventListener('change', function() {
            var filter = this.value;
            document.getElementById('month').style.display = filter === 'month' ? 'block' : 'none';
            document.getElementById('year').style.display = filter === 'year' ? 'block' : 'none';
            document.getElementById('start_date').style.display = filter === 'date_range' ? 'block' : 'none';
            document.getElementById('end_date').style.display = filter === 'date_range' ? 'block' : 'none';
        });
    </script>
</body>
</html>
