<?php
include 'begin_php.php';
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($_SESSION['lang']); ?>">
<head>
    <meta charset="UTF-8">
    <title><?php echo t('home_title'); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <div class="page-container">
        <?php include 'header.php'; ?>

        <main class="container">
            <div class="hero-section">
                <h1><?php echo t('main_title'); ?></h1>
                <p><?php echo t('subtitle'); ?></p>
            </div>

            <div class="auth-actions">
                <?php if (isset($_SESSION['access_token'])): ?>
                    <p><?php echo t('hello'); ?>, <b><?php echo htmlspecialchars($_SESSION['user_email']); ?></b> !</p>
                    <div class="menu">
                        <a href="dashboard.php" class="btn"><?php echo t('nav_dashboard'); ?></a>
                        <a href="quizz.php" class="btn"><?php echo t('nav_courses'); ?></a>
                        <a href="form.php" class="btn"><?php echo t('add_pdf_button'); ?></a>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="btn"><?php echo t('nav_login'); ?></a>
                    <a href="register.php" class="btn btn-secondary"><?php echo t('register_button'); ?></a>
                <?php endif; ?>
            </div>

            <!-- Section concept -->
            <section class="concept-section">
                <h2><?php echo t('concept_title'); ?></h2>
                <p><?php echo t('concept_text'); ?></p>

                <div class="features-grid">
                    <div class="feature-card">
                        <h3><?php echo t('feature1_title'); ?></h3>
                        <p><?php echo t('feature1_text'); ?></p>
                    </div>
                    <div class="feature-card">
                        <h3><?php echo t('feature2_title'); ?></h3>
                        <p><?php echo t('feature2_text'); ?></p>
                    </div>
                    <div class="feature-card">
                        <h3><?php echo t('feature3_title'); ?></h3>
                        <p><?php echo t('feature3_text'); ?></p>
                    </div>
                    <div class="feature-card">
                        <h3><?php echo t('feature4_title'); ?></h3>
                        <p><?php echo t('feature4_text'); ?></p>
                    </div>
                </div>

                <div class="cta-section">
                    <a href="form.php" class="btn btn-primary"><?php echo t('start_button'); ?></a>
                </div>
            </section>
        </main>

        <?php include 'footer.html'; ?>
    </div>
</body>
</html>