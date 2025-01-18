<?php
session_start();

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if `post_id` is provided
if (!isset($_GET['post_id'])) {
    die("Missing post_id");
}

$post_id = $_GET['post_id'];

// Fetch the post data from the API
$api_url = "http://localhost/IDS/restapi/api.php?resource=posts&id=" . $post_id;
$response = file_get_contents($api_url);
$postData = json_decode($response, true);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Prepare data for PUT request
    $updatedData = [
        'UserId'      => $user_id, 
        'Title'       => $_POST['Title'],
        'Description' => $_POST['Description'],
        'Status'      => $_POST['Status'] // e.g., "Published" or "Draft"
    ];

    $putUrl = "http://localhost/IDS/restapi/api.php?resource=posts&id=" . $post_id;
    $options = [
        'http' => [
            'header'  => "Content-Type: application/json\r\n",
            'method'  => 'PUT',
            'content' => json_encode($updatedData)
        ]
    ];
    $context  = stream_context_create($options);
    $result   = file_get_contents($putUrl, false, $context);

    if ($result === FALSE) { 
        die("Error updating post");
    }

    // Optionally decode response if needed
    // $updateResponse = json_decode($result, true);

    // Redirect back to account page or wherever you'd like
    header("Location: account.php");
    exit();
}

include("role_based_header.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Edit Post</title>

    <!-- Bootstrap CSS (CDN example; or use your local file path) -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    
    <style>
        body {
            background: #f8f9fa;
        }
        .edit-post-container {
            margin-top: 50px;
        }
        .edit-post-card {
            background: #fff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 1px 5px rgba(0,0,0,0.1);
        }
        .edit-post-card h2 {
            margin-bottom: 20px;
        }
        label {
            font-weight: 600;
        }
    </style>
</head>
<body>

<div class="container edit-post-container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="edit-post-card">
                <h2 class="mb-4">Edit Post</h2>
                <form method="post">
                    <!-- Title Field -->
                    <div class="form-group">
                        <label for="Title">Title:</label>
                        <input 
                            type="text"
                            name="Title"
                            id="Title"
                            class="form-control"
                            value="<?php echo htmlspecialchars($postData['Title'] ?? ''); ?>"
                            required
                        >
                    </div>

                    <!-- Description Field -->
                    <div class="form-group">
                        <label for="Description">Description:</label>
                        <textarea
                            name="Description"
                            id="Description"
                            class="form-control"
                            rows="5"
                            required
                        ><?php echo htmlspecialchars($postData['Description'] ?? ''); ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary mt-2">Update Post</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS (CDN example; or use your local file path) -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
