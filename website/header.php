<header class="main-header">
    <nav class="main-nav">
        <a href="index.php" class="nav-link">Accueil</a>
        <a href="form.php" class="nav-link">Générer</a>
        <a href="quizz.php" class="nav-link">Mes cours</a>
        <a href="dashboard.php" class="nav-link">Dashboard</a>
        <?php if (isset($_SESSION['access_token'])): ?>
            <a href="logout.php" class="nav-link">Déconnexion</a>
        <?php endif; ?>
    </nav>
</header>