<?php
/**
 * Uloskirjautuminen
 * 
 * Tarkoitus:
 * - Lopettaa käyttäjän session
 * - Kirjaa käyttäjän ulos järjestelmästä
 * 
 * Ominaisuudet:
 * - Tyhjentää session tiedot
 * - Tuhoaa session
 * - Ohjaa login-sivulle
 */
?>
<?php
session_start();

$_SESSION = [];
session_destroy();

header("Location: login.php");
exit();