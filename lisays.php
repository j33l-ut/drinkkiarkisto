<?php
// Käynnistetään session
session_start();

// Tarkistetaan, onko käyttäjä kirjautunut
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Näytetään oikea navigointi roolin mukaan
if ($_SESSION["role"] == 1) {
    include "naviAdmin.php"; // admin
} else {
    include "naviUser.php";  // tavallinen käyttäjä
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
// Otetaan tietokantayhteys
include "yhteys.php";

// Haetaan ainekset SELECT-valikkoja varten
$ainekset = [];
$stmt = $yhteys->prepare("SELECT aines_id, nimi FROM Aines ORDER BY nimi");
$stmt->execute();
$result = $stmt->get_result();
while ($r = $result->fetch_assoc()) {
    $ainekset[] = $r;
}
$stmt->close();
?>

<!-- Lomake uuden drinkin lisäämiseksi -->
<form method="post" action="">
    Nimi:<br>
    <input type="text" name="nimi" placeholder="nimi"><br><br>

    Juomalaji:<br>
    <input type="text" name="juomalaji" placeholder="juomalaji"><br><br>

    <!-- Kolme raaka-ainetta -->
    <?php for ($i=1; $i<=3; $i++): ?>
        <div class="aines-rivi">
            <div class="aines">
                <label>Raaka-aine <?= $i ?>:</label>
                <select name="aines<?= $i ?>">
                    <?php foreach ($ainekset as $a): ?>
                        <option value="<?= $a['aines_id'] ?>">
                            <?= htmlspecialchars($a['nimi']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="maara">
                <label>Määrä:</label>
                <input type="text" name="maara<?= $i ?>">
            </div>
        </div>
    <?php endfor; ?>

    <br>
    Ohjeet:<br>
    <textarea name="valmistusohje" placeholder="ohjeet" rows="4" cols="40"></textarea><br><br>

    <input type="submit" name="laheta" value="LISÄÄ RESEPTI">
</form>

<?php
// Käsitellään lomake
if (isset($_POST['laheta'])) {

    $virheet = [];

    // Otetaan käyttäjän syötteet
    $nimi = trim($_POST['nimi']);
    $juomalaji = trim($_POST['juomalaji']);
    $ohje = trim($_POST['valmistusohje']);
    $aineset = [];
    $maarät = [];

    for ($i=1; $i<=3; $i++) {
        $aineset[$i] = $_POST["aines$i"];
        $maarät[$i] = trim($_POST["maara$i"]);
    }

    // Tarkistetaan pakolliset kentät
    if ($nimi == "") $virheet[] = "Nimi ei saa olla tyhjä.";

    // Tarkistetaan, onko drinkki jo olemassa
    $stmt = $yhteys->prepare("SELECT drinkki_id FROM Drinkki WHERE nimi = ?");
    $stmt->bind_param("s", $nimi);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) $virheet[] = "Tämän niminen drinkki on jo olemassa.";
    $stmt->close();

    // Tarkistetaan, että vähintään yksi määrä on täytetty
    if ($maarät[1] == "" && $maarät[2] == "" && $maarät[3] == "") {
        $virheet[] = "Vähintään yksi raaka-aineen määrä täytyy täyttää.";
    }

    // Näytetään virheet, jos niitä on
    if (!empty($virheet)) {
        echo "<ul class='virhe'>";
        foreach ($virheet as $v) echo "<li>$v</li>";
        echo "</ul>";
    } else {
        // Lisätään drinkki tietokantaan
        $hyvaksytty = 0;
        $stmt = $yhteys->prepare("INSERT INTO Drinkki (nimi, juomalaji, valmistusohje, hyvaksytty) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $nimi, $juomalaji, $ohje, $hyvaksytty);
        $stmt->execute();
        $drinkkiId = $stmt->insert_id;
        $stmt->close();

        // Lisätään raaka-aineet
        $yksikko = "";
        for ($i=1; $i<=3; $i++) {
            if ($maarät[$i] != "") {
                $stmt = $yhteys->prepare("INSERT INTO DrinkinAines (drinkki_id, aines_id, maara, yksikko) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iiss", $drinkkiId, $aineset[$i], $maarät[$i], $yksikko);
                $stmt->execute();
                $stmt->close();
            }
        }

        echo "<p class='onnistunut'>Resepti lisätty onnistuneesti!</p>";
    }
}
$yhteys->close();
?>

</body>
</html>