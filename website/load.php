<?php
include "begin_php.php";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $flask_url = $env["FLASK_URL"];
    // Préparer les données POST
    $postData = [
        'option' => $_POST['option'] ?? '',
        'mod' => $_POST['mod'] ?? '',
    ];

    // Gérer les fichiers uploadés
    if (isset($_FILES['files']) && is_array($_FILES['files']['tmp_name'])) {
        foreach ($_FILES['files']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['files']['error'][$key] === UPLOAD_ERR_OK) {
                $filePath = $tmp_name;
                $fileName = $_FILES['files']['name'][$key];
                // Use standard array notation for multiple files
                $postData['files[]'] = new CURLFile($filePath, mime_content_type($filePath), $fileName);
            }
        }
    }

    $ch = curl_init($flask_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Erreur cURL : ' . curl_error($ch);
        exit;
    }

    // Stocker la réponse dans une variable d'environnement
    $_SESSION["result"] = $response;
    curl_close($ch);

    $url = $config['N8N_URL_BASE'] . $config['N8N_URL_TEST'] . $config['N8N_URL_END'];
    $ch = curl_init($url);
    // Configuration de la requête POST
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    // Envoie les données comme JSON (contenu brut du tableau encodé)
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);

    // Corps de la requête : JSON encodé
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        "payload" => $_SESSION["result"] // ou tu peux renvoyer directement une structure propre ici
    ]));

    // Exécution de la requête
    $response = curl_exec($ch);

    // Gestion des erreurs
    if (curl_errno($ch)) {
        echo 'Erreur cURL : ' . curl_error($ch);
    } else {
        $data = json_decode($response, true);
        if (isset($data['code']) && $data['code'] == 404) {
            echo "Erreur 404 : La ressource demandée n'a pas été trouvée.";
            exit;
        } else {
            echo "Réponse de n8n : " . $response;
            $_SESSION["reponse"] = $response;
        }
    }
    // Fermeture
    curl_close($ch);

    // Afficher la réponse
    header("Location: quizz");
    exit;
}
