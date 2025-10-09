<?php
function getAllData() {
    $url = 'https://sit.comune.rimini.it/arcgis/rest/services/OpenData/OD_SIT/MapServer/1/query';

    $params = [
        'f' => 'geojson',
        'outFields' => '*',
        'where' => '1=1'
    ];

    $requestUrl = $url . '?' . http_build_query($params);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $requestUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $response = curl_exec($ch);
    if ($response === false) {
        curl_close($ch);
        return null;
    }
    curl_close($ch);

    $data = json_decode($response, true);
    if ($data === null) {
        return null;
    }

    $result = [];
    foreach ($data['features'] as $vie) {
        $result[] = [
            "id"   => $vie['properties']['DBO.VIE.XVIA'],
            "nome" => $vie['properties']['DBO.Origine_toponomi.NOME_UFFICIALE']
        ];
    }

    return $result; // <-- array di record con id e nome
}

function getStradaFromId($id){
    $strade = getAllData();

    foreach ($strade as $strada) {
        if (isset($strada['id']) && $strada['id'] == intval($id)) {
            return $strada['nome']; // ritorno il nome
        }
    }
    return ""; // se non trova nulla
}
