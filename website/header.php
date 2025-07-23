<?php
// Assure la disponibilité de la fonction de traduction et de la variable de langue.
// Si begin_php.php n’a pas encore été inclus par la page appelante, on l’inclut ici.
if (!function_exists('t')) {
    include_once 'begin_php.php';
}

?>
<header class="main-header">
    <nav class="main-nav">
        <div class="nav-left">
            <!-- Sélecteur de langue -->
            <form method="get" class="lang-form">
                <select name="lang" class="lang-select" onchange="this.form.submit()">
                    <option value="fr" <?php echo ($_SESSION['lang'] === 'fr') ? 'selected' : ''; ?>>FR</option>
                    <option value="en" <?php echo ($_SESSION['lang'] === 'en') ? 'selected' : ''; ?>>EN</option>
                    <option value="hr" <?php echo ($_SESSION['lang'] === 'hr') ? 'selected' : ''; ?>>HR</option>
                    <option value="mk" <?php echo ($_SESSION['lang'] === 'mk') ? 'selected' : ''; ?>>MK</option>
                </select>
            </form>

            <!-- Lien Accueil -->
            <a href="index.php" class="nav-link"><?php echo t('nav_home'); ?></a>
        </div>

        <!-- Liens centraux (affichés uniquement si connecté) -->
        <div class="nav-center">
            <?php if (isset($_SESSION['access_token'])): ?>
                <a href="form.php" class="nav-link"><?php echo t('nav_generate'); ?></a>
                <a href="quizz.php" class="nav-link"><?php echo t('nav_courses'); ?></a>
                <a href="dashboard.php" class="nav-link"><?php echo t('nav_dashboard'); ?></a>
            <?php endif; ?>
        </div>

        <div class="nav-right">
            <?php if (isset($_SESSION['access_token'])): ?>
                <a href="logout.php" class="nav-link"><?php echo t('nav_logout'); ?></a>
            <?php else: ?>
                <a href="login.php" class="nav-link"><?php echo t('nav_login'); ?></a>
                <a href="register.php" class="nav-link"><?php echo t('register_button'); ?></a>
            <?php endif; ?>
        </div>
    </nav>
</header>