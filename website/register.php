<?php
include 'begin_php.php';

$supabase_url = $config['SUPABASE_URL'] ?? '';
$supabase_key = $config['SUPABASE_KEY'] ?? '';

$message = '';
$error = '';

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
        $error = 'Tous les champs sont obligatoires.';
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
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <div class="page-container">
        <?php include 'header.php'; ?>
        <main class="container auth-container">
            <div class="auth-form-wrapper">
                <h2>Inscription</h2>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if ($message): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>

                <form method="POST" action="" class="auth-form">
                    <div class="form-group">
                        <label for="first_name">Prénom :</label>
                        <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="last_name">Nom :</label>
                        <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email :</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Mot de passe :</label>
                        <input type="password" id="password" name="password" minlength="6" required>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirmer le mot de passe :</label>
                        <input type="password" id="confirm_password" name="confirm_password" minlength="6" required>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">S'inscrire</button>
                </form>

                <div class="form-footer">
                    <p>Déjà un compte ? <a href="login.php">Se connecter</a></p>
                </div>
            </div>
        </main>
    </div>
</body>
</html>