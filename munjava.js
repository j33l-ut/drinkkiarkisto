// moiii tää on mun js tiedosto

document.addEventListener("DOMContentLoaded", function() {

    const nappi = document.getElementById("ilmoita");
    if (nappi) {
        nappi.addEventListener("click", function() {
            alert("Kiitos rekisteröitymisestä!");
        });
    }

});
