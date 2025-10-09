<?php

class Tipo {

    public static function getTipoLabel($code) {
        $labels = [
            "A" => "Attivo",
            "P" => "Passivo",
        ];
        return $labels[$code] ?? "-";
    }

    
}


?>