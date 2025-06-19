<?php
header('Content-Type: application/json');
$conn = new mysqli("localhost", "root", "", "expense_planner");

// Comparison Mode
if (isset($_GET['year1']) && isset($_GET['month1']) && isset($_GET['year2']) && isset($_GET['month2'])) {
    $stmt1 = $conn->prepare("SELECT * FROM expenses WHERE year=? AND month=?");
    $stmt1->bind_param("is", $_GET['year1'], $_GET['month1']);
    $stmt1->execute();
    $res1 = $stmt1->get_result()->fetch_assoc();

    $stmt2 = $conn->prepare("SELECT * FROM expenses WHERE year=? AND month=?");
    $stmt2->bind_param("is", $_GET['year2'], $_GET['month2']);
    $stmt2->execute();
    $res2 = $stmt2->get_result()->fetch_assoc();

    echo json_encode(['first' => $res1, 'second' => $res2]);
    exit;
}

// View mode
if (isset($_GET['viewYear']) && isset($_GET['viewMonth'])) {
    $stmt = $conn->prepare("SELECT * FROM expenses WHERE year=? AND month=?");
    $stmt->bind_param("is", $_GET['viewYear'], $_GET['viewMonth']);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    echo json_encode($res ?? []);
    exit;
}

echo json_encode([]);
?>

