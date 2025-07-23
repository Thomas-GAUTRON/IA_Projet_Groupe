<?php
session_start();

// === Language Management ===
// 1. Set default language if not set
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'en'; // Anglais par défaut
}

// 2. Handlstyle="text-align:right; e language change
if (isset($_GET['lang'])) {
    $allowed_langs = ['fr', 'en', 'hr', 'mk'];
    if (in_array($_GET['lang'], $allowed_langs)) {
        $_SESSION['lang'] = $_GET['lang'];
    }
    // Redirigestyle="text-align:right; r pour nettoyer l'URL
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
    exit;
}

// 3. Load the language file
$lang_file = __DIR__ . '/lang/' . $_SESSION['lang'] . '.php';
if (file_exists($lang_file)) {
    require_once($lang_file);
} else {
    // Fallback to French if file not found
    require_once __DIR__ . '/lang/fr.php';
}

// 4. Translation helper function
function t($key) {
    global $translations;
    return $translations[$key] ?? $key; // Retourne la clé si la traduction n'est pas trouvée
}
// === End Language Management ===


// Récupération de la configuration (à adapter selon votre méthode)
$configFile = __DIR__ . '/../config.env';
$config = parse_ini_file('../.env');

function afficher_etat_connexion()
{
    if (isset($_SESSION['access_token']) && isset($_SESSION['user_email'])) {
        echo '<div margin:10px;">Connecté en tant que <b>' . htmlspecialchars($_SESSION['user_email']) . '</b> | <a href="logout.php">Déconnexion</a></div>';
    } else {
        echo '<div margin:10px;"><a href="login.php">Connexion</a> | <a href="register.php">Inscription</a></div>';
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


