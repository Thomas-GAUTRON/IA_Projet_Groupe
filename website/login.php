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
    } elseif (isset($_POST['apple_login'])) {
        initiateOAuthLogin('apple', $supabase_url, $supabase_key);
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
            
            header('Location: dashboard.php');
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
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        
        .form-container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }
        
        .oauth-buttons {
            margin-bottom: 30px;
        }
        
        .oauth-btn {
            width: 100%;
            padding: 12px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: white;
            cursor: pointer;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.3s;
        }
        
        .oauth-btn:hover {
            background-color: #f8f9fa;
        }
        
        .google-btn {
            color: #4285f4;
        }
        
        .apple-btn {
            color: #000;
        }
        
        .divider {
            text-align: center;
            margin: 20px 0;
            position: relative;
        }
        
        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background-color: #ddd;
        }
        
        .divider span {
            background-color: white;
            padding: 0 15px;
            color: #666;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: bold;
        }
        
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
        }
        
        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #007bff;
        }
        
        .btn {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background-color: #0056b3;
        }
        
        .error {
            color: #dc3545;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .success {
            color: #155724;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .register-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .register-link a {
            color: #007bff;
            text-decoration: none;
        }
        
        .register-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Connexion</h2>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($message): ?>
            <div class="success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <!-- Boutons OAuth -->
        <div class="oauth-buttons">
            <form method="POST" style="display: inline;">
                <button type="submit" name="google_login" class="oauth-btn google-btn">
                    🔴 Se connecter avec Google
                </button>
            </form>
            
            <form method="POST" style="display: inline;">
                <button type="submit" name="apple_login" class="oauth-btn apple-btn">
                    🍎 Se connecter avec Apple
                </button>
            </form>
        </div>
        
        <div class="divider">
            <span>ou</span>
        </div>
        
        <!-- Formulaire classique -->
        <form method="POST">
            <div class="form-group">
                <label for="email">Email :</label>
                <input type="email" id="email" name="email" 
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                       required>
            </div>
            
            <div class="form-group">
                <label for="password">Mot de passe :</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" name="email_login" class="btn">Se connecter</button>
        </form>
        
        <div class="register-link">
            <p>Pas encore de compte ? <a href="register.php">S'inscrire</a></p>
        </div>
    </div>
</body>
</html>