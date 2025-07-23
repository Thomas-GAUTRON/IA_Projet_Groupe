<?php
include 'begin_php.php';
if (!isset($_SESSION['access_token'])) {
    header('Location: login.php');
    exit;
}
$supabaseUrl = $config['SUPABASE_URL'];
$supabaseKey = $config['SUPABASE_KEY'];
$table = $config['SUPABASE_TABLE'];

$ch = curl_init();
// On sélectionne uniquement id_request avec les valeurs distinctes
$url = "$supabaseUrl/rest/v1/$table?select=id_request&id_user=eq.".$_SESSION['user_id'];

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // à désactiver en production si possible
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "apikey: $supabaseKey",
    "Authorization: Bearer $supabaseKey",
    "Content-Type: application/json",
    "Accept: application/json"
]);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo 'Erreur curl : ' . curl_error($ch);
    exit;
}
curl_close($ch);

// Décodage du JSON
$data = json_decode($response, true);
if (is_array($data)) {

    $uniqueIds = [];

    foreach ($data as $entry) {
        if (isset($entry['id_request'])) {
            $uniqueIds[] = $entry['id_request'];
        }
    }

    // Supprime les doublons
    $uniqueIds = array_unique($uniqueIds);

    // Affichage
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Dashboard | IA Projet Groupe</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="assets/css/styles.css">

</head>

<body>
    <div class="page-container">
        <?php include 'header.php'; ?>
        <main class="container">
            <div class="dashboard-header">
                <h1>Votre Dashboard</h1>
                <p>Bonjour, <b><?php echo htmlspecialchars($_SESSION['user_email']); ?></b> ! Retrouvez ici vos documents analysés.</p>
            </div>

            <section class="courses-list">
                <h2>Mes cours</h2>
                <?php
                if (isset($uniqueIds) && !empty($uniqueIds)) {
                    echo '<ul class="course-items">';
                    $i = 1;
                    foreach ($uniqueIds as $safeid) {
                        echo "<li><a href=\"change.php?id=$safeid&amp;type=quizz\" class=\"course-link\">Cours $i</a></li>";
                        $i++;
                    }
                    echo '</ul>';
                } else {
                    echo '<p class="no-courses">Vous n\'avez pas encore analysé de documents.</p>';
                }
                ?>
            </section>
        </main>
        <?php include 'footer.html'; ?>
    </div>
</body>

</html>