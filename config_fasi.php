<?php

class Fasi {

     public static function getAllFasi() {
        $reparti = [
           "Verde ornamentale",
            "Segnaletica",
            "Manutenzione arredi",
           "Strade",
            "Sinistro non competenza"
        ];
        return $reparti ?? [];
    }

}


?>