<?php
/**
 * Drinkkien hakusivu
 * 
 * Tarkoitus:
 * - Mahdollistaa drinkkien hakeminen nimellä tai ainesosalla
 * - Näyttää vain hyväksytyt drinkit (hyvaksytty = 1)
 * 
 * Ominaisuudet:
 * - Hakulomake (nimi / aines)
 * - Dynaaminen SQL-haku
 * - Ainesosien haku erillisellä kyselyllä
 * - Tulosten näyttäminen selkeässä muodossa
 */

session_start(); // Käynnistetään sessio, jotta voidaan käyttää kirjautumistietoja

// Tarkistetaan onko käyttäjä kirjautunut
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php"); // Jos ei ole kirjautunut, ohjataan login-sivulle
    exit(); // Lopetetaan skripti
}

// Näytetään oikea navigointivalikko käyttäjän roolin mukaan
if ($_SESSION["role"] == 1) {
    include "naviAdmin.php"; // Adminille oma navigaatio
} else {
    include "naviUser.php"; // Tavalliselle käyttäjälle oma navigaatio
}
?>

<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <!-- Responsiivisuus mobiililaitteille -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Drinkkihaku</title>
    <!-- Sivun CSS-tyylit -->
    <link rel="stylesheet" href="tyyli.css">
</head> 
<body>

<h2>Hae drinkkejä</h2>

<!-- Hakulomake drinkkien etsimiseen -->
<form method="post" action="">
    Hakusana:
    <!-- Tekstikenttä hakusanaa varten -->
    <input type="text" name="haku" placeholder="Kirjoita hakusana"><br><br>

    <!-- Valitaan haetaanko drinkin nimellä vai ainesosalla -->
    <select name="tyyppi">
        <option value="nimi">Nimi</option>
        <option value="aines">Aines</option>
    </select>
    <br><br>

    <!-- Lähetyspainike -->
    <input type="submit" name="laheta" value="HAE">
</form>

<?php
include 'yhteys.php'; // Otetaan tietokantayhteys käyttöön

// Tarkistetaan, onko HAE-painiketta painettu
if (isset($_POST["laheta"])) {

    // Otetaan hakusana lomakkeesta ja suojataan SQL-injektiolta
    $haku = $yhteys->real_escape_string($_POST["haku"] ?? '');
    
    // Otetaan haun tyyppi (nimi tai aines)
    $tyyppi = $_POST["tyyppi"] ?? 'nimi';

    // Jos hakukenttä on tyhjä, haetaan kaikki drinkit
    if ($haku == '') {
        $hakusql = "SELECT * FROM Drinkki WHERE hyvaksytty = 1";
    } else {
        // Muodostetaan SQL-haku valitun tyypin mukaan
        if ($tyyppi == "nimi") {

            // Haetaan drinkkejä nimen perusteella
            $hakusql = "SELECT * FROM Drinkki 
            WHERE nimi LIKE '%$haku%' 
            AND hyvaksytty = 1";

        } else if ($tyyppi == "aines") {

            // Haetaan drinkkejä ainesosan perusteella
            $hakusql = "
            SELECT DISTINCT Drinkki.* 
            FROM Drinkki
            JOIN DrinkinAines ON Drinkki.drinkki_id = DrinkinAines.drinkki_id
            JOIN Aines ON DrinkinAines.aines_id = Aines.aines_id
            WHERE Aines.nimi LIKE '%$haku%'
            AND Drinkki.hyvaksytty = 1
            ";
        }
    }

    // Suoritetaan haku tietokannasta
    $tulokset = $yhteys->query($hakusql);

    // Tarkistetaan löytyikö tuloksia
    if ($tulokset && $tulokset->num_rows > 0) {

        // Käydään kaikki löytyneet drinkit läpi
        while ($rivi = $tulokset->fetch_assoc()) {

            echo "<div class='drinkki'>";

            // Tulostetaan drinkin nimi ja juomalaji
            echo "<p><strong>Nimi:</strong> " . htmlspecialchars($rivi["nimi"]) . "<br>";
            echo "<strong>Juomalaji:</strong> " . htmlspecialchars($rivi["juomalaji"]) . "</p>";

            // Haetaan drinkin ainesosat erillisellä kyselyllä
            $drinkki_id = $rivi["drinkki_id"];

            $ainesql = "
            SELECT Aines.nimi, DrinkinAines.maara, DrinkinAines.yksikko
            FROM DrinkinAines
            JOIN Aines ON DrinkinAines.aines_id = Aines.aines_id
            WHERE DrinkinAines.drinkki_id = ?
            ";

            // Valmistellaan SQL-lause (turvallinen prepared statement)
            $stmt = $yhteys->prepare($ainesql);
            $stmt->bind_param("i", $drinkki_id); // sidotaan drinkki_id
            $stmt->execute(); // suoritetaan kysely
            $ainestulos = $stmt->get_result(); // haetaan tulokset
        
        echo "<p><strong>Ainesosat:</strong><br>";

        // Jos ainesosia löytyi
        if ($ainestulos->num_rows > 0) {

            while ($aines = $ainestulos->fetch_assoc()) {

                $riviTeksti = '';

                // Tulostetaan määrä jos se on olemassa
                if (isset($aines['maara']) && $aines['maara'] !== '') {
                    $riviTeksti .= htmlspecialchars($aines['maara']) . ' ';
                }

                // Tulostetaan ainesosan nimi
                $riviTeksti .= htmlspecialchars($aines['nimi']);

                echo "&nbsp;&nbsp;&nbsp;" . $riviTeksti . "<br>";
            }

        } else {
            // Jos ainesosia ei ole
            echo "&nbsp;&nbsp;&nbsp;Ei ainesosia.<br>";
        }

        echo "</p>";

            // Tulostetaan valmistusohje jos se on olemassa
            if (!empty($rivi["valmistusohje"])) {
                echo "<p><strong>Valmistusohje:</strong><br>" . htmlspecialchars($rivi["valmistusohje"]) . "</p>";
            }

            $stmt->close(); // Suljetaan prepared statement
            echo "</div>";
        }

    } else {
        // Jos hakutuloksia ei löytynyt
        echo "<p class='virhe'>Ei tuloksia.</p>";
    }

} else {
    // Jos hakua ei ole vielä tehty
    echo "<p>Kirjoita hakusana ja paina HAE.</p>";
}

// Suljetaan tietokantayhteys
$yhteys->close();
?>
</body>
</html>