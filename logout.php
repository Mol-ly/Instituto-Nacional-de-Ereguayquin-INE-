<?php
session_start();
$_SESSION = array();
session_destroy();
header("Location: login.php?mensaje=sesion_cerrada");
exit();
?>