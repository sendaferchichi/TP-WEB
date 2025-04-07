<?php
session_start();
require_once "pdoclasses.php";

if(!isset($_SESSION['user'])) {
    header('Location: pdoweb.php');
    exit();
}

$userManager = new UtilisateurManager();
$currentUser = $userManager->getUserById($_SESSION['user']['id']);
$etudiantManager = new EtudiantManager();
$sectionManager = new SectionManager();

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(isset($_POST['delete'])) {
        $etudiantManager->delete($_POST['id']);
        $_SESSION['success'] = "Étudiant supprimé avec succès";
    }
    header('Location: liste_etudiants.php');
    exit();
}

$etudiants = $etudiantManager->getAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Étudiants</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        .card {
            border: none;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border-radius: 0.5rem;
        }
        .card-header {
            background: transparent;
            border-bottom: 1px solid #eee;
            padding: 1rem 1.5rem;
        }
        .dropdown-menu {
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border: none;
        }
        .btn-export {
            background-color: #f1faff;
            color: #009ef7;
            border: none;
        }
        .btn-export:hover {
            background-color: #e1f0ff;
        }
        .student-img {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 50%;
        }
        .action-btns {
            white-space: nowrap;
        }
        .id-column {
            width: 80px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-people-fill me-2"></i>Liste des Étudiants</h5>
                
                <div class="dropdown">
                    <button class="btn btn-export dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
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
            
            <div class="card-body">
                <?php if(isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <table id="etudiantsTable" class="table table-hover">
                    <thead>
                        <tr>
                            <th class="id-column">ID</th>
                            <th>Photo</th>
                            <th>Nom</th>
                            <th>Date de naissance</th>
                            <th>Section</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($etudiants as $etudiant): ?>
                        <tr>
                            <td><?= $etudiant['id'] ?></td>
                            <td>
                                <img src="<?= $etudiant['image'] ?? 'defaut.jpg' ?>" 
                                     class="student-img" 
                                     alt="<?= htmlspecialchars($etudiant['name']) ?>">
                            </td>
                            <td><?= htmlspecialchars($etudiant['name']) ?></td>
                            <td><?= date('d/m/Y', strtotime($etudiant['birthday'])) ?></td>
                            <td><?= htmlspecialchars($etudiant['designation']) ?></td>
                            <td class="text-end action-btns">
                                <a href="details_etudiant.php?id=<?= $etudiant['id'] ?>" 
                                   class="btn btn-sm btn-info">
                                    <i class="bi bi-eye-fill"></i>
                                </a>
                                <?php if($currentUser->isAdmin()): ?>
                                <a href="edit_etudiant.php?id=<?= $etudiant['id'] ?>" 
                                   class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil-fill"></i>
                                </a>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="id" value="<?= $etudiant['id'] ?>">
                                    <button type="submit" name="delete" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Supprimer cet étudiant?')">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
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
        var table = $('#etudiantsTable').DataTable({
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'copy',
                    text: 'Copy',
                    className: 'd-none export-btn',
                    exportOptions: {
                        columns: ':visible'
                    }
                },
                {
                    extend: 'excel',
                    text: 'Excel',
                    className: 'd-none export-btn',
                    exportOptions: {
                        columns: ':visible'
                    }
                },
                {
                    extend: 'csv',
                    text: 'CSV',
                    className: 'd-none export-btn',
                    exportOptions: {
                        columns: ':visible'
                    }
                },
                {
                    extend: 'pdf',
                    text: 'PDF',
                    className: 'd-none export-btn',
                    exportOptions: {
                        columns: ':visible'
                    },
                    customize: function (doc) {
                        doc.defaultStyle.fontSize = 10;
                        doc.styles.tableHeader.fontSize = 11;
                        doc.title = 'Liste des Étudiants';
                    }
                }
            ],
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json'
            },
            columnDefs: [
                { orderable: false, targets: [5] } // Désactiver le tri sur la colonne Actions
            ]
        });

        $('.export-action').click(function(e) {
            e.preventDefault();
            var exportType = $(this).data('type');
            $('.buttons-' + exportType).trigger('click');
        });
    });
    </script>
</body>
</html>