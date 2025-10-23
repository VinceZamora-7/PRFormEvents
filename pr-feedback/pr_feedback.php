<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

    // Decode the answers and questions only if they are not NULL
    $answers = !is_null($feedback['answers']) ? json_decode($feedback['answers'], true) : [];
    $questions = !is_null($feedback['questions']) ? json_decode($feedback['questions'], true) : [];

    // Fetch questions for reference (this will be used if answers need to be looped)
    $query_questions = "SELECT * FROM questions";
    $questions_result = $mysqli->query($query_questions);

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
    
    <!-- Search Form -->
     <?php if (!$pr_id): ?>
    <form method="GET" action="pr_feedback.php" class="search-form">
        <div class="input-group">
            <input type="text" name="pr_id" class="search-input" placeholder="Enter PRID" value="<?= htmlspecialchars($pr_id ?? '') ?>" required>
            <button class="search-btn" type="submit">Search</button>
        </div>
    </form>
    <?php endif; ?>

    <!-- Conditionally display the Back button -->
    <?php if ($pr_id): ?>
        <a href="pr_feedback.php" class="btn btn-secondary mb-4">Back to All Feedback</a>
    <?php endif; ?>


    <!-- Feedback Content -->
    <?php if ($pr_id): ?>
        <?php if ($feedback): ?>
            <!-- Display Feedback Card -->
            <div class="feedback-card">
                <div class="taskInfo">
                    <h3><strong><?= htmlspecialchars($feedback['task_name']) ?></strong> </h3>
                    <p><strong><?= htmlspecialchars($feedback['pr_id']) ?></strong></p>
                    <p><strong>Peer Reviewer:</strong> <?= htmlspecialchars($feedback['peer_reviewer_name']) ?> (<?= htmlspecialchars($feedback['peer_reviewer_email']) ?>)</p>
                    <p><strong>Builder:</strong> <?= htmlspecialchars($feedback['builder_name']) ?> (<?= htmlspecialchars($feedback['builder_email']) ?>)</p>
                    <p><strong>Date:</strong> <?= htmlspecialchars($feedback['submission_date']) ?></p>
                    <p><strong>Status:</strong> <span id="prStatus"><?= htmlspecialchars($feedback['status']) ?></span></p>
                </div>
                <!-- Questions and Answers Section -->
                <h4>Feedback</h4>
                <ul>
                    <?php 
                        // Loop through each question and display the corresponding answer
                        while ($question = $questions_result->fetch_assoc()) {
                            $question_text = $question['question_text'];
                            $question_id = $question['question_id'];
                            
                            // Construct the key to fetch the answer from the decoded answers
                            $answer_key = 'q' . $question_id;
                            $answer = isset($answers[$answer_key]) && !empty($answers[$answer_key]) ? $answers[$answer_key] : null;
                            
                            // Display question and answer only if answer exists and is not 'Not Applicable'
                            if ($answer && strtolower($answer) !== 'not applicable') {
                                echo "<li><strong>" . htmlspecialchars($question_text) . "</strong><br>";
                                echo "<strong>Answer:</strong> " . htmlspecialchars($answer);
                                
                                // If answer is "Applicable", show Fatality and Remarks
                                if (strtolower($answer) === "applicable") {
                                    // Fatality key
                                    $fatality_key = 'fatality' . $question_id;
                                    $fatality = isset($answers[$fatality_key]) ? $answers[$fatality_key] : null;

                                    if ($fatality === 'fatal') {
                                        $fatality_display = "<span class='highlight'>Fatal Error</span>";
                                    } elseif ($fatality === 'nonFatal') {
                                        $fatality_display = "Non-Fatal Error";
                                    } else {
                                        $fatality_display = "Not specified";
                                    }

                                    echo "<br><strong>Fatality:</strong> " . $fatality_display;

                                    // Remarks key
                                    $remarks_key = 'remarks' . $question_id;
                                    $remarks = isset($answers[$remarks_key]) ? $answers[$remarks_key] : 'No remarks provided';
                                    echo "<br><strong>Remarks:</strong> " . htmlspecialchars($remarks);
                                }

                                // Show images for the current question only
                                echo "<br><strong>Proof:</strong><br>";
                                $images = isset($feedback['image_paths']) ? json_decode($feedback['image_paths'], true) : [];
                                $qKey = 'q' . $question_id;

                                if (isset($images[$qKey]) && count($images[$qKey]) > 0) {
                                    foreach ($images[$qKey] as $image) {
                                        $imagePath = '../uploads/' . htmlspecialchars($image);  // Adjusted relative path
                                        echo "<img src='{$imagePath}' class='img-thumbnail' alt='Uploaded Image' style='max-width:150px; margin:5px;' />";
                                    }
                                } else {
                                    echo "<p>No images uploaded.</p>";
                                }

                                echo "</li><hr>";
                            }
                        }
                    ?>

                </ul>
                <?php if ($feedback['status'] !== 'Completed'): ?>
                    <div id="actionButtons" class="action">
                        <button
                            type="button"
                            class="btn btn-danger"
                            id="appeal"
                        >
                            Appeal
                        </button>
                        <button
                            type="button"
                            class="btn btn-success"
                            id="accept"
                        >
                            Accept
                        </button>
                        <button id="sendEmailBtn" class="btn btn-primary">
                            <i class="bi bi-send"></i>Send Email
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">No feedback found for PRID <?= htmlspecialchars($pr_id) ?>.</div>
        <?php endif; ?>
    <?php else: ?>
        <!-- Display all PR Feedbacks if no PRID is provided -->
        <div class="feedback-table">
            <h3>All PR Feedbacks</h3>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>PRID</th>
                        <th>Task Name</th>
                        <th>Status</th>
                        <th>Submission Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($feedback = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($feedback['pr_id']) ?></td>
                            <td><?= htmlspecialchars($feedback['task_name']) ?></td>
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

<script>
document.getElementById('sendEmailBtn').addEventListener('click', function () {
  const urlParams = new URLSearchParams(window.location.search);
  const prId = urlParams.get("pr_id");

  const taskName = <?= json_encode($feedback['task_name'] ?? '') ?>;
  const builderEmail = <?= json_encode(filter_var($feedback['builder_email'] ?? '', FILTER_SANITIZE_EMAIL)) ?>;

  if (!prId || !taskName || !builderEmail) {
    alert("Missing required fields.");
    return;
  }

  let bodyText =
    `Peer Review Feedback:\n\n` +
    `Hope you're doing well!\n\n` +
    `Task Name: ${taskName}\n\n` +
    `I've noticed that there are some errors\n` +
    `For reference, here is the PRID: ${prId}\n\n` +
    `Here is the link to the feedback page: http://localhost/EVENTS/EVENT-PR/pr-feedback/pr_feedback.php?pr_id=${prId}\n\n` +
    `Thank you so much!`;

  const subject = encodeURIComponent(`PR Feedback: ${taskName}`);
  const body = encodeURIComponent(bodyText);
  const email = encodeURIComponent(builderEmail);

  window.location.href = `mailto:${email}?subject=${subject}&body=${body}`;
});

document.getElementById('accept').addEventListener('click', function () {
  const prId = new URLSearchParams(window.location.search).get("pr_id");

  if (!prId) {
    alert("PRID is missing.");
    return;
  }

  const xhr = new XMLHttpRequest();
  xhr.open('POST', 'update_status.php', true);
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

  xhr.onload = function () {
    if (xhr.status === 200) {
      document.getElementById('prStatus').textContent = 'Completed';

      const buttonsContainer = document.getElementById('actionButtons');
      if (buttonsContainer) {
        buttonsContainer.style.display = 'none';
      }
    } else {
      alert("Failed to update the status.");
    }
  };

  xhr.send('pr_id=' + encodeURIComponent(prId) + '&status=Completed');
});


</script>



</body>
</html>
