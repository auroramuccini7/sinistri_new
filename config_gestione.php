<?php

class Gestione {

  public static function getGestioneLabel($code) {
        $labels = [
            "A" => "Anthea",
             "C" => "Comune",
        ];
        return $labels[$code] ?? "-";
    }

    public static function getGestioneCode($label) {
    $labels = [
        "A" => "Anthea",
        "C" => "Comune",
    ];
    $codes = array_flip($labels);
    return $codes[$label] ?? "-";
}

}


?>