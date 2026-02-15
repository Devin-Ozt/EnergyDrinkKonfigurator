<?php
/**
 * Logout
 */
require_once 'config/database.php';
startSession();

// Session zerstören
session_destroy();

// Zur Startseite weiterleiten
header('Location: index.php');
exit;
