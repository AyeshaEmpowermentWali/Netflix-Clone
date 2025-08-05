<?php
require_once 'db.php';

// Destroy session
session_destroy();

// Redirect to home page
redirect_to('index.php');
?>
