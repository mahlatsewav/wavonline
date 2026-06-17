<?php
require_once 'includes/auth_helpers.php';
logout();
header("Location: index.php");
exit;
?>
