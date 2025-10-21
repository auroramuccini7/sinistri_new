<?php

class Fasi {

    public static function getFasi($conn, $id, $anno) {
        if (!empty($id) && !empty($anno)) {
            // Prepara la query per ottenere il sinistro
            $stmt = $conn->prepare("SELECT * FROM sinistri_nuovi WHERE id = ? AND anno = ?");
            if ($stmt === false) {
                die("Errore nella preparazione della query: " . $conn->error);
            }

            $stmt->bind_param("ii", $id, $anno);
            $stmt->execute();
            $result = $stmt->get_result();
            $sinistro = $result->fetch_assoc();
            $stmt->close();

            if ($sinistro) {
                // Prepara la query per ottenere le fasi
                $query = "
                    SELECT fasi_nuove.*, grid_tfas_csv.Descrizione AS DescrizioneFase
                    FROM fasi_nuove
                    JOIN grid_tfas_csv ON fasi_nuove.Fasi_Cod = grid_tfas_csv.Cod
                    WHERE fasi_nuove.sinistri_id = ?
                    OR (fasi_nuove.sinistri_anno = ? AND fasi_nuove.sinistri_numero = ?)
                ";

                $stmtFasi = $conn->prepare($query);
                if ($stmtFasi === false) {
                    die("Errore nella preparazione della query fasi: " . $conn->error);
                }

                $stmtFasi->bind_param("iii", $sinistro['id'], $anno, $sinistro['numero']);
                $stmtFasi->execute();
                $fasiResult = $stmtFasi->get_result();
                $fasi = $fasiResult->fetch_all(MYSQLI_ASSOC);
                $stmtFasi->close();

                return $fasi;
            } else {
                return null; // Sinistro non trovato
            }
        }

        return null; // ID o anno non valido
    }

}

?>