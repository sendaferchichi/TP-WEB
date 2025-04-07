<?php
require_once "pdoclasses.php";
session_start();

$userManager = new UtilisateurManager();
$currentUser = isset($_SESSION['user']) ? 
    (is_array($_SESSION['user']) ? $userManager->getUserById($_SESSION['user']['id']) : $_SESSION['user']) 
    : null;

if(!$currentUser || !$currentUser->isAdmin()) {
    header("Location: login.php");
    exit();
}

$sectionManager = new SectionManager();
$etudiantManager = new EtudiantManager();

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $designation = trim($_POST['designation']);
    $description = trim($_POST['description']);
    
    if(empty($designation)) {
        $_SESSION['message'] = ['type' => 'danger', 'text' => 'La désignation est obligatoire'];
    } else {
        if($id) {
            // Modification
            $section = $sectionManager->getById($id);
            if($section) {
                $section->setDesignation($designation);
                $section->setDescription($description);
                
                if($sectionManager->update($section)) {
                    $_SESSION['message'] = ['type' => 'success', 'text' => 'Section modifiée avec succès'];
                } else {
                    $errorInfo = $sectionManager->getPdo()->errorInfo();
                    $_SESSION['message'] = ['type' => 'danger', 'text' => 'Erreur lors de la modification: '.$errorInfo[2]];
                }
            }
        } else {
            // Ajout
            $section = new Section(null, $designation, $description);
            $result = $sectionManager->create($section);
            
            if($result) {
                $_SESSION['message'] = ['type' => 'success', 'text' => 'Section ajoutée avec succès'];
            } else {
                $errorInfo = $sectionManager->getPdo()->errorInfo();
                $errorMsg = ($errorInfo[1] == 1062) ? "Cette section existe déjà" : "Erreur lors de l'ajout";
                $_SESSION['message'] = ['type' => 'danger', 'text' => $errorMsg];
            }
        }
    }
    
    header("Location: liste_sections_admin.php");
    exit();
}
// Suppression
if(isset($_GET['action']) && $_GET['action'] === 'supprimer' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $section = $sectionManager->getById($id);
    
    if($section) {
  
        $etudiants = $etudiantManager->getBySectionId($id);
        if(count($etudiants) === 0) {
            if($sectionManager->delete($id)) {
                $_SESSION['message'] = ['type' => 'success', 'text' => 'Section supprimée'];
            }
        } else {
            $_SESSION['message'] = ['type' => 'danger', 'text' => 'Impossible de supprimer - section non vide'];
        }
    }
    
    header("Location: liste_sections_admin.php");
    exit();
}

$sections = $sectionManager->getAll();
$editingId = $_GET['edit'] ?? null;
$sectionToEdit = $editingId ? $sectionManager->getById($editingId) : null;?>




<!DOCTYPE html>
<html>
<head>
    <title>Gestion Sections</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
    <style>
        .form-container { display: none; }
        .form-container.active { display: block; }
        [data-bs-toggle="form"] { cursor: pointer; }
        .badge-count {
            background-color: #e8fff3;
            color: #50cd89;
            font-size: 0.85em;
        }
        .dt-buttons .btn {
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        
        <?php if(isset($_SESSION['message'])): ?>
            <div class="alert alert-<?= $_SESSION['message']['type'] ?> alert-dismissible fade show">
                <?= $_SESSION['message']['text'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-collection"></i> Gestion des sections</h2>
            <div>
                <a href="dashboard.php" class="btn btn-secondary me-2">
                    <i class="bi bi-house-door"></i> Dashboard
                </a>
                <button class="btn btn-primary" data-bs-toggle="form" data-target="#sectionForm" data-reset="true">
                    <i class="bi bi-plus-lg"></i> Ajouter
                </button>
                <div class="dropdown d-inline">
                    <button class="btn btn-success dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-download me-1"></i> Exporter
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                        <li><a class="dropdown-item export-action" href="#" data-type="copy"><i class="bi bi-clipboard me-2"></i>Copier</a></li>
                        <li><a class="dropdown-item export-action" href="#" data-type="excel"><i class="bi bi-file-earmark-excel me-2"></i>Excel</a></li>
                        <li><a class="dropdown-item export-action" href="#" data-type="csv"><i class="bi bi-filetype-csv me-2"></i>CSV</a></li>
                        <li><a class="dropdown-item export-action" href="#" data-type="pdf"><i class="bi bi-filetype-pdf me-2"></i>PDF</a></li>
                    </ul>
                </div>
            </div>
        </div>

        
        <div id="sectionForm" class="card mb-4 form-container <?= $editingId ? 'active' : '' ?>">
            <div class="card-body">
                <h5 class="card-title">
                    <?= $editingId ? '<i class="bi bi-pencil"></i> Modifier' : '<i class="bi bi-plus-lg"></i> Ajouter' ?> une section
                </h5>
                
                <form method="POST">
                    <input type="hidden" name="id" value="<?= $editingId ?>">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Désignation</label>
                            <input type="text" name="designation" class="form-control" 
                                   value="<?= $sectionToEdit ? htmlspecialchars($sectionToEdit->getDesignation()) : '' ?>" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Description</label>
                            <input type="text" name="description" class="form-control" 
                                   value="<?= $sectionToEdit ? htmlspecialchars($sectionToEdit->getDescription()) : '' ?>">
                        </div>
                        
                        <div class="col-12">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-lg"></i> Enregistrer
                            </button>
                            <button type="button" class="btn btn-secondary" data-bs-toggle="form" data-target="#sectionForm">
                                <i class="bi bi-x-lg"></i> Annuler
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

   
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="sectionsTable" class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Désignation</th>
                                <th>Description</th>
                                <th>Étudiants</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($sections as $section): 
                                $etudiants = $etudiantManager->getBySectionId($section->getId());
                            ?>
                                <tr>
                                    <td><?= $section->getId() ?></td>
                                    <td><?= htmlspecialchars($section->getDesignation()) ?></td>
                                    <td><?= htmlspecialchars($section->getDescription()) ?></td>
                                    <td>
                                        <span class="badge badge-count">
                                            <?= count($etudiants) ?> étudiant(s)
                                        </span>
                                    </td>
                                    <td>
                                        <a href="details_section.php?id=<?= $section->getId() ?>" 
                                           class="btn btn-sm btn-outline-info action-btn"
                                           title="Voir détails">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="liste_sections_admin.php?edit=<?= $section->getId() ?>" 
                                           class="btn btn-sm btn-outline-primary action-btn"
                                           title="Modifier"
                                           data-bs-toggle="form" 
                                           data-target="#sectionForm">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="liste_sections_admin.php?action=supprimer&id=<?= $section->getId() ?>" 
                                           class="btn btn-sm btn-outline-danger action-btn"
                                           title="Supprimer"
                                           onclick="return confirm('Êtes-vous sûr ? Cette action est irréversible.');">
                                            <i class="bi bi-trash"></i>
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.70/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.70/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

    <script>
    $(document).ready(function() {
      
        var table = $('#sectionsTable').DataTable({
            dom: '<"top"Bf>rt<"bottom"lip><"clear">',
            buttons: [
                {
                    extend: 'copyHtml5',
                    text: '<i class="bi bi-clipboard"></i> Copier',
                    title: 'Liste des Sections',
                    className: 'btn btn-outline-secondary',
                    exportOptions: {
                        columns: [0, 1, 2, 3]
                    }
                },
                {
                    extend: 'excelHtml5',
                    text: '<i class="bi bi-file-earmark-excel"></i> Excel',
                    title: 'Liste des Sections',
                    className: 'btn btn-outline-success',
                    exportOptions: {
                        columns: [0, 1, 2, 3]
                    }
                },
                {
                    extend: 'csvHtml5',
                    text: '<i class="bi bi-filetype-csv"></i> CSV',
                    title: 'Liste des Sections',
                    className: 'btn btn-outline-primary',
                    exportOptions: {
                        columns: [0, 1, 2, 3]
                    }
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="bi bi-filetype-pdf"></i> PDF',
                    title: 'Liste des Sections',
                    className: 'btn btn-outline-danger',
                    exportOptions: {
                        columns: [0, 1, 2, 3]
                    }
                }
            ],
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json'
            },
            columnDefs: [
                { orderable: false, targets: [4] } 
            ]
        });

        $('.export-action').on('click', function(e) {
            e.preventDefault();
            var exportType = $(this).data('type');
            
         
            if (exportType === 'copy') {
                table.button('0').trigger();
            } else if (exportType === 'excel') {
                table.button('1').trigger();
            } else if (exportType === 'csv') {
                table.button('2').trigger();
            } else if (exportType === 'pdf') {
                table.button('3').trigger();
            }
        });

        $('[data-bs-toggle="form"]').on('click', function() {
            const target = $(this).data('target');
            $(target).toggleClass('active');
            
            if ($(target).hasClass('active')) {
                $('html, body').animate({
                    scrollTop: $(target).offset().top - 20
                }, 500);
            }
        });
    });
    </script>
</body>
</html>