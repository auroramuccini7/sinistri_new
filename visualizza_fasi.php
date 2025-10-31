<?php $conn = new mysqli("localhost", "root", "", "sinistri_nuovi");
require_once 'estrai_fasi.php';
require_once 'config_tipo.php';
require_once 'config_reparti.php';
require_once 'config_gestione.php';
require_once 'config_stato.php';
if ($conn->connect_error)
    die("Connessione fallita: " . $conn->connect_error); // --- Parametri --- $idSinistro = (int)($_GET['id'] ?? 0); $anno = (int)($_GET['anno'] ?? date('Y')); $tipo = $_GET['tipo'] ?? ''; if ($idSinistro <= 0) { die("<p style='color:red;text-align:center;margin-top:2rem;'>❌ ID sinistro non valido</p>"); } // --- Inserimento nuova fase --- if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nuova_fase'])) { $cod = $conn->real_escape_string($_POST['cod'] ?? ''); $descrizione = $conn->real_escape_string($_POST['descrizione'] ?? ''); $inizio = !empty($_POST['inizio']) ? "'".$conn->real_escape_string($_POST['inizio'])."'" : "NULL"; $fine = !empty($_POST['fine']) ? "'".$conn->real_escape_string($_POST['fine'])."'" : "NULL"; $prot = $conn->real_escape_string($_POST['prot'] ?? ''); $valore = (float)($_POST['valore'] ?? 0); $annotazioni = $conn->real_escape_string($_POST['annotazioni'] ?? ''); $sqlIns = " INSERT INTO fasi (id_sinistro, anno, Fasi_Cod, Descrizione, DataInizio, DataFine, Prot_num, Valore, Annotazioni) VALUES ($idSinistro, $anno, '$cod', '$descrizione', $inizio, $fine, '$prot', $valore, '$annotazioni') "; $conn->query($sqlIns); header("Location: fasi.php?id=$idSinistro&anno=$anno&tipo=$tipo&ok=1"); exit; } // --- Recupero fasi --- $fasi = Fasi::getFasi($conn, $idSinistro, $anno); ?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>Fasi del sinistro <?= htmlspecialchars($tipo . " " . $anno . " / " . $idSinistro) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
        body {
            background: #f7f9fc;
        }

        .card {
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .table th {
            background-color: #e9f3ff;
        }

        .table-sm td,
        .table-sm th {
            padding: .3rem .5rem;
        }
    </style>
</head>

<body class="p-4">
    <div class="container">
        <h3 class="text-center mb-4">⚙️ Gestione Fasi — Sinistro <?= htmlspecialchars("$tipo $anno / $idSinistro") ?>
        </h3> <?php if (isset($_GET['ok'])): ?>
            <div class="alert alert-success py-2 text-center">✅ Fase aggiunta con successo</div> <?php endif; ?>
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="mb-3">➕ Aggiungi nuova fase</h5>
                <form method="post" class="row g-2 align-items-end"> <input type="hidden" name="nuova_fase" value="1">
                    <div class="col-md-1"> <label class="form-label">Cod.</label> <input type="text" name="cod"
                            class="form-control form-control-sm" required> </div>
                    <div class="col-md-3"> <label class="form-label">Descrizione</label> <input type="text"
                            name="descrizione" class="form-control form-control-sm" required> </div>
                    <div class="col-md-2"> <label class="form-label">Inizio</label> <input type="date" name="inizio"
                            class="form-control form-control-sm"> </div>
                    <div class="col-md-2"> <label class="form-label">Fine</label> <input type="date" name="fine"
                            class="form-control form-control-sm"> </div>
                    <div class="col-md-1"> <label class="form-label">Prot.</label> <input type="text" name="prot"
                            class="form-control form-control-sm"> </div>
                    <div class="col-md-1"> <label class="form-label">Valore</label> <input type="number" step="0.01"
                            name="valore" class="form-control form-control-sm"> </div>
                    <div class="col-md-2"> <label class="form-label">Annotazioni</label> <input type="text"
                            name="annotazioni" class="form-control form-control-sm"> </div>
                    <div class="col-md-12 text-end"> <button type="submit" class="btn btn-success btn-sm">💾 Salva
                            Fase</button> </div>
                </form>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h5 class="mb-3">📋 Elenco Fasi</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle">
                        <thead>
                            <tr>
                                <th>Cod.</th>
                                <th>Descrizione</th>
                                <th>Inizio</th>
                                <th>Fine</th>
                                <th>Prot.Num.</th>
                                <th>Valore (€)</th>
                                <th>Annotazioni</th>
                                <th style="width:90px;">Azioni</th>
                            </tr>
                        </thead>
                        <tbody> <?php if (!empty($fasi)): ?>     <?php foreach ($fasi as $f): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($f['Fasi_Cod']) ?></td>
                                        <td><?= htmlspecialchars($f['Descrizione'] ?? '') ?></td>
                                        <td><?= !empty($f['DataInizio']) ? (new DateTime($f['DataInizio']))->format('d/m/Y') : '' ?>
                                        </td>
                                        <td><?= !empty($f['DataFine']) ? (new DateTime($f['DataFine']))->format('d/m/Y') : '' ?>
                                        </td>
                                        <td><?= htmlspecialchars($f['Prot_num'] ?? '') ?></td>
                                        <td><?= number_format($f['Valore'], 2, ',', '.') ?></td>
                                        <td><?= htmlspecialchars($f['Annotazioni'] ?? '') ?></td>
                                        <td>
                                            <form action="elimina_fase.php" method="post" style="display:inline"
                                                onsubmit="return confirm('Eliminare questa fase?')"> <input type="hidden"
                                                    name="id" value="<?= (int) $f['id'] ?>"> <input type="hidden"
                                                    name="id_sinistro" value="<?= $idSinistro ?>"> <button type="submit"
                                                    class="btn btn-danger btn-sm">🗑️</button> </form>
                                        </td>
                                    </tr> <?php endforeach; ?> <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-3">Nessuna fase registrata</td>
                                </tr> <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <a href="elenco_sinistri.php" class="btn btn-secondary btn-sm mt-3">⬅️ Torna all'elenco sinistri</a>

            </div>
        </div>
    </div>
</body>

</html>