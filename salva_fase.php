<?php
$conn = new mysqli("localhost", "root", "", "sinistri_nuovi");
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sinistro_numero = $_POST['numero'];
  $anno = date('Y');

    // Recupera l'ID del sinistro
    $stmt = $conn->prepare("SELECT id FROM sinistri_nuovi WHERE numero = ? AND anno = ?");
    if ($stmt === false) {
        echo json_encode(['success' => false, 'error' => $conn->error]);
        exit;
    }

    $stmt->bind_param("ii", $sinistro_numero, $anno);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $id_sinistro = $row['id'];
    } else {
        echo json_encode(['success' => false, 'error' => 'Sinistro non trovato']);
        exit;
    }
    $stmt->close();

    // Prepara i dati della fase
    $tipo = $_POST['tipo'];
    $cod_fase = $_POST['cod_fase'];
    $data_inizio = $_POST['data_inizio'];
    $data_fine = $_POST['data_fine'];
    $esito = $_POST['esito'];
    $prot_num = $_POST['prot_num'];
    $valore = floatval($_POST['valore']);
    $annotazioni = $_POST['annotazioni'];


    // Inserisce la fase
    $stmt = $conn->prepare("INSERT INTO fasi_nuove
        (sinistri_id, sinistri_tipo, sinistri_anno, sinistri_numero, fasi_cod, dataInizio, dataFine, esito,Prot_num, valore, annotazioni)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    if ($stmt === false) {
        echo json_encode(['success' => false, 'error' => $conn->error]);
        exit;
    }

    $stmt->bind_param(
        "siisssssdds",
        $id_sinistro,
        $tipo,
        $anno,
        $sinistro_numero,
        $cod_fase,
        $data_inizio,
        $data_fine,
        $esito,
        $prot_num,
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