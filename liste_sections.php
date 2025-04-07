<?php
session_start();
require_once "pdoclasses.php";

if(!isset($_SESSION['user'])) {
    header('Location: pdoweb.php');
    exit();
}

$userManager = new UtilisateurManager();
$currentUser = $userManager->getUserById($_SESSION['user']['id']);
$sectionManager = new SectionManager();
$etudiantManager = new EtudiantManager();

$sections = $sectionManager->getAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Sections</title>
    
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
        .id-column {
            width: 80px;
        }
        .badge-count {
            background-color: #e8fff3;
            color: #50cd89;
            font-size: 0.85em;
        }
        .action-btns {
            white-space: nowrap;
        }
    </style>
</head>
<body>
    

    <div class="container mt-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-layers-fill me-2"></i>Liste des Sections</h5>
                
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
                <table id="sectionsTable" class="table table-hover">
                    <thead>
                        <tr>
                            <th class="id-column">ID</th>
                            <th>Désignation</th>
                            <th>Description</th>
                            <th>Étudiants inscrits</th>
                            <th class="text-end">Actions</th>
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
                            <td class="text-end action-btns">
                                <a href="details_section.php?id=<?= $section->getId() ?>" 
                                   class="btn btn-sm btn-info"
                                   title="Voir les étudiants de cette section">
                                    <i class="bi bi-eye-fill"></i>
                                </a>
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
        var table = $('#sectionsTable').DataTable({
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'copy',
                    text: 'Copy',
                    className: 'd-none export-btn',
                    exportOptions: {
                        columns: [0, 1, 2, 3] // Exclure la colonne Actions
                    }
                },
                {
                    extend: 'excel',
                    text: 'Excel',
                    className: 'd-none export-btn',
                    exportOptions: {
                        columns: [0, 1, 2, 3]
                    }
                },
                {
                    extend: 'csv',
                    text: 'CSV',
                    className: 'd-none export-btn',
                    exportOptions: {
                        columns: [0, 1, 2, 3]
                    }
                },
                {
                    extend: 'pdf',
                    text: 'PDF',
                    className: 'd-none export-btn',
                    exportOptions: {
                        columns: [0, 1, 2, 3]
                    },
                    customize: function (doc) {
                        doc.defaultStyle.fontSize = 10;
                        doc.styles.tableHeader.fontSize = 11;
                        doc.title = 'Liste des Sections';
                    }
                }
            ],
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json'
            },
            columnDefs: [
                { orderable: false, targets: [4] } // Désactiver le tri sur la colonne Actions
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