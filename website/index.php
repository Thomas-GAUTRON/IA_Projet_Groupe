<!DOCTYPE html>
<html lang="fr">
<?php
include "header.html"
?>

<body>

    <main>
        <h1>Analyseur de Documents PDF</h1>
        <h2>Télécharger un document</h2>
        <form action="upload-pdf.php" method="post" enctype="multipart/form-data">
            <label for="pdf">Sélectionnez un fichier PDF :</label><br>
            <input type="file" name="pdf" id="pdf" accept="application/pdf" required><br><br>
            <label for="category">Choisissez une catégorie :</label><br>
            <select name="category" id="category" required>
                <option value="">--Veuillez choisir une option--</option>
                <option value="cat1">Résumé</option>
                <option value="cat3">Quizz</option>
                <option value="cat2">Résumé + Quizz</option>
            </select><br><br>
            <button type="submit">Envoyer</button>
        </form>
    </main>
</body>
<?php
include "footer.html"
?>
    
</html>