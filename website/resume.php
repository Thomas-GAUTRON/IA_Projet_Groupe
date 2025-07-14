<?php
require 'begin_php.php';
require 'header.html';

if (!isset($_SESSION['config'])) {
    $json = file_get_contents('conf.config');
    $_SESSION['config'] = json_decode($json, true);
    $config = $_SESSION['config'];
} else {
    $config = $_SESSION['config'];
}

// Configuration
$supabaseUrl = $config['supabase_url'];
$supabaseKey = $config['supabase_key']; // généralement la clé anonyme (public)
$table = $config['table_name'];
$idRequest = json_decode($_SESSION['response']);    

$ch = curl_init();
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_URL, "$supabaseUrl/rest/v1/$table?id_request=eq.$idRequest");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "apikey: $supabaseKey",
    "Authorization: Bearer $supabaseKey",
    "Content-Type: application/json",
    "Accept: application/json"
]);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo 'Erreur cURL : ' . curl_error($ch);
} else {
    $data = json_decode($response, true);
    usort($data, function ($a, $b) {
        return $a['id'] <=> $b['id'];
    });

    if (!empty($data) && isset($data[0]['content'])) {
        foreach ($data as $row) {
            if (isset($row['metadata'])) {
                $metadata = json_decode($row['metadata'], true);
                if (isset($metadata['line']) && isset($row['content']) && $metadata['line'] == 1) {
                    echo $row['content'] . "\n\n"; // Séparé par deux sauts de ligne
                }
            }    
        }
    }
}

curl_close($ch);
