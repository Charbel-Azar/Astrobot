<?php
include "db_connnection.php"; // Includes PHP file that executes the connection to the database
include "sanitize.php"; // Includes PHP file with sanitize function
$conn = OpenCon();

$rankMessage = ""; // Initialize the rank message
$errorMessage = ""; // Initialize the error message for the username already taken

if ($_POST && isset($_POST["username"]) && isset($_POST["time"]) && $_POST["username"] != "") {
    $username = sanitizeInputVar($conn, $_POST["username"]); // Variable that stores Player's name
    $timeVal = $_POST["time"]; // Variable that stores the time of stopwatch
    $difficulty = 12; // Set difficulty to always be 12 cards
    $dateVal = date("Ymd"); // Variable that stores the current date

    // Check if the username already exists
    $checkUserQuery = $conn->prepare("SELECT * FROM leaderboards WHERE name = ? AND difficulty = ?");
    $checkUserQuery->bind_param("si", $username, $difficulty);
    $checkUserQuery->execute();
    $checkUserResult = $checkUserQuery->get_result();

    if ($checkUserResult->num_rows > 0) {
        // If user exists, set an error message
        $errorMessage = "Username already taken";
    } else {
        // If user does not exist, insert a new record
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

        $rankMessage = "You rank: $rank / $totalPlayers";

        CloseCon($conn); // Close the connection with the database

        // Redirect to avoid form resubmission on refresh
        header("Location: index.php?success=1&rankMessage=" . urlencode($rankMessage));
        exit();
    }
}

// Remove the difficulty selection and set cardNumber directly
$cardNumber = 4; // Always use 12 cards

// Function that defines the card numbers in one row
function CardsInRow($cardN)
{
    switch($cardN)
    {
        case 12: 
            return 4; // 4x3 grid for 12 cards
        break;

        // Other cases can be removed if not needed

        default:
            $result = ceil(sqrt($cardN));
            return $result;
    }
}

$imagePath = "img/astroresized/"; // Path where the icons are saved

$imagesArr = array(
    "ASTRO_BOT_OFFICIAL_009",
    "ASTRO_BOT_OFFICIAL_019",
    "ASTRO_BOT_OFFICIAL_020",
    "ASTRO_BOT_OFFICIAL_021",
    "ASTRO_BOT_OFFICIAL_024",
    "ASTRO_PLAYROOM_OFFICIAL_08",
    "bloodborne_hunter_pose_0001_2",
    "bothelper_pose_packshot_0001",
    "icon_astro_mine_pose",
    "icon_enemy_bird_pulltower_render_pose_rt_",
    "icon_enemy_hoovermissile_render_pose_rt_",
    "icon_VIP_Bot_GOW_Kratos_2",
    "icon_enemy_ironball_render_pose_rt_2",
    "icon_wildlife_fenec_1",
    "icon_wildlife_raven_1",
    "icon_wildlife_wolf_1",
    "toucan_pose_pachshot_0001",
    "wildlife_red_panda_packshot_0010"
);

$badge = "js-badge.png"; // Name of front side of card

$cardValue = 0; // Variable that defines the width and height of one card

$cardWidth = $cardValue; // Width of one card
$cardHeight = $cardValue; // Height of one card

$boxWidth = 860; // The total size of the box on which all cards are displayed
$boxHeight = ($cardValue * ($cardNumber / CardsInRow($cardNumber))) + 170; // Box height that changes depending on card numbers
?>
<!DOCTYPE html>
<html lang="en">
<link rel="shortcut icon" type="image/x-icon" href="img/logo white.png">
<head>
    <meta charset="UTF-8">
    <title>Astro Bot</title>
    <link href="css/styles.css" rel="stylesheet">
</head>
<body>
<header>
    <!-- Just Header with 3 links -->
</header>

<main>
<div class="card">
<div class="leaderboard-icon">
        <a href="leaderboards.php">
            <img src="img/winner.png" alt="Leaderboard">
        </a>
    </div>
    <div class="header-title">
        <img src="img/logo long.png" style="width:45%;" alt="Astro Bot Logo" class="logo">
    </div>

    
    <div class="container">
        <div class="parent-div">
            <div id="stopwatch">
                <span class="stopwatch-time">00:00:00.000</span>
            </div>
        </div>  

        <?php
        $columns = 4; // 4x3 grid for 12 cards
        echo "<div class='memory-game' style='grid-template-columns: repeat($columns, 1fr);'>";
        shuffle($imagesArr);
        $val = 0;
        for ($i = 0; $i < $cardNumber; $i++) {
            if ($i % 2 == 0) {
                $val += 1;
            }
            echo "<div class='memory-card' data-framework='{$imagesArr[$val]}'>";
            echo "<div class='front-face'><img src='{$imagePath}{$imagesArr[$val]}.png' alt='{$imagesArr[$val]}'></div>";
            echo "<div class='back-face'><img src='img/logo white.png' alt='Logo'></div>";
            echo "</div>";
        }
        echo "</div>";
        ?>

        <!-- The Congratulations window that appears after the player wins -->
        <div class="win-box">
            <div class="win-content">
                <h1>Congratulations, You won!</h1>
                <div id="stopwatch-end">
                    <span>Your time: </span>
                    <span class="stopwatch-time">00:00:00</span>
                </div>
                <!-- Form that is used to submit the results to the server database -->
                <form id="win-form">
                    <input type="hidden" class="stopwatch-time" value="" name="time">
                    <input type="hidden" id="difficulty" value="<?php echo $cardNumber; ?>" name="difficulty">
                    <label for="username" id="username-label" style="padding: 0">Type your name</label>
                    <input type="text" maxlength="32" size="32" id="username" name="username" required />
                    <span id="username-error" style="color: red; display: none;"></span>
                    <input type="submit" value="Submit" id="submit-button" style="margin-top: 25px;" />
     

                </form>
                <input type="button" value="Play Again" id="play-again-button" onclick="location.reload();">
                <script type="text/javascript">
               document.getElementById("win-form").onsubmit = function(e) {
    e.preventDefault(); // Prevent the form from submitting in the traditional way
    
    var username = document.getElementById("username").value;
    var time = document.querySelector(".stopwatch-time").textContent; // Capture the time
    var difficulty = document.getElementById("difficulty").value;

    // AJAX request to submit the form data
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "process_ranking.php", true); // Use a separate PHP file to handle the logic
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            var response = JSON.parse(xhr.responseText);

            if (response.status === "error") {
                document.getElementById("username-error").textContent = response.message;
                document.getElementById("username-error").style.display = "block";
            } else {
                document.getElementById("username-error").style.display = "none";

                // Display the rank message
                var rankMessageDiv = document.getElementById("rank-message");
                rankMessageDiv.innerHTML = "<h2>" + response.rankMessage + "</h2>" + 
                                           "<a href='leaderboards.php' class='start-again-button' style='margin-top: 20px;'>Leaderboard</a>";
                rankMessageDiv.style.textAlign = "center";
                rankMessageDiv.style.marginTop = "20px";
                rankMessageDiv.style.color = "green";

                // Hide the Submit button
                document.getElementById("submit-button").style.display = "none";

                // Show the Play Again button
                document.getElementById("play-again-button").style.display = "inline-block";
            }
        }
    };

    xhr.send("username=" + encodeURIComponent(username) + "&time=" + encodeURIComponent(time) + "&difficulty=" + encodeURIComponent(difficulty));
};



                </script>

                <?php 
                    if (isset($_GET['rankMessage'])) {
                        echo "<div id='rank-message' style='text-align: center; margin-top: 20px;'>
                                <a href='leaderboards.php' class='start-again-button' style='margin-top: 20px;'>Leaderboard</a>
                                   <h2>" . htmlspecialchars($_GET['rankMessage']) . "</h2>
                            </div>";
                    } else {
                        echo "<div id='rank-message' style='text-align: center; margin-top: 20px; color: red;'>
                                <h2></h2>
                            </div>";
                    }
                ?>


            </div>
        </div>
    </div>
</div>
</main>
</body>
<script type="text/javascript">
    function showWinBox() {
        document.querySelector('.win-box').style.display = 'block'; // Show the win box
        document.querySelector('.win-content').style.display = 'block'; // Ensure content is shown
    }

    function checkWinCondition() {
        const allCards = document.querySelectorAll('.memory-card');
        const allFlipped = Array.from(allCards).every(card => card.classList.contains('flip'));

        if (allFlipped) {
            showWinBox(); // Trigger the win box display
        }
    }

    function openLeaderboard() {
        // Open the leaderboard page in a new tab
        let leaderboardWindow = window.open('leaderboards.php', '_blank');
        
        // Once the form is submitted, focus on the leaderboard and refresh it
        setTimeout(function() {
            leaderboardWindow.location.reload();
        }, 500); // Adjust the timeout if necessary to ensure the form submission completes
    }
</script>
<script type="text/javascript">
    var cardNumber = <?php echo json_encode($cardNumber); ?>; // This code passes PHP variable cardNumber to JavaScript
</script>
<script type="text/javascript" src="scripts/script.js" async></script>
</html>
