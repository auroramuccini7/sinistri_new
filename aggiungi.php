<?php
require 'api_strade.php';
require 'config_reparti.php';
require 'config_stato.php';
require 'config_tipo.php';
require 'config_comuni.php';

// Connessione database
$conn = new mysqli("localhost", "root", "", "sinistri_nuovi");
if ($conn->connect_error) die("Connessione fallita: " . $conn->connect_error);

$success_msg = '';
$error_msg = '';

$anno = date('Y'); 

$stmt = $conn->prepare("SELECT MAX(Numero) AS ultimo_numero FROM sinistri_nuovi WHERE anno = ?");
$stmt->bind_param("i", $anno);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$ultimoNumero = $row['ultimo_numero'] ?? 0; 
$numero = $ultimoNumero +1;
$allFasi = $conn->query("SELECT * FROM grid_tfas_csv");

// Controllo se è richiesta AJAX
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $stato = !empty($_POST['stato'])? Stato::getStatoCode($_POST['stato']) : "A" ;
    $reparto = Reparto::getRepartoCode($_POST['reparto']);
    $comune = Comuni::getComuniCode($_POST['comune']);
    $tipo = $_POST['tipo'];
    $numero = $_POST['numero']; // Assicurati che esista
    $data_aggiunta = date('Y-m-d');

    $stmt = $conn->prepare("INSERT INTO sinistri_nuovi
        (tipo, anno, numero, repart, gestione, stato, dataEvento, tipoDanno, causa, Controparte, LegaleControparte, prot_num, strada, numCiv, comune, annotazioni, AGG_DATA) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("siissssssssssssss", 
        $tipo, $_POST['anno'], $numero, $reparto, $_POST['gestione'],
        $stato, $_POST['data_evento'], $_POST['tipo_danno'], $_POST['causa'], $_POST['controparte'], $_POST['legaleControparte'],
        $_POST['prot_num'], $_POST['strada'], $_POST['num_civ'], $comune, $_POST['annotazioni'], $data_aggiunta);

    if ($stmt->execute()) {
        $sinistro_id = $stmt->insert_id;
        if ($isAjax) {
            echo json_encode(['success' => true, 'sinistro_id' => $sinistro_id]);
            exit;
        } else {
            $success_msg = "✅ Sinistro inserito con successo!";
        }
    } else {
        if ($isAjax) {
            echo json_encode(['success' => false, 'error' => $stmt->error]);
            exit;
        } else {
            $error_msg = "❌ Errore durante l'inserimento del sinistro: " . $stmt->error;
        }
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<title>Nuovo Sinistro</title>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<style>
    body { font-family: Arial, sans-serif; margin: 30px; background: #f7f9fc; color: #333; }
    h2, h3 { color: #1a73e8; }
    form { background: #fff; padding: 20px 30px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); max-width: 800px; margin: auto; }
    .form-group { display: flex; flex-wrap: wrap; margin-bottom: 15px; }
    .form-group label { flex: 1 1 150px; padding-top: 5px; font-weight: bold; }
    .form-group input, .form-group select, .form-group textarea { flex: 2 1 250px; min-width: 250px; padding: 8px; border: 1px solid #ccc; border-radius: 6px; transition: border 0.3s;    box-sizing: border-box;   }
    .form-group input:focus, .form-group textarea:focus { border-color: #1a73e8; outline: none; }
    textarea { resize: vertical; min-height: 60px; }
    table { border-collapse: collapse; width: 100%; margin-top: 15px; }
    table th, table td { border: 1px solid #ddd; padding: 10px; text-align: center; }
    table th { background: #1a73e8; color: #fff; }
    table tr:nth-child(even) { background: #f2f6fc; }
    button, input[type="submit"] { background: #1a73e8; color: white; border: none; padding: 10px 16px; margin-top: 10px; border-radius: 6px; cursor: pointer; font-weight: bold; transition: background 0.3s; }
    button:hover, input[type="submit"]:hover { background: #155ab6; }
    .success, .error { padding: 10px; margin-bottom: 15px; border-radius: 6px; max-width: 800px; margin: 20px auto; text-align: center; }
    .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    .select2-container .select2-selection--single {
    height: 38px; 
    padding: 4px 8px;
    border: 1px solid #ccc;
    border-radius: 6px;
}

.form-group .select2-container {
    flex: 2 1 250px;
    min-width: 250px;
    font-family: inherit;
    font-size: 14px;
}

/
.select2-container .select2-selection--single {
    height: 38px !important;      
    border: 1px solid #ccc !important;
    border-radius: 6px !important;
    padding: 0 8px !important;
    display: flex;
    box-sizing: border-box;        
}


.select2-container--default .select2-selection--single .select2-selection__rendered {
    padding-left: 0 !important;    
}


.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 100% !important;
    right: 6px;
}


</style>
<script>

$(document).ready(function() {
    $('#strada-select, #reparto-select').select2({ placeholder: "Seleziona o cerca", allowClear: true });

    $('#form-sinistro').submit(function(e){
        e.preventDefault();

        $.ajax({
            url: '', // stessa pagina
            method: 'POST',
            data: $(this).serialize(),
            success: function(response){
                try {
                    let data = JSON.parse(response);
                    if(data.success){
                        alert("✅ Sinistro salvato!");
                        $('#btn-add-fase').prop('disabled', false);
                        $('#msg-fasi').text("Ora puoi aggiungere le fasi");
                        $('#form-sinistro input, #form-sinistro select, #form-sinistro textarea').prop('disabled', true);
                    } else {
                        alert("❌ Errore: "+data.error);
                    }
                } catch(e) {
                    alert("Errore imprevisto nella risposta del server.");
                }
            },
            error: function(){
                alert("Errore nella comunicazione col server.");
            }
        });
    });
});

function aggiungiFase() {
    let table = document.getElementById("fasi");
    let row = table.insertRow();
    <?php
        $options = "";
        while ($fase = $allFasi->fetch_assoc()) {
            $options .= "<option value='{$fase['Cod']}'>{$fase['Cod']}</option>";
        }
    ?>
    row.innerHTML = `
        <td><select name="cod_fase[]"><?= $options ?></select></td>
        <td><input type="date" name="data_inizio[]"></td>
        <td><input type="date" name="data_fine[]"></td>
        <td><input type="text" name="esito[]"></td>
        <td><input type="number" step="0.01" name="valore[]"></td>
        <td><input type="text" name="annotazioni_fase[]"></td>
        <td>
            <button type="button" onclick="salvaFase(this)">💾 Salva</button>
            <button type="button" onclick="eliminaFase(this)">❌ Elimina</button>
        </td>`;
}

function eliminaFase(btn) { btn.closest("tr").remove(); }

function salvaFase(btn) {
    let row = btn.closest("tr");

let numero = <?= json_encode($numero) ?>; 
let data = {
    tipo: document.getElementById('tipo').value, 
    cod_fase: row.querySelector('select[name="cod_fase[]"]').value,
    data_inizio: row.querySelector('input[name="data_inizio[]"]').value,
    data_fine: row.querySelector('input[name="data_fine[]"]').value,
    esito: row.querySelector('input[name="esito[]"]').value,
    valore: row.querySelector('input[name="valore[]"]').value,
    annotazioni: row.querySelector('input[name="annotazioni_fase[]"]').value,
    numero: numero 
};


    $.ajax({
        url: 'salva_fase.php',
        method: 'POST',
        data: data,
        success: function(response) {
            try {
                let res = JSON.parse(response);
                if(res.success) {
                    row.querySelectorAll('input, select').forEach(i => i.disabled = true);
                    btn.style.display = 'none'; 
                    alert("✅ Fase salvata correttamente!");
                } else {
                    alert("❌ Errore: " + res.error);
                }
            } catch(e) {
                alert("Errore imprevisto nella risposta del server.");
            }
        },
        error: function() {
            alert("Errore nella comunicazione col server.");
        }
    });
}

</script>
</head>
<body>

<?php if($success_msg): ?>
<div class="success"><?= $success_msg ?></div>
<?php endif; ?>
<?php if($error_msg): ?>
<div class="error"><?= $error_msg ?></div>
<?php endif; ?>

<form method="post" id="form-sinistro">
    <h2>Inserisci Sinistro</h2>
    <div class="form-group">
        <label>Tipo:</label>
        <select name="tipo" id="tipo">
            <option value="A">Attivo</option>
            <option value="P">Passivo</option>
        </select>
    </div>
    <div class="form-group"><label>Anno:</label><input type="number" name="anno" value="<?= date('Y') ?>" required></div>
    <div class="form-group"><label>Numero:</label><input type="number" name="numero" required readonly value=<?= $ultimoNumero+1 ?>></div>
    <div class="form-group">
    <label>Reparto:</label>
    <select name="reparto" id="reparto-select">
        <option></option> 
        <?php 
        $reparti = Reparto::getAllReparti(); 
        foreach( $reparti  as $reparto){ 
            echo "<option value=\"$reparto\">$reparto</option>";
        }
        ?>
    </select>
</div>

    <div class="form-group"><label>Gestione:</label><select name="gestione" id="gestione">
            <option value="C">Comune</option>
            <option value="A">Anthea</option>
        </select></div></div>
    <div class="form-group"><label>Stato:</label> <select name="stato" id="stato">
            <option value="aperto">Aperto</option>
            <option value="chiuso">Chiuso</option>
        </select></div>
    <div class="form-group"><label>Data evento:</label><input type="date" name="data_evento" required></div>
      <div class="form-group"><label>Tipo danno:</label> <select name="tipo_danno" id="tipo_danno">
            <option value="C">Cose</option>
            <option value="P">Persone</option>
        </select></div>
    <div class="form-group"><label>Causa:</label><input type="text" name="causa"></div>
        <div class="form-group"><label>Controparte:</label><input type="text" name="controparte"></div>
        <div class="form-group"><label>Legale controparte:</label><input type="text" name="legaleControparte"></div>
<div class="form-group">
    <label>Strada:</label>
    <select name="strada" id="strada-select">
        <option></option> 
        <?php 
        $strade = getAllData(); 
        foreach($strade as $strada){ 
              echo '<option value="' . $strada['id'] . '">' . $strada['nome'] . '</option>';

        }
        ?>
    </select>
</div>



    <div class="form-group"><label>Num. civico:</label><input type="number" name="num_civ"></div>
    <div class="form-group"><label>Comune:</label><input type="text" name="comune"></div>
     <div class="form-group"><label>Tipo gestione:</label><input type="text" name="tipoGestione"></div>
    <div class="form-group"><label>Annotazioni:</label><textarea name="annotazioni"></textarea></div>
    <div style="display:flex; justify-content:flex-end;"> <input type="submit" value="💾 Salva Sinistro">   </div>
    <h3>Fasi</h3>
    <table id="fasi">
        <tr>
            <th>Cod. fase</th><th>Data inizio</th><th>Data fine</th>
            <th>Esito</th><th>Valore</th><th>Annotazioni</th>
        </tr>
    </table>
   <button type="button" id="btn-add-fase" onclick="aggiungiFase()" disabled>+ Aggiungi Fase</button>
<p id="msg-fasi" style="color:#888; font-size:14px;">
    Salva prima il sinistro per poter aggiungere le fasi.
</p>




<button type="button" onclick="window.location.href='index.php'">INDIETRO</button>

</form>
</body>
</html>
