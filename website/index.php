<?php
include 'begin_php.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accueil | IA Projet Groupe</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <div class="page-container">
        <?php include 'header.php'; ?>

        <main class="container">
            <div class="hero-section">
                <h1>IA Projet Groupe</h1>
                <p>Votre plateforme d’analyse et de quiz de documents PDF, propulsée par l'IA.</p>
            </div>

            <div class="auth-actions">
                <?php if (isset($_SESSION['access_token'])): ?>
                    <p>Bonjour, <b><?php echo htmlspecialchars($_SESSION['user_email']); ?></b> !</p>
                    <div class="menu">
                        <a href="dashboard.php" class="btn">Dashboard</a>
                        <a href="quizz.php" class="btn">Quiz</a>
                        <a href="form.php" class="btn">Ajouter un PDF</a>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="btn">Se connecter</a>
                    <a href="register.php" class="btn btn-secondary">Créer un compte</a>
                <?php endif; ?>
            </div>

            <!-- Section concept -->
            <section class="concept-section">
                <h2>Le concept</h2>
                <p>
                    IA&nbsp;Projet&nbsp;Groupe est une plateforme pédagogique qui exploite la puissance de l’intelligence artificielle&nbsp;—
                    notamment <strong>Google&nbsp;Gemini&nbsp;2.5&nbsp;Flash</strong>&nbsp;— pour transformer vos PDF en contenus interactifs&nbsp;:
                    résumés LaTeX élégants et quiz corrigés. L’objectif&nbsp;? Vous aider à assimiler, réviser et partager plus efficacement
                    vos documents scientifiques, cours ou travaux de recherche.
                </p>

                <div class="features-grid">
                    <div class="feature-card">
                        <h3>📄 Upload multi-PDF</h3>
                        <p>Glissez-déposez plusieurs fichiers et laissez le backend les traiter en parallèle.</p>
                    </div>
                    <div class="feature-card">
                        <h3>🧠 Résumé LaTeX</h3>
                        <p>Un condensé généré en LaTeX, prêt à être compilé, avec les équations intactes.</p>
                    </div>
                    <div class="feature-card">
                        <h3>❓ Quiz interactif</h3>
                        <p>Questions à choix multiples corrigées instantanément avec explications détaillées.</p>
                    </div>
                    <div class="feature-card">
                        <h3>☁️ Sauvegarde Supabase</h3>
                        <p>Retrouvez tous vos résultats dans le Dashboard, disponibles à tout moment.</p>
                    </div>
                </div>

                <div class="cta-section">
                    <a href="form.php" class="btn btn-primary">Commencer →</a>
                </div>
            </section>
        </main>

        <?php include 'footer.html'; ?>
    </div>
</body>
</html>