<?php

require 'begin_php.php';
require 'header.html';
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
    //print_r($data);
    usort($data, function ($a, $b) {
        return $a['id'] <=> $b['id'];
    });

    if (!empty($data) && isset($data[0]['content'])) {
        echo "<h1> Quizz </h1>";
        $page = "";
        foreach ($data as $row) {
            if (isset($row['metadata'])) {
                $metadata = json_decode($row['metadata'], true);
                if (isset($metadata['line']) && isset($row['content']) && $metadata['line'] == 2) {
                    $page = $page . $row['content'] . "\n\n"; // Séparé par deux sauts de ligne
                }
            }    
        }

        if (preg_match('/```html(.*?)```/s',  $page, $matches)) {
            $contenu = trim($matches[1]); // On enlève les espaces inutiles
            echo $contenu;   
        }
        else{
            echo $page;
        }
       # echo "<button onclick=\"corriger()\">Valider mes réponses</button>";
        echo "<div id=\"score\"></div></div>";

    }
}


curl_close($ch);
