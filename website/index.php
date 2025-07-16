<?php
include "begin_php.php";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $flask_url = "http://127.0.0.1:5000/";
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
    curl_close($ch);

    // Stocker la réponse dans une variable d'environnement
    $_SESSION["response"] = $response;

    $url = $config['n8n_webhook_url_base'] . $config['n8n_webhook_url_test'] . "endpoint";
    // Initialisation cURL
    $ch = curl_init($url);

    // Configuration de la requête POST
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);

    // Envoie les données comme JSON (contenu brut du tableau encodé)
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);

    // Corps de la requête : JSON encodé
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        "payload" => $json // ou tu peux renvoyer directement une structure propre ici
    ]));

    // Exécution de la requête
    $response = curl_exec($ch);

    // Gestion des erreurs
    if (curl_errno($ch)) {
        echo 'Erreur cURL : ' . curl_error($ch);
    } else {
        echo "Réponse de n8n : " . $response;
    }

    $url = $config['n8n_webhook_url_base'] . $config['n8n_webhook_url_test'] . "endpoint";

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
        echo "Réponse de n8n : " . $response;
        $_SESSION["reponse"] = $response;
    }

    // Fermeture
    curl_close($ch);

    // Afficher la réponse
    header("Location: quizz2");
    exit;
}
?>
<!doctype html>
<html>

<head>
    <title>Upload PDF (via PHP vers Flask)</title>
    <link rel="stylesheet" href="assets/css/styles.css" />

</head>

<body>
    <h1>Upload PDF Files</h1>
    <form action="index.php" method="post" enctype="multipart/form-data">
        <label for="file">Enter your files</label>
        <input type="file" name="files[]" multiple required>
        <br><br>
        <label for="option">Choose an option:</label>
        <select name="option" id="option">
            <option value="1">Abstract Only</option>
            <option value="2">Quiz From Source Only</option>
            <option value="3">Abstract & Quiz From Source</option>
            <option value="4">Abstract & Quiz From Abstract</option>
        </select>
        <br><br>
        <label for="radio1">Result For All (one abstract and/or one quiz combining all sources)</label>
        <input type="radio" id="radio1" name="mod" value="sngl" checked>
        <br>
        <label for="radio2">Result for Each (each source get its abstract and/or quiz)</label>
        <input type="radio" id="radio2" name="mod" value="mtpl">
        <br><br>
        <input type="submit" value="Upload and Extract Text">
    </form>
    <?php // include "footer.html"; ?>
</body>

</html>