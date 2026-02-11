<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <title>Rekisteröinti lomake</title>
    <link rel="stylesheet" href="tyyli.css">
    <script src="munJava.js" defer></script>
</head>
<body class="rekisteri">
    <?php
// Tämä lisää navigointipalkinS
include 'naviGuest.php';
?>
    <h1>Rekisteröidy drinkkiarkiston käyttäjäksi</h1>

<form method="post" action="" class="AineisLis-j">
    <input type="text" name="kayttajatunnus" placeholder="Käyttäjätunnus"><br>
    <input type="password" name="salasana" placeholder="Salasana"><br><br>
    <input type="email" name="sahkoposti" placeholder="Sähköposti"><br><br>
    <button type="submit" name="rekisteroidy" id="ilmoita">Rekisteröidy</button>
</form>

<?php
include 'yhteys.php';  // tietokantayhteys

if (isset($_POST['rekisteroidy'])) {
    $kayttajatunnus = trim($_POST['kayttajatunnus']);
    $salasana = trim($_POST['salasana']);
    $sahkoposti = trim($_POST['sahkoposti']);

    if ($kayttajatunnus == "") {
        echo "<p class='virhe'>Käyttäjätunnus ei voi olla tyhjä!</p>";
    } else {
        // Tarkista, onko käyttäjätunnus jo olemassa
        $stmt = $yhteys->prepare("SELECT * FROM Kayttaja WHERE kayttajatunnus = ?");
        $stmt->bind_param("s", $kayttajatunnus);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "<p class='virhe'>Käyttäjätunnus on jo käytössä!</p>";
        } else {
            // Lisää uusi käyttäjä roolilla 'user'
            $hashed_password = password_hash($salasana, PASSWORD_DEFAULT);
            $stmt = $yhteys->prepare("INSERT INTO Kayttaja (kayttajatunnus, salasana, sahkoposti, rooli) VALUES (?, ?, ?, 'user')");
            $stmt->bind_param("sss", $kayttajatunnus, $hashed_password, $sahkoposti);

            if ($stmt->execute()) {
                echo "<p class='onnistunut'>Rekisteröinti onnistui!</p>";
            } else {
                echo "<p class='virhe'>Virhe rekisteröinnissä.</p>";
            }
        }

        $stmt->close();
    }
}

$yhteys->close();
?>

<img src="h1.jpg" alt="Kuva 1" class="rekisterikuva">
<img src="h2.jpg" alt="Kuva 2" class="rekisterikuva">


<p>
Rekisteröityessäsi drinkkiarkiston käyttäjäksi hyväksyt henkilötietojesi käsittelyehdot.<br>
Lue täältä <a href="tietosuoja.php">Lue tietosuojaseloste</a>
</p>

</body>
</html>
