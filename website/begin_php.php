<?php 
session_start();
$config = parse_ini_file('../.env');

function afficher_etat_connexion() {
    if (isset($_SESSION['access_token']) && isset($_SESSION['user_email'])) {
        echo '<div style="text-align:right; margin:10px;">Connecté en tant que <b>' . htmlspecialchars($_SESSION['user_email']) . '</b> | <a href="logout.php">Déconnexion</a></div>';
    } else {
        echo '<div style="text-align:right; margin:10px;"><a href="login.php">Connexion</a> | <a href="register.php">Inscription</a></div>';
    }
}
?>