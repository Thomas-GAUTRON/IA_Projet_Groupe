<?php
session_start(); // Toujours nécessaire pour accéder à la session

if (isset($_SESSION['response'])) {

    if (preg_match('/```html(.*?)```/s', $_SESSION['response'], $matches)) {
    $contenu = trim($matches[1]); // On enlève les espaces inutiles
    echo $contenu;
    }

    // Optionnel : on supprime la donnée après affichage
    // unset($_SESSION['response']);
} else {
    echo "Aucun message à afficher.";
}
?>