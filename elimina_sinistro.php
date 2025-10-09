<?php
// Connessione al database
$conn = new mysqli("localhost", "root", "", "sinistri_nuovi");

if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

// Controllo che ci sia l'ID
if (isset($_POST['numero'])) {
    $numero = (int) $_POST['numero'];
 $id = (int) $_POST['id'];
 $fasi =  $conn->query("SELECT * FROM fasi_nuove WHERE sinistri_numero = $numero");
    // Prima eliminiamo le fasi legate al sinistro
    if(!empty($fasi)){

        $conn->query("DELETE FROM fasi_nuove WHERE sinistri_numero  = $numero");
    }

    // Poi eliminiamo il sinistro
    $conn->query("DELETE FROM sinistri_nuovi WHERE numero = $numero");
}

// Torna alla lista
header("Location: visualizza_sinistri.php");
exit;
?>
