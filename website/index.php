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
        /* Section concept */
        .concept {
            max-width: 900px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 30px 40px;
            line-height: 1.6em;
        }
        .concept h2 { color: #007bff; margin-top: 0; }
        .features { display: flex; flex-wrap: wrap; gap: 20px; margin-top: 20px; }
        .feature { flex: 1 1 250px; background:#f5f7fa; border-radius:8px; padding:15px; }
        .feature h3 { margin-top:0; font-size:1.1em; color:#007bff; }
        @media(max-width:600px){ .features{flex-direction:column;} }
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

    <!-- Section concept -->
    <div class="concept">
        <h2>Le concept en quelques mots</h2>
        <p>
            IA&nbsp;Projet&nbsp;Groupe est une plateforme pédagogique qui exploite la puissance de l’intelligence artificielle&nbsp;—
            notamment <strong>Google&nbsp;Gemini&nbsp;2.5&nbsp;Flash</strong>&nbsp;— pour transformer vos PDF en contenus interactifs&nbsp;:
            résumés LaTeX élégants et quiz corrigés. L’objectif&nbsp;? Vous aider à assimiler, réviser et partager plus efficacement
            vos documents scientifiques, cours ou travaux de recherche.
        </p>

        <div class="features">
            <div class="feature">
                <h3>📄 Upload multi-PDF</h3>
                <p>Glissez-déposez plusieurs fichiers et laissez le backend les traiter en parallèle.</p>
            </div>
            <div class="feature">
                <h3>🧠 Résumé LaTeX</h3>
                <p>Un condensé généré en LaTeX, prêt à être compilé, avec les équations intactes.</p>
            </div>
            <div class="feature">
                <h3>❓ Quiz interactif</h3>
                <p>Questions à choix multiples corrigées instantanément avec explications détaillées.</p>
            </div>
            <div class="feature">
                <h3>☁️ Sauvegarde Supabase</h3>
                <p>Retrouvez tous vos résultats dans le Dashboard, disponibles à tout moment.</p>
            </div>
        </div>

        <p style="text-align:center; margin-top:30px;">
            <a href="form.php" class="btn">Commencer →</a>
        </p>
    </div>
    <?php include 'footer.html'; ?>
</body>
</html>