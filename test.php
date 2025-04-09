<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <title>CALCUL DE MOYENNE</title>
    <style>
        .maindiv {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 30px;
            margin: 20px auto;
            padding: 20px;
            max-width: 1200px;
        }
        .divetudiant {
            width: 45%;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .table-header {
            background-color: #3f4244;
            color: white;
            font-weight: bold;
        }
        .moyenne {
            border-radius: 0 0 10px 10px;
            font-weight: bold;
            text-align: center;
        }
        .form-container {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .note-inputs {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        .note-input {
            width: 80px;
        }
        .add-note-btn {
            margin-top: 10px;
        }
        .reset-btn {
            margin-top: 20px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-4">
        <h1 class="text-center mb-4">Système de Calcul de Moyenne</h1>
        
        <?php
        session_start();
        require "exercice1.php";

       
        if (!isset($_SESSION['etudiants'])) {
            $_SESSION['etudiants'] = [];
        }

       
        if (isset($_GET['reset'])) {
            $_SESSION['etudiants'] = [];
            header("Location: ".strtok($_SERVER["REQUEST_URI"], '?'));
            exit();
        }

       
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['studentName'])) {
            $name = $_POST['studentName'] ?? '';
            $notes = array_filter($_POST['notes'] ?? [], function($note) {
                return $note !== '';
            });
            
            if (!empty($name) && !empty($notes)) {
                $etudiant = new Etudiant($name, ...array_map('floatval', $notes));
                $_SESSION['etudiants'][] = serialize($etudiant);
            }
        }
        
        function affichage(Etudiant $e) {
            $nom = $e->getNom();
            $tab = $e->getTab();
            $moyenne = $e->moyenne();
            
            echo '<div class="divetudiant">';
            echo '<table class="table table-hover mb-0">';
            echo '<thead><tr class="table-header"><th style="background-color:#e9ecef;color:black;" colspan="2">'.$nom.'</th></tr></thead>';
            echo '<tbody>';
            
            foreach ($tab as $note) {
                $class = $note < 10 ? 'table-danger' : ($note > 10 ? 'table-success' : 'table-warning');
                echo '<tr class="'.$class.'"><td>Note</td><td>'.$note.'/20</td></tr>';
            }
            
            $moyenneClass ='table-info';
            echo '<tr><td colspan="2" class="'.$moyenneClass.' moyenne">Moyenne: '.number_format($moyenne, 2).'/20 - '.$e->admis().'</td></tr>';
            echo '</tbody></table>';
            echo '</div>';
        }
        ?>

        <div class="form-container">
            <h2 class="mb-3">Ajouter un étudiant</h2>
            <form id="studentForm" method="post">
                <div class="mb-3">
                    <label for="studentName" class="form-label">Nom de l'étudiant</label>
                    <input type="text" class="form-control" id="studentName" name="studentName" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <div id="notesContainer" class="note-inputs"></div>
                    <button type="button" class="btn btn-secondary add-note-btn" id="addNoteBtn">+ Ajouter une note</button>
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Calculer la moyenne</button>
                    <a href="?reset=1" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir réinitialiser tous les étudiants ?')">Réinitialiser</a>
                </div>
            </form>
        </div>
        
        <div class="maindiv" id="studentsContainer">
            <?php
            foreach ($_SESSION['etudiants'] as $etudiantSerialized) {
                $etudiant = unserialize($etudiantSerialized);
                affichage($etudiant);
            }
            ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const notesContainer = document.getElementById('notesContainer');
            const addNoteBtn = document.getElementById('addNoteBtn');
            const studentForm = document.getElementById('studentForm');
            let noteCount = 0;
            
            function addNoteInput(value = '') {
                const group = document.createElement('div');
                group.className = 'note-input-group d-flex align-items-center mb-2';
                
                const input = document.createElement('input');
                input.type = 'number';
                input.className = 'form-control note-input me-2';
                input.name = 'notes[]';
                input.placeholder = 'Note';
                input.min = '0';
                input.max = '20';
                input.step = '0.1';
                input.value = value;
                input.required = true;
                
                const deleteBtn = document.createElement('button');
                deleteBtn.type = 'button';
                deleteBtn.className = 'btn btn-sm btn-outline-danger';
                deleteBtn.innerHTML = '&times;';
                deleteBtn.onclick = function() {
                    group.remove();
                };
                
                group.appendChild(input);
                group.appendChild(deleteBtn);
                notesContainer.appendChild(group);
                noteCount++;
            }
            
            
            for (let i = 0; i < 3; i++) {
                addNoteInput();
            }
            
       
            addNoteBtn.addEventListener('click', function() {
                addNoteInput();
            });
            
          
            studentForm.addEventListener('submit', function(e) {
                const inputs = notesContainer.querySelectorAll('input');
                let isValid = true;
                
                inputs.forEach(input => {
                    if (input.value === '' || input.value < 0 || input.value > 20) {
                        input.classList.add('is-invalid');
                        isValid = false;
                    } else {
                        input.classList.remove('is-invalid');
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    alert('Veuillez entrer des notes valides (entre 0 et 20)');
                }
            });
        });
    </script>
</body>
</html>