<?php
require_once 'includes/session.php';
logout();
header('Location: index.php');
exit(); 