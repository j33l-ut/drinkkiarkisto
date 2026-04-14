<?php
/**
 * Kirjautumissivu
 * 
 * Tarkoitus:
 * - Mahdollistaa käyttäjän kirjautuminen järjestelmään
 * 
 * Ominaisuudet:
 * - Tarkistaa käyttäjätunnus ja salasana
 * - Salasanan tarkistus password_verify-funktiolla
 * - Tallentaa käyttäjän tiedot sessioon
 * - Ohjaa kirjautumisen jälkeen hakusivulle
 */ 

session_start(); // Käynnistetään sessio, jotta voidaan tallentaa kirjautumistiedot

require_once "yhteys.php"; // Otetaan tietokantayhteys käyttöön
?>

<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <!-- Sivun tyylit -->
    <link rel="stylesheet" href="tyyli.css">
    <title>Kirjautuminen</title>
</head>
<body>

<?php include "naviGuest.php"; // Näytetään navigaatio vierailijalle ?>

<h2>Kirjautuminen</h2>

<!-- Kirjautumislomake -->
<form method="post" action="">
    
    <!-- Käyttäjätunnus -->
    <label>Käyttäjätunnus</label><br>
    <input type="text" name="username" required><br><br>

    <!-- Salasana -->
    <label>Salasana</label><br>
    <input type="password" name="password" required><br><br>

    <!-- Lähetetään lomake -->
    <button type="submit" name="login">Kirjaudu</button>
</form>

<?php

// Tarkistetaan onko kirjautumispainiketta painettu
if (isset($_POST["login"])) {

    // Otetaan lomakkeesta käyttäjätunnus ja salasana
    $username = $_POST["username"];
    $password = $_POST["password"];

    // Valmistellaan SQL-kysely käyttäjän hakemiseksi tietokannasta
    // Prepared statement suojaa SQL-injektiolta
    $stmt = $yhteys->prepare("SELECT kayttaja_id, salasana, rooli FROM Kayttaja WHERE kayttajatunnus = ?");
    
    // Sidotaan käyttäjätunnus kyselyyn
    $stmt->bind_param("s", $username);

    // Suoritetaan kysely
    $stmt->execute();

    // Haetaan tulos
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Tarkistetaan löytyikö käyttäjä ja onko salasana oikein
    if ($user && password_verify($password, $user["salasana"])) {

        // Tallennetaan käyttäjän tiedot sessioon
        $_SESSION["user_id"] = $user["kayttaja_id"];
        $_SESSION["role"] = $user["rooli"];

        // Ohjataan käyttäjä hakusivulle
        header("Location: haku.php");
        exit();

    } else {

        // Jos kirjautuminen epäonnistuu
        echo "<p class='virhe'>Virheellinen käyttäjätunnus tai salasana</p>";
    }
}

?>

</body>
</html>