<?php
/**
 * Drinkin lisääminen
 * 
 * Tarkoitus:
 * - Mahdollistaa uuden drinkin lisääminen
 * 
 * Ominaisuudet:
 * - Admin: drinkki hyväksytään heti
 * - User: drinkki menee hyväksyttäväksi
 * 
 * Huom:
 * - hyvaksytty = 0 → ei vielä hyväksytty (odottaa adminin hyväksyntää)
 * - hyvaksytty = 1 → hyväksytty (näkyy käyttäjille)
 */
?>

<?php
session_start();

// Tarkistetaan, että käyttäjä on kirjautunut
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Näytetään navigointi roolin mukaan
if ($_SESSION["role"] == 1) {
    include "naviAdmin.php";
} else {
    include "naviUser.php";
}
?>

<!DOCTYPE html>
<html lang="fi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Lisää Ehdotus</title>
<link rel="stylesheet" href="tyyli.css">
</head>
<body>

<h2>Lisää Ehdotus</h2>

<?php
include "yhteys.php";

// Haetaan ainekset select-valikkoa varten
$ainekset = [];
$stmt = $yhteys->prepare("SELECT aines_id, nimi FROM Aines ORDER BY nimi");
$stmt->execute();
$result = $stmt->get_result();
while ($r = $result->fetch_assoc()) {
    $ainekset[] = $r;
}
$stmt->close();
?>

<form method="post">
Nimi:<br>
<input type="text" name="nimi"><br><br>

Juomalaji:<br>
<input type="text" name="juomalaji"><br><br>

<?php for ($i=1; $i<=3; $i++): ?>
<div class="aines-rivi">
<label>Raaka-aine <?= $i ?>:</label>
<select name="aines<?= $i ?>">
<?php foreach ($ainekset as $a): ?>
<option value="<?= $a['aines_id'] ?>">
<?= htmlspecialchars($a['nimi']) ?>
</option>
<?php endforeach; ?>
</select>

<label>Määrä:</label>
<input type="text" name="maara<?= $i ?>">
<br><br>
</div>
<?php endfor; ?>

Ohjeet:<br>
<textarea name="valmistusohje" rows="4" cols="40"></textarea><br><br>

<input type="submit" name="laheta" value="LISÄÄ RESEPTI">
</form>

<?php
if (isset($_POST['laheta'])) {

    $virheet = [];

    // Käyttäjän syötteet
    $nimi = trim($_POST['nimi']);
    $juomalaji = trim($_POST['juomalaji']);
    $ohje = trim($_POST['valmistusohje']);
    $aineset = [];
    $maarät = [];

    for ($i=1; $i<=3; $i++) {
        $aineset[$i] = $_POST["aines$i"];
        $maarät[$i] = trim($_POST["maara$i"]);
    }

    // Tarkistukset
    if ($nimi == "") $virheet[] = "Nimi ei saa olla tyhjä.";

    $stmt = $yhteys->prepare("SELECT drinkki_id FROM Drinkki WHERE nimi = ?");
    $stmt->bind_param("s", $nimi);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) $virheet[] = "Tämän niminen drinkki on jo olemassa.";
    $stmt->close();

    if ($maarät[1] == "" && $maarät[2] == "" && $maarät[3] == "") {
        $virheet[] = "Vähintään yksi raaka-aineen määrä täytyy täyttää.";
    }

    if (!empty($virheet)) {
        echo "<ul class='virhe'>";
        foreach ($virheet as $v) echo "<li>$v</li>";
        echo "</ul>";
    } else {
        // Käyttäjän ehdotus → hyvaksytty = 0
        $hyvaksytty = 0;

        // Lisätään drinkki
        $stmt = $yhteys->prepare("INSERT INTO Drinkki (nimi, juomalaji, valmistusohje, hyvaksytty) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $nimi, $juomalaji, $ohje, $hyvaksytty);
        $stmt->execute();
        $drinkkiId = $stmt->insert_id;
        $stmt->close();

        // Lisätään raaka-aineet
        $yksikko = ""; // halutessasi voit lisätä yksikön
        for ($i=1; $i<=3; $i++) {
            if ($maarät[$i] != "") {
                $stmt = $yhteys->prepare("INSERT INTO DrinkinAines (drinkki_id, aines_id, maara, yksikko) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iiss", $drinkkiId, $aineset[$i], $maarät[$i], $yksikko);
                $stmt->execute();
                $stmt->close();
            }
        }

        echo "<p class='onnistunut'>Ehdotus lisätty onnistuneesti! Odottaa hyväksyntää adminilta.</p>";
    }
}

$yhteys->close();
?>

</body>
</html>