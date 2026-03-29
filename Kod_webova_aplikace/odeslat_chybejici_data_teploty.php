<?php
$hostname = "localhost";
$username = "root";      
$password = "Hurvinek168";          
$database = "zaverecka"; 

$conn = mysqli_connect($hostname, $username, $password, $database);

if (!$conn) {
    die(json_encode(array("chyba" => "Připojení selhalo: " . mysqli_connect_error())));
}

if(isset($_POST['cas_ulozeni']) && isset($_POST['teplota']) && isset($_POST['tlak']) && 
   isset($_POST['vlhkost']) && isset($_POST['nadmorska_vyska'])) {
    
    $cas = mysqli_real_escape_string($conn, $_POST['cas_ulozeni']);
    $teplota = floatval($_POST['teplota']);
    $tlak = floatval($_POST['tlak']);
    $vlhkost = floatval($_POST['vlhkost']);
    $vyska = floatval($_POST['nadmorska_vyska']);

    $kontrolaSql = "SELECT id FROM data_senzor_teploty WHERE cas_ulozeni = '$cas'";
    $vysledekKontroly = mysqli_query($conn, $kontrolaSql);
    
    if(mysqli_num_rows($vysledekKontroly) > 0) {
        echo json_encode(array(
            "uspech" => false,
            "zprava" => "Záznam již existuje"
        ));
    } else {
        $sql = "INSERT INTO data_senzor_teploty (cas_ulozeni, teplota, tlak, vlhkost, nadmorska_vyska) 
                VALUES ('$cas', $teplota, $tlak, $vlhkost, $vyska)";

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