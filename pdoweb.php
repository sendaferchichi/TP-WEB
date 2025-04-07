<?php
session_start();
// Redirige si déjà connecté
if(isset($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit();
}

$error_message = '';
if(isset($_GET['error'])) {
    switch($_GET['error']) {
        case '1': $error_message = 'Identifiants incorrects'; break;
        case '2': $error_message = 'Utilisateur non inscrit'; break;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Students Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #f8f9fc;
        }
        
        body {
            background-color: var(--secondary-color);
            height: 100vh;
            display: flex;
            align-items: center;
        }
        
        .login-container {
            max-width: 400px;
            margin: 0 auto;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        
        .brand-logo {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.25);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="text-center mb-4">
                <i class="fas fa-graduation-cap brand-logo"></i>
                <h2>Students Management</h2>
                <p class="text-muted">Veuillez vous connecter</p>
                
                <?php if($error_message): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error_message) ?>
                </div>
                <?php endif; ?>
            </div>
            
            <form action="auth.php" method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label">Nom d'utilisateur</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="password" class="form-label">Mot de passe</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                </div>
                
                <div class="d-grid gap-2 mb-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt me-2"></i> Se connecter
                    </button>
                </div>
            </form>

            <div class="text-center mt-3">
                <p class="mb-2">Pas encore de compte ?</p>
                <a href="inscription.php" class="btn btn-outline-primary">
                    <i class="fas fa-user-plus me-2"></i>S'inscrire
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>