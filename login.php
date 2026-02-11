<?php
session_start();
require_once "yhteys.php"; // sisältää tietokantayhteyden
?>

<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="tyyli.css">
    <title>Kirjautuminen</title>
</head>
<body>

<?php include "naviGuest.php"; ?>

<h2>Kirjautuminen</h2>

<form method="post" action="">
    <label>Käyttäjätunnus</label><br>
    <input type="text" name="username" required><br><br>
    <label>Salasana</label><br>
    <input type="password" name="password" required><br><br>

    <button type="submit" name="login">Kirjaudu</button>
</form>

<?php
if (isset($_POST["login"])) {
    $username = $_POST["username"];
    $password = $_POST["password"];

    // mysqli-query
    $stmt = $yhteys->prepare("SELECT kayttaja_id, salasana, rooli FROM Kayttaja WHERE kayttajatunnus = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user["salasana"])) {
        $_SESSION["user_id"] = $user["kayttaja_id"];
        $_SESSION["role"] = $user["rooli"];
        header("Location: haku.php");
        exit();
    } else {
        echo "<p class='virhe'>Virheellinen käyttäjätunnus tai salasana</p>";
    }
}

?>

</body>
</html>
