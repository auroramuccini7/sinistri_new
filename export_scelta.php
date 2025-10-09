<?php
$campi = [
    'ID' => 'ID',
    'Tipo' => 'Tipo',
    'Anno' => 'Anno',
    'Numero' => 'Numero',
    'Repart' => 'Reparto',
    'Gestione' => 'Gestione',
    'Stato' => 'Stato',
    'DataEvento' => 'Data Evento',
    'TipoDanno' => 'Tipo Danno',
    'Causa' => 'Causa',
    'strada' => 'Strada',
    'NumCiv' => 'Num Civ',
    'Annotazioni' => 'Annotazioni',
    'Fasi_Cod' => 'Cod. Fase',
    'DescrizioneFase' => 'Descrizione Fase',
    'DataInizio' => 'Data Inizio Fase',
    'DataFine' => 'Data Fine Fase',
    'Esito' => 'Esito',
    'Valore' => 'Valore',
    'AnnotazioniFase' => 'Annotazioni Fase'
];
?>

<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<title>Seleziona Campi da Esportare</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background: #f4f6f8;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
    }
    .container {
        background: white;
        padding: 25px 40px;
        border-radius: 15px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    h2 {
        color: #1a73e8;
        margin-bottom: 20px;
    }
    .campi {
        display: grid;
        grid-template-columns: repeat(2, 220px);
        gap: 10px;
    }
    button {
        margin-top: 20px;
        background: #1a73e8;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        cursor: pointer;
    }
    button:hover {
        background: #155ab6;
    }
</style>
</head>
<body>
<div class="container">
    <h2>Scegli i campi da esportare</h2>
    <form method="post" action="export_excel.php">
        <div class="campi">
        <?php foreach ($campi as $campo => $label): ?>
            <label>
                <input type="checkbox" name="campi[]" value="<?= $campo ?>" checked>
                <?= $label ?>
            </label>
        <?php endforeach; ?>
        </div>
        <div style="display:flex;justify-content:space-between;">
        <button type="submit">Esporta CSV</button>
        <button type="button" onclick="window.location.href='index.php'">INDIETRO</button>
        </div>
    </form>
</div>
</body>
</html>
