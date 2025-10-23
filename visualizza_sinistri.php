<?php
// Connessione al database
$conn = new mysqli("localhost", "root", "", "sinistri_nuovi");
require_once 'config_tipo.php';
require_once 'config_reparti.php';
require_once 'config_stato.php';
require_once 'config_gestione.php';
require_once 'estrai_fasi.php';
// Controllo errori di connessione
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

// --- Impostazioni paginazione ---
$perPagina = 20; 
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($pagina < 1) $pagina = 1;
$offset = ($pagina - 1) * $perPagina;

// Conteggio totale record
$sqlCount = "SELECT COUNT(*) as totale FROM sinistri_nuovi";
$resCount = $conn->query($sqlCount);
$totale = $resCount->fetch_assoc()['totale'];
$totPagine = ceil($totale / $perPagina);

$where = [];
if (!empty($_GET['tipo'])) {
    $tipo = $conn->real_escape_string($_GET['tipo']);
    $where[] = "Tipo LIKE '%$tipo%'";
}
if (!empty($_GET['causa'])) {
$causa = $conn->real_escape_string($_GET['causa']);;
    $where[] = "Causa LIKE '%$causa%'";
}
if (!empty($_GET['proprietario'])) {
$proprietario = $conn->real_escape_string($_GET['proprietario']);;
    $where[] = "proprietario LIKE '%$proprietario%'";
}


if (!empty($_GET['anno'])) {
    $anno = (int)$_GET['anno'];
    $where[] = "Anno = $anno";
}
if (!empty($_GET['reparto'])) {
    $reparto = Reparto::getRepartoCode($_GET['reparto']);
    $where[] = "Repart = '$reparto'";
}
if (!empty($_GET['gestione'])) {
    $gestione = Gestione::getGestioneCode($_GET['gestione']);
    $where[] = "Gestione = '$gestione'";
}
if (!empty($_GET['descrizione'])) {
   $descrizione = $conn->real_escape_string($_GET['descrizione']);;
    $where[] = "Descrizione LIKE '%$descrizione%'";
}

if (!empty($_GET['numero'])) {
    $numero = (int)$_GET['numero'];
    $where[] = "Numero = $numero";
}
if (!empty($_GET['controparte'])) {
    $controparte = $conn->real_escape_string($_GET['controparte']);;
    $where[] = "Controparte  LIKE '%$controparte%'";
}
if (!empty($_GET['legale_controparte'])) {
    $legaleControparte = $conn->real_escape_string($_GET['legale_controparte']);;
    $where[] = "legale_controparte  LIKE '%$legaleControparte%'";
}
if (!empty($_GET['stato'])) {
    $stato = Stato::getStatoCode ($conn->real_escape_string($_GET['stato']));
    $where[] = "Stato = '$stato'";
}
if (!empty($_GET['dataevento'])) {
    $dataevento = $conn->real_escape_string($_GET['dataevento']);
    $where[] = "DataEvento = '$dataevento'";
}

$whereSql = $where ? "WHERE " . implode(" AND ", $where) : "";
$sql = "SELECT * FROM sinistri_nuovi $whereSql ORDER BY Anno DESC, numero DESC LIMIT $perPagina OFFSET $offset";
$result = $conn->query($sql);


?>

<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<title>Elenco Sinistri</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Aggiungi queste righe nell'head -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<style>
    body { background: #f7f9fc; }
    .card { border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
    .collapse-row td { background: #fdfdfd; border-top: none; }
    .table-sm th { background: #e9f3ff; }
    .btn-toggle { transition: 0.3s; }
    .btn-toggle.collapsed::after { content: " ▼"; }
    .btn-toggle:not(.collapsed)::after { content: " ▲"; }
    .pagination-sm .page-link { padding: 0.25rem 0.6rem; font-size: 0.8rem; }
    .col-tipo {
    width: 80px;       
    max-width: 80px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis; 
}
 .col-tipo { width: 70px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .col-anno { width: 60px; }
    .col-numero { width: 60px; }
    .col-causa { width: 80px; }
    .col-controparte { width: 150px; }
    .col-proprietario { width: 90px; }
    .col-reparto { width: 80px; }
    .col-gestione { width: 80px; }
    .col-descrizione { width: 280px; }
    .col-stato { width: 100px; }
    .col-dataevento { width: 100px; }
    .col-azioni { width: 120px; }
    .col-LegaleControparte{
        width: 100px;
    }

    table {
        table-layout: fixed;
        width: 100%;
    }

    td, th {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
</style>
</head>
<body>

<div class="mt-5">
    <h2 class="text-center mb-4">📋 Elenco Sinistri</h2>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
               <table id="sinistriTable" class="table table-bordered align-middle" style="table-layout: fixed; width: 100%;">
        <thead class="table-primary">
        <tr>
    <form method="get">
        <th class="col-tipo">
            <select name="tipo" id="tipo" class="form-select form-select-sm">
                <option value="">--</option>
                <option value="A" <?= ($_GET['tipo'] ?? '') == "A" ? "selected" : "" ?>>Attivo</option>
                <option value="P" <?= ($_GET['tipo'] ?? '') == "P" ? "selected" : "" ?>>Passivo</option>
            </select>
        </th>
        <th class="col-anno">
            <input type="number" name="anno" value="<?= $_GET['anno'] ?? '' ?>" 
                   placeholder="Anno" class="form-control form-control-sm">
        </th>
        <th class="col-numero">
            <input type="number" name="numero" value="<?= $_GET['numero'] ?? '' ?>" 
                   placeholder="Num" class="form-control form-control-sm">
        </th>
        <th class="col-reparto">
            <input type="text" name="reparto" value="<?= $_GET['reparto'] ?? '' ?>" 
                   placeholder="Reparto" class="form-control form-control-sm">
        </th>
        <th class="col-gestione">
            <input type="text" name="gestione" value="<?= $_GET['gestione'] ?? '' ?>" 
                   placeholder="Gestione" class="form-control form-control-sm">
        </th>
             <th class="col-descrizione">
            <input type="text" name="descrizione" value="<?= $_GET['descrizione'] ?? '' ?>" 
                   placeholder="Descrizione" class="form-control form-control-sm">
        </th>
        <th class="col-causa">
            <input type="text" name="causa" value="<?= $_GET['causa'] ?? '' ?>" 
                   placeholder="Causa" class="form-control form-control-sm">
        </th>
        <th class="col-controparte">
            <input type="text" name="controparte" value="<?= $_GET['controparte'] ?? '' ?>" 
                   placeholder="Controparte" class="form-control form-control-sm">
        </th>
  <th class="col-proprietario">
            <input type="text" name="proprietario" value="<?= $_GET['proprietario'] ?? '' ?>" 
                   placeholder="Proprietario" class="form-control form-control-sm">
        </th>
        <th class="col-stato">
            <input type="text" name="stato" value="<?= $_GET['stato'] ?? '' ?>" 
                   placeholder="Stato" class="form-control form-control-sm">
        </th>
        <th class="col-dataevento">
            <input type="date" name="dataevento" value="<?= $_GET['dataevento'] ?? '' ?>" 
                   class="form-control form-control-sm">
        </th>
        <th class="col-azioni">
            <button class="btn btn-sm btn-primary w-100" type="submit">🔍</button>
        </th>
    </form>
</tr>

            <tr>
                <th class="col-tipo">Tipo</th>
                <th class="col-anno">Anno</th>
                <th class="col-numero">Numero</th>
                <th class="col-reparto">Reparto</th>
                <th class="col-gestione">Gestione</th>
                <th class="col-descrizione">Descrizione</th>
                <th class="col-causa">Causa</th>
                <th class="col-controparte">Controparte</th>
                <th class="col-proprietario">Proprietario</th>
                <th class="col-stato">Stato</th>
                <th class="col-dataevento">Data Evento</th>
                <th class="col-azioni">Azioni</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td class="col-tipo"><?= Tipo::getTipoLabel($row['tipo']) ?></td>
                <td class="col-anno"><?= $row['anno'] ?></td>
                <td class="col-numero"><?= $row['numero'] ?></td>
               <td class="col-reparto">
<?php 
 echo Reparto::getRepartLabel($row['repart']);

?>
</td>
<td class="col-gestione">
    <?php 
 echo Gestione::getGestioneLabel($row['gestione']);

?>
 <td class="col-descrizione" title="<?= htmlspecialchars($row['Descrizione']) ?>">
    <?= htmlspecialchars(mb_strimwidth($row['Descrizione'], 0, 50, '...')) ?>
</td>

                <td class="col-causa"><?= $row['causa'] ?></td>
                <td class="col-controparte"><?= $row['controparte'] ?></td>
                <td class="col-proprietario"><?= $row['Proprietario'] ?></td>
  <td class="col-stato">
    <?= htmlspecialchars($row['stato'])=="A" ? "Aperta" : "Chiusa" ?>
</td>


                <td class="col-dataevento">
                    <?= !empty($row['DataEvento']) ? (new DateTime($row['DataEvento']))->format('d/m/Y') : '' ?>
                </td>
                <td class="col-azioni">
    <!-- Bottone fasi -->
    <button class="btn btn-sm btn-info text-white btn-toggle collapsed" 
            data-bs-toggle="collapse" 
            data-bs-target="#fasi<?= htmlspecialchars($row['tipo'] . $row['anno'] . $row['numero']) ?>">
        Fasi
    </button>

    <!-- Modifica -->
    <a href="modifica_sinistro.php?id=<?= (int)$row['id'] ?>&anno=<?= (int)$row['anno'] ?>&numero=<?= (int)$row['numero'] ?>" 
       class="btn btn-sm btn-warning text-white">✏️</a>

    <!-- Elimina (via POST) -->
    <form action="elimina_sinistro.php" 
          method="post" 
          style="display:inline"
          onsubmit="return confirm('Sei sicuro di voler eliminare questo sinistro?')">
        <input type="hidden" name="tipo" value="<?= htmlspecialchars($row['tipo']) ?>">
        <input type="hidden" name="anno" value="<?= htmlspecialchars($row['anno']) ?>">
        <input type="hidden" name="numero" value="<?= htmlspecialchars($row['numero']) ?>">
        <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
        <button type="submit" class="btn btn-sm btn-danger">🗑️</button>
    </form>
</td>

            </tr>
                        <tr class="collapse collapse-row" id="fasi<?= $row['tipo'] . $row['anno'] . $row['numero'] ?>">
                            <td colspan="9">
                                <?php
                                $fasi = Fasi::getFasi($conn, $row['id'], $row['anno']);
                                if (!empty($fasi)): ?>
                                    <table class="table table-sm table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Cod.</th>
                                                <th>Descrizione</th>
                                                <th>Inizio</th>
                                                <th>Fine</th>
                                                <th>Prot. Num.</th>
                                                <th>Valore</th>
                                                <th>Annotazioni</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($fasi as $f):  ?>
                                            <tr>
                                                <td><?= $f['Fasi_Cod'] ?></td>
                                                <td><?= !empty($f['Descrizione']) ? $f['Descrizione'] : "" ?></td>
                                                <td><?php 
                                                $d = new DateTime($row['DataEvento']);
                                                echo $d->format('d/m/Y'); 
                                                ?></td>
                                                <td>
                                                <?= (new DateTime($f['DataFine']))->format('d/m/Y'); ?>
                                                </td>
                                                <td><?= htmlspecialchars($f['Prot_num']) ?></td>
                                                <td><?= $f['Valore'] ?></td>
                                                <td><?= !empty($f['Annotazioni']) ? $f['Annotazioni'] : "" ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php else: ?>
                                    <span class="text-muted">Nessuna fase registrata</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginazione -->
            <nav>
                <ul class="pagination pagination-sm justify-content-center mb-0">
                    <!-- Freccia indietro -->
                    <li class="page-item <?= ($pagina <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?pagina=<?= $pagina-1 ?>">⬅️</a>
                    </li>

                    <!-- Prima pagina -->
                    <li class="page-item <?= ($pagina == 1) ? 'active' : '' ?>">
                        <a class="page-link" href="?pagina=1">1</a>
                    </li>

                    <?php if ($pagina > 3): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; ?>

                    <!-- Pagine vicine alla corrente -->
                    <?php
                    $start = max(2, $pagina - 1);
                    $end = min($totPagine - 1, $pagina + 1);
                    for ($i = $start; $i <= $end; $i++): ?>
                        <li class="page-item <?= ($i == $pagina) ? 'active' : '' ?>">
                            <a class="page-link" href="?pagina=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($pagina < $totPagine - 2): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; ?>

                    <!-- Ultima pagina -->
                    <?php if ($totPagine > 1): ?>
                        <li class="page-item <?= ($pagina == $totPagine) ? 'active' : '' ?>">
                            <a class="page-link" href="?pagina=<?= $totPagine ?>"><?= $totPagine ?></a>
                        </li>
                    <?php endif; ?>

                    <!-- Freccia avanti -->
                    <li class="page-item <?= ($pagina >= $totPagine) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?pagina=<?= $pagina+1 ?>">➡️</a>
                    </li>
                </ul>
            </nav>

            <div class="d-flex justify-content-between mt-3">
                <a href="index.php" class="btn btn-secondary">⬅️ Indietro</a>
                <!-- <a href="ricerca_sinistri.php" class="btn btn-info text-white">🔍 Ricerca</a> -->
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>



</body>
</html>
