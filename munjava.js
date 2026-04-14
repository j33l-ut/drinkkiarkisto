/**
 * JavaScript-tiedosto (munJava.js)
 * 
 * Tarkoitus:
 * - Lisää pieniä toiminnallisuuksia sivulle
 * 
 * Ominaisuudet:
 * - Näyttää ilmoituksen käyttäjälle rekisteröitymisen yhteydessä
 * 
 * Huom:
 * - Suoritetaan vasta kun sivu on ladattu (DOMContentLoaded)
 */

// moiii tää on mun js tiedosto

document.addEventListener("DOMContentLoaded", function() {

    const nappi = document.getElementById("ilmoita");
    if (nappi) {
        nappi.addEventListener("click", function() {
            alert("Kiitos rekisteröitymisestä!");
        });
    }

});
