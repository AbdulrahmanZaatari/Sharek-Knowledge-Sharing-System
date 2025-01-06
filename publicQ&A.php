<?php
session_start();
include("./role_based_header.php"); // Include the header
?>

<div class="container">
    <div class="qa-header">
        <h1>Questions & Answers</h1>
        <button class="create-post-btn" onclick="openModal()">Create New Post</button>
    </div>

    <div id="qa-list">
        <!-- Posts will be loaded dynamically here -->
    </div>
</div>

<!-- Modal for Creating a Post -->
<div id="createPostModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Create New Post</h2>
            <span class="modal-close" onclick="closeModal()">&times;</span>
        </div>
        <div class="modal-body">
            <input type="text" id="postTitle" placeholder="Post Title">
            <textarea id="postDescription" placeholder="Post Description"></textarea>
            <select id="postTags">
                <option value="" disabled selected>Select Tags</option>
                <!-- Dynamically load tags -->
            </select>
        </div>
        <div class="modal-footer">
            <button class="cancel-btn" onclick="closeModal()">Cancel</button>
            <button class="save-btn" onclick="createPost()">Save</button>
        </div>
    </div>
</div>

<script>
    // Fetch posts from the API
    fetch('restapi/api.php?resource=posts')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            const qaList = document.getElementById('qa-list');
            qaList.innerHTML = ''; // Clear previous content

            if (!Array.isArray(data) || data.length === 0) {
                qaList.innerHTML = '<p>No posts available.</p>';
                return;
            }

            data.forEach(post => {
                const qaContainer = document.createElement('div');
                qaContainer.className = 'qa-container';

                qaContainer.innerHTML = `
                    <div class="post-card">
                        <div class="post-header">
                            <div class="profile-pic"></div>
                            <span class="user-name">${post.Username}</span>
                        </div>
                        <hr class="post-divider">
                        <div class="post-content">
                            <h2 class="post-title">${post.Title}</h2>
                            <div class="qa-tags">Tags: ${post.Tags || 'None'}</div>
                            <div class="qa-meta">
                                Posted on: ${new Date(post.CreatedAt).toLocaleDateString()}
                                <div class="post-actions">
                                    <button class="like-btn ${post.liked ? 'liked' : ''}" onclick="toggleLike(${post.Id}, this)">
                                        ${post.Likes} <i class="fa fa-thumbs-up"></i>
                                    </button>
                                    <button class="comment-btn" onclick="openCommentModal(${post.Id})">
                                        ${post.Comments} <i class="fa fa-comment"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                qaList.appendChild(qaContainer);
            });
        })
        .catch(error => {
            console.error('Error fetching posts:', error);
            const qaList = document.getElementById('qa-list');
            qaList.innerHTML = '<p>Error loading posts. Please try again later.</p>';
        });

    // Like or unlike a post
    function toggleLike(postId, button) {
        const liked = button.classList.contains('liked');
        fetch('restapi/api.php?resource=posts', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'like', increment: !liked, postId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const likes = parseInt(button.innerText) + (liked ? -1 : 1);
                button.innerText = `${likes} `;
                button.appendChild(document.createElement('i')).className = 'fa fa-thumbs-up';
                button.classList.toggle('liked');
            }
        })
        .catch(error => console.error('Error liking post:', error));
    }

    // Open the comment modal
    function openCommentModal(postId) {
        const comment = prompt("Write your comment:");
        if (comment) {
            fetch('restapi/api.php?resource=posts', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'comment', comment, postId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Comment added successfully!');
                    location.reload();
                }
            })
            .catch(error => console.error('Error adding comment:', error));
        }
    }

    // Modal handling
    function openModal() {
        document.getElementById('createPostModal').style.display = 'flex';
        document.body.classList.add('modal-active');
    }

    function closeModal() {
        document.getElementById('createPostModal').style.display = 'none';
        document.body.classList.remove('modal-active');
    }

    function createPost() {
        const title = document.getElementById('postTitle').value;
        const description = document.getElementById('postDescription').value;
        const tags = document.getElementById('postTags').value;

        if (!title || !description) {
            alert('Title and description are required.');
            return;
        }

        // Save the post via API
        fetch('restapi/api.php?resource=posts', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ Title: title, Description: description, Tags: tags })
        })
        .then(response => response.json())
        .then(data => {
            alert('Post created successfully!');
            location.reload();
        })
        .catch(error => console.error('Error creating post:', error));
    }
</script>
