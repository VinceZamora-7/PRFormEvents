<?php
// DB Connection
$mysqli = new mysqli('localhost', 'root', '', 'peer_review_db');

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Get the PRID from the URL parameter
$pr_id = isset($_GET['pr_id']) ? $_GET['pr_id'] : null;  // Get PRID from URL

// Fetch the feedback based on the PRID
if ($pr_id) {
    // Ensure you use the 'pr_id' as a string since it has a custom format like 'PRID000001'
    $stmt = $mysqli->prepare("SELECT * FROM pr_submissions WHERE pr_id = ?");
    $stmt->bind_param("s", $pr_id);  // Use 's' for string, as PRID000001 is a string
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
    <link href="pr_feedback.css" rel="stylesheet">
</head>
<body>

<div class="container">

    <!-- Header Section -->
    <header>
        <h1>Peer Review Feedback</h1>
        <p>Review the submitted feedback and responses.</p>
    </header>

    <!-- Conditionally display the Back button -->
    <?php if ($pr_id): ?>
        <!-- Only show this button if a specific PR feedback is being viewed -->
        <a href="pr_feedback.php" class="btn btn-secondary mb-4">Back to All Feedback</a>
    <?php endif; ?>

    <!-- Search Form -->
    <form method="GET" action="pr_feedback.php" class="search-form">
        <div class="input-group">
            <input type="number" name="pr_id" class="search-input" placeholder="Enter PRID" value="<?= htmlspecialchars($pr_id ?? '') ?>" required>
            <button class="search-btn" type="submit">Search</button>
        </div>
    </form>

    <!-- Feedback Content -->
    <?php if ($pr_id): ?>
        <?php if ($feedback): ?>
            <!-- Display Feedback Card -->
            <div class="feedback-card">
                <h3>PR Feedback #<?= htmlspecialchars($feedback['pr_id']) ?></h3>
                <p><strong>Task Name:</strong> <?= htmlspecialchars($feedback['task_name']) ?></p>
                <p><strong>Peer Reviewer:</strong> <?= htmlspecialchars($feedback['peer_reviewer_name']) ?> (<?= htmlspecialchars($feedback['peer_reviewer_email']) ?>)</p>
                <p><strong>Builder:</strong> <?= htmlspecialchars($feedback['builder_name']) ?> (<?= htmlspecialchars($feedback['builder_email']) ?>)</p>

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
                <div class="images-gallery">
                    <?php
                    $images = json_decode($feedback['image_paths'], true);
                    if ($images) {
                        foreach ($images as $image) {
                            echo "<img src='uploads/$image' class='img-thumbnail' />";
                        }
                    } else {
                        echo "<p>No images uploaded.</p>";
                    }
                    ?>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">No feedback found for PRID <?= htmlspecialchars($pr_id) ?>.</div>
        <?php endif; ?>
    <?php else: ?>
        <!-- Display all PR Feedbacks if no PRID is provided -->
        <div class="feedback-table">
            <h3>All PR Feedbacks</h3>
            <table>
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
        </div>
    <?php endif; ?>

</div>

<script src="pr_feedback.js"></script>

</body>
</html>
