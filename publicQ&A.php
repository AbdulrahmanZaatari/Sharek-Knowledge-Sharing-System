<?php
session_start();
include("./role_based_header.php");
// Pass UserId from session to frontend
echo "<script>const USER_ID = " . json_encode($_SESSION['user_id'] ?? null) . ";</script>";
?>

<div class="container">
    <div class="qa-header">
        <h1>Questions & Answers</h1>
        <button class="create-post-btn" onclick="openModal()">Create New Post</button>
    </div>
    <div class="filters">
    <select id="filterCategory" onchange="filterPosts()">
        <option value="" selected>All Categories</option>
        <!-- Categories will be dynamically loaded -->
    </select>

    <select id="filterTag" onchange="filterPosts()">
        <option value="" selected>All Tags</option>
        <!-- Tags will be dynamically loaded -->
    </select>
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
            <select id="postCategory">
            <option value="" disabled selected>Select Category</option>
            <!-- Categories will be dynamically loaded -->
        </select>
        <select id="postTags" multiple class="select2-tags">
            <option value="" disabled>Select Tags</option>
        <!-- Tags will be dynamically loaded -->
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
    function loadPosts(categoryId = null, tagId = null) {
    let url = `restapi/api.php?resource=posts`;

    if (categoryId || tagId) {
        const params = new URLSearchParams();
        if (categoryId) params.append('categoryId', categoryId);
        if (tagId) params.append('tagId', tagId);
        url += `&${params.toString()}`;
    }

    fetch(url)
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

                const profilePicture = post.Profile_Picture
                    ? `restapi/${post.Profile_Picture}`
                    : 'restapi/uploads/profile_pictures/default.jpg';

                qaContainer.innerHTML = `
                    <div class="post-card">
                        <div class="post-header">
                            <img src="${profilePicture}" alt="Profile Picture" class="profile-pic">
                            <span class="user-name">${post.Username}</span>
                        </div>

                        <h2 class="post-title">
                            <a href="postDetails.php?postId=${post.Id}" class="post-link">${post.Title}</a>
                        </h2>

                        <hr class="post-divider">
                        <div class="post-content"><b> Content: ${post.Description}</b></div>
                        <div class="post-tags">
                            <span class="category-pill">Category: ${post.Category || 'Category: None'}, </span>
                            Tags: ${post.Tags && post.Tags.length 
                                ? post.Tags.split(',').join(', ') 
                                : 'None'}
                        </div>
                        <div class="qa-meta">
                            Posted on: ${new Date(post.CreatedAt).toLocaleDateString()}
                            <div class="post-actions">
                                <button class="like-btn ${post.liked ? 'liked' : ''}" onclick="toggleLike(${post.Id}, this)">
                                    ${post.Likes} <i class="fa fa-thumbs-up"></i>
                                </button>
                                <button class="comment-btn" onclick="openCommentModal(${post.Id})">
                                    ${post.comments || 0} <i class="fa fa-comment"></i>
                                </button>
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
}


    // Like or unlike a post
    function toggleLike(postId, button) {
    const liked = button.classList.contains('liked'); // Check if the post is already liked
    const increment = !liked; // Increment if not liked, decrement if already liked

    if (!USER_ID) {
        alert("You must be logged in to like or unlike a post.");
        return;
    }

    fetch('restapi/api.php?resource=posts', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: 'like',
            userId: USER_ID,
            postId: postId,
            increment: increment
        })
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update the like count and toggle neon orange
                const likes = parseInt(button.innerText) + (increment ? 1 : -1); // Adjust likes count
                button.innerHTML = `${likes} <i class="fa fa-thumbs-up"></i>`;
                button.classList.toggle('liked'); // Toggle the neon border
            } else {
                console.error("Error:", data.error);
                alert(data.error);
            }
        })
        .catch(error => console.error("Error liking or unliking post:", error));
}

function loadCategoriesAndTags() {
    // Load Categories
    fetch('restapi/api.php?resource=categories')
        .then(response => response.json())
        .then(categories => {
            const categoryFilter = document.getElementById('filterCategory');
            const categorySelect = document.getElementById('postCategory');
            categories.forEach(category => {
                const option = document.createElement('option');
                option.value = category.Id;
                option.textContent = category.Name;
                categoryFilter.appendChild(option);

                const postOption = document.createElement('option');
                postOption.value = category.Id;
                postOption.textContent = category.Name;
                categorySelect.appendChild(postOption);
            });
        });

    // Load Tags for Filtering
    fetch('restapi/api.php?resource=tags')
        .then(response => response.json())
        .then(tags => {
            const tagFilter = document.getElementById('filterTag');
            tags.forEach(tag => {
                const option = document.createElement('option');
                option.value = tag.Id;
                option.textContent = tag.Name;
                tagFilter.appendChild(option);
            });
        })
        .catch(error => console.error('Error loading tags for filtering:', error));

    // Initialize Select2 for the tags dropdown in modal
    $(document).ready(function () {
        $('.select2-tags').select2({
            placeholder: 'Select Tags',
            allowClear: true,
            closeOnSelect: false, // Keep the dropdown open for multiple selections
            width: '100%' // Adjust width to fit the container
        });

        // Reinitialize Select2 for Filter Tags
        $('#filterTag').select2({
            placeholder: 'All Tags',
            allowClear: true,
            width: '100%' // Adjust width to fit the container
        });
    });
}


function filterPosts() {
    const filterCategory = document.getElementById('filterCategory').value;
    const filterTag = document.getElementById('filterTag').value;
    loadPosts(filterCategory, filterTag);
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
    const category = document.getElementById('postCategory').value;
    const tags = $('#postTags').val();

    if (!title || !description || !category) {
        alert('Title, description, and category are required.');
        return;
    }

    if (!USER_ID) {
        alert('User is not logged in.');
        return;
    }

    // Save the post via API
    fetch('restapi/api.php?resource=posts', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            UserId: USER_ID,
            Title: title,
            Description: description,
            CategoryId: category, // Pass the selected category
            Tags: tags // Pass selected tags
        }
    
    )
        
    })
        
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error('Error:', data.error);
                alert(`Error: ${data.error}`);
            } else {
                console.log('Selected Tags:', tags);
                alert('Post created successfully!');
                location.reload();
            }
        })
        .catch(error => console.error('Error creating post:', error));
}

loadPosts();
// Load categories, tags, and posts on page load
loadCategoriesAndTags();

</script>
