<?php
// dettaglio.php
require_once 'config_reparti.php';
require_once 'config_comuni.php';
require_once 'config_tipo.php';
require_once 'config_stato.php';
require_once 'config_danno.php';
require_once 'config_gestione.php';
require 'api_strade.php';
require 'api_strade_bellaria.php';
// Connessione al database
$conn = new mysqli("localhost", "root", "", "sinistri_nuovi");
if ($conn->connect_error) die("Connessione fallita: " . $conn->connect_error);

if (!isset($_GET['id'])) die("ID sinistro mancante.");

$id = intval($_GET['id']);

// Recupero dati sinistro
$stmt = $conn->prepare("SELECT * FROM sinistri_nuovi WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$sinistro = $stmt->get_result()->fetch_assoc();
if (!$sinistro) die("Sinistro non trovato.");

// Recupero fasi
$stmt = $conn->prepare("SELECT * FROM fasi_nuove JOIN grid_tfas_csv ON cod = Fasi_cod WHERE Sinistri_Fasi_id = ? ORDER BY DataInizio");
$stmt->bind_param("i", $id);
$stmt->execute();
$fasi = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<title>Dettaglio Sinistro #<?= $sinistro['numero'] ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: #f7f9fc; font-family: Arial, sans-serif; color: #333; margin: 30px; }
.card { background: #fff; padding: 20px 30px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); margin-bottom: 20px; }
h2, h3 { color: #1a73e8; }
.detail-row { display: flex; padding: 8px 0; border-bottom: 1px solid #eee; }
.detail-row strong { width: 180px; flex-shrink: 0; color: #555; }
table { border-collapse: collapse; width: 100%; margin-top: 15px; }
table th, table td { border: 1px solid #ddd; padding: 10px; text-align: center; }
table th { background: #1a73e8; color: #fff; }
table tr:nth-child(even) { background: #f2f6fc; }
button { background: #1a73e8; color: white; border: none; padding: 10px 16px; border-radius: 6px; cursor: pointer; font-weight: bold; transition: background 0.3s; }
button:hover { background: #155ab6; }
.actions { text-align: center; margin-top: 20px; }
</style>
</head>
<body>

<div class="card">
    <h2>Dettaglio Sinistro #<?= $sinistro['numero'] ?></h2>
    <div class="detail-row"><strong>Tipo:</strong> <?= Tipo::getTipoLabel($sinistro['tipo']) ?></div>
    <div class="detail-row"><strong>Anno:</strong> <?= htmlspecialchars($sinistro['anno']) ?></div>
    <div class="detail-row"><strong>Numero:</strong> <?= htmlspecialchars($sinistro['numero']) ?></div>
        <div class="detail-row"><strong>Comune:</strong> <?= Comuni::getComuniLabel($sinistro['Comune']) ?></div>
    <div class="detail-row"><strong>Reparto:</strong> <?= Reparto::getRepartLabel($sinistro['repart']) ?></div>
    <div class="detail-row"><strong>Gestione:</strong> <?=  Gestione::getGestioneLabel($sinistro['gestione']) ?></div>
    <div class="detail-row"><strong>Stato:</strong> <?= Stato::getStatoLabel(htmlspecialchars($sinistro['stato'])) ?></div>
    <div class="detail-row"><strong>Data Evento:</strong> <?= (new DateTime($sinistro['DataEvento']))->format('d/m/Y'); ?></div>
    <div class="detail-row"><strong>Tipo Danno:</strong> <?= Danno::getDannoLabel($sinistro['TipoDanno']) ?></div>
    <div class="detail-row"><strong>Causa:</strong> <?= htmlspecialchars($sinistro['causa']) ?></div>
<div class="detail-row">
    <strong>Strada:</strong>
    <?php
    if (!empty($sinistro['strada'])) {
        $strada = getStradaFromId($sinistro['strada']);
        if (!empty($strada)) {
            echo htmlspecialchars($strada) . " " . htmlspecialchars($sinistro['NumCiv']);
        }else{
   $strada =       getStradaFromIdBellaria($sinistro['strada']);
            echo htmlspecialchars($strada) . " " . htmlspecialchars($sinistro['NumCiv']);
        }
    }
   
    ?>
</div>

    <div class="detail-row"><strong>Annotazioni:</strong> <?= nl2br(htmlspecialchars($sinistro['annotazioni'])) ?></div>
</div>

<div class="card">
    <h3>Fasi</h3>
    <?php if ($fasi->num_rows > 0): ?>
    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <thead class="table-primary">
                <tr>
                    <th>Cod. Fase</th>
                    <th>Descrizione</th>
                    <th>Data Inizio</th>
                    <th>Data Fine</th>
                    <th>Esito</th>
                    <th>Valore</th>
                    <th>Annotazioni</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($fase = $fasi->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($fase['Fasi_Cod']) ?></td>
                    <td><?= !empty($fase['Descrizione']) ? $fase['Descrizione'] : "" ?></td>
                    <td><?= htmlspecialchars($fase['DataInizio']) ?></td>
                    <td><?= htmlspecialchars($fase['DataFine']) ?></td>
                    <td><?= htmlspecialchars($fase['esito']) ?></td>
                    <td><?= htmlspecialchars($fase['valore']) ?></td>
                    <td><?= htmlspecialchars($fase['Annotazioni']) ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <p><em>Nessuna fase registrata.</em></p>
    <?php endif; ?>
</div>

<div class="actions">
    <button onclick="window.location.href='index.php'">⬅️ Torna all'elenco</button>
</div>

</body>
</html>
