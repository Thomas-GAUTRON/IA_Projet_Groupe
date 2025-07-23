<?php
include 'begin_php.php';

$supabase_url = $config['SUPABASE_URL'] ?? '';
$supabase_key = $config['SUPABASE_KEY'] ?? '';

$message = '';
$error = '';

// === OAuth Signup (Google / GitHub) ===
// Fonction pour initier l'authentification OAuth (même logique que login.php)
function initiateOAuthLogin($provider, $supabase_url, $supabase_key, $redirect_to = null) {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $callback_url = $protocol . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/oauth_callback.php';

    $oauth_url = $supabase_url . '/auth/v1/authorize?' . http_build_query([
        'provider' => $provider,
        'redirect_to' => $callback_url
    ]);

    header('Location: ' . $oauth_url);
    exit;
}

// Déclenchement au clic sur les boutons OAuth
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['google_signup'])) {
        initiateOAuthLogin('google', $supabase_url, $supabase_key);
    }
    if (isset($_POST['github_signup'])) {
        initiateOAuthLogin('github', $supabase_url, $supabase_key);
    }
}

// Fonction pour faire une requête à Supabase Auth
function supabaseAuthRequest($url, $key, $endpoint, $data) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_URL, $url . '/auth/v1/' . $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'apikey: ' . $key,
        'Authorization: Bearer ' . $key
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'response' => json_decode($response, true),
        'http_code' => $httpCode
    ];
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    
    // Validation des données
    if (empty($email) || empty($password) || empty($first_name) || empty($last_name)) {
        $error = 'To                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        us les champs sont obligatoires.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'L\'adresse email n\'est pas valide.';
    } elseif (strlen($password) < 6) {
        $error = 'Le mot de passe doit contenir au moins 6 caractères.';
    } elseif ($password !== $confirm_password) {
        $error = 'Les mots de passe ne correspondent pas.';
    } else {
        // Tentative d'inscription
        $userData = [
            'email' => $email,
            'password' => $password,
            'data' => [
                'first_name' => $first_name,
                'last_name' => $last_name
            ]
        ];
        
        $result = supabaseAuthRequest($supabase_url, $supabase_key, 'signup', $userData);
        
        if ($result['http_code'] === 200) {
            $message = 'Inscription réussie';

            // Optionnel : rediriger vers une page de confirmation
            header('Location: form.php');
            // exit;
        } else {
            // Gestion des erreurs spécifiques
            $errorResponse = $result['response'];
            
            if (isset($errorResponse['error_description'])) {
                $errorMsg = $errorResponse['error_description'];
                
                // Vérifier si l'erreur indique un email déjà utilisé
                if (strpos($errorMsg, 'already registered') !== false || 
                    strpos($errorMsg, 'already exists') !== false ||
                    strpos($errorMsg, 'User already registered') !== false) {
                    $error = 'Cette adresse email est déjà utilisée. Veuillez utiliser une autre adresse email ou vous connecter.';
                } else {
                    $error = 'Erreur lors de l\'inscription : ' . $errorMsg;
                }
            } elseif (isset($errorResponse['message'])) {
                $error = 'Erreur lors de l\'inscription : ' . $errorResponse['message'];
            } 
            elseif(isset($errorResponse['msg'])){
                $error = 'Erreur lors de l\'inscription : ' . $errorResponse['msg'];
            }
            else {
                $error = 'Une erreur inattendue s\'est produite. Veuillez réessayer.';
                print_r($result);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($_SESSION['lang']); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t('register_title_full'); ?></title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <script src="https://accounts.google.com/gsi/client" async defer></script>
</head>
<body>
    <div class="page-container">
        <?php include 'header.php'; ?>
        <main class="container auth-container">
            <div class="auth-form-wrapper">
                <h2><?php echo t('register_heading'); ?></h2>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if ($message): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>

                <!-- Boutons OAuth -->
                <div class="oauth-buttons">
                    <form method="POST" class="oauth-form">
                        <button type="submit" name="google_signup" class="btn-social btn-google">
                            <svg version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" class="google-icon"><g><path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"></path><path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"></path><path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"></path><path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"></path><path fill="none" d="M0 0h48v48H0z"></path></g></svg>
                            <?php echo t('register_with_google'); ?>
                        </button>
                    </form>
                    <form method="POST" class="oauth-form">
                        <button type="submit" name="github_signup" class="btn-social btn-github">
                            <svg class="github-icon" viewBox="0 0 16 16" version="1.1" aria-hidden="true"><path fill-rule="evenodd" d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.013 8.013 0 0016 8c0-4.42-3.58-8-8-8z"></path></svg>
                            <?php echo t('register_with_github'); ?>
                        </button>
                    </form>
                </div>

                <div class="divider">
                    <span><?php echo t('register_or'); ?></span>
                </div>

                <form method="POST" action="" class="auth-form">
                    <div class="form-group">
                        <label for="first_name"><?php echo t('register_first_name'); ?></label>
                        <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="last_name"><?php echo t('register_last_name'); ?></label>
                        <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email"><?php echo t('register_email'); ?></label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="password"><?php echo t('register_password'); ?></label>
                        <input type="password" id="password" name="password" minlength="6" required>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password"><?php echo t('register_confirm_password'); ?></label>
                        <input type="password" id="confirm_password" name="confirm_password" minlength="6" required>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block"><?php echo t('register_button_submit'); ?></button>
                </form>

                <div class="form-footer">
                    <p><?php echo t('register_have_account'); ?> <a href="login.php"><?php echo t('register_login_link'); ?></a></p>
                </div>
            </div>
        </main>
    </div>
</body>
</html>