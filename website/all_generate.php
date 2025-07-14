<?php
include 'begin_php.php';
include 'header.html';

$supabaseUrl = $config['supabase_url'];
$supabaseKey = $config['supabase_key'];
$table = $config['table_name'];

$ch = curl_init();

// On sélectionne uniquement id_request avec les valeurs distinctes
$url = "$supabaseUrl/rest/v1/$table?select=id_request";

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // à désactiver en production si possible
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "apikey: $supabaseKey",
    "Authorization: Bearer $supabaseKey",
    "Content-Type: application/json",
    "Accept: application/json"
]);




$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo 'Erreur curl : ' . curl_error($ch);
    exit;
}

curl_close($ch);

// Décodage du JSON
$data = json_decode($response, true);

if (is_array($data)) {

    $uniqueIds = [];

    foreach ($data as $entry) {
        if (isset($entry['id_request'])) {
            $uniqueIds[] = $entry['id_request'];
        }
    }

    // Supprime les doublons
    $uniqueIds = array_unique($uniqueIds);

    // Affichage
    foreach ($uniqueIds as $id) {
        echo "id_request unique : " . htmlspecialchars($id) . "<br>
            <ul>
                <li><a href='change?id=$id&type=resume'>- Voir résumé associé</a></li>
                <li><a href='change?id=$id&type=quizz'>- Voir quiz associé</a></li>
            </ul>";
    }
} else {
    echo "Erreur : données reçues invalides ou réponse vide.";
}

include 'footer.html';
