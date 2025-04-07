<?php
require_once "pdoclasses.php";
session_start();

if(!isset($_GET['id']) || !isset($_SESSION['user'])) {
    header("Location: liste_sections.php");
    exit();
}

$id = $_GET['id'];
$sectionManager = new SectionManager();
$etudiantManager = new EtudiantManager();

$section = $sectionManager->getById($id);
if(!$section) {
    header("Location: liste_sections.php");
    exit();
}

$etudiants = $etudiantManager->getBySectionId($id);
$return_page = (isset($_SESSION['user']['role']) && $_SESSION['user']['role']!='user') ? 
               'liste_sections_admin.php' : 'liste_sections.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Détails Section</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .card-header {
            border-bottom: none;
            background-color: #0d6efd;
            color: white;
        }
        .info-label {
            font-weight: 500;
            color: #495057;
            width: 200px;
            display: inline-block;
        }
        .student-card {
            transition: transform 0.2s;
        }
        .student-card:hover {
            transform: translateY(-5px);
        }
        .student-photo {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #0d6efd;
        }
        .badge-section {
            font-size: 1rem;
            padding: 0.5em 0.8em;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="bi bi-collection me-2"></i>Détails de la section</h5>
                <a href="<?= $return_page ?>" class="btn btn-light btn-sm">
                    <i class="bi bi-arrow-left"></i> Retour
                </a>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <span class="info-label">ID Section:</span> 
                                <span class="badge bg-secondary"><?= $section->getId() ?></span>
                            </li>
                            <li class="list-group-item">
                                <span class="info-label">Désignation:</span> 
                                <span class="badge bg-primary badge-section">
                                    <?= htmlspecialchars($section->getDesignation()) ?>
                                </span>
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <div class="list-group-item">
                            <span class="info-label">Description:</span> 
                            <p class="mt-2"><?= htmlspecialchars($section->getDescription()) ?></p>
                        </div>
                    </div>
                </div>

                <h5 class="mb-3"><i class="bi bi-people-fill me-2"></i>Étudiants inscrits (<?= count($etudiants) ?>)</h5>
                
                <?php if(count($etudiants) > 0): ?>
                    <div class="row">
                        <?php foreach($etudiants as $etudiant): ?>
                            <div class="col-md-4 mb-3">
                                <div class="card student-card h-100">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <img src="<?= $etudiant->getImage() ? $etudiant->getImage() : 'default.jpg' ?>" 
                                                 class="student-photo me-3" 
                                                 alt="Photo de <?= htmlspecialchars($etudiant->getName()) ?>">
                                            <div>
                                                <h6 class="card-title mb-1"><?= htmlspecialchars($etudiant->getName()) ?></h6>
                                                <p class="card-text text-muted small mb-1">
                                                    <?= date('d/m/Y', strtotime($etudiant->getBirthday())) ?>
                                                    (<?= $etudiant->getAge() ?> ans)
                                                </p>
                                                <a href="details_etudiant.php?id=<?= $etudiant->getId() ?>" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    Voir détails
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        Aucun étudiant inscrit dans cette section pour le moment.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>