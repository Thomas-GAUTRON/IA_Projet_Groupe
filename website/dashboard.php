<?php
include 'begin_php.php';
if (!isset($_SESSION['access_token'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | IA Projet Groupe</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f5f7fa; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.07); padding: 30px 20px; text-align: center; }
        h1 { color: #007bff; }
        .menu { margin: 30px 0 0 0; }
        .menu a { margin: 0 10px; color: #007bff; text-decoration: none; font-weight: bold; }
        .menu a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Bienvenue sur votre dashboard</h1>
        <p>Bonjour, <b><?php echo htmlspecialchars($_SESSION['user_email']); ?></b> !</p>
        <div class="menu">
            <a href="quizz.php">Quiz</a>
            <a href="form.php">Ajouter pdf</a>
            <a href="logout.php">Déconnexion</a>
            <a href="index.php">Accueil</a>
        </div>
        <p style="margin-top:40px;color:#888;">Ici, tu pourras retrouver tes activités, tes résultats, et accéder rapidement aux fonctionnalités principales.</p>
    </div>
</body>
</html>
