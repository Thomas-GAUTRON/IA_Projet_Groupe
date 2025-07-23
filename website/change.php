<?php
include 'begin_php.php';
// Sécuriser la récupération des paramètres GET
$id = isset($_GET['id']) ? trim($_GET['id']) : null;
$type = isset($_GET['type']) ? trim($_GET['type']) : null;

// Vérification basique des paramètres
if (!$id || !$type) {
    echo "Paramètres manquants ou invalides.";
    exit;
}

// Autoriser seulement certains types pour éviter abus
$allowedTypes = ['quiz', 'resume'];
if (!in_array($type, $allowedTypes)) {
    echo "Type invalide.";
    exit;
}

// Exemple de logique selon le type demandé
switch ($type) {
    case 'quiz':
        // Ici, tu peux inclure ou générer le quiz correspondant à $id
        $_SESSION['reponse'] = $id;
        header("Location: quiz");
        // Exemple : include "quiz_generator.php"; ou récupération BD, etc.
        break;

    case 'resume':
        // Ici, tu peux inclure ou générer le résumé correspondant à $id
        $_SESSION['reponse'] = $id;
        header("Location: resume");
        // Exemple : include "resume_generator.php";
        break;

    default:
        echo "Type non supporté.";
        break;
}
