<?php
require 'api_strade.php';
require_once 'config_gestione.php';
require_once 'config_reparti.php';
require_once 'config_stato.php';
require_once 'config_tipo.php';
require_once 'config_danno.php';
// Connessione al DB
$conn = new mysqli("localhost", "root", "", "sinistri_nuovi");
if ($conn->connect_error) die("Connessione fallita: " . $conn->connect_error);

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$anno = isset($_GET['anno']) ? intval($_GET['anno']) : 0;
if ($id <= 0) die("ID sinistro non valido.");

// Aggiornamento dati sinistro
if ($_SERVER["REQUEST_METHOD"] == "POST") {

     if (!empty($_POST['delete_fase_id'])) {
        $fase_id = (int)$_POST['delete_fase_id'];
        $stmt = $conn->prepare("DELETE FROM fasi_nuove WHERE id=?");
        $stmt->bind_param("i", $fase_id);
        $stmt->execute();
        $stmt->close();

        header("Location: modifica_sinistro.php?id=$id&anno=$anno&deleted=1");
        exit;
    }
    // Aggiorno i campi del sinistro
    $stmt = $conn->prepare("UPDATE sinistri_nuovi SET 
        tipo=?, anno=?, numero=?, repart=?, gestione=?, stato=?, dataEvento=?, tipoDanno=?, causa=?, strada=?, numCiv=?, annotazioni=? 
        WHERE id=?");
    $stmt->bind_param("siisssssssssi",
        $_POST['tipo'], $_POST['anno'], $_POST['numero'], $_POST['reparto'], $_POST['gestione'],
        $_POST['stato'], $_POST['data_evento'], $_POST['tipo_danno'], $_POST['causa'],
        $_POST['strada'], $_POST['num_civ'], $_POST['annotazioni'], $id);
    $stmt->execute();

    // Inserisco eventuali nuove fasi
   if (!empty($_POST['cod_fase'])) {
    foreach ($_POST['cod_fase'] as $i => $cod) {
        if (!empty($cod)) {
            $sql = "INSERT INTO fasi_nuove
                (sinistri_tipo, sinistri_numero, sinistri_anno, fasi_cod, dataInizio, dataFine, esito, valore, annotazioni) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"; // 9 colonne -> 9 placeholder

            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                die("Prepare failed: " . $conn->error);
            }

            // assegno le variabili per chiarezza
            $tipo        = Tipo::getTipoLabel($_POST['tipo']);
            $numero      = $_POST['numero'];
            $anno        = $_POST['anno'];
            $fasi_cod    = $cod;
            $dataInizio  = $_POST['data_inizio'][$i];
            $dataFine    = $_POST['data_fine'][$i];
            $esito       = $_POST['esito'][$i];
            $valore      = $_POST['valore'][$i];
            $annotazioni = $_POST['annotazioni_fase'][$i];

          
            $stmt->bind_param(
                "sisssssis",
                $tipo, $numero, $anno, $fasi_cod,
                $dataInizio, $dataFine, $esito,
                $valore, $annotazioni
            );

            $stmt->execute();
            $stmt->close();
        }
    }
}


    echo "<div class='success'>✅ Sinistro aggiornato con successo!</div>";
      header("Location: modifica_sinistro.php?id=$id&anno=$anno&success=1");
    exit;
}

// Carico i dati attuali del sinistro
$stmt = $conn->prepare("SELECT * FROM sinistri_nuovi WHERE id = ? AND anno = ?");
$stmt->bind_param("ii", $id, $anno);
$stmt->execute();
$sinistro = $stmt->get_result()->fetch_assoc();

$fasi = $conn->query(
    "SELECT fasi_nuove.*, grid_tfas_csv.Descrizione AS DescrizioneFase
     FROM fasi_nuove
     JOIN grid_tfas_csv ON fasi_nuove.Fasi_Cod = grid_tfas_csv.Cod
     WHERE fasi_nuove.Sinistri_Anno = " . (int)$sinistro['anno'] . " 
     AND fasi_nuove.Sinistri_Numero = " . (int)$sinistro['numero']
);


$allFasi = $conn->query("SELECT * FROM grid_tfas_csv");

?>

<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<title>Modifica Sinistro</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<style>
    body { font-family: Arial; margin:30px; background:#f7f9fc; }
    h2,h3 { color:#1a73e8; }
    form { background:#fff; padding:20px 30px; border-radius:12px; max-width:800px; margin:auto;}
    .form-group { display:flex; flex-wrap:wrap; margin-bottom:15px; }
    .form-group label { flex:1 1 150px; padding-top:5px; font-weight:bold; }
    .form-group input, .form-group select, .form-group textarea {
        flex:2 1 250px; min-width:250px; padding:8px;
        border:1px solid #ccc; border-radius:6px;
    }
    table { border-collapse:collapse; width:100%; margin-top:15px; }
    table th, table td { border:1px solid #ddd; padding:10px; text-align:center; }
    table th { background:#1a73e8; color:#fff; }
    .success { background:#d4edda; color:#155724; padding:10px; margin:15px auto; border:1px solid #c3e6cb; border-radius:6px; text-align:center; max-width:800px; }
    button, input[type="submit"] { background:#1a73e8; color:white; border:none; padding:10px 16px; margin-top:10px; border-radius:6px; cursor:pointer; font-weight:bold; }
    button:hover, input[type="submit"]:hover { background:#155ab6; }

.form-group input,
.form-group select,
.form-group textarea {
    flex: 2 1 250px;
    min-width: 250px;
    padding: 10px 12px;
    border: 1px solid #ccc;
    border-radius: 6px;
    background: #fff;
    font-size: 14px;
    font-family: inherit;
    transition: border-color 0.2s, box-shadow 0.2s;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    height: 40px; /* 👈 forza la stessa altezza */
    box-sizing: border-box;
}

.form-group textarea {
    height: auto; /* lascia la textarea espandibile */
    min-height: 80px;
    resize: vertical;
}


</style>
<script>

function salvaFase(btn) {
    const row = btn.closest("tr");
    const data = {
        sinistri_fasi_id:<?= (int)$sinistro['id'] ?>,
        numero: <?= (int)$sinistro['numero'] ?>, // numero del sinistro
        anno: <?= (int)$sinistro['anno'] ?>,     // anno del sinistro
        tipo: <?=($sinistro['tipo']) ?>, // tipo del sinistro
        cod_fase: row.querySelector('[name="cod_fase[]"]').value,
        data_inizio: row.querySelector('[name="data_inizio[]"]').value,
        data_fine: row.querySelector('[name="data_fine[]"]').value,
        esito: row.querySelector('[name="esito[]"]').value,
        valore: row.querySelector('[name="valore[]"]').value,
        annotazioni: row.querySelector('[name="annotazioni_fase[]"]').value
    };

    $.post('salva_fase.php', data, function(res) {
        if (res.success) {
            alert("✅ Fase salvata!");
            row.remove();
            location.reload();
        } else {
            alert("❌ Errore: " + res.error);
        }
    }, 'json');
}

function aggiungiFase() {
    let table = document.getElementById("nuove_fasi");
    let row = table.insertRow();
<?php
$options = "";
while ($fase = $allFasi->fetch_assoc()) {
    $options .= "<option value='{$fase['Cod']}'>{$fase['Cod']}</option>";
}
?>

    row.innerHTML = `
        <td>
            <select name="cod_fase[]">
                <?= $options ?>
            </select>
        </td>
        <td><input type="text" name="des_fase[]" disabled></td>
        <td><input type="date" name="data_inizio[]"></td>
        <td><input type="date" name="data_fine[]"></td>
        <td><input type="text" name="esito[]"></td>
        <td><input type="number" step="0.01" name="valore[]"></td>
        <td><input type="text" name="annotazioni_fase[]"></td>
        <td><button type="button" onclick="rimuoviFase(this)">❌</button></td>
        <td> <button type="button" onclick="salvaFase(this)">💾</button></td>
    `;
}


function rimuoviFase(btn) {
    let row = btn.closest("tr");
    row.remove();
}


</script>
</head>
<body>

<form method="post">
    <h2>Modifica Sinistro #<?php echo  $sinistro['numero']; ?> del <?php echo  $sinistro['anno'];  ?></h2>

    <div class="form-group"><label>Tipo:</label>
        <select name="tipo">
            <option value="A" <?php if($sinistro['tipo']=="A") echo "selected"; ?>>Attivo</option>
            <option value="P" <?php if($sinistro['tipo']=="P") echo "selected"; ?>>Passivo</option>
        </select>
    </div>

    <div class="form-group"><label>Anno:</label>
        <input type="number" name="anno" value="<?php echo $sinistro['anno']; ?>" required>
    </div>
    <div class="form-group"><label>Numero:</label>
        <input type="number" name="numero" value="<?php echo $sinistro['numero']; ?>" required>
    </div>
    <div class="form-group"><label>Reparto:</label>
        <input type="text" name="reparto" value="<?php echo Reparto::getRepartLabel($sinistro['repart']); ?>">
    </div>
    <div class="form-group"><label>Gestione:</label>
        <input type="text" name="gestione" value="<?php echo Gestione::getGestioneLabel($sinistro['gestione']); ?>">
    </div>
    <div class="form-group"><label>Stato:</label>
        <input type="text" name="stato" value="<?php echo Stato::getStatoLabel($sinistro['stato']); ?>">
    </div>
    <div class="form-group"><label>Data evento:</label>
  <input type="date" name="dataEvento" 
         value="<?php echo substr($sinistro['DataEvento'], 0, 10); ?>">
    </div>
    <div class="form-group"><label>Tipo danno:</label>
        <input type="text" name="tipo_danno" value="<?php echo Danno::getDannoLabel($sinistro['TipoDanno']); ?>">
    </div>
    <div class="form-group"><label>Causa:</label>
        <input type="text" name="causa" value="<?php echo $sinistro['causa']; ?>">
    </div>
    <div class="form-group"><label>Strada:</label>
        <input type="text" name="strada" value="<?php echo getStradaFromId($sinistro['strada']); ?>">
    </div>
    <div class="form-group"><label>Num. civico:</label>
        <input type="text" name="num_civ" value="<?php echo $sinistro['NumCiv']; ?>">
    </div>
    <div class="form-group"><label>Annotazioni:</label>
        <textarea name="annotazioni"><?php echo $sinistro['annotazioni']; ?></textarea>
    </div>
        <div style="display:flex; justify-content:flex-end;"> <input type="submit" value="💾 Salva Sinistro">   </div>
   
    <h3>Fasi già registrate</h3>
<table>
    <tr>
        <th>Cod.</th><th>Descrizione</th><th>Data inizio</th><th>Data fine</th>
        <th>Esito</th><th>Valore</th><th>Annotazioni</th><th>Azioni</th>
    </tr>
    <?php while($f = $fasi->fetch_assoc()): ?>
    <tr>
        <td><?= $f['Fasi_Cod'] ?></td>
        <td><?= $f['DescrizioneFase'] ?></td>
        <td><?= $f['DataInizio'] ?></td>
        <td><?= $f['DataFine'] ?></td>
        <td><?= $f['Esito'] ?></td>
        <td><?= $f['Valore'] ?></td>
        <td><?= $f['Annotazioni'] ?></td>
        <td>
       <form method="post"style="display:inline">
    <input type="hidden" name="delete_fase_id" value="<?= htmlspecialchars($f['id']) ?>">
    <button type="submit">❌</button>
    
</form>

        </td>
    </tr>
    <?php endwhile; ?>
</table>


    <h3>Aggiungi nuove fasi</h3>
    <table id="nuove_fasi">
        <tr>
            <th>Cod. fase</th><th>Descrizione</th><th>Data inizio</th><th>Data fine</th>
            <th>Esito</th><th>Valore</th><th>Annotazioni</th>
        </tr>
    </table>
    <button type="button" onclick="aggiungiFase()">+ Aggiungi Fase</button>

    <div style="display:flex; justify-content:space-between; margin-top:20px;">
        <button type="button" onclick="window.location.href='visualizza_sinistri.php'">⬅️ Indietro</button>
    </div>
</form>

</body>
</html>
