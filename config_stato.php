<?php

class Stato {

    public static function getStatoLabel($code) {
        $labels = [
            "A" => "Aperto",
            "C" => "Chiuso",
        ];
        return $labels[$code] ?? "-";
    }

    
    public static function getStatoCode($label) {
        $code = [
          "aperto" => "A"  ,
        "chiuso"  =>"C" ,
        ];
        
    $label = strtolower($label);

    foreach ($code as $key => $value) {
        if (str_contains(strtolower($key), $label)) {
            return $value;
        }
    }

    return "-";
    }
}


?>