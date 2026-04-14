<?php
/**
 * Aineksen lisääminen -sivu
 * Tarkoitus:
 * - Mahdollistaa uusien ainesosien lisääminen tietokantaan
 * - Estää duplikaattien lisääminen
 * - Näyttää kaikki lisätyt ainekset listana
 * 
 * Ominaisuudet:
 * - Kirjautumisen tarkistus
 * - Roolipohjainen navigaatio (admin/user)
 * - Lomakkeen validointi
 * - Tietokantahaku ja lisäys
 */

// Käynnistetään session, jotta tiedetään kuka on kirjautunut
session_start();

// Jos käyttäjä ei ole kirjautunut, ohjataan login-sivulle
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Näytetään oikea navigointi roolin mukaan
if ($_SESSION["role"] == 1) {
    include "naviAdmin.php"; // adminille
} else {
    include "naviUser.php";  // tavalliselle käyttäjälle
}
?>

<!DOCTYPE html>
<html lang="fi">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <title>Aineksen lisääminen</title>
    <link rel="stylesheet" href="tyyli.css">
</head>
<body>

    <!-- Sivun pääotsikko -->
    <h1>Aineksen lisääminen</h1>

    <!-- Lomake uuden aineksen lisäämiseen -->
    <form method="post" action="">
        <label for="aines">Aines:</label>
        <input type="text" id="aines" name="aines">
        <button type="submit" name="lisaa">Lisää</button>
    </form>
<?php
// Otetaan tietokantayhteys
include 'yhteys.php';

// Tarkistetaan, onko käyttäjä painanut "Lisää"-painiketta
if (isset($_POST['lisaa'])) {  
    $aines = trim($_POST['aines']);  // Poistetaan turhat välilyönnit

    // Kentän tyhjä tarkistus
    if ($aines == "") {
        echo "<p class='virhe'>Aines ei voi olla tyhjä!</p>";
    } else {

        // Tarkistetaan, onko aines jo tietokannassa
        $stmt = $yhteys->prepare("SELECT * FROM Aines WHERE nimi = ?");
        $stmt->bind_param("s", $aines);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "<p class='virhe'>Aines on jo tietokannassa!</p>";
        } else {

            // Lisätään uusi aines tietokantaan
            $stmt = $yhteys->prepare("INSERT INTO Aines (nimi) VALUES (?)");
            $stmt->bind_param("s", $aines);

            if ($stmt->execute()) {
                echo "<p class='onnistunut'>Aines lisätty onnistuneesti!</p>";
            } else {
                echo "<p class='virhe'>Virhe lisättäessä ainetta.</p>";
            }
        }
    }
}

// Haetaan kaikki ainekset listaksi
$ainekset = $yhteys->query("SELECT * FROM Aines ORDER BY aines_id ASC");

if ($ainekset->num_rows > 0) {
    echo "<h2>Kaikki ainekset:</h2><ul>";
    while ($row = $ainekset->fetch_assoc()) {
        // htmlspecialchars estää mahdolliset haitalliset merkit
        echo "<li>Aineisosa: " . htmlspecialchars($row['nimi']) . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p>Ei vielä aineksia.</p>";
}

// Suljetaan tietokantayhteys
$yhteys->close();
?>
</body>
</html>
