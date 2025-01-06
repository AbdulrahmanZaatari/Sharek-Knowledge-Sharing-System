<?php
session_start();
include("./role_based_header.php");

// Fetch the post ID from the URL
$postId = isset($_GET['postId']) ? intval($_GET['postId']) : null;

// Check if the postId is valid
if (!$postId) {
    echo "Invalid Post ID.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Details</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link to the CSS file -->
</head>
<body>
    <div class="container">
        <div id="post-details">
            <!-- Post details will be dynamically loaded here -->
        </div>

        <div id="comments-section">
            <h3>Comments</h3>
            <div id="comments-list">
                <!-- Comments will be dynamically loaded here -->
            </div>

            <div id="comment-form">
                <h4>Add a Comment</h4>
                <textarea id="commentText" placeholder="Write your comment..."></textarea>
                <button onclick="addComment()">Submit</button>
            </div>
        </div>
    </div>

    <script>
        // Fetch post details
        fetch(`restapi/api.php?resource=posts&id=<?= $postId ?>`)
            .then(response => response.json())
            .then(post => {
                if (post.error) {
                    console.error('Error loading post details:', post.error);
                    document.getElementById('post-details').innerHTML = '<p>Post not found.</p>';
                    return;
                }
                const profilePicture = post.Profile_Picture
                    ? `restapi/${post.Profile_Picture}`
                    : 'restapi/uploads/profile_pictures/default.jpg';

                const postDetails = document.getElementById('post-details');
                postDetails.innerHTML = `
                    <div class="post-card">
                        <div class="post-header">
                            <img src="${profilePicture}" alt="Profile Picture" class="profile-pic">
                            <div class="post-info">
                                <h3>${post.Title}</h3>
                                <p>Posted by: <strong>${post.Username}</strong> on ${new Date(post.CreatedAt).toLocaleDateString()}</p>
                                <p>${post.Likes} Likes | ${post.comments || 0} Comments</p>
                            </div>
                        </div>
                        <div class="post-content">
                            <p>${post.Description}</p>
                        </div>
                    </div>
                `;
            })
            .catch(error => console.error('Error loading post details:', error));

        // Fetch comments for the post
        function loadComments() {
            fetch(`restapi/api.php?resource=comments&postId=<?= $postId ?>`)
                .then(response => response.json())
                .then(comments => {
                    const commentsList = document.getElementById('comments-list');
                    commentsList.innerHTML = '';

                    if (!Array.isArray(comments) || comments.length === 0) {
                        commentsList.innerHTML = '<p>No comments yet. Be the first to comment!</p>';
                        return;
                    }

                    comments.forEach(comment => {
                        const commentProfilePicture = comment.Profile_Picture
                            ? `restapi/${comment.Profile_Picture}`
                            : 'restapi/uploads/profile_pictures/default.jpg';

                        const commentItem = document.createElement('div');
                        commentItem.className = 'comment-item';
                        commentItem.innerHTML = `
                            <div class="comment-profile">
                                <img src="${commentProfilePicture}" alt="Profile Picture" class="profile-pic-small">
                            </div>
                            <div class="comment-content">
                                <div class="comment-username">${comment.Username}</div>
                                <div class="comment-text">${comment.CommentText}</div>
                                <div class="comment-date">${new Date(comment.CreatedAt).toLocaleDateString()}</div>
                            </div>
                        `;
                        commentsList.appendChild(commentItem);
                    });
                })
                .catch(error => console.error('Error loading comments:', error));
        }

        // Add a new comment
        function addComment() {
            const commentText = document.getElementById('commentText').value;

            if (!commentText.trim()) {
                alert('Comment cannot be empty.');
                return;
            }

            fetch('restapi/api.php?resource=comments', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    postId: <?= $postId ?>,
                    userId: <?= json_encode($_SESSION['user_id'] ?? null) ?>,
                    commentText: commentText
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Comment added successfully!');
                    document.getElementById('commentText').value = '';
                    loadComments(); // Reload comments
                } else {
                    console.error('Error:', data.error);
                    alert(data.error);
                }
            })
            .catch(error => console.error('Error adding comment:', error));
        }

        // Load comments on page load
        loadComments();
    </script>
</body>
</html>
