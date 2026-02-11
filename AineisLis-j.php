<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <title>Aineksen lisääminen</title>
    <link rel="stylesheet" href="tyyli.css">
</head>
<?php
// lisää navigointipalkin
include 'naviAdmin.php'; 
?>
<body>
    <h1>Aineksen lisääminen</h1>

    <form method="post" action="">
        <label for="aines">Aines:</label>
        <input type="text" id="aines" name="aines">
        <button type="submit" name="lisaa">Lisää</button>
    </form>

<?php
include 'yhteys.php';  // tietokantayhteys

if (isset($_POST['lisaa'])) {  
    $aines = trim($_POST['aines']);  

    if ($aines == "") {
        echo "<p class='virhe'>Aines ei voi olla tyhjä!</p>";
    } else {
        // Tarkista, onko aines jo olemassa
        $stmt = $yhteys->prepare("SELECT * FROM Aines WHERE nimi = ?");
        $stmt->bind_param("s", $aines);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "<p class='virhe'>Aines on jo tietokannassa!</p>";
        } else {
            // Lisää uusi aines
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

// Hae kaikki ainekset tietokannasta ja näytä lista
$ainekset = $yhteys->query("SELECT * FROM Aines ORDER BY aines_id ASC");

if ($ainekset->num_rows > 0) {
    echo "<h2>Kaikki ainekset:</h2><ul>";
    while ($row = $ainekset->fetch_assoc()) {
        echo "<li>Aineisosa: " . htmlspecialchars($row['nimi']) . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p>Ei vielä aineksia.</p>";
}

$yhteys->close();
?>

</body>
</html>
