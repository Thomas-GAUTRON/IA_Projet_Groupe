<?php include 'begin_php.php';
if (!isset($_SESSION['access_token'])) {
    header('Location: login.php');
    exit;
}
?>

<!doctype html>
<html>

<head>
    <title>Upload PDF (via PHP vers Flask)</title>
    <link rel="stylesheet" href="assets/css/styles.css" />

</head>

<body>
    <div class="page-container">
        <?php include 'header.php'; ?>
        <main class="container">
            <h1>Générer un nouveau contenu</h1>
            <form action="load.php" method="post" enctype="multipart/form-data" class="upload-form">
                <div class="form-group">
                    <label for="files">Sélectionnez un ou plusieurs fichiers PDF :</label>
                    <input type="file" name="files[]" id="files" multiple required accept=".pdf">
                </div>

                <div class="form-group">
                    <label for="option">Choisissez le type de contenu à générer :</label>
                    <select name="option" id="option" required>
                        <option value="1">Résumé seulement</option>
                        <option value="2">Quiz seulement</option>
                        <option value="3">Résumé & Quiz (depuis la source)</option>
                        <option value="4">Résumé & Quiz (depuis le résumé)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Mode de résumé :</label>
                    <div class="toggle-group">
                        <span class="toggle-label">Professionnel</span>
                        <label class="switch">
                            <input type="checkbox" name="mode" id="mode-toggle" value="educational">
                            <span class="slider round"></span>
                        </label>
                        <span class="toggle-label">Pédagogique</span>
                    </div>
                </div>

                <div class="form-group">
                    <label>Portée de la génération :</label>
                    <div class="radio-group">
                        <label class="radio-option">
                            <input type="radio" name="modifier" value="sngl" checked>
                            Un seul résultat pour tous les documents
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="modifier" value="mtpl">
                            Un résultat pour chaque document
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" id="submit-btn">Lancer la génération</button>
            </form>

            <div id="loader" class="loader-overlay" style="display:none;">
                <div class="loader-content">
                    <div class="spinner"></div>
                    <p class="loader-text">Traitement en cours, veuillez patienter...</p>
                </div>
            </div>
        </main>
    </div>
    <script>
        document.querySelector('form').addEventListener('submit', function() {
            document.getElementById('submit-btn').disabled = true;
            document.getElementById('loader').style.display = 'block';
        });
    </script>

    <?php include "footer.html"; ?>
</body>

</html>