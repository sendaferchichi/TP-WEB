<?php
require_once "pdoclasses.php";
session_start();

// Vérification admin
$userManager = new UtilisateurManager();
$currentUser = isset($_SESSION['user']) ? 
    (is_array($_SESSION['user']) ? $userManager->getUserById($_SESSION['user']['id']) : $_SESSION['user']) 
    : null;

if(!$currentUser || !$currentUser->isAdmin()) {
    header("Location: login.php");
    exit();
}

$etudiantManager = new EtudiantManager();
$sectionManager = new SectionManager();

// Traitement des actions
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ajout ou modification
    $id = $_POST['id'] ?? null;
    $name = $_POST['name'];
    $birthday = $_POST['birthday'];
    $sectionId = $_POST['section_id'];
    
    // Gestion de l'image
    $image = null;
    if(isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        if(!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        
        $imageName = uniqid().'_'.$_FILES['image']['name'];
        $targetPath = $uploadDir.$imageName;
        
        if(move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $image = $targetPath;
        }
    }
    
    $section = $sectionManager->getById($sectionId);
    
    if($id) {
        // Modification
        $etudiant = $etudiantManager->getById($id);
        if($etudiant) {
            // Conserver l'image existante si aucune nouvelle n'est uploadée
            if(!$image) $image = $etudiant->getImage();
            
            $etudiant->setName($name)
                    ->setBirthday($birthday)
                    ->setSection($section)
                    ->setImage($image);
            
            if($etudiantManager->update($etudiant)) {
                $_SESSION['message'] = ['type' => 'success', 'text' => 'Étudiant modifié avec succès'];
            }
        }
    } else {
        // Ajout
        $etudiant = new Etudiant($name, $birthday, $section, $image);
        if($etudiantManager->create($etudiant)) {
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Étudiant ajouté avec succès'];
        }
    }
    
    header("Location: liste_etudiants_admin.php");
    exit();
}

// Suppression
if(isset($_GET['action']) && $_GET['action'] === 'supprimer' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $etudiant = $etudiantManager->getById($id);
    
    if($etudiant) {
        $imagePath = $etudiant->getImage();
        if($etudiantManager->delete($id)) {
            if($imagePath && file_exists($imagePath)) unlink($imagePath);
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Étudiant supprimé'];
        }
    }
    
    header("Location: liste_etudiants_admin.php");
    exit();
}

$etudiants = $etudiantManager->getAll();
$sections = $sectionManager->getAll();
$editingId = $_GET['edit'] ?? null;
$etudiantToEdit = $editingId ? $etudiantManager->getById($editingId) : null;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Gestion Étudiants</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .student-photo { width: 40px; height: 40px; object-fit: cover; border-radius: 50%; }
        .student-photo-preview { width: 100px; height: 100px; object-fit: cover; border-radius: 5px; }
        .form-container { display: none; }
        .form-container.active { display: block; }
        [data-bs-toggle="form"] { cursor: pointer; }
    </style>
    
</head>
<body>
    <div class="container mt-4">
        <!-- Messages -->
        <?php if(isset($_SESSION['message'])): ?>
            <div class="alert alert-<?= $_SESSION['message']['type'] ?> alert-dismissible fade show">
                <?= $_SESSION['message']['text'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-people-fill"></i> Gestion des étudiants</h2>
            <button class="btn btn-primary" data-bs-toggle="form" data-target="#studentForm" data-reset="true">
                <i class="bi bi-plus-lg"></i> Ajouter
            </button>
            <a href="dashboard.php" class="btn btn-secondary">
    <i class="bi bi-arrow-left-circle"></i> Retour au dashboard
</a>
        </div>

        <!-- Formulaire (Ajout/Modification) -->
        <div id="studentForm" class="card mb-4 form-container <?= $editingId ? 'active' : '' ?>">
            <div class="card-body">
                <h5 class="card-title">
                    <?= $editingId ? '<i class="bi bi-pencil"></i> Modifier' : '<i class="bi bi-plus-lg"></i> Ajouter' ?> un étudiant
                </h5>
                
                <form method="POST" enctype="multipart/form-data" id="etudiantForm">
                    <input type="hidden" name="id" value="<?= $editingId ?>">
                    
                    <div class="row g-3">
                        <div class="col-md-2 text-center">
                            <img src="<?= $etudiantToEdit ? ($etudiantToEdit->getImage() ?: 'default.jpg') : 'default.jpg' ?>" 
                                 id="imagePreview" class="student-photo-preview mb-2">
                            <input type="file" name="image" id="imageInput" class="form-control form-control-sm" accept="image/*">
                        </div>
                        
                        <div class="col-md-10">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nom complet</label>
                                    <input type="text" name="name" class="form-control" 
                                           value="<?= $etudiantToEdit ? htmlspecialchars($etudiantToEdit->getName()) : '' ?>" required>
                                </div>
                                
                                <div class="col-md-3">
                                    <label class="form-label">Date de naissance</label>
                                    <input type="date" name="birthday" class="form-control" 
                                           value="<?= $etudiantToEdit ? htmlspecialchars($etudiantToEdit->getBirthday()) : '' ?>" required>
                                </div>
                                
                                <div class="col-md-3">
                                    <label class="form-label">Section</label>
                                    <select name="section_id" class="form-select" required>
                                        <?php foreach($sections as $section): ?>
                                            <option value="<?= $section->getId() ?>"
                                                <?= ($etudiantToEdit && $section->getId() == $etudiantToEdit->getSection()->getId()) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($section->getDesignation()) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="col-12">
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-check-lg"></i> Enregistrer
                                    </button>
                                    <button type="button" class="btn btn-secondary" data-bs-toggle="form" data-target="#studentForm">
                                        <i class="bi bi-x-lg"></i> Annuler
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tableau des étudiants -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Photo</th>
                                <th>Nom</th>
                                <th>Date naiss.</th>
                                <th>Section</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($etudiants as $etudiant): ?>
                                <tr>
                                    <td><?= $etudiant['id'] ?></td>
                                    <td>
                                        <img src="<?= !empty($etudiant['image']) && file_exists($etudiant['image']) 
                                            ? $etudiant['image'] 
                                            : 'default.jpg' ?>" 
                                            class="student-photo">
                                    </td>
                                    <td><?= htmlspecialchars($etudiant['name']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($etudiant['birthday'])) ?></td>
                                    <td>
                                        <span class="badge bg-primary">
                                            <?= htmlspecialchars($etudiant['designation'] ?? 'Non assigné') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="liste_etudiants_admin.php?edit=<?= $etudiant['id'] ?>" 
                                           class="btn btn-sm btn-outline-primary action-btn"
                                           title="Modifier"
                                           data-bs-toggle="form" 
                                           data-target="#studentForm">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="liste_etudiants_admin.php?action=supprimer&id=<?= $etudiant['id'] ?>" 
                                           class="btn btn-sm btn-outline-danger action-btn"
                                           title="Supprimer"
                                           onclick="return confirm('Êtes-vous sûr ?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                        <a href="details_etudiant.php?id=<?= $etudiant['id'] ?>" 
       class="btn btn-sm btn-outline-info action-btn"
       title="Voir détails">
        <i class="bi bi-eye"></i>
    </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Gestion des formulaires
        document.querySelectorAll('[data-bs-toggle="form"]').forEach(btn => {
            btn.addEventListener('click', function() {
                const target = document.getElementById(this.getAttribute('data-target').substring(1));
                const isActive = target.classList.contains('active');
                
                // Reset form si demandé
                if(this.getAttribute('data-reset') === 'true' && !isActive) {
                    document.getElementById('etudiantForm').reset();
                    document.getElementById('imagePreview').src = 'default.jpg';
                }
                
                // Basculer l'affichage
                document.querySelectorAll('.form-container').forEach(f => f.classList.remove('active'));
                if(!isActive) target.classList.add('active');
                
                // Scroll vers le formulaire
                if(!isActive) target.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            });
        });
        
        // Prévisualisation de l'image
        document.getElementById('imageInput').addEventListener('change', function(e) {
            if(this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('imagePreview').src = e.target.result;
                }
                reader.readAsDataURL(this.files[0]);
            }
        });
        
        // Activer le formulaire si on arrive avec ?edit=ID
        <?php if($editingId): ?>
            document.getElementById('studentForm').scrollIntoView({ behavior: 'smooth' });
        <?php endif; ?>
    </script>
</body>
</html>