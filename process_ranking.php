<?php
include "db_connnection.php";
include "sanitize.php";
$conn = OpenCon();

$response = array('status' => 'error', 'message' => '', 'rankMessage' => '');

if ($_POST && isset($_POST["username"]) && isset($_POST["time"]) && $_POST["username"] != "") {
    $username = sanitizeInputVar($conn, $_POST["username"]);
    $timeStr = $_POST["time"];
    $difficulty = 12;
    $dateVal = date("Ymd");

    // Convert time string to seconds
    $timeParts = explode(':', $timeStr);
    $milliseconds = 0;
    
    // Handle milliseconds if they exist in the last part of the time string
    if (strpos(end($timeParts), '.') !== false) {
        list($seconds, $milliseconds) = explode('.', array_pop($timeParts));
        $timeParts[] = $seconds;
    } else {
        $milliseconds = 0;
    }

    // Adjust the timeParts array to ensure it has 3 elements: hours, minutes, and seconds
    while (count($timeParts) < 3) {
        array_unshift($timeParts, 0);
    }

    // Calculate the total time in seconds
    $timeVal = ($timeParts[0] * 3600) + ($timeParts[1] * 60) + $timeParts[2] + ($milliseconds / 1000);

    // Check if the username already exists
    $checkUserQuery = $conn->prepare("SELECT * FROM leaderboards WHERE name = ? AND difficulty = ?");
    $checkUserQuery->bind_param("si", $username, $difficulty);
    $checkUserQuery->execute();
    $checkUserResult = $checkUserQuery->get_result();

    if ($checkUserResult->num_rows > 0) {
        $response['message'] = "Username already taken";
    } else {
        // Insert the new record
        $insertQuery = $conn->prepare("INSERT INTO leaderboards (name, difficulty, time, date) VALUES (?, ?, ?, ?)");
        $insertQuery->bind_param("sids", $username, $difficulty, $timeVal, $dateVal);
        
        if ($insertQuery->execute()) {
            // Calculate the player's rank
            $rankQuery = $conn->prepare("SELECT COUNT(*) AS rank FROM leaderboards WHERE difficulty = ? AND time <= ?");
            $rankQuery->bind_param("id", $difficulty, $timeVal);
            $rankQuery->execute();
            $rankResult = $rankQuery->get_result();
            $rank = $rankResult->fetch_assoc()['rank'];

            // Get the total number of players
            $totalPlayersQuery = $conn->prepare("SELECT COUNT(*) AS total FROM leaderboards WHERE difficulty = ?");
            $totalPlayersQuery->bind_param("i", $difficulty);
            $totalPlayersQuery->execute();
            $totalPlayersResult = $totalPlayersQuery->get_result();
            $totalPlayers = $totalPlayersResult->fetch_assoc()['total'];

            $response['status'] = "success";
            $response['rankMessage'] = "Your rank: $rank / $totalPlayers";
        } else {
            $response['message'] = "Error inserting record: " . $conn->error;
        }
    }
} else {
    $response['message'] = "Invalid form submission or missing required fields.";
}

CloseCon($conn);

// Return the response as JSON
echo json_encode($response);
?>
