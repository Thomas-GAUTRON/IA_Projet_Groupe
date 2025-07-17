<?php
include 'begin_php.php';

$supabase_url = $config['SUPABASE_URL'] ?? '';
$supabase_key = $config['SUPABASE_KEY'] ?? '';


// Fonction pour obtenir les détails de l'utilisateur
function getUserDetails($access_token, $supabase_url, $supabase_key) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_URL, $supabase_url . '/auth/v1/user');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apikey: ' . $supabase_key,
        'Authorization: Bearer ' . $access_token
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'response' => json_decode($response, true),
        'http_code' => $httpCode
    ];
}

// Traitement des paramètres OAuth envoyés par JavaScript
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['access_token'])) {
    $access_token = $_POST['access_token'];
    $refresh_token = $_POST['refresh_token'] ?? '';
    $expires_at = $_POST['expires_at'] ?? '';
    $provider_token = $_POST['provider_token'] ?? '';
    
    // Obtenir les détails de l'utilisateur
    $userResult = getUserDetails($access_token, $supabase_url, $supabase_key);
    
    if ($userResult['http_code'] === 200) {
        $user = $userResult['response'];
        
        // Stocker les informations de session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['access_token'] = $access_token;
        $_SESSION['refresh_token'] = $refresh_token;
        $_SESSION['expires_at'] = $expires_at;
        $_SESSION['provider_token'] = $provider_token;
        
        // Stocker les informations du profil OAuth
        if (isset($user['user_metadata'])) {
            $_SESSION['user_name'] = $user['user_metadata']['full_name'] ?? $user['user_metadata']['name'] ?? '';
            $_SESSION['user_avatar'] = $user['user_metadata']['avatar_url'] ?? $user['user_metadata']['picture'] ?? '';
        }
        
        if (isset($user['app_metadata'])) {
            $_SESSION['oauth_provider'] = $user['app_metadata']['provider'] ?? 'unknown';
        }
        
        // Retourner une réponse de succès pour le JavaScript
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'redirect' => 'dashboard.php?oauth_success=1']);
        exit;
    } else {
        // Erreur lors de la récupération des détails utilisateur
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => "Coucou" . $userResult['response'] . $userResult['http_code']]);
        exit;
    }
}

// Gestion des erreurs OAuth classiques (si elles arrivent via GET)
if (isset($_GET['error'])) {
    $error = $_GET['error'];
    $error_description = $_GET['error_description'] ?? '';
    
    header('Location: login.php?error=oauth_error&description=' . urlencode($error_description));
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authentification en cours...</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background-color: #f5f5f5;
        }
        
        .loading-container {
            text-align: center;
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #007bff;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        h3 {
            color: #333;
            margin-bottom: 10px;
        }
        
        p {
            color: #666;
            margin: 0;
        }
        
        .error {
            color: #dc3545;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 15px;
            border-radius: 4px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="loading-container">
        <div class="spinner"></div>
        <h3>Authentification en cours...</h3>
        <p>Veuillez patienter pendant que nous vous connectons.</p>
        <div id="error-message" class="error" style="display: none;"></div>
    </div>

    <script>
        // Fonction pour extraire les paramètres du fragment URL
        function getHashParams() {
            const hashParams = {};
            const hash = window.location.hash.substring(1);
            const params = hash.split('&');
            
            for (let i = 0; i < params.length; i++) {
                const param = params[i].split('=');
                if (param[0] && param[1]) {
                    hashParams[param[0]] = decodeURIComponent(param[1]);
                }
            }
            
            return hashParams;
        }
        
        // Fonction pour envoyer les paramètres OAuth au serveur
        function sendOAuthParams(params) {
            const formData = new FormData();
            Object.keys(params).forEach(key => {
                formData.append(key, params[key]);
            });
            
            fetch(window.location.pathname, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Rediriger vers le dashboard
                    window.location.href = data.redirect;
                } else {
                    showError('Erreur lors de l\'authentification: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('Erreur de connexion au serveur');
            });
        }
        
        // Fonction pour afficher une erreur
        function showError(message) {
            const errorDiv = document.getElementById('error-message');
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
            
            // Cacher le spinner
            document.querySelector('.spinner').style.display = 'none';
            document.querySelector('h3').textContent = 'Erreur d\'authentification';
            document.querySelector('p').textContent = 'Une erreur s\'est produite lors de la connexion.';
            
            // Rediriger vers la page de connexion après 3 secondes
            setTimeout(() => {
                window.location.href = 'login.php?error=oauth_callback_error';
            }, 3000);
        }
        
        // Traitement principal
        document.addEventListener('DOMContentLoaded', function() {
            const hashParams = getHashParams();
            
            // Vérifier si nous avons des paramètres OAuth
            if (hashParams.access_token && hashParams.refresh_token) {
                // Envoyer les paramètres au serveur
                sendOAuthParams(hashParams);
            } else if (hashParams.error) {
                // Gérer les erreurs OAuth
                const errorDescription = hashParams.error_description || hashParams.error;
                showError('Erreur OAuth: ' + errorDescription);
            } else {
                // Aucun paramètre OAuth valide trouvé
                showError('Aucun paramètre OAuth valide trouvé');
            }
        });
    </script>
</body>
</html>