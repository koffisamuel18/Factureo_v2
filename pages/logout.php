<?php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';
$email = isset($_SESSION['admin_email']) ? $_SESSION['admin_email'] : '';
if ($email) {
    enregistrer_action($conn, "Déconnexion", $email);
}
session_unset();
session_destroy();
header('Location: login.php');
exit; 