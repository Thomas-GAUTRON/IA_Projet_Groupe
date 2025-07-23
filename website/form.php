<?php include 'begin_php.php';
if (!isset($_SESSION['access_token'])) {
    header('Location: login.php');
    exit;
}
?>

<!doctype html>
<html lang="<?php echo htmlspecialchars($_SESSION['lang']); ?>">

<head>
    <title><?php echo t('form_title_full'); ?></title>
    <link rel="stylesheet" href="assets/css/styles.css" />

</head>

<body>
    <div class="page-container">
        <?php include 'header.php'; ?>
        <main class="container">
            <h1><?php echo t('form_heading'); ?></h1>
            <form action="load.php" method="post" enctype="multipart/form-data" class="upload-form">
                <div class="form-group">
                    <label for="files"><?php echo t('form_label_select_files'); ?></label>
                    <div id="drop-area" class="drop-area">
                        <input type="file" name="files[]" id="files" multiple required accept=".pdf" style="display: none;">
                        <div id="file-text"><?php echo t('form_no_file'); ?></div>
                        <button type="button" id="custom-browse-btn" class="btn btn-primary"><?php echo t('form_browse'); ?></button>
                    </div>
                </div>
                <div class="form-group">
                    <label for="option"><?php echo t('form_label_choose_type'); ?></label>
                    <select name="option" id="option" required>
                        <option value="1"><?php echo t('form_option_summary_only'); ?></option>
                        <option value="2"><?php echo t('form_option_quiz_only'); ?></option>
                        <option value="3"><?php echo t('form_option_summary_quiz_source'); ?></option>
                        <option value="4"><?php echo t('form_option_summary_quiz_summary'); ?></option>
                    </select>
                </div>
                <div class="form-group">
                    <label><?php echo t('form_label_summary_mode'); ?></label>
                    <div class="toggle-group">
                        <span class="toggle-label"><?php echo t('toggle_professional'); ?></span>
                        <label class="switch">
                            <input type="checkbox" name="mode" id="mode-toggle" value="educational">
                            <span class="slider round"></span>
                        </label>
                        <span class="toggle-label"><?php echo t('toggle_educational'); ?></span>
                    </div>
                </div>
                <div class="form-group">
                    <label><?php echo t('form_label_generation_scope'); ?></label>
                    <div class="radio-group">
                        <label class="radio-option">
                            <input type="radio" name="modifier" value="sngl" checked>
                            <?php echo t('radio_single_result'); ?>
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="modifier" value="mtpl">
                            <?php echo t('radio_multiple_result'); ?>
                        </label>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" id="submit-btn"><?php echo t('form_submit'); ?></button>
            </form>

            <style>
                .drop-area {
                    border: 2px dashed #ccc;
                    border-radius: 5px;
                    padding: 20px;
                    text-align: center;
                    margin-bottom: 20px;
                    cursor: pointer;
                }

                .drop-area:hover,
                .drop-area.dragover {
                    border-color: #888;
                }
            </style>

            <script>
                const dropArea = document.getElementById('drop-area');
                const fileInput = document.getElementById('files');
                const customBrowseBtn = document.getElementById('custom-browse-btn');
                const fileText = document.getElementById('file-text');

                // Empêcher le comportement par défaut pour les événements de glisser-déposer
                ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                    dropArea.addEventListener(eventName, preventDefaults, false);
                });

                function preventDefaults(e) {
                    e.preventDefault();
                    e.stopPropagation();
                }

                // Mettre en surbrillance la zone de dépôt lorsque l'utilisateur glisse un fichier
                ['dragenter', 'dragover'].forEach(eventName => {
                    dropArea.addEventListener(eventName, highlight, false);
                });

                ['dragleave', 'drop'].forEach(eventName => {
                    dropArea.addEventListener(eventName, unhighlight, false);
                });

                function highlight() {
                    dropArea.classList.add('dragover');
                }

                function unhighlight() {
                    dropArea.classList.remove('dragover');
                }

                // Gérer les fichiers déposés
                dropArea.addEventListener('drop', handleDrop, false);

                function handleDrop(e) {
                    const dt = e.dataTransfer;
                    const files = dt.files;
                    fileInput.files = files;
                    updateFileText(files);
                }

                customBrowseBtn.addEventListener('click', () => {
                    fileInput.click();
                });

                fileInput.addEventListener('change', () => {
                    updateFileText(fileInput.files);
                });

                function updateFileText(files) {
                    if (files.length > 0) {
                        fileText.textContent = Array.from(files).map(file => file.name).join(', ');
                    } else {
                        fileText.textContent = '<?php echo t('form_no_file'); ?>';
                    }
                }
            </script>


            <?php include "footer.html"; ?>
</body>

</html>