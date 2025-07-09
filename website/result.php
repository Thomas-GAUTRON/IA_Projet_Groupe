<?php
session_start(); // Toujours nécessaire pour accéder à la session

if (isset($_SESSION['response'])) {
    echo "<head>
            <meta charset=\"UTF-8\">
            <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
            <title>My Website</title>
            <link rel=\"stylesheet\" href=\"styles.css\">
            <script src=\"script.js\" defer></script>
        </head>";

    $data = json_decode($_SESSION['response']);
    if (preg_match('/```html(.*?)```/s', $data->resume, $matches)) {
        $contenu = trim($matches[1]); // On enlève les espaces inutiles
        echo $contenu;   
    }
    if (preg_match('/```html(.*?)```/s', $data->quizz, $matches)) {
        $contenu = trim($matches[1]); // On enlève les espaces inutiles
        echo $contenu;   
    }
    echo "<button onclick=\"corriger()\">Valider mes réponses</button>";
    echo "<div id=\"score\"></div>";

} else {
    echo "<p>Aucune réponse disponible.";
    }
?>