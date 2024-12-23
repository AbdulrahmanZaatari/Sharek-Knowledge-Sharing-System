<?php
header("Content-Type: application/json");
include 'connection.php';

$method = $_SERVER['REQUEST_METHOD'];
$request = isset($_GET['resource']) ? explode('/', trim($_GET['resource'], '/')) : [];
$resource = array_shift($request); // The resource (e.g., users, posts)
$id = array_shift($request); // The optional ID

switch ($resource) {
    case 'users':
        handleUsers($pdo, $method, $id);
        break;
    case 'tags':
        handleTags($pdo, $method, $id);
        break;
    case 'categories':
        handleCategories($pdo, $method, $id);
        break;
    case 'achievements':
        handleAchievements($pdo, $method, $id);
        break;
    case 'challenges':
        handleChallenges($pdo, $method, $id);
        break;
    case 'posts':
        handlePosts($pdo, $method, $id);
        break;
    case 'posttags':
        handlePostTags($pdo, $method, $id);
        break;
    case 'polls':
        handlePolls($pdo, $method, $id);
        break;
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Resource not found']);
        break;
}

// Functions to handle resources

function handleUsers($pdo, $method, $id) {
    switch ($method) {
        case 'GET':
            if ($id) {
                $stmt = $pdo->prepare("SELECT * FROM Users WHERE Id = ?");
                $stmt->execute([$id]);
                echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
            } else {
                $stmt = $pdo->query("SELECT * FROM Users");
                echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            }
            break;
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("INSERT INTO Users (Username, Email, PasswordHash) VALUES (?, ?, ?)");
            $stmt->execute([$data['Username'], $data['Email'], $data['PasswordHash']]);
            echo json_encode(['message' => 'User created', 'id' => $pdo->lastInsertId()]);
            break;
        case 'PUT':
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing user ID']);
                return;
            }
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("UPDATE Users SET Username = ?, Email = ?, PasswordHash = ? WHERE Id = ?");
            $stmt->execute([$data['Username'], $data['Email'], $data['PasswordHash'], $id]);
            echo json_encode(['message' => 'User updated']);
            break;
        case 'DELETE':
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing user ID']);
                return;
            }
            $stmt = $pdo->prepare("DELETE FROM Users WHERE Id = ?");
            $stmt->execute([$id]);
            echo json_encode(['message' => 'User deleted']);
            break;
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }
}

function handleTags($pdo, $method, $id) {
    switch ($method) {
        case 'GET':
            if ($id) {
                $stmt = $pdo->prepare("SELECT * FROM Tags WHERE Id = ?");
                $stmt->execute([$id]);
                echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
            } else {
                $stmt = $pdo->query("SELECT * FROM Tags");
                echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            }
            break;
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("INSERT INTO Tags (Name) VALUES (?)");
            $stmt->execute([$data['Name']]);
            echo json_encode(['message' => 'Tag created', 'id' => $pdo->lastInsertId()]);
            break;
        case 'PUT':
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing tag ID']);
                return;
            }
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("UPDATE Tags SET Name = ? WHERE Id = ?");
            $stmt->execute([$data['Name'], $id]);
            echo json_encode(['message' => 'Tag updated']);
            break;
        case 'DELETE':
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing tag ID']);
                return;
            }
            $stmt = $pdo->prepare("DELETE FROM Tags WHERE Id = ?");
            $stmt->execute([$id]);
            echo json_encode(['message' => 'Tag deleted']);
            break;
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }
}

function handleCategories($pdo, $method, $id) {
    switch ($method) {
        case 'GET':
            if ($id) {
                $stmt = $pdo->prepare("SELECT * FROM Categories WHERE Id = ?");
                $stmt->execute([$id]);
                echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
            } else {
                $stmt = $pdo->query("SELECT * FROM Categories");
                echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            }
            break;
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("INSERT INTO Categories (Name, ParentCategoryId) VALUES (?, ?)");
            $stmt->execute([$data['Name'], $data['ParentCategoryId']]);
            echo json_encode(['message' => 'Category created', 'id' => $pdo->lastInsertId()]);
            break;
        case 'PUT':
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing category ID']);
                return;
            }
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("UPDATE Categories SET Name = ?, ParentCategoryId = ? WHERE Id = ?");
            $stmt->execute([$data['Name'], $data['ParentCategoryId'], $id]);
            echo json_encode(['message' => 'Category updated']);
            break;
        case 'DELETE':
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing category ID']);
                return;
            }
            $stmt = $pdo->prepare("DELETE FROM Categories WHERE Id = ?");
            $stmt->execute([$id]);
            echo json_encode(['message' => 'Category deleted']);
            break;
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }
}

function handleAchievements($pdo, $method, $id) {
    switch ($method) {
        case 'GET':
            if ($id) {
                $stmt = $pdo->prepare("SELECT * FROM Achievements WHERE Id = ?");
                $stmt->execute([$id]);
                echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
            } else {
                $stmt = $pdo->query("SELECT * FROM Achievements");
                echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            }
            break;
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("INSERT INTO Achievements (Name, Description, Points) VALUES (?, ?, ?)");
            $stmt->execute([$data['Name'], $data['Description'], $data['Points']]);
            echo json_encode(['message' => 'Achievement created', 'id' => $pdo->lastInsertId()]);
            break;
        case 'PUT':
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing achievement ID']);
                return;
            }
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("UPDATE Achievements SET Name = ?, Description = ?, Points = ? WHERE Id = ?");
            $stmt->execute([$data['Name'], $data['Description'], $data['Points'], $id]);
            echo json_encode(['message' => 'Achievement updated']);
            break;
        case 'DELETE':
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing achievement ID']);
                return;
            }
            $stmt = $pdo->prepare("DELETE FROM Achievements WHERE Id = ?");
            $stmt->execute([$id]);
            echo json_encode(['message' => 'Achievement deleted']);
            break;
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }
}

function handleChallenges($pdo, $method, $id) {
    switch ($method) {
        case 'GET':
            if ($id) {
                $stmt = $pdo->prepare("SELECT * FROM Challenges WHERE Id = ?");
                $stmt->execute([$id]);
                echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
            } else {
                $stmt = $pdo->query("SELECT * FROM Challenges");
                echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            }
            break;
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("INSERT INTO Challenges (Name, Description, StartDate, EndDate, RewardPoints) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$data['Name'], $data['Description'], $data['StartDate'], $data['EndDate'], $data['RewardPoints']]);
            echo json_encode(['message' => 'Challenge created', 'id' => $pdo->lastInsertId()]);
            break;
        case 'PUT':
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing challenge ID']);
                return;
            }
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("UPDATE Challenges SET Name = ?, Description = ?, StartDate = ?, EndDate = ?, RewardPoints = ? WHERE Id = ?");
            $stmt->execute([$data['Name'], $data['Description'], $data['StartDate'], $data['EndDate'], $data['RewardPoints'], $id]);
            echo json_encode(['message' => 'Challenge updated']);
            break;
        case 'DELETE':
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing challenge ID']);
                return;
            }
            $stmt = $pdo->prepare("DELETE FROM Challenges WHERE Id = ?");
            $stmt->execute([$id]);
            echo json_encode(['message' => 'Challenge deleted']);
            break;
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }
}

function handlePosts($pdo, $method, $id) {
    switch ($method) {
        case 'GET':
            if ($id) {
                $stmt = $pdo->prepare("SELECT * FROM Posts WHERE Id = ?");
                $stmt->execute([$id]);
                echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
            } else {
                $stmt = $pdo->query("SELECT * FROM Posts");
                echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            }
            break;
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("INSERT INTO Posts (UserId, Title, Description, Status) VALUES (?, ?, ?, ?)");
            $stmt->execute([$data['UserId'], $data['Title'], $data['Description'], $data['Status']]);
            echo json_encode(['message' => 'Post created', 'id' => $pdo->lastInsertId()]);
            break;
        case 'PUT':
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing post ID']);
                return;
            }
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("UPDATE Posts SET UserId = ?, Title = ?, Description = ?, Status = ? WHERE Id = ?");
            $stmt->execute([$data['UserId'], $data['Title'], $data['Description'], $data['Status'], $id]);
            echo json_encode(['message' => 'Post updated']);
            break;
        case 'DELETE':
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing post ID']);
                return;
            }
            $stmt = $pdo->prepare("DELETE FROM Posts WHERE Id = ?");
            $stmt->execute([$id]);
            echo json_encode(['message' => 'Post deleted']);
            break;
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }
}

function handlePostTags($pdo, $method, $id) {
    switch ($method) {
        case 'GET':
            if ($id) {
                $stmt = $pdo->prepare("SELECT * FROM PostTags WHERE Id = ?");
                $stmt->execute([$id]);
                echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
            } else {
                $stmt = $pdo->query("SELECT * FROM PostTags");
                echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            }
            break;
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("INSERT INTO PostTags (PostId, TagId) VALUES (?, ?)");
            $stmt->execute([$data['PostId'], $data['TagId']]);
            echo json_encode(['message' => 'PostTag created', 'id' => $pdo->lastInsertId()]);
            break;
        case 'PUT':
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing post tag ID']);
                return;
            }
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("UPDATE PostTags SET PostId = ?, TagId = ? WHERE Id = ?");
            $stmt->execute([$data['PostId'], $data['TagId'], $id]);
            echo json_encode(['message' => 'PostTag updated']);
            break;
        case 'DELETE':
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing post tag ID']);
                return;
            }
            $stmt = $pdo->prepare("DELETE FROM PostTags WHERE Id = ?");
            $stmt->execute([$id]);
            echo json_encode(['message' => 'PostTag deleted']);
            break;
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }
}

function handlePolls($pdo, $method, $id) {
    switch ($method) {
        case 'GET':
            if ($id) {
                $stmt = $pdo->prepare("SELECT * FROM Polls WHERE Id = ?");
                $stmt->execute([$id]);
                echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
            } else {
                $stmt = $pdo->query("SELECT * FROM Polls");
                echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            }
            break;
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("INSERT INTO Polls (PostId, Question) VALUES (?, ?)");
            $stmt->execute([$data['PostId'], $data['Question']]);
            echo json_encode(['message' => 'Poll created', 'id' => $pdo->lastInsertId()]);
            break;
        case 'PUT':
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing poll ID']);
                return;
            }
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("UPDATE Polls SET PostId = ?, Question = ? WHERE Id = ?");
            $stmt->execute([$data['PostId'], $data['Question'], $id]);
            echo json_encode(['message' => 'Poll updated']);
            break;
        case 'DELETE':
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing poll ID']);
                return;
            }
            $stmt = $pdo->prepare("DELETE FROM Polls WHERE Id = ?");
            $stmt->execute([$id]);
            echo json_encode(['message' => 'Poll deleted']);
            break;
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }
}
?>