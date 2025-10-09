<?php

class Reparto {

     public static function getAllReparti() {
        $reparti = [
           "Verde ornamentale",
            "Segnaletica",
            "Manutenzione arredi",
           "Strade",
            "Sinistro non competenza"
        ];
        return $reparti ?? [];
    }

    public static function getRepartLabel($code) {
        $labels = [
            "VO" => "Verde ornamentale",
            "SE" => "Segnaletica",
            "MS" => "Manutenzione arredi",
            "ST" => "Strade",
            "SNCA" => "Sinistro non competenza"
        ];
        return $labels[$code] ?? "-";
    }

 public static function getRepartoCode($label) {
    $code = [
        "Verde ornamentale"      => "VO",
        "Segnaletica"            => "SE",
        "Manutenzione arredi"    => "MS",
        "Strade"                 => "ST",
        "Sinistro non competenza"=> "SNCA"
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