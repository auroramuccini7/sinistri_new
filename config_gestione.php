<?php

class Gestione {

  public static function getGestioneLabel($code) {
        $labels = [
            "A" => "Anthea",
             "C" => "Comune",
        ];
        return $labels[$code] ?? "-";
    }

}


?>