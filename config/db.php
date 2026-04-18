<?php
// config/db.php
$host = 'localhost';
$user = 'root';
$pass ='' ;
$dbname = 'factureo';

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die('Erreur de connexion à la base de données : ' . mysqli_connect_error());
} 