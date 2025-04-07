<?php
class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        $this->pdo = new PDO(
            'mysql:host=localhost;dbname=dbphp',
            'root',
            '123456789',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance->pdo;
    }
}

class Utilisateur {
    private $id;
    private $username;
    private $email;
    private $role;

    public function __construct($username, $email, $role = 'user') {
        $this->username = $username;
        $this->email = $email;
        $this->role = $role;
    }
    
    public function getId() { return $this->id; }
    public function getUsername() { return $this->username; }
    public function getEmail() { return $this->email; }
    public function getRole() { return $this->role; }
    public function isAdmin() { return $this->role === 'admin'; }

    public function setId($id) { $this->id = $id; }
    public function setUsername($username) { $this->username = $username; }
    public function setEmail($email) { $this->email = $email; }
    public function setRole($role) { $this->role = $role; }
}

class UtilisateurManager {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    public function create(Utilisateur $user, $password) {
        return $this->createUser(
            $user->getUsername(),
            $user->getEmail(),
            $password,
            $user->getRole()
        );
    }

  
    
        public function authenticate($username, $password) {
            $stmt = $this->pdo->prepare("SELECT * FROM utilisateurs WHERE username = :username AND password = :password");
            $stmt->execute([
                ':username' => $username,
                ':password' => $password 
            ]);
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if ($userData) {
                $user = new Utilisateur($userData['username'], $userData['email'], $userData['role']);
                $user->setId($userData['id']);
                return $user;
            }
            return false;
        }
    
        public function createUser($username, $email, $password, $role = 'user') {
            if($this->userExists($username)) {
                return false;
            }
            
            $stmt = $this->pdo->prepare("
                INSERT INTO utilisateurs (username, email, password, role) 
                VALUES (:username, :email, :password, :role)
            ");
            
            return $stmt->execute([
                ':username' => $username,
                ':email' => $email,
                ':password' => $password, 
                ':role' => $role
            ]);
        }
    

    public function userExists($username) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM utilisateurs WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetchColumn() > 0;
    }
    
 

    public function getUserById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($data) {
            $user = new Utilisateur($data['username'], $data['email'], $data['role']);
            $user->setId($data['id']);
            return $user;
        }
        return null;
    }
}
class Etudiant {
    private $id;
    private $name;
    private $birthday;
    private $image;
    private $section;

    public function __construct($name, $birthday, $section, $image = null) {
        $this->name = $name;
        $this->birthday = $birthday;
        $this->section = $section;
        $this->image = $image;
    }


    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function getBirthday() {
        return $this->birthday;
    }

    public function getImage() {
        return $this->image;
    }

    public function getSection() {
        return $this->section;
    }


    public function setId($id) {
        $this->id = $id;
        return $this; 
    }

    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function setBirthday($birthday) {
        $this->birthday = $birthday;
        return $this;
    }

    public function setImage($image) {
        $this->image = $image;
        return $this;
    }

    public function setSection($section) {
        $this->section = $section;
        return $this;
    }

    public function getAge() {
        $birthday = new DateTime($this->birthday);
        $now = new DateTime();
        return $now->diff($birthday)->y;
    }
}


class EtudiantManager {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    public function create(Etudiant $etudiant) {
        $stmt = $this->pdo->prepare("
            INSERT INTO etudiants (name, birthday, image, section_id)
            VALUES (:name, :birthday, :image, :section_id)
        ");
        $stmt->execute([
            ':name' => $etudiant->getName(),
            ':birthday' => $etudiant->getBirthday(),
            ':image' => $etudiant->getImage(),
            ':section_id' => $etudiant->getSection()->getId()
        ]);
        $etudiant->setId($this->pdo->lastInsertId());
    }
    public function update(Etudiant $etudiant) {
        $stmt = $this->pdo->prepare("
            UPDATE etudiants 
            SET name = :name, 
                birthday = :birthday, 
                image = :image, 
                section_id = :section_id 
            WHERE id = :id
        ");
        
        return $stmt->execute([
            ':name' => $etudiant->getName(),
            ':birthday' => $etudiant->getBirthday(),
            ':image' => $etudiant->getImage(),
            ':section_id' => $etudiant->getSection()->getId(),
            ':id' => $etudiant->getId()
        ]);
    }
    public function getById($id) {
        $stmt = $this->pdo->prepare("
            SELECT e.*, s.designation, s.description 
            FROM etudiants e
            LEFT JOIN sections s ON e.section_id = s.id
            WHERE e.id = ?
        ");
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($data) {
            $section = new Section($data['section_id'], $data['designation'], $data['description']);
            $etudiant = new Etudiant(
                $data['name'],
                $data['birthday'],
                $section,
                $data['image']
            );
            $etudiant->setId($data['id']);
            return $etudiant;
        }
        return null;
    }
    public function getAll() {
        $stmt = $this->pdo->query("
            SELECT e.*, s.designation 
            FROM etudiants e 
            LEFT JOIN sections s ON e.section_id = s.id
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function delete($id) {
        try {
            
            $stmt = $this->pdo->prepare("SELECT image FROM etudiants WHERE id = ?");
            $stmt->execute([$id]);
            $image = $stmt->fetchColumn();
            
            if ($image && file_exists("uploads/$image")) {
                unlink("uploads/$image");
            }
    
          
            $stmt = $this->pdo->prepare("DELETE FROM etudiants WHERE id = ?");
            return $stmt->execute([$id]);
        } catch(PDOException $e) {
            error_log("Erreur suppression étudiant: " . $e->getMessage());
            return false;
        }
    }
    public function getBySectionId($sectionId) {
        $stmt = $this->pdo->prepare("
            SELECT e.*, s.designation as section_designation, s.description as section_description 
            FROM etudiants e
            LEFT JOIN sections s ON e.section_id = s.id
            WHERE e.section_id = ?
            ORDER BY e.name
        ");
        $stmt->execute([$sectionId]);
        
        $etudiants = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $section = new Section(
                $row['section_id'], // ID
                $row['section_designation'], // Désignation
                $row['section_description']  // Description
            );
            
            $etudiant = new Etudiant(
                $row['name'],
                $row['birthday'],
                $section,
                $row['image']
            );
            $etudiant->setId($row['id']);
            $etudiants[] = $etudiant;
        }
        return $etudiants;
    }
}

class Section {
    private $id;
    private $designation;
    private $description;


    public function __construct($id = null, $designation = '', $description = '') {
        $this->id = $id;
        $this->designation = $designation;
        $this->description = $description;
    }

 
    public function getId() { return $this->id; }
    public function getDesignation() { return $this->designation; }
    public function getDescription() { return $this->description; }

    public function setId($id) { $this->id = $id; }
    public function setDesignation($designation) { $this->designation = $designation; }
    public function setDescription($description) { $this->description = $description; }
}

class SectionManager {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    
    public function getAll() {
        $stmt = $this->pdo->query("SELECT * FROM sections");
        
        $sections = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $section = new Section();
            $section->setId($row['id']);
            $section->setDesignation($row['designation']);
            $section->setDescription($row['description']);
            $sections[] = $section;
        }
        return $sections;
    }
    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM sections WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($data) {
            return new Section(
                $data['id'],           // ID
                $data['designation'],  // Désignation
                $data['description']   // Description
            );
        }
        return null;
    }
    public function create(Section $section) {
        // 1. Validation des données
        if (empty(trim($section->getDesignation()))) {
            error_log("Erreur: Désignation vide");
            return false;
        }
    
        try {
            // 2. Préparation avec vérification
            $stmt = $this->pdo->prepare("
                INSERT INTO sections (designation, description) 
                VALUES (:designation, :description)
            ");
            
            if (!$stmt) {
                error_log("Erreur préparation: " . print_r($this->pdo->errorInfo(), true));
                return false;
            }
    
            // 3. Exécution avec paramètres
            $success = $stmt->execute([
                ':designation' => $section->getDesignation(),
                ':description' => $section->getDescription()
            ]);
            
            // 4. Gestion du résultat
            if ($success) {
                $newId = $this->pdo->lastInsertId();
                
                if (!$newId) {
                    error_log("Erreur: Aucun ID retourné");
                    return false;
                }
                
                $section->setId($newId);
                error_log("Nouvelle section créée avec ID: " . $newId); // Log de suivi
                return true;
            }
            
            // 5. Gestion des erreurs SQL
            $errorInfo = $stmt->errorInfo();
            if ($errorInfo[1] == 1062) { // Code erreur MySQL pour doublon
                error_log("Erreur: Désignation déjà existante");
            } else {
                error_log("Erreur SQL: " . print_r($errorInfo, true));
            }
            
            return false;
            
        } catch (PDOException $e) {
            
            error_log("Exception PDO: " . $e->getMessage());
            return false;
        }
    }

    public function update(Section $section) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE sections 
                SET designation = :designation, 
                    description = :description 
                WHERE id = :id
            ");
            
            $success = $stmt->execute([
                ':designation' => $section->getDesignation(),
                ':description' => $section->getDescription(),
                ':id' => $section->getId()
            ]);
            
           
            if($success && $stmt->rowCount() === 0) {
                error_log("Aucune ligne modifiée pour la section ID: ".$section->getId());
                return false;
            }
            
            return $success;
        } catch(PDOException $e) {
            error_log("Erreur modification section: " . $e->getMessage());
            return false;
        }
    }
  
public function getPdo() {
    return $this->pdo;
}

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM sections WHERE id = ?");
        return $stmt->execute([$id]);
    }
}

?>