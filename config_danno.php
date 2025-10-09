<?php

class Danno {

    public static function getDannoLabel($code) {
        $labels = [
            "C" => "Cose",
            "P" => "Persone"
           
        ];
        return $labels[$code] ?? "-";
    }
}


?>