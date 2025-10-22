<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// DB Connection
$mysqli = new mysqli('localhost', 'root', '', 'peer_review_db');

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Check if PRID and new status are provided
if (isset($_POST['pr_id']) && isset($_POST['status'])) {
    $pr_id = $_POST['pr_id'];
    $status = $_POST['status'];

    // Prepare the SQL update query
    $stmt = $mysqli->prepare("UPDATE pr_submissions SET status = ? WHERE pr_id = ?");
    $stmt->bind_param("ss", $status, $pr_id);

    if ($stmt->execute()) {
        echo "Status updated successfully";
    } else {
        echo "Failed to update the status.";
    }

    $stmt->close();
} else {
    echo "Missing PRID or status.";
}

$mysqli->close();
?>
