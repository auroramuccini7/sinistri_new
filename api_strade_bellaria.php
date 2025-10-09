<?php
function getAllDataBellaria() {
    $url = 'https://cartografia.globogis.it/arcgis/rest/services/BellariaIgeaMarina/Geostreets/MapServer/1/query';

    $params = [
        'where' => '1=1',
        'outFields' => '*',
        'returnGeometry' => 'false',
        'f' => 'json'
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
    if (isset($data['features'])) {
        foreach ($data['features'] as $vie) {
            $props = $vie['attributes'];
            $result[] = [
                "id"   => $props['cod_strada'] ?? null,       
                "nome" => $props['toponimo'] ?? null     
            ];
        }
    }

    return $result;
}


function getStradaFromIdBellaria($id){
    $strade = getAllDataBellaria();

    foreach ($strade as $strada) {
        if (isset($strada['id']) && $strada['id'] == intval($id)) {
            return $strada['nome']; // ritorno il nome
        }
    }
    return ""; // se non trova nulla
}
