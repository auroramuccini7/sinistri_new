<?php
$conn = new mysqli("localhost", "root", "", "sinistri_nuovi");
if ($conn->connect_error) die("Connessione fallita: " . $conn->connect_error);
$anno = date('Y'); 
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_sinistro=$_POST['sinistri_fasi_id'];
    $tipo = $_POST['tipo'];
    $sinistro_numero = $_POST['numero'];
    $cod_fase = $_POST['cod_fase'];
    $data_inizio = $_POST['data_inizio'];
    $data_fine = $_POST['data_fine'];
    $esito = $_POST['esito'];
    $valore = $_POST['valore'];
    $annotazioni = $_POST['annotazioni'];

    $stmt = $conn->prepare("INSERT INTO fasi_nuove
    (sinistri_fasi_id,sinistri_tipo, sinistri_anno, sinistri_numero, fasi_cod, dataInizio, dataFine, esito, valore, annotazioni) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param(
    "siisssdsss", 
    $id_sinistro,
    $tipo, 
    $anno, 
    $sinistro_numero, 
    $cod_fase, 
    $data_inizio, 
    $data_fine, 
    $esito, 
    $valore, 
    $annotazioni
);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }
    $stmt->close();
    exit;
}

?>
