<?php
include "db_connnection.php"; // Includes PHP file that executes the connection to the database
include "sanitize.php"; // Includes PHP file with sanitize function

$conn = OpenCon();

if (isset($_POST["username"])) {
    $username = sanitizeInputVar($conn, $_POST["username"]); // Variable that stores Player's name
    $difficulty = 12; // Set difficulty to always be 12 cards

    // Check if the username already exists
    $checkUserQuery = "SELECT * FROM leaderboards WHERE name = '$username' AND difficulty = $difficulty";
    $checkUserResult = $conn->query($checkUserQuery);

    if ($checkUserResult->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "Username already taken"]);
    } else {
        echo json_encode(["status" => "success"]);
    }

    CloseCon($conn); // Close the connection with the database
}
?>
