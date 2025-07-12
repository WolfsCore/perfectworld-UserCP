<?php
define('UCP_ACCESS', true);
require_once 'config/config.php';
require_once 'php/classes/UCP.php';

$ucp = new UCP();

// Perform logout
$ucp->logout();

// Redirect to login page
header('Location: login.php?logout=success');
exit;
?> 