<?php

session_start();
session_destroy();

header('Location: ../index.php');
exit;

require_once 'session.php';

session_start();
session_destroy();
header("Location: ../index.php");
exit();
?>

