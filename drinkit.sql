CREATE TABLE Kayttaja (
    kayttaja_id INT PRIMARY KEY AUTO_INCREMENT,
    kayttajatunnus VARCHAR(50) NOT NULL UNIQUE,
    salasana VARCHAR(255) NOT NULL,
    sahkoposti VARCHAR(100),
    rooli VARCHAR(20)
);

CREATE TABLE Drinkki (
    drinkki_id INT PRIMARY KEY AUTO_INCREMENT,
    nimi VARCHAR(100) NOT NULL,
    juomalaji VARCHAR(50),
    valmistusohje TEXT,
    hyvaksytty BOOLEAN
); 

CREATE TABLE Aines (
    aines_id INT PRIMARY KEY AUTO_INCREMENT,
    nimi VARCHAR(50) NOT NULL
);


CREATE TABLE DrinkinAines (
    drinkki_id INT,
    aines_id INT,
    maara VARCHAR(20),
    yksikko VARCHAR(10),
    PRIMARY KEY (drinkki_id, aines_id),
    FOREIGN KEY (drinkki_id) REFERENCES Drinkki(drinkki_id),
    FOREIGN KEY (aines_id) REFERENCES Aines(aines_id)
);

