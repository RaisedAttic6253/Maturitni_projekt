<?php

$hostname = "localhost";
$username = "root";
$password = "Hurvinek168";
$database = "zaverecka";

$conn = mysqli_connect($hostname, $username, $password, $database);

if (!$conn) {
    die("Chyba s připojením k databázi: " . mysqli_connect_error());
}

echo "Úspěšně připojeno k databázi<br>";

if(isset($_POST['cas_ulozeni']) && isset($_POST['pohyb']) && isset($_POST['pocet_osob'])) {
    $cas_ulozeni = $_POST['cas_ulozeni'];
    $pohyb = intval($_POST['pohyb']);
    $pocet_osob = intval($_POST['pocet_osob']);

    $sql = "INSERT INTO data_senzor_pohybu (cas_ulozeni, pohyb, pocet_osob) VALUES ('".$cas_ulozeni."', ".$pohyb.", ".$pocet_osob.")";

    if (mysqli_query($conn, $sql)) {
        echo "Nový záznam úspěšně vytvořen.<br>";
    } else {
        echo "Chyba: " . $sql . "<br>" . mysqli_error($conn);
    }
} else {
    echo "Potřebná data nebyla doložena.<br>";
}

?>