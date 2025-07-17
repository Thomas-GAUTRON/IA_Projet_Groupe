
<!doctype html>
<html>

<head>
    <title>Upload PDF (via PHP vers Flask)</title>
    <link rel="stylesheet" href="assets/css/styles.css" />

</head>

<body>
    <h1>Upload PDF Files</h1>
    <form action="load.php" method="post" enctype="multipart/form-data">
        <label for="file">Enter your files</label>
        <input type="file" name="files[]" multiple required>
        <br><br>
        <label for="option">Choose an option:</label>
        <select name="option" id="option">
            <option value="1">Abstract Only</option>
            <option value="2">Quiz From Source Only</option>
            <option value="3">Abstract & Quiz From Source</option>
            <option value="4">Abstract & Quiz From Abstract</option>
        </select>
        <br><br>
        <label for="radio1">Result For All (one abstract and/or one quiz combining all sources)</label>
        <input type="radio" id="radio1" name="mod" value="sngl" checked>
        <br>
        <label for="radio2">Result for Each (each source get its abstract and/or quiz)</label>
        <input type="radio" id="radio2" name="mod" value="mtpl">
        <br><br>
        <input type="submit" value="Upload and Extract Text">
    </form>
    <?php // include "footer.html"; ?>
</body>

</html>