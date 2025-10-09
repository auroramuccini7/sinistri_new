<?php
$inputFile = "C:\\Users\\aurora.muccini\\OneDrive - ANTHEA S.R.L\\Desktop\\sinistri.csv";
$outputFile = "C:\\Users\\aurora.muccini\\OneDrive - ANTHEA S.R.L\\Desktop\\sinistri_update.sql";
$tableName = "sinistri_nuovi";

// Apri file CSV in lettura
if (($handle = fopen($inputFile, "r")) !== FALSE) {
    $sqlFile = fopen($outputFile, "w");

    // Salta la prima riga con le intestazioni
    fgetcsv($handle, 0, "\t");

    // Indici colonne
    $numeroIndex = 2;  // colonna 2
    $annoIndex = 1;    // colonna 3
    $stradaIndex = 15; // colonna P

    while (($data = fgetcsv($handle, 0, ";")) !== FALSE) {
        $strada = trim($data[$stradaIndex]);
        $numero = trim($data[$numeroIndex]);
        $anno = trim($data[$annoIndex]);

        // Escape per testo
        $stradaEscaped = str_replace("'", "''", $strada);

        // Gestione NULL
        $stradaSQL = (strtoupper($strada) === "NULL" || $strada === "") ? "NULL" : "'$stradaEscaped'";

        // Genera riga UPDATE usando numero e anno come chiave
        $sqlLine = "UPDATE $tableName SET strada = $stradaSQL WHERE numero = '$numero' AND anno = '$anno';\n";
        fwrite($sqlFile, $sqlLine);
    }

    fclose($handle);
    fclose($sqlFile);
    echo "File SQL di UPDATE creato con successo: $outputFile\n";
} else {
    echo "Errore nell'apertura del CSV\n";
}
?>
