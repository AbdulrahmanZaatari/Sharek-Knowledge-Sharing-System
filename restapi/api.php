<?php
session_start();
header("Content-Type: application/json");
include 'connection.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$method = $_SERVER['REQUEST_METHOD'];
// Retrieve query parameters
$resource = isset($_GET['resource']) ? $_GET['resource'] : null; // e.g., 'users', 'posts'
$id = isset($_GET['id']) ? $_GET['id'] : null; // Optional ID for specific resource

if (!isset($_GET['resource'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Resource parameter is missing']);
    exit;
}


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
    case 'comments':
        handleComments($pdo, $method, $id); 
        break;
    case 'leaderboard': 
        getLeaderboard($pdo); 
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
            error_log("API method GET called for users resource.");
    
            if (isset($id) && !empty($id)) {
                error_log("Fetching user with ID: {$id}");
    
                $stmt = $pdo->prepare("SELECT * FROM Users WHERE Id = ?");
                if ($stmt->execute([$id])) {
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
                    if ($user) {
                        echo json_encode($user);
                    } else {
                        error_log("User with ID {$id} not found.");
                        http_response_code(404);
                        echo json_encode(['error' => 'User not found']);
                    }
                } else {
                    error_log("Database query error: " . json_encode($stmt->errorInfo()));
                    http_response_code(500);
                    echo json_encode(['error' => 'Database query failed']);
                }
            } else {
                error_log("Missing or invalid user ID in API request.");
                http_response_code(400);
                echo json_encode(['error' => 'Missing or invalid user ID']);
            }
            break;

            case 'POST':
                try {
                    // Validate if Profile_Picture is set in the $_FILES array
                    $profilePicture = 'uploads/profile_pictures/default.jpg'; // Default picture
                    if (!empty($_FILES['Profile_Picture']) && $_FILES['Profile_Picture']['error'] === UPLOAD_ERR_OK) {
                        $uploadDir = __DIR__ . '/uploads/profile_pictures/';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0777, true); // Create the directory if it doesn't exist
                        }
                        $fileName = uniqid() . '-' . basename($_FILES['Profile_Picture']['name']);
                        $targetFilePath = $uploadDir . $fileName;
            
                        if (move_uploaded_file($_FILES['Profile_Picture']['tmp_name'], $targetFilePath)) {
                            $profilePicture = 'uploads/profile_pictures/' . $fileName; // Store relative path
                        } else {
                            throw new Exception("Failed to upload the profile picture.");
                        }
                    }
            
                    // Extract and validate other user fields
                    $username = $_POST['Username'] ?? null;
                    $email = $_POST['Email'] ?? null;
                    $password = $_POST['Password'] ?? null;
            
                    if (!$username || !$email || !$password) {
                        throw new Exception("Missing required fields.");
                    }
            
                    // Check email format
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        throw new Exception("Invalid email format.");
                    }
            
                    // Hash password
                    $passwordHash = password_hash($password, PASSWORD_BCRYPT);
            
                    // Insert user into database
                    $stmt = $pdo->prepare("INSERT INTO Users (Username, Email, PasswordHash, Profile_Picture) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$username, $email, $passwordHash, $profilePicture]);
            
                    echo json_encode(['message' => 'User created', 'id' => $pdo->lastInsertId()]);
                } catch (Exception $e) {
                    http_response_code(400);
                    echo json_encode(['error' => $e->getMessage()]);
                }
                break;
            
            
                case 'PUT':
                    if (!$id) {
                        http_response_code(400);
                        echo json_encode(['error' => 'Missing user ID']);
                        return;
                    }
        
                    // Decode the JSON input
                    $data = json_decode(file_get_contents('php://input'), true);
                    $username = $data['username'] ?? null;
                    $email = $data['email'] ?? null;
                    $profilePicture = $data['profile_picture'] ?? null;
        
                    if (!$username || !$email) {
                        http_response_code(400);
                        echo json_encode(['error' => 'Missing required fields']);
                        return;
                    }
        
                    // Validate email format
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        http_response_code(400);
                        echo json_encode(['error' => 'Invalid email format']);
                        return;
                    }
        
                    // Update user data
                    $query = "UPDATE Users SET Username = ?, Email = ?";
                    $params = [$username, $email];
        
                    if ($profilePicture) {
                        $query .= ", Profile_Picture = ?";
                        $params[] = $profilePicture;
                    }
        
                    $query .= " WHERE Id = ?";
                    $params[] = $id;
        
                    $stmt = $pdo->prepare($query);
                    if ($stmt->execute($params)) {
                        echo json_encode(['message' => 'Profile updated successfully']);
                    } else {
                        http_response_code(500);
                        echo json_encode(['error' => 'Database update failed']);
                    }
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
            try {
                $stmt = $pdo->query("SELECT * FROM Tags");
                echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['error' => $e->getMessage()]);
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
            try {
                // Debug log of session
                error_log("Session data in handlePosts: " . print_r($_SESSION, true));
        
                // --- Extract parameters ---
                $id          = $id; // single post if present
                $categoryId  = $_GET['categoryId']  ?? null; 
                $tagId       = $_GET['tagId']       ?? null;
                $searchQuery = $_GET['searchQuery'] ?? '';
                $userId      = $_SESSION['user_id'] ?? null; 
                $userOnly    = (isset($_GET['user_only']) && $_GET['user_only'] === 'true');
        
                // Sorting parameter => 'desc' (default), 'asc', or 'likes'
                $sortOption  = $_GET['sort'] ?? 'desc';
        
                // If you don't store "Likes" in Posts, we can do a subselect:
                $likeCountSelect = "(SELECT COUNT(*) FROM PostLikes WHERE PostLikes.PostId = Posts.Id) AS LikeCount";
        
                // Decide how to ORDER
                switch ($sortOption) {
                    case 'asc':
                        $orderClause = "Posts.CreatedAt ASC";
                        break;
                    case 'likes':
                        $orderClause = "LikeCount DESC";
                        break;
                    default:
                        // 'desc' => newest first
                        $orderClause = "Posts.CreatedAt DESC";
                }
        
                // If $id is present => single post
                if ($id) {
                    // Single post query
                    $stmt = $pdo->prepare("
                        SELECT 
                            Posts.*,
                            GROUP_CONCAT(Tags.Name) AS Tags, 
                            Users.Username,
                            Users.Profile_Picture,
                            Categories.Name AS Category,
                            CASE WHEN :uid IS NOT NULL THEN EXISTS (
                                SELECT 1
                                FROM PostLikes
                                WHERE PostLikes.UserId = :uid2
                                  AND PostLikes.PostId = Posts.Id
                            ) ELSE 0 END AS liked
                        FROM Posts
                        LEFT JOIN PostTags     ON Posts.Id = PostTags.PostId
                        LEFT JOIN Tags         ON PostTags.TagId = Tags.Id
                        LEFT JOIN Categories   ON Posts.CategoryId = Categories.Id
                        JOIN Users             ON Posts.UserId = Users.Id
                        WHERE Posts.Id = :postId
                        GROUP BY Posts.Id
                    ");
                    $stmt->bindValue(':uid',   $userId, PDO::PARAM_INT);
                    $stmt->bindValue(':uid2',  $userId, PDO::PARAM_INT);
                    $stmt->bindValue(':postId',$id,     PDO::PARAM_INT);
                    $stmt->execute();
        
                    $post = $stmt->fetch(PDO::FETCH_ASSOC);
                    echo json_encode($post ?: ['error' => 'Post not found']);
                }
                else {
                    // Multiple posts query
                    $query = "
                        SELECT
                            Posts.*,
                            $likeCountSelect,  /* subselect for total likes */
                            GROUP_CONCAT(Tags.Name) AS Tags,
                            Users.Username,
                            Users.Profile_Picture,
                            Categories.Name AS Category,
                            CASE WHEN ? IS NOT NULL THEN EXISTS (
                                SELECT 1 FROM PostLikes
                                WHERE PostLikes.UserId = ? AND PostLikes.PostId = Posts.Id
                            ) ELSE 0 END as liked
                        FROM Posts
                        LEFT JOIN PostTags   ON Posts.Id = PostTags.PostId
                        LEFT JOIN Tags       ON PostTags.TagId = Tags.Id
                        LEFT JOIN Categories ON Posts.CategoryId = Categories.Id
                        JOIN Users           ON Posts.UserId = Users.Id
                    ";
        
                    // Build conditions & parameters
                    $params = [$userId, $userId];
                    $conditions = [];
        
                    // 1) Category filter
                    if ($categoryId) {
                        $conditions[] = "Posts.CategoryId = ?";
                        $params[]     = $categoryId;
                    }
        
                    // 2) Tag filter
                    if ($tagId) {
                        $conditions[] = "PostTags.TagId = ?";
                        $params[]     = $tagId;
                    }
        
                    // 3) User-only filter
                    if ($userOnly && $userId) {
                        $conditions[] = "Posts.UserId = ?";
                        $params[]     = $userId;
                    }
        
                    // 4) Search
                    if ($searchQuery) {
                        $conditions[] = "(Posts.Title LIKE ? OR Users.Username LIKE ?)";
                        $params[] = '%' . $searchQuery . '%';
                        $params[] = '%' . $searchQuery . '%';
                    }
        
                    // If conditions exist, add them
                    if ($conditions) {
                        $query .= " WHERE " . implode(" AND ", $conditions);
                    }
        
                    // GROUP BY, then ORDER
                    $query .= "
                        GROUP BY Posts.Id
                        ORDER BY $orderClause
                    ";
        
                    error_log("Query: $query");
                    error_log("Params: " . print_r($params, true));
        
                    $stmt = $pdo->prepare($query);
                    $stmt->execute($params);
        
                    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    echo json_encode($results);
                }
            }
            catch (Exception $e) {
                error_log("handlePosts() exception: " . $e->getMessage());
                http_response_code(500);
                echo json_encode(['error' => $e->getMessage()]);
            }
            break;
        
        case 'POST':
            try {
                $data = json_decode(file_get_contents('php://input'), true);

                if (isset($data['action']) && $data['action'] === 'like') {
                    $userId = $data['userId'] ?? null;
                    $postId = $data['postId'] ?? null;
                    $increment = $data['increment'] ?? true; // true to like, false to unlike

                    if (!$userId || !$postId) {
                        throw new Exception('UserId and PostId are required for the like action.');
                    }

                    if ($increment) {
                        // Add a like
                        $stmt = $pdo->prepare("SELECT * FROM PostLikes WHERE UserId = ? AND PostId = ?");
                        $stmt->execute([$userId, $postId]);

                        if ($stmt->rowCount() > 0) {
                            throw new Exception('You have already liked this post.');
                        }

                        $stmt = $pdo->prepare("INSERT INTO PostLikes (UserId, PostId) VALUES (?, ?)");
                        $stmt->execute([$userId, $postId]);

                        $stmt = $pdo->prepare("UPDATE Posts SET Likes = Likes + 1 WHERE Id = ?");
                        $stmt->execute([$postId]);

                        // Add 1 point to the user for the like
                        $stmt = $pdo->prepare("UPDATE Users SET Points = Points + 1 WHERE Id = ?");
                        $stmt->execute([$userId]);

                        echo json_encode(['message' => 'Like added successfully', 'success' => true]);
                    } else {
                        // Remove a like
                        $stmt = $pdo->prepare("SELECT * FROM PostLikes WHERE UserId = ? AND PostId = ?");
                        $stmt->execute([$userId, $postId]);

                        if ($stmt->rowCount() === 0) {
                            throw new Exception('You have not liked this post.');
                        }

                        $stmt = $pdo->prepare("DELETE FROM PostLikes WHERE UserId = ? AND PostId = ?");
                        $stmt->execute([$userId, $postId]);

                        $stmt = $pdo->prepare("UPDATE Posts SET Likes = Likes - 1 WHERE Id = ?");
                        $stmt->execute([$postId]);

                        // Subtract 1 point from the user for the like removal
                        $stmt = $pdo->prepare("UPDATE Users SET Points = Points - 1 WHERE Id = ?");
                        $stmt->execute([$userId]);

                        echo json_encode(['message' => 'Like removed successfully', 'success' => true]);
                    }
                } elseif (isset($data['action']) && $data['action'] === 'comment') {
                    // Handle comments
                    $id = $data['postId'] ?? null; // Ensure the postId is fetched
                    if (!$id) {
                        throw new Exception('Post ID is required for comment action');
                    }

                    $comment = $data['comment'] ?? null;
                    if (!$comment) {
                        throw new Exception('Comment text is required');
                    }

                    // Insert comment into the Comments table
                    $stmt = $pdo->prepare("INSERT INTO Comments (PostId, CommentText, CreatedAt) VALUES (?, ?, NOW())");
                    $stmt->execute([$id, $comment]);

                    // Increment the Comments count in the Posts table
                    $stmt = $pdo->prepare("UPDATE Posts SET Comments = Comments + 1 WHERE Id = ?");
                    $stmt->execute([$id]);

                    // Add 2 points to the post's author
                    $stmt = $pdo->prepare("SELECT UserId FROM Posts WHERE Id = ?");
                    $stmt->execute([$id]);
                    $postOwner = $stmt->fetch(PDO::FETCH_ASSOC)['UserId'];

                    $stmt = $pdo->prepare("UPDATE Users SET Points = Points + 2 WHERE Id = ?");
                    $stmt->execute([$postOwner]);

                    echo json_encode(['message' => 'Comment added', 'success' => true]);
                } else {
                    // Handle new post creation
                    $userId = $data['UserId'] ?? null;
                    $title = $data['Title'] ?? null;
                    $description = $data['Description'] ?? null;
                    $categoryId = $data['CategoryId'] ?? null; // Fetch the CategoryId from the request

                    if (!$userId || !$title || !$description) {
                        throw new Exception('UserId, Title, and Description are required');
                    }

                    $stmt = $pdo->prepare("INSERT INTO Posts (UserId, Title, Description, Status, CategoryId) VALUES (?, ?, ?, 'Published', ?)");
                    $stmt->execute([$userId, $title, $description, $categoryId]);
                    $postId = $pdo->lastInsertId();

                    if (!empty($data['Tags']) && is_array($data['Tags'])) {
                        $tagStmt = $pdo->prepare("INSERT INTO PostTags (PostId, TagId) VALUES (?, ?)");
                        foreach ($data['Tags'] as $tagId) {
                            $tagStmt->execute([$postId, $tagId]);
                        }
                    }

                    // Add 5 points to the user for creating the post
                    $stmt = $pdo->prepare("UPDATE Users SET Points = Points + 5 WHERE Id = ?");
                    $stmt->execute([$userId]);

                    echo json_encode(['message' => 'Post created', 'PostId' => $postId]);
                }
            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode(['error' => $e->getMessage()]);
            }
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
    function handleComments($pdo, $method, $id) {
        switch ($method) {
            case 'GET':
                // Get comments for a specific post
                if (isset($_GET['postId'])) {
                    $postId = intval($_GET['postId']);
                    $stmt = $pdo->prepare("
                        SELECT Comments.*, Users.Username, Users.Profile_Picture 
                        FROM Comments 
                        JOIN Users ON Comments.UserId = Users.Id 
                        WHERE Comments.PostId = ?
                        ORDER BY Comments.CreatedAt DESC
                    ");
                    $stmt->execute([$postId]);
                    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'Post ID is required to fetch comments']);
                }
                break;
    
                case 'POST':
                    // Add a new comment
                    $data = json_decode(file_get_contents('php://input'), true);
                    $postId = $data['postId'] ?? null;
                    $userId = $data['userId'] ?? null;
                    $commentText = $data['commentText'] ?? null;
                
                    if (!$postId || !$userId || !$commentText) {
                        http_response_code(400);
                        echo json_encode(['error' => 'Post ID, User ID, and Comment Text are required']);
                        return;
                    }
                
                    try {
                        // Insert the comment into the Comments table
                        $stmt = $pdo->prepare("
                            INSERT INTO Comments (PostId, UserId, CommentText, CreatedAt) 
                            VALUES (?, ?, ?, NOW())
                        ");
                        $stmt->execute([$postId, $userId, $commentText]);
                
                        // Increment the comment count in the Posts table
                        $stmt = $pdo->prepare("
                            UPDATE Posts 
                            SET Comments = Comments + 1 
                            WHERE Id = ?
                        ");
                        $stmt->execute([$postId]);
                
                        echo json_encode(['success' => true, 'message' => 'Comment added successfully']);
                    } catch (Exception $e) {
                        http_response_code(500);
                        echo json_encode(['error' => 'Error adding comment: ' . $e->getMessage()]);
                    }
                    break;
                
    
            case 'PUT':
                // Update an existing comment
                if (!$id) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Comment ID is required to update']);
                    return;
                }
                $data = json_decode(file_get_contents('php://input'), true);
                $commentText = $data['commentText'] ?? null;
    
                if (!$commentText) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Comment Text is required']);
                    return;
                }
    
                $stmt = $pdo->prepare("UPDATE Comments SET CommentText = ? WHERE Id = ?");
                $stmt->execute([$commentText, $id]);
                echo json_encode(['message' => 'Comment updated successfully']);
                break;
    
            case 'DELETE':
                // Delete a comment
                if (!$id) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Comment ID is required to delete']);
                    return;
                }
    
                $stmt = $pdo->prepare("DELETE FROM Comments WHERE Id = ?");
                $stmt->execute([$id]);
                echo json_encode(['message' => 'Comment deleted successfully']);
                break;
    
            default:
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
                break;
        }
        function getLeaderboard($pdo) {
            try {
                // Query to get top 10 users ordered by points
                $stmt = $pdo->query("SELECT Id, Username, Points FROM Users ORDER BY Points DESC LIMIT 10");
                $leaderboard = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
                if ($leaderboard) {
                    echo json_encode(['leaderboard' => $leaderboard]);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'No users found']);
                }
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['error' => $e->getMessage()]);
            }
        }        
    }    
?>