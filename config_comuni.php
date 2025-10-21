<?php

class Comuni {

    public static function getComuniLabel($code) {
        $labels = [
            "RN" => "Rimini",
            "BE" => "Bellaria",

        ];
        return $labels[$code] ?? "-";
    }

    public static function getComuniCode($label) {
    $labels = [
        "RN" => "Rimini",
        "BE" => "Bellaria",
    ];


    $inverted = array_flip($labels);

    return $inverted[$label] ?? null;
}
}


?>