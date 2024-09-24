<?php
require '../models/database.php';

// Fetch available jobs
$stmt = $pdo->prepare("SELECT * FROM jobs WHERE id NOT IN (SELECT job_id FROM job_applications WHERE user_id = ?)");
$stmt->execute([$_SESSION['user_id']]);
$available_jobs = $stmt->fetchAll();

// If the form is submitted (applying for a job)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply']) && isset($_POST['job_id'])) {
    $job_id = $_POST['job_id'];
    $user_id = $_SESSION['user_id'];

    try {
        // Check if the user has already applied for the job
        $checkStmt = $pdo->prepare("SELECT * FROM job_applications WHERE user_id = ? AND job_id = ?");
        $checkStmt->execute([$user_id, $job_id]);

        if ($checkStmt->rowCount() > 0) {
            echo "<p class='error'>You have already applied for this job!</p>";
        } else {
            // Insert a new job application into the database
            $insertStmt = $pdo->prepare("INSERT INTO job_applications (user_id, job_id, status) VALUES (?, ?, 'Pending')");
            $insertStmt->execute([$user_id, $job_id]);

            echo "<p class='success'>You have successfully applied for the job!</p>";
        }
    } catch (Exception $e) {
        // Log the error and display a message
        error_log("Error applying for job: " . $e->getMessage());
        echo "<p class='error'>An error occurred while applying for the job. Please try again later.</p>";
    }
}