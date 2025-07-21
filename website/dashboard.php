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
$id = getUserIdFromAccessToken($_SESSION['access_token'], $config['SUPABASE_URL'], $config['SUPABASE_KEY']);
// On sélectionne uniquement id_request avec les valeurs distinctes
$url = "$supabaseUrl/rest/v1/$table?select=id_request&id_user=eq.$id";

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
    <style>


        .container2 {
            max-width: 600px;
            margin: 40px auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.07);
            padding: 30px 20px;
            text-align: center;
        }

        h1 {
            color: #007bff;
        }

    </style>
</head>

<body>
    <?php include 'header.html'; ?>
    <div class="container2">
        <h1>Bienvenue sur votre dashboard</h1>
        <p>Bonjour, <b><?php echo htmlspecialchars($_SESSION['user_email']); ?></b> !</p>

        <?php
        echo "<h2>Mes cours</h2>";
        if (isset($uniqueIds)) {
            $i = 1;
            foreach ($uniqueIds as $safeid) {
                echo "<a href=\"change?id=$safeid&amp;type=quizz\">Cours $i</a><br>";
                $i++;
            }
        }
        ?>
    </div>
</body>

</html>