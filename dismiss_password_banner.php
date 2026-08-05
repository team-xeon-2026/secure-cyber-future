<?php
// Start the session
session_start();

// Set a flag to hide the banner for this session only
$_SESSION['password_banner_dismissed'] = true;

// Return a success response
echo "success";
?> 


