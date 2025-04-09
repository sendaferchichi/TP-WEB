<?php
class Session {
    private static $iscreated = false;
    private static $s;
    
    private function __construct() {
        session_start();
        self::$iscreated = true;
    }
    
    public static function getInstance() {
        if (!self::$iscreated) {
            self::$s = new self();
        }
        return self::$s;
    }
    
    public function set($key, $value) {
        $_SESSION[$key] = $value;
    }
    
    public function get($key) {
        return $_SESSION[$key] ?? null;
    }
    
    public function __toString() {
        $message = "";
        foreach ($_SESSION as $key => $value) {
            $message .= 'Clé: '.$key.' Valeur: '.$value.' ';
        }
        return self::$iscreated ? 'Session active: '.$message : 'Session inactive';
    }
    
    public function delete() {
        session_unset();
        session_destroy();
        self::$iscreated = false;
        self::$s = null;
    }
    
    public function nbvisite() {
        if (!isset($_SESSION['nb'])) {
            $this->set("nb", 1);
            return '<div class="alert alert-success text-center">Bienvenue sur notre plateforme!</div>';
        } else {
            $this->set("nb", $this->get('nb') + 1);
            return '<div class="alert alert-info text-center">Merci pour votre fidélité, c\'est votre '.$this->get("nb").'ème visite.</div>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compteur de visites</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
        }
        .center-container {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 80vh;
        }
        .visit-card {
            width: 100%;
            max-width: 500px;
            border: none;
            border-radius: 15px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            background: white;
            overflow: hidden;
        }
        .welcome-header {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            padding: 1.5rem;
            text-align: center;
        }
        .visit-content {
            padding: 2rem;
            text-align: center;
        }
        .reset-btn {
            background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
            border: none;
            padding: 0.5rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .reset-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .session-info {
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1.5rem;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="center-container">
        <div class="visit-card">
            <div class="welcome-header">
                <h2>Compteur de Visites</h2>
                <p class="mb-0">Nous sommes ravis de vous accueillir</p>
            </div>
            
            <div class="visit-content">
                <?php
                $s = Session::getInstance();
                
                if (isset($_POST['reset'])) {
                    $s->delete();
                    header("Refresh:0");
                }
                
                if (!isset($_SESSION['nb'])) {
                    echo '<div class="alert alert-success">
                            <i class="bi bi-emoji-smile"></i> Bienvenue sur notre plateforme!
                          </div>';
                    $s->set("nb", 1);
                } else {
                    $s->set("nb", $s->get('nb') + 1);
                    echo '<div class="alert alert-primary">
                            <i class="bi bi-arrow-repeat"></i> Merci pour votre fidélité!<br>
                            <strong>Visite n°'.$s->get("nb").'</strong>
                          </div>';
                }
                ?>
                
                <form method="post" class="mt-4">
                    <button type="submit" name="reset" class="btn reset-btn text-white">
                        <i class="bi bi-arrow-counterclockwise"></i> Réinitialiser
                    </button>
                </form>
                
                
            </div>
        </div>
    </div>


    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
   
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>