<?php
session_start();
include 'db/connection.php';
include 'header.php';
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fetch candidates for the checkbox list
$sqlCandidates = "SELECT * FROM candidates";
$candidatesResult = mysqli_query($conn, $sqlCandidates);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_event'])) {
    $eventName = mysqli_real_escape_string($conn, $_POST['event_name']);
    $eventDate = mysqli_real_escape_string($conn, $_POST['update_at']);
    $posterImage = $_FILES['poster_image']['name'];
    $targetDir = "image/";
    $targetFile = $targetDir . basename($posterImage);

    // Move uploaded poster image
    if (move_uploaded_file($_FILES['poster_image']['tmp_name'], $targetFile)) {
        echo "File uploaded successfully.";
    } else {
        echo "Error uploading file.";
    }

    // Insert event into the database
    $sqlEvent = "INSERT INTO events (name, poster_image, update_at) VALUES ('$eventName', '$posterImage', '$eventDate')";
    if (mysqli_query($conn, $sqlEvent)) {
        $eventId = mysqli_insert_id($conn);  // Get the last inserted event ID
        // Assign candidates to the event
        if (!empty($_POST['candidate_ids'])) {
            foreach ($_POST['candidate_ids'] as $candidateId) {
                $sqlCandidateEvent = "INSERT INTO candidates_events (candidate_id, event_id) VALUES ('$candidateId', '$eventId')";
                if (!mysqli_query($conn, $sqlCandidateEvent)) {
                    echo "Error assigning candidate: " . mysqli_error($conn);
                }
            }
        } else {
            echo "No candidates selected.";
        }

        echo "<p>Event created successfully.</p>";
    } else {
        echo "Error creating event: " . mysqli_error($conn);  // Display error from MySQL
    }
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Create Event</title>
        <link rel="stylesheet" href="style.css">
    </head>
        <header class="sticky-header">
            <div class="header-container">
                <h1 class="logo">Vote System</h1>
                <nav>
                    <a href="event_management.php">Manage event</a>
                    <a href="candidate_management.php">Manage Candidates</a>
                    <a href="staff_management.php">Manage Staff</a>
                    <a href="dashboard.php">Dashboard</a>
                    <a href="logout.php">Logout</a>
                </nav>
            </div>
        </header>
    <body>

        <h2>Create New Event</h2>

        <form action="create_event.php" method="POST" enctype="multipart/form-data">
            <label for="event_name">Event Name</label>
            <input type="text" name="event_name" id="event_name" required>

            <label for="event_date">Event Date</label>
            <input type="date" name="event_date" id="event_date" required>

            <label for="poster_image">Event Poster</label>
            <input type="file" name="poster_image" id="poster_image" required>

            <label for="candidate_ids">Select Candidates</label>
            <div class="candidate-list">
                <?php while ($candidate = mysqli_fetch_assoc($candidatesResult)) { ?>
                    <div class="candidate-item">
                        <input type="checkbox" name="candidate_ids[]" value="<?= $candidate['id']; ?>" id="candidate-<?= $candidate['id']; ?>" class="candidate-checkbox" required>
                        <label for="candidate-<?= $candidate['id']; ?>" class="candidate-info">
                            <img src="image/<?= $candidate['photo']; ?>" alt="<?= $candidate['name']; ?> Image">
                            <strong><?= $candidate['name']; ?></strong>
                            <span><?= $candidate['party']; ?></span>
                        </label>
                    </div>
                <?php } ?>
            </div>

            <button type="submit" name="create_event">Create Event</button>
        </form>

    </body>
</html>


<style>
    /* Styling candidates list */
    .candidate-list {
        display: flex;
        flex-wrap: wrap;
    }

    .candidate-item {
        display: flex;
        align-items: center;
        width: 200px;
        margin: 10px;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .candidate-item:hover {
        background-color: #f0f0f0;
    }

    .candidate-item img {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        margin-right: 10px;
    }

    .candidate-info {
        flex: 1;
    }

    .candidate-info strong {
        display: block;
        font-weight: bold;
    }

    .candidate-info span {
        font-size: 12px;
        color: #666;
    }

    .candidate-checkbox {
        margin-left: 10px;
    }

    label {
        font-weight: bold;
    }
    /* General styles for the page */
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        background-color: #f4f7fc;
    }

    /* Sticky Header Styles */
    .sticky-header {
        position: sticky;
        top: 0;
        background-color: white;
        color: black;
        padding: 15px;
        z-index: 1000;
    }

    .header-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        max-width: 1200px;
        margin: 0 auto;
    }

    .sticky-header h1 {
        margin: 0;
    }

    .sticky-header nav {
        display: flex;
        gap: 20px;
    }

    .sticky-header nav a {
        color: black;
        text-decoration: none;
        font-size: 16px;
        padding: 5px 10px;
        transition: background-color 0.3s ease;
    }

    .sticky-header nav a:hover {
        background-color: #ddd;
    }

    /* Container for the content */
    .container {
        margin: 20px auto;
        max-width: 1200px;
        padding: 20px;
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    /* Candidate Management Section */
    .candidate-management {
        margin-top: 20px;
    }

    /* Form Styles */
    form {
        margin-bottom: 20px;
        display: flex;
        flex-direction: column;
    }

    form input[type="text"], form input[type="file"] {
        padding: 10px;
        margin-bottom: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
    }

    form label {
        font-size: 14px;
        margin-bottom: 5px;
    }

    form button[type="submit"] {
        background-color: #87CEEB; /* Light Blue */
        color: white;
        border: none;
        cursor: pointer;
        padding: 10px 20px;
        border-radius: 5px;
        font-size: 16px;
        transition: background-color 0.3s ease;
    }

    form button[type="submit"]:hover {
        background-color: #00BFFF; /* Slightly darker blue */
    }

    /* Table Styles */
    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }

    table, th, td {
        border: 1px solid #ddd;
    }

    th, td {
        padding: 12px;
        text-align: left;
    }

    th {
        background-color: #f4f7fc;
        color: #333;
    }

    td img {
        max-width: 100px;
        max-height: 100px;
        object-fit: cover;
    }
</style>