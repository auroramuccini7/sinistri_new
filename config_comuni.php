<?php

class Comuni {

    public static function getComuniLabel($code) {
        $labels = [
            "RN" => "Rimini",
            "BE" => "Bellaria",

        ];
        return $labels[$code] ?? "-";
    }
}


?>