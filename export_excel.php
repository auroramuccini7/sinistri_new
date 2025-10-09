<?php
if (empty($_POST['campi'])) {
    die("Nessun campo selezionato.");
}

$campiSelezionati = $_POST['campi'];

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="sinistri.csv"');

$output = fopen('php://output', 'w');

$conn = new mysqli("localhost", "root", "", "sinistri_nuovi");
if ($conn->connect_error) die("Connessione fallita: " . $conn->connect_error);

// Intestazioni
$headers = [];
foreach ($campiSelezionati as $c) {
    $headers[] = $c; // potresti mappare in etichette più leggibili
}
fputcsv($output, $headers, ';');

// Query sinistri
$sinistri = $conn->query("SELECT * FROM sinistri_nuovi ORDER BY Anno DESC, Numero ASC");

while ($s = $sinistri->fetch_assoc()) {
    $fasi = $conn->query("SELECT * FROM fasi_nuove WHERE sinistri_fasi_id = ".$s['id']." ORDER BY DataInizio");

    if ($fasi->num_rows > 0) {
        while ($f = $fasi->fetch_assoc()) {
            $row = [];
            foreach ($campiSelezionati as $campo) {
                $found = false;

                // Cerca in $s
                foreach ($s as $key => $value) {
                    if (strcasecmp($key, $campo) === 0) {
                        $row[] = $value;
                        $found = true;
                        break;
                    }
                }

                // Se non trovato in $s, cerca in $f
                if (!$found) {
                    foreach ($f as $key => $value) {
                        if (strcasecmp($key, $campo) === 0) {
                            $row[] = $value;
                            $found = true;
                            break;
                        }
                    }
                }

                // Se non trovato in nessuno, metti stringa vuota
                if (!$found) {
                    $row[] = '';
                }
            }
            fputcsv($output, $row, ';');
        }
    } else {
        $row = [];
        foreach ($campiSelezionati as $campo) {
            $found = false;

            // Cerca in $s
            foreach ($s as $key => $value) {
                if (strcasecmp($key, $campo) === 0) {
                    $row[] = $value;
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $row[] = '';
            }
        }
        fputcsv($output, $row, ';');
    }
}

fclose($output);
exit;
