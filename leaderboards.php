<?php
session_start();
include("./restapi/connection.php");
include("./role_based_header.php");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Function to get leaderboard data from API
function fetchLeaderboard($pdo) {
    try {
        $stmt = $pdo->query("SELECT Id, Username, Points FROM Users ORDER BY Points DESC LIMIT 10");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return null;
    }
}

$leaderboard = fetchLeaderboard($pdo);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap">
    <style>

    
        h1 {
            color: #333;
            text-align: center;
            font-size: 2.5em;
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 12px;
            text-align: center;
        }

        th {
            background-color: #1d2b4f;
            color: white;
            font-size: 1.2em;
        }

        td {
            background-color: #f9f9f9;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #eaeaea;
        }

        .rank {
            font-weight: bold;
            color: #2d87f0;
        }

        .username {
            color: #555;
        }

        .points {
            color: #888;
        }

        footer {
            text-align: center;
            margin-top: 30px;
            padding: 10px;
            background-color: #2e3a59;
            color: white;
        }
    </style>
</head>
<body>

<header>
    <h1>Leaderboard</h1>
</header>

<div class="container">
    <h2>Top 10 Users</h2>
    <table>
        <thead>
            <tr>
                <th>Rank</th>
                <th>Username</th>
                <th>Points</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($leaderboard): ?>
                <?php foreach ($leaderboard as $index => $user): ?>
                    <tr>
                        <td class="rank"><?= $index + 1 ?></td>
                        <td class="username"><?= htmlspecialchars($user['Username']) ?></td>
                        <td class="points"><?= $user['Points'] ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3">No leaderboard data found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<footer>
    <p>&copy; 2025 Share|K. All rights reserved.</p>
</footer>

</body>
</html>
