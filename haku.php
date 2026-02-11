<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <title>Drinkkihaku</title>
    <link rel="stylesheet" href="tyyli.css">
</head>
<?php
include 'naviUser.php';
?>
<body>

<h2>Hae drinkkejä</h2>

<form method="post" action="">
    Hakusana:
    <input type="text" name="haku" placeholder="Kirjoita hakusana"><br><br>

    <select name="tyyppi">
        <option value="nimi">Nimi</option>
        <option value="aines">Aines</option>
    </select>
    <br><br>

    <input type="submit" name="laheta" value="HAE">
</form>

<hr>

<?php
include 'yhteys.php';

// Tarkistetaan, onko HAE painettu ja hakukenttä ei ole tyhjä
if (isset($_POST["laheta"]) && !empty($_POST["haku"])) {

    $haku = $yhteys->real_escape_string($_POST["haku"]);
    $tyyppi = $_POST["tyyppi"];

    // Rakennetaan SQL haku tyypin mukaan
    if ($tyyppi == "nimi") {
        $hakusql = "
        SELECT *
        FROM Drinkki
        WHERE nimi LIKE '%$haku%'
        ";
    } else if ($tyyppi == "aines") {
        $hakusql = "
        SELECT DISTINCT Drinkki.* 
        FROM Drinkki
        JOIN DrinkinAines ON Drinkki.drinkki_id = DrinkinAines.drinkki_id
        JOIN Aines ON DrinkinAines.aines_id = Aines.aines_id
        WHERE Aines.nimi LIKE '%$haku%'
        ";
    }

    // Suoritetaan haku
    $tulokset = $yhteys->query($hakusql);

    // Tulostetaan tulokset
    if ($tulokset && $tulokset->num_rows > 0) {
        while ($rivi = $tulokset->fetch_assoc()) {

            // Tulostetaan nimi ja juomalaji
            echo "<p><strong>Nimi:</strong> " . $rivi["nimi"] . "<br>";
            echo "<strong>Juomalaji:</strong> " . $rivi["juomalaji"] . "</p>";

            // Haetaan ainesosat
            $drinkki_id = $rivi["drinkki_id"];
            $ainesql = "
            SELECT Aines.nimi, DrinkinAines.maara, DrinkinAines.yksikko
            FROM DrinkinAines
            JOIN Aines
                ON DrinkinAines.aines_id = Aines.aines_id
            WHERE DrinkinAines.drinkki_id = $drinkki_id
            ";

            $ainestulos = $yhteys->query($ainesql);

            // Tulostetaan ainesosat
            echo "<p><strong>Ainesosat:</strong><br>";
            if ($ainestulos->num_rows > 0) {
                while ($aines = $ainestulos->fetch_assoc()) {
                    echo "&nbsp;&nbsp;&nbsp;" . $aines["nimi"];
                    if ($aines["maara"]) {
                        echo " " . $aines["maara"];
                        if ($aines["yksikko"]) {
                            echo " " . $aines["yksikko"];
                        }
                    }
                    echo "<br>";
                }
            }
            echo "</p>";

            // Tulostetaan valmistusohje
            if (!empty($rivi["valmistusohje"])) {
                echo "<p><strong>Valmistusohje:</strong><br>" . $rivi["valmistusohje"] . "</p>";
            }

            echo "<hr>"; 
        }
    } else {
        echo "<p>Ei tuloksia.</p>";
    }

} else {
    // Jos hakukenttä on tyhjä tai HAE ei painettu
    echo "<p>Kirjoita hakusana ja paina HAE.</p>";
}

// Suljetaan yhteys
$yhteys->close();
?>

</body>
</html>
