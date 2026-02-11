<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <title>Poista drinkki</title>
    <link rel="stylesheet" href="tyyli.css">
</head>
<body>

<h2>Poista drinkki</h2>

<?php
require "yhteys.php"; // tietokantayhteys

// Käsitellään Poista-nappi
if (isset($_POST["poista"])) {
    $id = $_POST["drinkki_id"];
    $stmt = $yhteys->prepare("DELETE FROM Drinkki WHERE drinkki_id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "<p class='onnistunut'>Drinkki poistettu.</p>";
    } else {
        echo "<p class='virhe'>Drinkkiä ei voitu poistaa!</p>";
    }
}

// Haetaan kaikki drinkit
$tulos = $yhteys->query("SELECT drinkki_id, nimi FROM Drinkki");

// Tulostetaan lomakkeet
while ($d = $tulos->fetch_assoc()) {
    echo '<form method="post">';
    echo 'Drinkki: ' . htmlspecialchars($d["nimi"]) . ' ';
    echo '<input type="hidden" name="drinkki_id" value="' . $d["drinkki_id"] .  '">';
    echo '<button type="submit" name="poista">Poista</button>';
    echo '</form>';
}

$yhteys->close();
?>

</body>
</html>
