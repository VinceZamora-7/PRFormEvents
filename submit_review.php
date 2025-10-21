<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. DB Connection
$mysqli = new mysqli('localhost', 'root', '', 'peer_review_db');

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// 2. Collect answers dynamically
$answers = [];
foreach ($_POST as $key => $value) {
    if (strpos($key, 'q') === 0 || strpos($key, 'fatality') === 0) {
        $answers[$key] = $value;
    }
}

// 3. Handle file uploads
$imagePaths = [];
$uploadDir = 'uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

foreach ($_FILES as $file) {
    if ($file['error'] === UPLOAD_ERR_OK) {
        $filename = uniqid() . "_" . basename($file['name']);
        $targetFile = $uploadDir . $filename;
        move_uploaded_file($file['tmp_name'], $targetFile);
        $imagePaths[] = $filename;
    }
}

// 4. Generate PRID dynamically by fetching all pr_id and finding max number
$result = $mysqli->query("SELECT pr_id FROM pr_submissions");

$max_num = 0;
while ($row = $result->fetch_assoc()) {
    $num = (int)substr($row['pr_id'], 4);
    if ($num > $max_num) {
        $max_num = $num;
    }
}

$next_pr_id = 'PRID' . str_pad($max_num + 1, 6, '0', STR_PAD_LEFT);

// 5. Save to DB
$task_name = $_POST['task_name'];
$pr_name = $_POST['peer_reviewer_name'];
$pr_email = $_POST['peer_reviewer_email'];
$builder_name = $_POST['builder_name'];
$builder_email = $_POST['builder_email'];
$submitter_email = 'v-jopastoral@microsoft.com'; // hardcoded for now
$answers_json = json_encode($answers);
$images_json = json_encode($imagePaths);
$status = 'Pending';

$stmt = $mysqli->prepare("INSERT INTO pr_submissions (pr_id, submitter_email, task_name, peer_reviewer_name, peer_reviewer_email, builder_name, builder_email, answers, image_paths, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssssssss", $next_pr_id, $submitter_email, $task_name, $pr_name, $pr_email, $builder_name, $builder_email, $answers_json, $images_json, $status);

$stmt->execute();
$pr_id = $next_pr_id;
$stmt->close();
$mysqli->close();


// 6. Redirect the user to the feedback page after saving
header("Location: http://localhost/EVENTS/EVENT-PR/pr-feedback/pr_feedback.php?pr_id=$pr_id");

exit;

?>
