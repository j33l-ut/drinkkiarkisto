<?php
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
    <link rel="stylesheet" href="tyyli.css">
    <title>Reseptien hyväksyminen</title>
<body>

<h1>Drinkkiehdotukset</h1>

<?php
// Otetaan yhteys
include 'yhteys.php';

// Lomakkeiden käsittely
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['id'];

    if (isset($_POST['hyvaksy'])) {
        // Hyväksy drinkki
        $yhteys->query("UPDATE Drinkki SET hyvaksytty = 1 WHERE drinkki_id = $id");
    } elseif (isset($_POST['hylkaa'])) {
        // Hylkää drinkki ja siihen liittyvät ainesosat
        $yhteys->query("DELETE FROM Drinkki WHERE drinkki_id = $id");
        $yhteys->query("DELETE FROM DrinkinAines WHERE drinkki_id = $id");
    }

    // Päivitä sivu
    header("Location: hyvaksy.php");
    exit;
}

// Haetaan kaikki hyväksymättömät drinkit ja niiden ainesosat
$sql = "SELECT d.drinkki_id, d.nimi, d.valmistusohje,
               GROUP_CONCAT(CONCAT(a.nimi, ' ', da.maara, ' ', da.yksikko) SEPARATOR ', ') AS ainesosat
        FROM Drinkki d
        LEFT JOIN DrinkinAines da ON d.drinkki_id = da.drinkki_id
        LEFT JOIN Aines a ON da.aines_id = a.aines_id
        WHERE d.hyvaksytty = 0
        GROUP BY d.drinkki_id";

$tulos = $yhteys->query($sql);

if ($tulos->num_rows == 0) {
    echo '<p class="onnistunut">Ei hyväksymättömiä drinkkejä.</p>';
} else {
    while ($drink = $tulos->fetch_assoc()) {
        ?>
        <div class="drink">
            <h4><?= htmlspecialchars($drink['nimi']) ?></h4>
            <p><strong>Ainesosat:</strong> <?= htmlspecialchars($drink['ainesosat'] ?? 'Ei lisättyjä ainesosia') ?></p>
            <p><strong>Ohje:</strong> <?= nl2br(htmlspecialchars($drink['valmistusohje'] ?? '')) ?></p>

            <form method="post">
                <input type="hidden" name="id" value="<?= $drink['drinkki_id'] ?>">
                <button type="submit" name="hyvaksy">Hyväksy</button>
                <button type="submit" name="hylkaa">Hylkää</button>
            </form>
        </div>
        <?php
    }
}

// Suljetaan yhteys
$yhteys->close();
?>

</body>
</html>
