<?php
// Start the session for logins later
session_start();

// Load the core application file
require_once '../core/Controller.php';
require_once '../core/App.php';

// Initialize the app
$app = new App();