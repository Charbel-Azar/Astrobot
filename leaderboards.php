<?php
include "db_connnection.php";
$conn = OpenCon(); // Open the connection with the database
?>
<!DOCTYPE html>
<html lang="en">
<link rel="shortcut icon" type="image/x-icon" href="img/logo white.png">
    <head>
        <meta charset="UTF-8">
        <title>Leaderboards</title>
        <link href="css/styles.css" rel="stylesheet">
        <style>
             
        </style>
    </head>
<body>
    <header>
        <!-- Header with title and navigation links -->
      
    </header>


    <main>
    <div class="card">
    <div class="header-title">
            <img src="img/logo long.png" style="width:45%;" alt="Astro Bot Logo" class="logo">
        </div>
            <!-- Search Bar -->
            <div class="search-bar">
                    <input type="text" id="searchInput" placeholder="Find your ranking..." onkeyup="filterTable()">
                </div>

    
                <div class="container" >
            <!-- Centered leaderboard table with styled borders -->
            <?php
            // Select name and time, and order by time (fastest first)
            $sql = "SELECT name, time FROM leaderboards ORDER BY time ASC";
            $result = $conn->query($sql);

            echo "<div class='leaderboard-box' style=\"    border: 3px solid #000;\">";
            echo "<table id='leaderboardTable'>";
            echo "<tr>";
            echo "<th>#</th>"; // Added column for numbering
            echo "<th>Name</th>";
            echo "<th>Time</th>";
            echo "</tr>";

            if ($result->num_rows > 0) {
                $rank = 1; // Initialize rank counter
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $rank++ . "</td>"; // Display rank number
                    echo "<td>" . htmlspecialchars($row['name']) . "</td>"; // Secure output of name
                    
                    // Convert the time from milliseconds to hours, minutes, seconds, and milliseconds
                    $timeInMillis = floatval($row["time"]);
                    $hours = floor($timeInMillis / 3600000);
                    $minutes = floor(($timeInMillis % 3600000) / 60000);
                    $seconds = floor(($timeInMillis % 60000) / 1000);
                    $milliseconds = $timeInMillis % 1000;
                    
                    $formattedTime = sprintf('%02d:%02d:%02d.%03d', $hours, $minutes, $seconds, $milliseconds);

                    echo "<td>" . htmlspecialchars($formattedTime) . "</td>"; // Correctly format and display the time
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='3' style='text-align: center;'>No results</td></tr>";
            }

            echo "</table>";
            echo "</div>";

            CloseCon($conn); // Close the connection with the database
            ?>
        </div>

        <!-- Start Again button -->
        <a href="index.php" class="start-again-button">Start Again</a>
        
        
        </div>
    </main>

    <script>
        function filterTable() {
            // Declare variables
            var input, filter, table, tr, td, i, txtValue;
            input = document.getElementById("searchInput");
            filter = input.value.toUpperCase();
            table = document.getElementById("leaderboardTable");
            tr = table.getElementsByTagName("tr");

            // Loop through all table rows, and hide those who don't match the search query
            for (i = 1; i < tr.length; i++) { // Start at 1 to skip the table header
                td = tr[i].getElementsByTagName("td")[1]; // Search in the second column (Name)
                if (td) {
                    txtValue = td.textContent || td.innerText;
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }       
            }
        }
    </script>
</body>
</html>
