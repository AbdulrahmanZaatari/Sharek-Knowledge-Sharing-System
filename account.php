<?php
session_start();
// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Fetch logged-in user's email and ID from session
$user_email = $_SESSION['email'];
$user_id = $_SESSION['user_id'];

// API endpoint
$api_base_url = "http://localhost/IDS/restapi/api.php";

// Helper function to fetch data from the API
function fetchFromApi($endpoint, $method = 'GET', $data = null) {
    $url = $endpoint;
    $options = [
        'http' => [
            'header' => "Content-Type: application/json\r\n",
            'method' => $method,
        ]
    ];

    if ($data) {
        $options['http']['content'] = json_encode($data);
    }

    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    if ($result === FALSE) {
        throw new Exception("Error fetching data from API");
    }

    return json_decode($result, true);
}

try {
    // Fetch user details
    $user_endpoint = "{$api_base_url}?resource=users&id={$user_id}";
    $user = fetchFromApi($user_endpoint);

    if (!$user) {
        throw new Exception("User not found.");
    }

    // Fetch user's posts
    $user_posts_endpoint = "{$api_base_url}?resource=posts&user_id={$user_id}";
    $user_posts = fetchFromApi($user_posts_endpoint);

    // Fetch public posts
    $public_posts_endpoint = "{$api_base_url}?resource=posts&user_id!=${user_id}";
    $public_posts = fetchFromApi($public_posts_endpoint);

    // Fetch user achievements
    $achievements_endpoint = "{$api_base_url}?resource=achievements&user_id={$user_id}";
    $user_achievements = fetchFromApi($achievements_endpoint);

} catch (Exception $e) {
    // Log and handle errors
    error_log("Error: " . $e->getMessage());
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

include("./role_based_header.php");
?>

<div class="ltn__utilize-overlay"></div>

<!-- BREADCRUMB AREA START -->
<div class="ltn__breadcrumb-area text-left bg-overlay-white-30 bg-image" data-bs-bg="img/connection2.avif" 
    style="background: url('img/connection2.avif') center/cover no-repeat; padding: 150px 0; text-align: center;">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="ltn__breadcrumb-inner">
                    <h1 class="page-title" style="font-size: 36px; font-weight: 700; color: #2c3e50; text-shadow: 1px 1px 5px rgba(0,0,0,0.2);">
                        My Account
                    </h1>
                    <p style="font-size: 16px; color: #555; margin-top: 10px;">Access your account details, posts, and more!</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- BREADCRUMB AREA END -->

<!-- ACCOUNT AREA START -->
<div class="liton__wishlist-area pb-70">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <!-- PRODUCT TAB AREA START -->
                <div class="ltn__product-tab-area">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-4">
                                <div class="ltn__tab-menu-list mb-50">
                                    <div class="nav">
                                        <a class="active show" data-bs-toggle="tab" href="#liton_tab_1_1">Dashboard<i class="fas fa-home"></i></a>
                                        <a data-bs-toggle="tab" href="#liton_tab_1_2">My Posts<i class="fas fa-file-alt"></i></a>
                                        <a data-bs-toggle="tab" href="#liton_tab_1_3">Public Posts<i class="fas fa-globe"></i></a>
                                        <a data-bs-toggle="tab" href="#liton_tab_1_4">Account Details<i class="fas fa-user"></i></a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-8">
                                <div class="tab-content">
                                    <!-- Dashboard Tab -->
                                    <div class="tab-pane fade active show" id="liton_tab_1_1">
                                        <div class="ltn__myaccount-tab-content-inner">
                                            <p style="font-size: 20px; font-weight: bold; margin-bottom: 10px;">
                                                Hello <span style="color:rgb(167, 120, 40);"><?php echo htmlspecialchars($user['Username']); ?></span>!
                                            </p>
                                            <p style="font-size: 16px; color: #555; margin-bottom: 20px;">
                                                You can <a href="logout.php" style="color: #007bff; text-decoration: underline; font-weight: bold;">log out</a> directly from here.
                                            </p>
                                            <p style="font-size: 16px; line-height: 1.6;">
                                                From your account dashboard, you can view your posts, public posts, and edit your account details.
                                            </p>
                                        </div>
                                    </div>
                                    <!-- My Posts Tab -->
                                    <div class="tab-pane fade" id="liton_tab_1_2">
                                        <div class="ltn__myaccount-tab-content-inner">
                                            <div class="table-responsive">
                                                <table class="table">
                                                    <thead>
                                                        <tr>
                                                            <th>Post ID</th>
                                                            <th>Title</th>
                                                            <th>Created At</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="my-posts-body">
                                                        <!-- My Posts will be loaded dynamically -->
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Public Posts Tab -->
                                    <div class="tab-pane fade" id="liton_tab_1_3">
                                        <div class="ltn__myaccount-tab-content-inner">
                                            <div class="table-responsive">
                                                <table class="table">
                                                    <thead>
                                                        <tr>
                                                            <th>Post ID</th>
                                                            <th>Title</th>
                                                            <th>Created At</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="public-posts-body">
                                                        <!-- Public Posts will be loaded dynamically -->
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Account Details Tab -->
                                    <div class="tab-pane fade" id="liton_tab_1_4">
    <div class="ltn__myaccount-tab-content-inner">
        <h4>Update Your Profile</h4>
        <!-- Display current profile picture -->
        <div class="form-group">
            <label>Profile Picture</label>
            <div class="mb-3">
            <img src="<?php echo 'restapi/' . htmlspecialchars($user['Profile_Picture']); ?>" alt="Profile Picture" style="width: 150px; height: 150px; object-fit: cover; border-radius: 50%; border: 2px solid #ddd;">
            </div>
        </div>
        <form action="update_profile.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($user['Username']); ?>" class="form-control">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user_email); ?>" class="form-control">
            </div>
            <div class="form-group">
                <label>Change Profile Picture</label>
                <input type="file" name="Profile_Picture" accept="image/*" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary mt-3">Update Profile</button>
        </form>
    </div>
</div>
            </div>
        </div>
    </div>
</div>
<!-- ACCOUNT AREA END -->

<script>
    const userPosts = <?php echo json_encode($user_posts); ?>;
    const publicPosts = <?php echo json_encode($public_posts); ?>;

    const myPostsBody = document.getElementById("my-posts-body");
    const publicPostsBody = document.getElementById("public-posts-body");

    function displayPosts(posts, container) {
        container.innerHTML = "";
        if (posts.length > 0) {
            posts.forEach(post => {
                const row = `
                    <tr>
                        <td>${post.Id}</td>
                        <td>${post.Title}</td>
                        <td>${new Date(post.CreatedAt).toLocaleDateString()}</td>
                        <td><a href="view_post.php?post_id=${post.PostId}" class="btn btn-primary">View</a></td>
                    </tr>
                `;
                container.innerHTML += row;
            });
        } else {
            container.innerHTML = "<tr><td colspan='4'>No posts found.</td></tr>";
        }
    }

    displayPosts(userPosts, myPostsBody);
    displayPosts(publicPosts, publicPostsBody);
</script>
<?php
include("./pharmacist_footer.php");
?>
