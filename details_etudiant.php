<?php
require_once "pdoclasses.php";
session_start();

if(!isset($_GET['id']) || !isset($_SESSION['user'])) {
    header("Location: liste_etudiants.php");
    exit();
}

$id = $_GET['id'];
$etudiantManager = new EtudiantManager();
$sectionManager = new SectionManager();

$etudiant = $etudiantManager->getById($id);
if(!$etudiant) {
    header("Location: liste_etudiants.php");
    exit();
}

$section = $etudiant->getSection();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Détails Étudiant</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 255, 247, 0.1);
        }
        .card-header {
            border-bottom: none;
        }
        .info-label {
            font-weight: 500;
            color:rgb(1, 101, 177);
            width: 150px;
            display: inline-block;
        }
        .student-photo {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid rgb(1, 49, 121);
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div  class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                <h5><i class="bi bi-person-badge me-2"></i>Fiche étudiant</h5>
                <a href="liste_etudiants.php" class="btn btn-light btn-sm">
                    <i class="bi bi-arrow-left"></i> Retour
                </a>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 text-center mb-3">
                        <img src="<?= $etudiant->getImage() ? $etudiant->getImage() : 'default.jpg' ?>" 
                             class="student-photo mb-3" 
                             alt="Photo de <?= htmlspecialchars($etudiant->getName()) ?>">
                    </div>
                    <div class="col-md-8">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <span class="info-label">ID:</span> <?= $etudiant->getId() ?>
                            </li>
                            <li class="list-group-item">
                                <span class="info-label">Nom complet:</span> <?= htmlspecialchars($etudiant->getName()) ?>
                            </li>
                            <li class="list-group-item">
                                <span class="info-label">Date de naissance:</span> 
                                <?= date('d/m/Y', strtotime($etudiant->getBirthday())) ?>
                                (<?= (new DateTime($etudiant->getBirthday()))->diff(new DateTime)->y ?> ans)
                            </li>
                            <li class="list-group-item">
                                <span class="info-label">Section:</span> 
                                <span class="badge bg-primary">
                                    <?= htmlspecialchars($section->getDesignation()) ?>
                                </span>
                            </li>
                            <li class="list-group-item">
                                <span class="info-label">Description section:</span> 
                                <?= htmlspecialchars($section->getDescription()) ?>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>