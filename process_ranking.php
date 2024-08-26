<?php
include "db_connnection.php"; // Includes PHP file that executes the connection to the database
include "sanitize.php"; // Includes PHP file with sanitize function
$conn = OpenCon();

$response = array('status' => 'error', 'message' => '', 'rankMessage' => '');

if ($_POST && isset($_POST["username"]) && isset($_POST["time"]) && $_POST["username"] != "") {
    $username = sanitizeInputVar($conn, $_POST["username"]);
    $timeVal = $_POST["time"];
    $difficulty = 12;
    $dateVal = date("Ymd");

    // Check if the username already exists
    $checkUserQuery = $conn->prepare("SELECT * FROM leaderboards WHERE name = ? AND difficulty = ?");
    $checkUserQuery->bind_param("si", $username, $difficulty);
    $checkUserQuery->execute();
    $checkUserResult = $checkUserQuery->get_result();

    if ($checkUserResult->num_rows > 0) {
        // If the username exists, send back an error message
        $response['message'] = "Username already taken";
    } else {
        // Insert the new record
        $insertQuery = $conn->prepare("INSERT INTO leaderboards (name, difficulty, time, date) VALUES (?, ?, ?, ?)");
        $insertQuery->bind_param("siss", $username, $difficulty, $timeVal, $dateVal);
        $insertQuery->execute();

        // Calculate the player's rank
        $rankQuery = $conn->prepare("SELECT COUNT(*) AS rank FROM leaderboards WHERE time <= ? AND difficulty = ?");
        $rankQuery->bind_param("ii", $timeVal, $difficulty);
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
        $response['rankMessage'] = "You rank: $rank / $totalPlayers";
    }
}

CloseCon($conn);

// Return the response as JSON
echo json_encode($response);
