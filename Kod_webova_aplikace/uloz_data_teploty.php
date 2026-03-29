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

if(isset($_POST['cas_ulozeni']) && isset($_POST['teplota']) && isset($_POST['tlak']) && isset($_POST['vlhkost']) && isset($_POST['nadmorska_vyska'])) {
    $cas_ulozeni = $_POST['cas_ulozeni'];
    $teplota = floatval($_POST['teplota']);
    $tlak = floatval($_POST['tlak']);
    $vlhkost = floatval($_POST['vlhkost']);
    $nadmorska_vyska = intval($_POST['nadmorska_vyska']);

    $sql = "INSERT INTO data_senzor_teploty (cas_ulozeni, teplota, tlak, vlhkost, nadmorska_vyska) VALUES ('".$cas_ulozeni."', ".$teplota.", ".$tlak.", ".$vlhkost.", ".$nadmorska_vyska.")";

    if (mysqli_query($conn, $sql)) {
        echo "Nový záznam úspěšně vytvořen.<br>";
    } else {
        echo "Chyba: " . $sql . "<br>" . mysqli_error($conn);
    }
} else {
    echo "Potřebná data nebyla doložena.<br>";
}

?>