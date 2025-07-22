<?php
include "begin_php.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $flask_url = $config["FLASK_URL"];
    // Préparer les données POST
    $postData = [
        'option' => $_POST['option'] ?? '',
        'modifier' => $_POST['modifier'] ?? '',
        'mode' => $_POST['mode'] ?? '',
    ];

    // Gérer les fichiers uploadés
    $cfiles = [];
    if (isset($_FILES['files']) && is_array($_FILES['files']['tmp_name'])) {
        foreach ($_FILES['files']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['files']['error'][$key] === UPLOAD_ERR_OK) {
                $filePath = $tmp_name;
                $fileName = $_FILES['files']['name'][$key];
                // Utiliser la notation cURLFile pour les fichiers
                $cfiles["files[$key]"] = new CURLFile($filePath, mime_content_type($filePath), $fileName);
            }
        }
    }

    $ch = curl_init($flask_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    // Combinez les champs de formulaire et les fichiers
    curl_setopt($ch, CURLOPT_POSTFIELDS, array_merge($postData, $cfiles));

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        // Gérer l'erreur et informer l'utilisateur
        $_SESSION['error_message'] = 'Erreur cURL : ' . curl_error($ch);
        header("Location: form.php"); // Rediriger vers le formulaire avec un message d'erreur
        exit;
    }
    curl_close($ch);

    $responseData = json_decode($response, true);
    if (isset($responseData['task_id'])) {
        $_SESSION['task_id'] = $responseData['task_id'];
        // Rediriger vers la page de quiz qui gérera l'attente
        header("Location: quizz.php");
        exit;
    } else {
        // Gérer le cas où la réponse n'est pas ce qui est attendu
        $_SESSION['error_message'] = "La réponse du serveur de traitement est invalide : " . $response;
        header("Location: form.php");
        exit;
    }
}
?>