<?php
/**
 * Käyttäjien hallinta (admin)
 * 
 * Tarkoitus:
 * - Mahdollistaa käyttäjien poistaminen
 * 
 * Ominaisuudet:
 * - Näyttää kaikki käyttäjät
 * - Estää adminia poistamasta itseään
 * - Käyttää prepared statementeja turvallisuuteen
 */


session_start(); 
// Käynnistetään sessio, jotta voidaan tarkistaa kirjautuminen ja rooli

// Tarkistetaan onko käyttäjä kirjautunut
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php"); 
    // Jos ei ole kirjautunut, ohjataan login-sivulle
    exit();
}

// Tarkistetaan onko käyttäjä admin (rooli = 1)
if ($_SESSION["role"] != 1) {
    die("Tämä sivu on vain ylläpitäjälle.");
    // Jos ei ole admin, sivu pysäytetään tähän
}

// Näytetään adminin navigointipalkki
include "naviAdmin.php";
?>

<!DOCTYPE html>
<html lang="fi">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <title>Poista käyttäjä</title>
    <link rel="stylesheet" href="tyyli.css">
</head>
<body>

<h2>Poista käyttäjä</h2>

<?php
require "yhteys.php"; 
// Otetaan tietokantayhteys käyttöön

// Tarkistetaan onko Poista-nappia painettu
if (isset($_POST["poista"])) {

    // Haetaan lomakkeesta piilotettu käyttäjän ID
    $id = $_POST["kayttaja_id"];

    // Estetään adminia poistamasta itseään
    if ($id == $_SESSION["user_id"]) {
        echo "<p class='virhe'>Et voi poistaa itseäsi!</p>";
    } else {

        // Valmistellaan DELETE-lause (prepared statement turvallisuuden vuoksi)
        $stmt = $yhteys->prepare("DELETE FROM Kayttaja WHERE kayttaja_id = ?");
        
        // Sidotaan ID kokonaislukuna (i = integer)
        $stmt->bind_param("i", $id);

        // Suoritetaan poistokysely
        if ($stmt->execute()) {
            echo "<p class='onnistunut'>Käyttäjä poistettu.</p>";
        } else {
            echo "<p class='virhe'>Käyttäjää ei voitu poistaa!</p>";
        }

        // Suljetaan statement
        $stmt->close();
    }
}

// Haetaan kaikki käyttäjät tietokannasta
$tulos = $yhteys->query("SELECT kayttaja_id, kayttajatunnus, rooli FROM Kayttaja");

// Käydään jokainen käyttäjä läpi ja tulostetaan oma lomake
while ($k = $tulos->fetch_assoc()) {

    echo '<form method="post">';
    
    // Näytetään käyttäjänimi turvallisesti
    echo 'Käyttäjä: ' . htmlspecialchars($k["kayttajatunnus"]);

    // Näytetään rooli tekstinä
    if ($k["rooli"] == 1) {
        echo " (Admin)";
    } else {
        echo " (User)";
    }

    // Piilotettu kenttä, joka sisältää käyttäjän ID:n
    echo '<input type="hidden" name="kayttaja_id" value="' . $k["kayttaja_id"] . '">';
    
    // Poista-nappi
    echo '<button type="submit" name="poista">Poista</button>';
    
    echo '</form>';
}

// Suljetaan tietokantayhteys
$yhteys->close();
?>

</body>
</html>