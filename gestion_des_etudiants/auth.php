<?php
session_start();
require_once "pdoclasses.php";

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = htmlspecialchars($_POST['username']);
    $password = $_POST['password'];

    $userManager = new UtilisateurManager();
    
    // D'abord vérifier si l'utilisateur existe
    if(!$userManager->userExists($username)) {
        header('Location: index.php?error=2'); // Utilisateur non inscrit
        exit();
    }
    
    // Ensuite tenter l'authentification
    $user = $userManager->authenticate($username, $password);

    if($user) {
        $_SESSION['user'] = [
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'role' => $user->getRole()
        ];
        header('Location: dashboard.php');
        exit();
    } else {
        header('Location: index.php?error=1'); // Mot de passe incorrect
        exit();
    }
}

// Si la requête n'est pas POST
header('Location: index.php');
exit();
?>
