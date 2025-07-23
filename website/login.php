<?php
include 'begin_php.php';

$supabase_url = $config['SUPABASE_URL'] ?? '';
$supabase_key = $config['SUPABASE_KEY'] ?? '';

$message = '';
$error = '';

// Fonction pour initier l'authentification OAuth
function initiateOAuthLogin($provider, $supabase_url, $supabase_key, $redirect_to = null) {
    // URL de callback après authentification
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $callback_url = $protocol . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/oauth_callback.php';
    echo $callback_url;
    // Construire l'URL OAuth
    $oauth_url = $supabase_url . '/auth/v1/authorize?' . http_build_query([
        'provider' => $provider,
        'redirect_to' => $callback_url
    ]);
    
    // Rediriger vers l'URL OAuth
    header('Location: ' . $oauth_url);
    exit;
}

// Gestion des clics sur les boutons OAuth
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['google_login'])) {
        initiateOAuthLogin('google', $supabase_url, $supabase_key);
    }
    if (isset($_POST['github_login'])) {
        initiateOAuthLogin('github', $supabase_url, $supabase_key);
    }
}

// Fonction pour l'authentification par email/mot de passe
function emailPasswordLogin($email, $password, $supabase_url, $supabase_key) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_URL, $supabase_url . '/auth/v1/token?grant_type=password');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'email' => $email,
        'password' => $password
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'apikey: ' . $supabase_key,
        'Authorization: Bearer ' . $supabase_key
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'response' => json_decode($response, true),
        'http_code' => $httpCode
    ];
}

// Traitement du formulaire email/mot de passe
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email_login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Veuillez remplir tous les champs.';
    } else {
        $result = emailPasswordLogin($email, $password, $supabase_url, $supabase_key);
        
        if ($result['http_code'] === 200 && isset($result['response']['access_token'])) {
            // Connexion réussie
            $user = $result['response'];
            
            session_start();
            $_SESSION['user_id'] = $user['user']['id'];
            $_SESSION['user_email'] = $user['user']['email'];
            $_SESSION['access_token'] = $user['access_token'];
            $_SESSION['refresh_token'] = $user['refresh_token'];
            
            header('Location: form.php');
            exit;
        } else {
            $errorResponse = $result['response'];
            if (isset($errorResponse['error_description'])) {
                $error = 'Erreur : ' . $errorResponse['error_description'];
            } else {
                $error = 'Erreur de connexion. Veuillez réessayer.';
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
    <title><?php echo t('login_title'); ?></title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <script src="https://accounts.google.com/gsi/client" async defer></script>
</head>
<body>
    <div class="page-container">
        <?php include 'header.php'; ?>
        <main class="container auth-container">
            <div class="auth-form-wrapper">
                <h2><?php echo t('login_heading'); ?></h2>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if ($message): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>

                <!-- Boutons OAuth -->
                <div class="oauth-buttons">
                    <form method="POST" class="oauth-form">
                        <button type="submit" name="google_login" class="btn-social btn-google">
                            <svg version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" class="google-icon"><g><path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"></path><path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"></path><path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"></path><path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"></path><path fill="none" d="M0 0h48v48H0z"></path></g></svg>
                            <?php echo t('login_with_google'); ?>
                        </button>
                    </form>
                    <form method="POST" class="oauth-form">
                        <button type="submit" name="github_login" class="btn-social btn-github">
                            <svg class="github-icon" viewBox="0 0 16 16" version="1.1" aria-hidden="true"><path fill-rule="evenodd" d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.013 8.013 0 0016 8c0-4.42-3.58-8-8-8z"></path></svg>
                            <?php echo t('login_with_github'); ?>
                        </button>
                    </form>
                </div>

                <div class="divider">
                    <span><?php echo t('login_or'); ?></span>
                </div>

                <!-- Formulaire classique -->
                <form method="POST" class="auth-form">
                    <div class="form-group">
                        <label for="email"><?php echo t('login_email'); ?></label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="password"><?php echo t('login_password'); ?></label>
                        <input type="password" id="password" name="password" required>
                    </div>

                    <button type="submit" name="email_login" class="btn btn-primary btn-block"><?php echo t('login_button'); ?></button>
                </form>

                <div class="form-footer">
                    <p><?php echo t('login_no_account'); ?> <a href="register.php"><?php echo t('login_signup_link'); ?></a></p>
                </div>
            </div>
        </main>
    </div>
</body>
</html>