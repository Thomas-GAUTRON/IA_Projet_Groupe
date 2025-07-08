<?php
// Configuration : URL du webhook n8n
$n8nWebhookUrl = 'https://n8n.louazon.fr/webhook-test/upload-pdf';

// Vérifie que la requête est bien un POST avec fichier
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['pdf']) && $_FILES['pdf']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['pdf']['tmp_name'];
        $fileName = $_FILES['pdf']['name'];
        $fileType = $_FILES['pdf']['type'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if ($fileExtension === 'pdf' && $fileType === 'application/pdf') {
            $curl = curl_init();
            $cfile = new CURLFile($fileTmpPath, $fileType, $fileName);

            // Récupération du champ category
            $category = isset($_POST['category']) ? $_POST['category'] : '';

            // Prépare les données POST pour curl
            $postData = [
                'pdf' => new CURLFile($fileTmpPath, $fileType, $fileName),
                'category' => $category,
            ];

            curl_setopt_array($curl, [
                CURLOPT_URL => $n8nWebhookUrl,
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POSTFIELDS => $postData,
                // autres options ...
            ]);
            
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

            $response = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $error = curl_error($curl);
            curl_close($curl);

            if ($response === false) {
                echo "<p>Erreur CURL : $error</p>";
            } else {
                echo "<p>Fichier envoyé à n8n. Réponse HTTP : $httpCode</p>";
            }
        } else {
            echo "<p>Le fichier n'est pas un PDF valide.</p>";
        }
    } else {
        echo "<p>Erreur lors du téléchargement : code " . $_FILES['pdf']['error'] . "</p>";
    }
} else {
    echo "<p>Requête non valide. Utilisez POST.</p>";
}
?>