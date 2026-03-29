<?php
$hostname = "localhost";
$username = "root";      
$password = "Hurvinek168";          
$database = "zaverecka"; 

$conn = mysqli_connect($hostname, $username, $password, $database);

if (!$conn) {
    die(json_encode(array("chyba" => "Připojení selhalo: " . mysqli_connect_error())));
}

if(isset($_POST['cas_ulozeni']) && isset($_POST['pohyb']) && isset($_POST['pocet_osob'])) {
    
    $cas = mysqli_real_escape_string($conn, $_POST['cas_ulozeni']);
    $pohyb = floatval($_POST['pohyb']);
    $pocet_osob = floatval($_POST['pocet_osob']);

    $kontrolaSql = "SELECT id FROM data_senzor_pohybu WHERE cas_ulozeni = '$cas'";
    $vysledekKontroly = mysqli_query($conn, $kontrolaSql);
    
    if(mysqli_num_rows($vysledekKontroly) > 0) {
        echo json_encode(array(
            "uspech" => false,
            "zprava" => "Záznam již existuje"
        ));
    } else {
        $sql = "INSERT INTO data_senzor_pohybu (cas_ulozeni, pohyb, pocet_osob) 
                VALUES ('$cas', $pohyb, $pocet_osob)";

        if (mysqli_query($conn, $sql)) {
            echo json_encode(array(
                "uspech" => true,
                "zprava" => "Záznam úspěšně vložen"
            ));
        } else {
            echo json_encode(array(
                "uspech" => false,
                "zprava" => "Chyba: " . mysqli_error($conn)
            ));
        }
    }
} else {
    echo json_encode(array(
        "uspech" => false,
        "zprava" => "Chybějící data v požadavku"
    ));
}

mysqli_close($conn);
?>