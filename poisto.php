<?php
session_start(); // Käynnistetään sessio, jotta voidaan tarkistaa käyttäjän kirjautuminen

// Tarkistetaan onko käyttäjä kirjautunut
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php"); // Jos ei ole kirjautunut, ohjataan login-sivulle
    exit();
}

// Tarkistetaan onko käyttäjä ylläpitäjä (rooli 1)
if ($_SESSION["role"] != 1) {
    die("Tämä sivu on vain ylläpitäjälle."); // Estetään pääsy tavallisilta käyttäjiltä
}

// Näytetään admin-navigaatio
include "naviAdmin.php";
?>

<!DOCTYPE html>
<html lang="fi">
<head>
    <!-- Responsiivisuus mobiililaitteille -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta charset="UTF-8">
    <title>Poista Resepti</title>

    <!-- Sivun CSS-tyylit -->
    <link rel="stylesheet" href="tyyli.css">
</head>
<body>

<h2>Poista drinkki</h2>

<?php
require "yhteys.php"; // Luodaan tietokantayhteys

// =======================
// DRINKIN POISTAMINEN
// =======================

// Tarkistetaan onko Poista-painiketta painettu
if (isset($_POST["poista"])) {

    // Haetaan poistettavan drinkin id lomakkeesta
    $id = $_POST["drinkki_id"];

    // Prepared statement drinkin poistamiseen
    $stmt = $yhteys->prepare("DELETE FROM Drinkki WHERE drinkki_id = ?");

    // Sidotaan id SQL-kyselyyn
    $stmt->bind_param("i", $id);

    // Suoritetaan poistaminen
    if ($stmt->execute()) {
        echo "<p class='onnistunut'>Drinkki poistettu.</p>";
    } else {
        echo "<p class='virhe'>Drinkkiä ei voitu poistaa!</p>";
    }
}

// =======================
// DRINKKIEN LISTAUS
// =======================

// Haetaan kaikki drinkit tietokannasta
$tulos = $yhteys->query("SELECT drinkki_id, nimi FROM Drinkki");

// Tulostetaan jokaiselle drinkille oma lomake poistamista varten
while ($d = $tulos->fetch_assoc()) {

    echo '<form method="post">';

    // Näytetään drinkin nimi
    echo 'Drinkki: ' . htmlspecialchars($d["nimi"]) . ' ';

    // Piilotettu kenttä drinkin id:tä varten
    echo '<input type="hidden" name="drinkki_id" value="' . $d["drinkki_id"] .  '">';

    // Poista-painike
    echo '<button type="submit" name="poista">Poista</button>';

    echo '</form>';
}

// Suljetaan tietokantayhteys
$yhteys->close();
?>

</body>
</html>