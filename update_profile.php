<?php
session_start();
include("./restapi/connection.php");

try {
    // Check if the user is logged in
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        throw new Exception("User not logged in.");
    }

    // Retrieve and sanitize input fields
    $username = $_POST['username'] ?? null;
    $email = $_POST['email'] ?? null;

    if (!$username || !$email) {
        throw new Exception("Missing required fields.");
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email format.");
    }

    // Handle profile picture upload
    $profilePicture = null;
    if (!empty($_FILES['Profile_Picture']) && $_FILES['Profile_Picture']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/uploads/profile_pictures/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true); // Create the directory if it doesn't exist
        }

        $fileName = uniqid() . '-' . basename($_FILES['Profile_Picture']['name']);
        $targetFilePath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['Profile_Picture']['tmp_name'], $targetFilePath)) {
            $profilePicture = 'uploads/profile_pictures/' . $fileName;
        } else {
            throw new Exception("Failed to upload profile picture.");
        }
    }

    // Prepare API call data
    $url = "http://localhost/IDS/restapi/api.php?resource=users&id=$userId";
    $data = [
        'username' => $username,
        'email' => $email,
        'profile_picture' => $profilePicture ?? null,
    ];

    // Send the API request
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Handle API response
    if ($httpCode === 200) {
        $_SESSION['success_message'] = "Profile updated successfully.";
    } else {
        $error = json_decode($response, true)['error'] ?? 'Unknown error';
        throw new Exception($error);
    }

    // Redirect to profile page
    header('Location: account.php');
    exit;
} catch (Exception $e) {
    // Handle errors
    $_SESSION['error_message'] = $e->getMessage();
    header('Location: account.php'); // Redirect back to profile page with error
    exit;
}
