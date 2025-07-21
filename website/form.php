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
    <?php include 'header.html'; ?>
    <h1>Upload PDF Files</h1>
    <form action="load.php" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="files">Select PDF Files:</label>
            <input type="file" name="files[]" id="files" multiple required accept=".pdf">
        </div>

        <div class="form-group">
            <label for="option">Choose Processing Option:</label>
            <select name="option" id="option" required>
                <option value="1">Abstract Only</option>
                <option value="2">Quiz From Source Only</option>
                <option value="3">Abstract & Quiz From Source</option>
                <option value="4">Abstract & Quiz From Abstract</option>
            </select>
        </div>

        <div class="form-group">
            <label>Mode: (influences the abstract)</label>
            <div class="toggle-group">
                <span class="toggle-label">Professional</span>
                <label class="switch">
                    <input type="checkbox" name="mode" id="mode-toggle" value="educational">
                    <span class="slider round"></span>
                </label>
                <span class="toggle-label">Educational</span>
            </div>
        </div>

        <div class="form-group">
            <label>Processing Mode:</label>
            <div class="radio-group">
                <label class="radio-option">
                    <input type="radio" id="radio1" name="modifier" value="sngl" checked>
                    Result For All (one abstract and/or one quiz combining all sources)
                </label>
                <label class="radio-option">
                    <input type="radio" id="radio2" name="modifier" value="mtpl">
                    Result for Each (each source gets its own abstract and/or quiz)
                </label>
            </div>
        </div>

        <button type="submit" class="btn" id="submit-btn">Upload and Process Files</button>
    </form>
    <div id="loader" style="display:none;text-align:center;margin-top:20px;">
        <div class="spinner" style="margin:auto;width:60px;height:60px;border:8px solid #f3f3f3;border-top:8px solid #3498db;border-radius:50%;animation:spin 1s linear infinite;"></div>
        <p>Traitement en cours, veuillez patienter...</p>
    </div>
    <script>
        document.querySelector('form').addEventListener('submit', function() {
            document.getElementById('submit-btn').disabled = true;
            document.getElementById('loader').style.display = 'block';
        });
    </script>
    <style>
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
    <?php // include "footer.html"; 
    ?>
</body>

</html>