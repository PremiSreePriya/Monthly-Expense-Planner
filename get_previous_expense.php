<?php
$year = $_GET['year'];
$month = $_GET['month'];

$months = [
    "January" => 1, "February" => 2, "March" => 3, "April" => 4,
    "May" => 5, "June" => 6, "July" => 7, "August" => 8,
    "September" => 9, "October" => 10, "November" => 11, "December" => 12
];

if (!isset($months[$month])) {
    echo json_encode(["status" => "Invalid month"]);
    exit;
}

$currentMonthNum = $months[$month];
$prevMonthNum = $currentMonthNum - 1;
$prevYear = $year;

if ($prevMonthNum == 0) {
    $prevMonthNum = 12;
    $prevYear--;
}

$prevMonth = array_search($prevMonthNum, $months);

$conn = new mysqli("localhost", "root", "", "expense_planner");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM expenses WHERE year=$prevYear AND month='$prevMonth' ORDER BY id DESC LIMIT 1";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    echo json_encode($result->fetch_assoc());
} else {
    echo json_encode(["status" => "No data found"]);
}

$conn->close();
?>
