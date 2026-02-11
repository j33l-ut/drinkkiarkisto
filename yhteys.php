<?php
$host = "localhost";
$db = "drinkitj33l";
$user = "root";  
$pass = "";     

$yhteys = new mysqli($host, $user, $pass, $db);


if ($yhteys->connect_error) {
    die("Yhteys epäonnistui: " . $yhteys->connect_error);
}
?>
