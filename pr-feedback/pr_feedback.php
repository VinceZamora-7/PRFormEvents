<?php
// DB Connection
$mysqli = new mysqli('localhost', 'root', '', 'peer_review_db');

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Get the PRID from the URL parameter
$pr_id = isset($_GET['pr_id']) ? (int)$_GET['pr_id'] : null;

// Fetch the feedback based on the PRID
if ($pr_id) {
    $stmt = $mysqli->prepare("SELECT * FROM pr_submissions WHERE pr_id = ?");
    $stmt->bind_param("i", $pr_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $feedback = $result->fetch_assoc();
    $stmt->close();
} else {
    // If no PRID is provided, fetch all feedback
    $stmt = $mysqli->prepare("SELECT * FROM pr_submissions ORDER BY submission_date DESC");
    $stmt->execute();
    $result = $stmt->get_result();
}

$mysqli->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PR Feedback</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>PR Feedback</h1>

        <!-- Search Form -->
        <form method="GET" action="pr_feedback.php" class="mb-4">
            <div class="input-group">
                <input type="number" name="pr_id" class="form-control" placeholder="Enter PRID" value="<?= htmlspecialchars($pr_id ?? '') ?>" required>
                <button class="btn btn-primary" type="submit">Search</button>
            </div>
        </form>

        <?php if ($pr_id): ?>
            <?php if ($feedback): ?>
                <h3>PR Feedback #<?= htmlspecialchars($feedback['pr_id']) ?></h3>
                <p><strong>Submitted by:</strong> <?= htmlspecialchars($feedback['submitter_email']) ?></p>
                <p><strong>Date:</strong> <?= htmlspecialchars($feedback['submission_date']) ?></p>
                <p><strong>Status:</strong> <?= htmlspecialchars($feedback['status']) ?></p>

                <h4>Answers:</h4>
                <ul>
                    <?php
                    $answers = json_decode($feedback['answers'], true);
                    foreach ($answers as $question => $answer) {
                        echo "<li><strong>$question:</strong> " . htmlspecialchars($answer) . "</li>";
                    }
                    ?>
                </ul>

                <h4>Uploaded Images:</h4>
                <?php
                $images = json_decode($feedback['image_paths'], true);
                if ($images) {
                    foreach ($images as $image) {
                        echo "<img src='uploads/$image' class='img-thumbnail' style='max-width: 200px; margin: 5px;' />";
                    }
                } else {
                    echo "<p>No images uploaded.</p>";
                }
                ?>
            <?php else: ?>
                <p>No feedback found for PRID <?= htmlspecialchars($pr_id) ?>.</p>
            <?php endif; ?>
        <?php else: ?>
            <h3>All PR Feedbacks:</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>PRID</th>
                        <th>Submitted By</th>
                        <th>Status</th>
                        <th>Submission Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($feedback = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($feedback['pr_id']) ?></td>
                            <td><?= htmlspecialchars($feedback['submitter_email']) ?></td>
                            <td><?= htmlspecialchars($feedback['status']) ?></td>
                            <td><?= htmlspecialchars($feedback['submission_date']) ?></td>
                            <td>
                                <a href="pr_feedback.php?pr_id=<?= htmlspecialchars($feedback['pr_id']) ?>" class="btn btn-info btn-sm">View Feedback</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
