<?php
$inputFile = "C:\\Users\\aurora.muccini\\OneDrive - ANTHEA S.R.L\\Desktop\\sinistri.csv";
$outputFile = "C:\\Users\\aurora.muccini\\OneDrive - ANTHEA S.R.L\\Desktop\\sinistri.sql";
$tableName = "sinistri_nuovi";

// Apri file CSV in lettura
if (($handle = fopen($inputFile, "r")) !== FALSE) {
    $sqlFile = fopen($outputFile, "w");

    // Legge la prima riga con i nomi delle colonne e rimuove eventuale BOM
    $headers = fgetcsv($handle, 0, ';');
    $headers[0] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $headers[0]);
    $columns = implode(", ", $headers);

    while (($data = fgetcsv($handle, 0, ';')) !== FALSE) {
        $values = array();
        foreach ($data as $value) {
            $value = trim($value);
            
            // Rimuove eventuali ; residui all'interno dei valori
            $value = str_replace(';', '', $value);

            // Gestione NULL
            if (strtoupper($value) === "NULL" || $value === "") {
                $values[] = "NULL";
            }
            // Riconoscimento date gg/mm/yyyy hh:mm
            elseif (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})( (\d{2}):(\d{2})(:(\d{2}))?)?$/', $value, $matches)) {
                $hour = isset($matches[4]) ? $matches[5] : '00';
                $minute = isset($matches[4]) ? $matches[6] : '00';
                $second = isset($matches[8]) ? $matches[8] : '00';
                $date = $matches[3] . '-' . $matches[2] . '-' . $matches[1] . " $hour:$minute:$second";
                $values[] = "'$date'";
            }
            // Numeri
            elseif (is_numeric($value)) {
                $values[] = $value;
            }
            // Testo
            else {
                $escaped = str_replace("'", "''", $value);
                $values[] = "'$escaped'";
            }
        }

        // Genera riga INSERT terminata da ;
        $sqlLine = "INSERT INTO $tableName ($columns) VALUES (" . implode(", ", $values) . ");\n";
        fwrite($sqlFile, $sqlLine);
    }

    fclose($handle);
    fclose($sqlFile);
    echo "File SQL creato con successo: $outputFile\n";
} else {
    echo "Errore nell'apertura del CSV\n";
}
?>
