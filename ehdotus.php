<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Lisää Ehdotus</title>
    <link rel="stylesheet" href="tyyli.css">
</head>
<body> 

<h2>Lisää Ehdotus</h2>

<?php

require "yhteys.php"; //tietokantayhteys

// Haetaan ainekset tietokannasta
$ainekset = [];
$tulos = $yhteys->query("SELECT aines_id, nimi FROM Aines ORDER BY nimi");
while ($r = $tulos->fetch_assoc()) {
    $ainekset[] = $r;
}
?>

<form method="post" action="">

Nimi:<br>
<input type="text" name="nimi" placeholder="nimi">

Juomalaji:<br>
<input type="text" name="juomalaji" placeholder="juomalaji"><br><br>

<div class="aines-rivit">

  <div class="aines-rivi">
    <div class="aines">
      <label>Raaka-aine:</label>
      <select name="aines1">
        <?php foreach ($ainekset as $a): ?>
          <option value="<?= $a['aines_id'] ?>">
            <?= htmlspecialchars($a['nimi']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="maara">
      <label>Raaka-aineen määrä:</label>
      <input type="text" name="maara1">
    </div>
  </div>

  <div class="aines-rivi">
    <div class="aines">
      <select name="aines2">
        <?php foreach ($ainekset as $a): ?>
          <option value="<?= $a['aines_id'] ?>">
            <?= htmlspecialchars($a['nimi']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="maara">
      <input type="text" name="maara2">
    </div>
  </div>

  <div class="aines-rivi">
    <div class="aines">
      <select name="aines3">
        <?php foreach ($ainekset as $a): ?>
          <option value="<?= $a['aines_id'] ?>">
            <?= htmlspecialchars($a['nimi']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="maara">
      <input type="text" name="maara3">
    </div>
  </div>

</div>

<br>

Ohjeet:<br>
<textarea name="valmistusohje" placeholder="ohjeet" rows="4" cols="40"></textarea>
<br><br>

<input type="submit" name="laheta" value="LISÄÄ RESEPTI">

</form>

<hr>

<?php
// LOMAKKEEN KÄSITTELY

if (isset($_POST['laheta'])) {

    $virheet = [];

    $nimi = trim($_POST['nimi']);
    $juomalaji = trim($_POST['juomalaji']);
    $ohje = trim($_POST['valmistusohje']);

    $aines1 = $_POST['aines1'];
    $maara1 = trim($_POST['maara1']);

    $aines2 = $_POST['aines2'];
    $maara2 = trim($_POST['maara2']);

    $aines3 = $_POST['aines3'];
    $maara3 = trim($_POST['maara3']);

    // Tarkistus 1: nimi ei tyhjä
    if ($nimi == "") {
        $virheet[] = "Nimi ei saa olla tyhjä.";
    }

    // Tarkistus 2: nimi ei saa olla jo tietokannassa
    $check = $yhteys->query("SELECT drinkki_id FROM Drinkki WHERE nimi = '$nimi'");
    if ($check->num_rows > 0) {
        $virheet[] = "Tämän niminen drinkki on jo olemassa.";
    }

    // Tarkistus 3: vähintään yksi määrä täytetty
    if ($maara1 == "" && $maara2 == "" && $maara3 == "") {
        $virheet[] = "Vähintään yksi raaka-aineen määrä täytyy täyttää.";
    }

    // Tulostetaan virheet tai lisätään tiedot
    if (count($virheet) > 0) {
        echo "<ul class='virhe'>";
        foreach ($virheet as $v) {
            echo "<li>$v</li>";
        }
        echo "</ul>";
    } else {

        // Lisätään drinkki
        $hyvaksytty = 0;
        $yhteys->query("INSERT INTO Drinkki (nimi, juomalaji, valmistusohje, hyvaksytty)
                        VALUES ('$nimi', '$juomalaji', '$ohje', $hyvaksytty)");

        // Haetaan ID
        $drinkkiId = $yhteys->insert_id;

        $yksikko = "";

        // Lisätään ainekset
        if ($maara1 != "") {
            $yhteys->query("INSERT INTO DrinkinAines (drinkki_id, aines_id, maara, yksikko)
                            VALUES ($drinkkiId, $aines1, '$maara1', '$yksikko')");
        }

        if ($maara2 != "") {
            $yhteys->query("INSERT INTO DrinkinAines (drinkki_id, aines_id, maara, yksikko)
                            VALUES ($drinkkiId, $aines2, '$maara2', '$yksikko')");
        }

        if ($maara3 != "") {
            $yhteys->query("INSERT INTO DrinkinAines (drinkki_id, aines_id, maara, yksikko)
                            VALUES ($drinkkiId, $aines3, '$maara3', '$yksikko')");
        }

        echo "<p class='onnistunut'>Resepti lisätty onnistuneesti!</p>";
    }
}
?>

</body>
</html>
