<?php
// Connessione database
$conn = new mysqli("localhost", "root", "", "sinistri");
if ($conn->connect_error) die("Connessione fallita: " . $conn->connect_error);

// Recupero colonne della tabella
$result = $conn->query("SHOW COLUMNS FROM sinistri");
$columns = [];
while ($row = $result->fetch_assoc()) {
    $columns[] = $row['Field'];
}

// Recupero sinistri
$sinistri = $conn->query("SELECT * FROM sinistri");
?>

<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<title>Visualizza Sinistri & Export Excel</title>
<style>
  body { font-family: Arial, sans-serif; margin: 20px; background: #f4f6f8; color: #333; }
  table { border-collapse: collapse; width: 100%; background: #fff; margin-bottom: 20px; }
  th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
  th { background: #4285f4; color: white; }
  form { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
  .btn { background: #4285f4; color: white; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer; margin-top: 10px; }
  .btn:hover { background: #3367d6; }
</style>
</head>
<body>

<h2>Lista Sinistri</h2>
<table>
  <tr><?php foreach ($columns as $col): ?>
    <th><?php echo htmlspecialchars($col); ?></th>
  <?php endforeach; ?></tr>
  <?php while ($r = $sinistri->fetch_assoc()): ?>
  <tr><?php foreach ($columns as $col): ?>
    <td><?php echo htmlspecialchars($r[$col]); ?></td>
  <?php endforeach; ?></tr>
  <?php endwhile; ?>
</table>

<h3>Esporta in Excel</h3>
<form method="post" action="export_excel.php">
  <label><strong>Seleziona campi da esportare:</strong></label><br>
  <?php foreach ($columns as $col): ?>
    <input type="checkbox" name="fields[]" value="<?php echo htmlspecialchars($col); ?>" checked>
    <?php echo htmlspecialchars($col); ?><br>
  <?php endforeach; ?>
  <button type="submit" class="btn">Esporta Excel</button>
</form>

</body>
</html>
