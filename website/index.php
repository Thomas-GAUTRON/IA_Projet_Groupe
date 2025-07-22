<?php
include 'begin_php.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accueil | IA Projet Groupe</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f5f7fa; margin: 0; padding: 0; }
        .header { background: #007bff; color: white; padding: 30px 0 20px 0; text-align: center; }
        .header h1 { margin: 0; font-size: 2.5em; }
        .header p { margin: 10px 0 0 0; font-size: 1.2em; }
        .container { max-width: 500px; margin: 40px auto; background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.07); padding: 30px 20px; text-align: center; }
        .btn { display: inline-block; margin: 10px 10px 0 10px; padding: 12px 30px; background: #007bff; color: white; border: none; border-radius: 25px; font-size: 1.1em; cursor: pointer; text-decoration: none; transition: background 0.2s; }
        .btn:hover { background: #0056b3; }
        .btn-green { background: #28a745; }
        .menu { margin: 30px 0 0 0; }
        .menu a { margin: 0 10px; color: #007bff; text-decoration: none; font-weight: bold; }
        .menu a:hover { text-decoration: underline; }
        @media (max-width: 600px) { .container { padding: 15px 5px; } .header h1 { font-size: 1.5em; } }
    </style>
</head>
<body>
    <div class="header">
        <h1>IA Projet Groupe</h1>
        <p>Bienvenue sur votre plateforme d’analyse et de quiz de documents PDF</p>
    </div>
    <div class="container">
        <?php if (isset($_SESSION['access_token'])): ?>
            <p>Bonjour, <b><?php echo htmlspecialchars($_SESSION['user_email']); ?></b> !</p>
            <div class="menu">
                <a href="dashboard.php">Dashboard</a>
                <a href="quizz.php">Quiz</a>
                <a href="form.php">Ajouter pdf</a>
                <a href="logout.php">Déconnexion</a>
            </div>
        <?php else: ?>
            <a href="login.php" class="btn">Se connecter</a>
            <a href="register.php" class="btn btn-green">Créer un compte</a>
        <?php endif; ?>
    </div>
    <?php include 'footer.html'; ?>
</body>
</html>