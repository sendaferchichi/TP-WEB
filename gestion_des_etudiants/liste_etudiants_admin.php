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
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
    <style>
        .student-photo { width: 40px; height: 40px; object-fit: cover; border-radius: 50%; }
        .student-photo-preview { width: 100px; height: 100px; object-fit: cover; border-radius: 5px; }
        .form-container { display: none; }
        .form-container.active { display: block; }
        [data-bs-toggle="form"] { cursor: pointer; }
        .dt-buttons .btn {
            margin-right: 5px;
        }
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
            <div>
                <a href="dashboard.php" class="btn btn-secondary me-2">
                    <i class="bi bi-arrow-left-circle"></i> Dashboard
                </a>
                <button class="btn btn-primary" data-bs-toggle="form" data-target="#studentForm" data-reset="true">
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

    
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="studentsTable" class="table table-hover">
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
                                        <a href="details_etudiant.php?id=<?= $etudiant['id'] ?>" 
                                           class="btn btn-sm btn-outline-info action-btn"
                                           title="Voir détails">
                                            <i class="bi bi-eye"></i>
                                        </a>
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
    
        var table = $('#studentsTable').DataTable({
            dom: '<"top"Bf>rt<"bottom"lip><"clear">',
            buttons: [
                {
                    extend: 'copyHtml5',
                    text: '<i class="bi bi-clipboard"></i> Copier',
                    title: 'Liste des Étudiants',
                    className: 'btn btn-outline-secondary',
                    exportOptions: {
                        columns: [0, 2, 3, 4], 
                        format: {
                            body: function(data, row, column, node) {
                                
                                if (column === 3) { // Colonne Date
                                    return data.replace(/\//g, '-'); 
                                }
                                return data;
                            }
                        }
                    }
                },
                {
                    extend: 'excelHtml5',
                    text: '<i class="bi bi-file-earmark-excel"></i> Excel',
                    title: 'Liste des Étudiants',
                    className: 'btn btn-outline-success',
                    exportOptions: {
                        columns: [0, 2, 3, 4]
                    }
                },
                {
                    extend: 'csvHtml5',
                    text: '<i class="bi bi-filetype-csv"></i> CSV',
                    title: 'Liste des Étudiants',
                    className: 'btn btn-outline-primary',
                    exportOptions: {
                        columns: [0, 2, 3, 4]
                    }
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="bi bi-filetype-pdf"></i> PDF',
                    title: 'Liste des Étudiants',
                    className: 'btn btn-outline-danger',
                    exportOptions: {
                        columns: [0, 2, 3, 4]
                    },
                    customize: function(doc) {
                        doc.defaultStyle.fontSize = 10;
                        doc.styles.tableHeader.fontSize = 11;
                    }
                }
            ],
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json'
            },
            columnDefs: [
                { orderable: false, targets: [1, 5] } // Désactiver le tri sur les colonnes Photo et Actions
            ]
        });

        // Gestion du dropdown d'export
        $('.export-action').on('click', function(e) {
            e.preventDefault();
            var exportType = $(this).data('type');
            
            // Déclencher le bon bouton d'export
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

        // Gestion de l'affichage du formulaire
        $('[data-bs-toggle="form"]').on('click', function() {
            const target = $(this).data('target');
            $(target).toggleClass('active');
            
            if ($(target).hasClass('active')) {
                $('html, body').animate({
                    scrollTop: $(target).offset().top - 20
                }, 500);
            }
        });

        // Prévisualisation de l'image
        $('#imageInput').on('change', function(e) {
            if(this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#imagePreview').attr('src', e.target.result);
                }
                reader.readAsDataURL(this.files[0]);
            }
        });
        
        // Activer le formulaire si on arrive avec ?edit=ID
        <?php if($editingId): ?>
            $('#studentForm').scrollIntoView({ behavior: 'smooth' });
        <?php endif; ?>
    });
    </script>
</body>
</html>