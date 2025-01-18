<?php
session_start();
include("./role_based_header.php");
// Pass UserId from session to frontend
echo "<script>const USER_ID = " . json_encode($_SESSION['user_id'] ?? null) . ";</script>";
?>

<div class="container">
    <div class="qa-header">
        <h1>Welcome to the Posts Page!</h1>
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

        <!-- Sort By dropdown -->
        <select id="sortOrder" onchange="filterPosts()">
            <option value="desc" selected>Newest</option>
            <option value="asc">Oldest</option>
            <option value="likes">Most Liked</option>
        </select>

        <style>
            .filters {
                display: flex;
                justify-content: flex-start;
                align-items: center;
                gap: 15px;
                margin-bottom: 20px;
            }

            .filters select {
                padding: 10px;
                font-size: 16px;
                border: 1px solid #ddd;
                border-radius: 5px;
                background-color: #f9f9f9;
                color: #333;
                cursor: pointer;
                transition: all 0.3s ease;
            }

            .filters select:focus {
                border-color: #007bff;
                outline: none;
                background-color: #fff;
                box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
            }
            .filters select,
            #searchBar {
                padding: 10px;
                font-size: 16px;
                border: 1px solid #ddd;
                margin-bottom: 50px;
                border-radius: 5px;
                background-color: #f9f9f9;
                color: #333;
                cursor: pointer;
                transition: all 0.3s ease;
                width: 100px; /* Added width for the search bar */
                height: 50px;
            }

            .filters select:focus,
            #searchBar:focus {
                border-color: #007bff;
                outline: none;
                background-color: #fff;
                box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
            }

            .filters select:hover,
            #searchBar:hover {
                background-color: #fff;
                border-color: #007bff;
            }

            .filters option {
                padding: 10px;
            }

            #searchBar {
                width: 300px; /* Wider search bar */
                font-size: 16px;
                padding: 10px 20px; /* Add padding to make it look more elegant */
                border-radius: 30px; /* Rounded corners */
                border: 1px solid #007bff; /* Blue border */
                background-color: #f1f1f1;
                transition: all 0.3s ease;
            }

            #searchBar:focus {
                border-color: #007bff;
                box-shadow: 0 0 5px rgba(0, 123, 255, 0.5); /* Add focus shadow */
            }

            .filters select:hover {
                background-color: #fff;
                border-color: #007bff;
            }

            .filters option {
                padding: 10px;
            }
        </style>
        
        <input 
            type="text" 
            id="searchBar" 
            placeholder="Search posts or users..."
            oninput="searchPosts()"  
            onkeydown="if(event.key === 'Enter') searchPosts()"
        >
    </div>

    <!-- Style ... (unchanged from your snippet) -->

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

<!-- jQuery (and optional Select2) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
/**
 * Load posts from the API, allowing for filters & sort.
 * @param {string|null} categoryId 
 * @param {string|null} tagId 
 * @param {string|null} searchQuery 
 * @param {string|null} sortOrder 
 */
function loadPosts(categoryId = null, tagId = null, searchQuery = null, sortOrder = null) {
    let url = `restapi/api.php?resource=posts`;

    // Build query parameters
    const params = new URLSearchParams();
    if (categoryId)  params.append('categoryId', categoryId);
    if (tagId)       params.append('tagId',      tagId);
    if (searchQuery) params.append('searchQuery', searchQuery);
    if (sortOrder)   params.append('sort', sortOrder);

    // If we have any params, append them
    if ([...params].length > 0) {
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

            // Render each post
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
                            <span class="category-pill">Category: ${post.Category || 'Category: None'}</span>
                            <span class="tag-pill">Tags: ${
                                post.Tags && post.Tags.length 
                                ? post.Tags.split(',').join(', ')
                                : 'None'
                            }</span>
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
            qaList.innerHTML = `<p>Error loading posts: ${error.message}</p>`;
        });
}

/**
 * Called when category, tag, or sort changes
 */
function filterPosts() {
    const filterCategory = document.getElementById('filterCategory').value;
    const filterTag      = document.getElementById('filterTag').value;
    const sortOrder      = document.getElementById('sortOrder').value;

    // We do NOT pass searchQuery here, so pass null or an empty string
    // (We assume user hasn't typed anything in the search bar if they're changing filters)
    loadPosts(filterCategory, filterTag, null, sortOrder);
}

/**
 * Called on every keystroke (and Enter) in the search bar
 * - We also pass the current filterCategory, filterTag, and sortOrder
 *   so that searching does NOT reset them.
 */
function searchPosts() {
    const searchQuery    = document.getElementById('searchBar').value;
    const filterCategory = document.getElementById('filterCategory').value;
    const filterTag      = document.getElementById('filterTag').value;
    const sortOrder      = document.getElementById('sortOrder').value;

    loadPosts(filterCategory, filterTag, searchQuery, sortOrder);
}

// Like or unlike a post
function toggleLike(postId, button) {
    const liked     = button.classList.contains('liked');
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
            const likes = parseInt(button.innerText) + (increment ? 1 : -1);
            button.innerHTML = `${likes} <i class="fa fa-thumbs-up"></i>`;
            button.classList.toggle('liked');
        } else {
            console.error("Error:", data.error);
            alert(data.error);
        }
    })
    .catch(error => console.error("Error liking/unliking post:", error));
}

// Load categories and tags for the filters & post creation modal
function loadCategoriesAndTags() {
    // Load Categories
    fetch('restapi/api.php?resource=categories')
        .then(response => response.json())
        .then(categories => {
            const categoryFilter = document.getElementById('filterCategory');
            const categorySelect = document.getElementById('postCategory');

            categories.forEach(category => {
                // Filter dropdown
                const optionFilter = document.createElement('option');
                optionFilter.value = category.Id;
                optionFilter.textContent = category.Name;
                categoryFilter.appendChild(optionFilter);

                // "Create Post" dropdown
                const optionCreate = document.createElement('option');
                optionCreate.value = category.Id;
                optionCreate.textContent = category.Name;
                categorySelect.appendChild(optionCreate);
            });
        });

    // Load Tags
    fetch('restapi/api.php?resource=tags')
        .then(response => response.json())
        .then(tags => {
            // Tag filter
            const tagFilter = document.getElementById('filterTag');
            tags.forEach(tag => {
                const option = document.createElement('option');
                option.value = tag.Id;
                option.textContent = tag.Name;
                tagFilter.appendChild(option);
            });

            // "Create Post" tags
            const postTagsSelect = document.getElementById('postTags');
            tags.forEach(tag => {
                const option = document.createElement('option');
                option.value = tag.Id;
                option.textContent = tag.Name;
                postTagsSelect.appendChild(option);
            });

            // If using Select2 for multiple tags:
            $('.select2-tags').select2({
                placeholder: 'Select Tags',
                allowClear: true,
                closeOnSelect: false,
                width: '100%'
            });

            // Optionally turn the tag filter into Select2 as well:
            $('#filterTag').select2({
                placeholder: 'All Tags',
                allowClear: true,
                width: '100%'
            });
        })
        .catch(error => console.error('Error loading tags:', error));
}

/** 
 * Modal handling
 */
function openModal() {
    document.getElementById('createPostModal').style.display = 'flex';
    document.body.classList.add('modal-active');
}

function closeModal() {
    document.getElementById('createPostModal').style.display = 'none';
    document.body.classList.remove('modal-active');
}

/**
 * Create a new post
 */
function createPost() {
    const title       = document.getElementById('postTitle').value;
    const description = document.getElementById('postDescription').value;
    const category    = document.getElementById('postCategory').value;
    const tags        = $('#postTags').val();

    if (!title || !description || !category) {
        alert('Title, description, and category are required.');
        return;
    }
    if (!USER_ID) {
        alert('You must be logged in to create a post.');
        return;
    }

    fetch('restapi/api.php?resource=posts', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            UserId: USER_ID,
            Title: title,
            Description: description,
            CategoryId: category,
            Tags: tags
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            console.error('Error:', data.error);
            alert(`Error: ${data.error}`);
        } else {
            console.log('Selected Tags:', tags);
            alert('Post created successfully!');
            closeModal();

            // Reload the post list (if you want to see the newly created post):
            // You might also preserve the current filters & sort. For simplicity:
            loadPosts();
        }
    })
    .catch(error => console.error('Error creating post:', error));
}

// On page load
loadPosts();           // By default => no category, no tag, no search, no sort => fetch all, default server sort
loadCategoriesAndTags();
</script>
