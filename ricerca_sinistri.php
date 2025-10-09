<?php
require_once 'config_reparti.php';
require_once 'config_stato.php';
require_once 'config_gestione.php';
require_once 'config_tipo.php';
$conn = new mysqli("localhost", "root", "", "sinistri_nuovi");
if ($conn->connect_error) die("Connessione fallita: " . $conn->connect_error);

$where = [];
$params = [];
$types = "";

// Filtri GET
if (!empty($_GET)) {
    if (!empty($_GET['anno'])) { $where[] = "Anno = ?"; $params[] = $_GET['anno']; $types .= "i"; }
    if (!empty($_GET['numero'])) { $where[] = "Numero = ?"; $params[] = $_GET['numero']; $types .= "i"; }
    if (!empty($_GET['stato'])) { $where[] = "Stato LIKE ?"; $params[] = "%".$_GET['stato']."%"; $types .= "s"; }
    if (!empty($_GET['reparto'])) { $where[] = "Repart LIKE ?"; $params[] = "%".$_GET['reparto']."%"; $types .= "s"; }
    if (!empty($_GET['gestione'])) { $where[] = "Gestione LIKE ?"; $params[] = "%".$_GET['gestione']."%"; $types .= "s"; }
    if (!empty($_GET['data_evento'])) { $where[] = "DataEvento = ?"; $params[] = $_GET['data_evento']; $types .= "s"; }
}

// Paginazione
$perPage = 20;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $perPage;

// Conteggio totale
$count_sql = "SELECT COUNT(*) FROM sinistri_nuovi" . (!empty($where) ? " WHERE ".implode(" AND ", $where) : "");
$stmt_count = $conn->prepare($count_sql);
if (!empty($where)) $stmt_count->bind_param($types, ...$params);
$stmt_count->execute();
$stmt_count->bind_result($total);
$stmt_count->fetch();
$stmt_count->close();

$totalPages = ceil($total / $perPage);

// Query principale con LIMIT
$sql = "SELECT * FROM sinistri_nuovi" . (!empty($where) ? " WHERE ".implode(" AND ", $where) : "") . " ORDER BY ID DESC LIMIT ?, ?";
$stmt = $conn->prepare($sql);
// Prepara i parametri per LIMIT

$params_with_limit = array_merge($params, [$offset, $perPage]);
$types_with_limit = $types . "ii";

// bind_param richiede riferimenti, quindi dobbiamo usare questa funzione helper
function refValues($arr){
    $refs = [];
    foreach($arr as $key => $value) {
        $refs[$key] = &$arr[$key];
    }
    return $refs;
}

$stmt = $conn->prepare($sql);
call_user_func_array([$stmt, 'bind_param'], refValues(array_merge([$types_with_limit], $params_with_limit)));
$stmt->execute();
$result = $stmt->get_result();


?>

<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<title>Ricerca Sinistri</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: #f7f9fc; font-family: Arial, sans-serif; }
h2 { color: #1a73e8; }
.card { border-radius: 12px; }
.table th, .table td { vertical-align: middle; }

/* Paginazione piccola */
.pagination-sm .page-link { padding: 0.25rem 0.6rem; font-size: 0.8rem; }

/* Stile uniforme per input e select */
input[type="text"], input[type="number"], input[type="date"], select {
    flex: 1 1 100%;
    padding: 0.5rem 0.75rem;
    border: 1px solid #ced4da;
    border-radius: 0.5rem;
    box-sizing: border-box;
    font-size: 0.9rem;
    transition: border-color 0.3s, box-shadow 0.3s;
}
   
input[type="text"]:focus, input[type="number"]:focus, input[type="date"]:focus, select:focus {
    border-color: #1a73e8;
    box-shadow: 0 0 0 0.2rem rgba(26,115,232,0.25);
    outline: none;
}

/* Margine uniforme tra i campi del form */
form.row.g-3 > div {
    display: flex;
    flex-direction: column;
    margin-bottom: 0.75rem;
}

/* Stile uniforme per input e select */
input[type="text"], input[type="number"], input[type="date"], select {
    width: 100%;
    padding: 0.55rem 0.75rem;
    border: 1px solid #ced4da;
    border-radius: 0.5rem;
    font-size: 0.9rem;
    transition: border-color 0.3s, box-shadow 0.3s;
    background-color: #fff;
    box-sizing: border-box;
}

/* Effetto focus più evidente */
input[type="text"]:focus, 
input[type="number"]:focus, 
input[type="date"]:focus, 
select:focus {
    border-color: #1a73e8;
    box-shadow: 0 0 0 0.2rem rgba(26,115,232,0.25);
    outline: none;
}

/* Margine uniforme tra i campi del form */
form.row.g-3 > div {
    display: flex;
    flex-direction: column;
    margin-bottom: 1rem;
}

/* Miglior allineamento label-input */
label.form-label {
    margin-bottom: 0.25rem;
    font-weight: 500;
    font-size: 0.9rem;
}

/* Rende i select uniformi al resto dei campi */
select.form-control {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    background-position: right 0.75rem center;
    background-repeat: no-repeat;
    background-size: 1rem auto;
}

/* Pulsanti form */
button.btn {
    padding: 0.45rem 1rem;
    font-size: 0.9rem;
    border-radius: 0.5rem;
}

</style>

</head>
<body>
<div class="container mt-5">

<div class="d-flex justify-content-end mb-3">
    <button type="button" onclick="window.location.href='index.php'" class="btn btn-primary">INDIETRO</button>
</div>

<!-- Form di ricerca -->
<div class="card shadow-sm mb-4">
    <h23 class="mb-4 text-center">🔍 Ricerca Sinistri</h2>
    <div class="card-body">
        <form method="get" class="row g-3">
            <div class="col-md-2"><label class="form-label">Anno</label><input type="number" name="anno" class="form-control" value="<?= $_GET['anno'] ?? '' ?>"></div>
            <div class="col-md-2"><label class="form-label">Numero</label><input type="number" name="numero" class="form-control" value="<?= $_GET['numero'] ?? '' ?>"></div>
            <div class="col-md-2"><label class="form-label">Stato</label><input type="text" name="stato" class="form-control" value="<?= $_GET['stato'] ?? '' ?>"></div>
            <div class="col-md-2"><label class="form-label">Reparto</label><select name="reparto" id="reparto-select">
        <option></option> 
        <?php 
        $reparti = Reparto::getAllReparti(); 
        foreach( $reparti  as $reparto){ 
            echo "<option value=\"$reparto\">$reparto</option>";
        }
        ?>
    </select></div>
            <div class="col-md-2"><label class="form-label">Gestione</label><input type="text" name="gestione" class="form-control" value="<?= $_GET['gestione'] ?? '' ?>"></div>
            <div class="col-md-2"><label class="form-label">Data evento</label><input type="date" name="data_evento" class="form-control" value="<?= $_GET['data_evento'] ?? '' ?>"></div>

        <div class="col-12 text-center mt-3">
    <div class="d-inline-flex justify-content-end gap-2">
        <button type="submit" class="btn btn-primary">Cerca</button>
        <a href="ricerca_sinistri.php" class="btn btn-secondary">Reset</a>
    </div>
</div>

        </form>
    </div>
</div>

<!-- Tabella risultati -->
<?php if ($_SERVER["REQUEST_METHOD"] == "GET"): ?>
<div class="card shadow-sm">
    <div class="card-body">
        <h5 class="card-title">Risultati ricerca</h5>
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead class="table-primary">
                    <tr>
                        <th>Tipo</th><th>Anno</th><th>Numero</th><th>Reparto</th><th>Gestione</th><th>Stato</th><th>Data Evento</th><th>Azioni</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <!-- <td><?= $row['ID'] ?></td> -->
                            <td><?= Tipo::getTipoLabel($row['tipo']) ?></td>
                            <td><?= $row['anno'] ?></td>
                            <td><?= $row['numero'] ?></td>
                            <td><?= Reparto::getRepartLabel($row['repart']); ?></td>
                            <td><?= Gestione::getGestioneLabel($row['gestione']) ?></td>
                            <td><?= Stato::getStatoLabel($row['stato']) ?></td>
                            <td><?= $row['DataEvento'] = (new DateTime($row['DataEvento']))->format('d/m/Y');?></td>
                            <td><a href="dettaglio_sinistro.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary">🔍 Dettagli</a></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="9" class="text-center text-muted">Nessun sinistro trovato</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Paginazione -->
        <?php if($totalPages > 1): ?>
        <nav>
            <ul class="pagination pagination-sm justify-content-center">
                <?php if($page > 1): ?>
                    <li class="page-item"><a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page'=>$page-1])) ?>">«</a></li>
                <?php endif; ?>

                <?php
                $start = max(1, $page-2);
                $end = min($totalPages, $page+2);
                if($start>1) echo '<li class="page-item"><span class="page-link">1</span></li><li class="page-item disabled"><span class="page-link">…</span></li>';
                for($i=$start;$i<=$end;$i++):
                ?>
                    <li class="page-item <?= $i==$page?'active':'' ?>"><a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page'=>$i])) ?>"><?= $i ?></a></li>
                <?php endfor;
                if($end<$totalPages) echo '<li class="page-item disabled"><span class="page-link">…</span></li><li class="page-item"><a class="page-link" href="?'.http_build_query(array_merge($_GET, ['page'=>$totalPages])).'">'.$totalPages.'</a></li>';
                ?>

                <?php if($page < $totalPages): ?>
                    <li class="page-item"><a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page'=>$page+1])) ?>">»</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
