<?php
session_start();
$config = parse_ini_file('../.env');

function afficher_etat_connexion()
{
    if (isset($_SESSION['access_token']) && isset($_SESSION['user_email'])) {
        echo '<div style="text-align:right; margin:10px;">Connecté en tant que <b>' . htmlspecialchars($_SESSION['user_email']) . '</b> | <a href="logout.php">Déconnexion</a></div>';
    } else {
        echo '<div style="text-align:right; margin:10px;"><a href="login.php">Connexion</a> | <a href="register.php">Inscription</a></div>';
    }
}

function split_text_by_words($text, $max_char)
{
    // Nettoyage du texte (espaces multiples → un seul)
    $text = trim(preg_replace('/\s+/', ' ', $text));

    // Séparation en mots
    $words = explode(' ', $text);

    $chunks = [];
    $current_chunk = '';

    foreach ($words as $word) {
        // Si on ajoute ce mot, dépassera-t-on la limite ?
        if (strlen($current_chunk . ' ' . $word) > $max_char) {
            // On ajoute le chunk actuel et recommence un nouveau
            $chunks[] = trim($current_chunk);
            $current_chunk = $word;
        } else {
            // Sinon, on continue à remplir le chunk
            $current_chunk .= (empty($current_chunk) ? '' : ' ') . $word;
        }
    }

    // Ajouter le dernier morceau s'il reste quelque chose
    if (!empty($current_chunk)) {
        $chunks[] = trim($current_chunk);
    }

    return $chunks;
}

function insert_in_supabase($supabase_url, $supabase_key, $table, $data)
{

    $ch = curl_init("$supabase_url/rest/v1/$table");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "apikey: $supabase_key",
        "Authorization: Bearer $supabase_key",
        "Content-Type: application/json",
        "Prefer: return=representation"
    ]);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    // Un seul appel ici :
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'http_code' => $http_code,
        'response' => $response
    ];
}

function split_text($texte)
{
    $taille_chunk = 500;
    $chunks = split_text_by_words($texte, $taille_chunk);
    return $chunks;
}


