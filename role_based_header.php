<?php
// Determine the user's role
$role = isset($_SESSION['role']) ? $_SESSION['role'] : '';

// Include the appropriate header file based on the role
switch ($role) {
    case 'User':
        include('header.php');
        break;
    case 'Admin':
        include('owner_header.php');
        break;
    default:
        include('default_header.php');
        break;
}
?>
