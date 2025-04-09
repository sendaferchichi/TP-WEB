<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="node_modules/bootstrap/dist/css/bootstrap.min.css">
    <title>MOYENNE </title>
    <style>
        .maindiv {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 30px;
            margin: 10px auto;
            padding: 10px;
            max-width: 900px;
        }
        .divetudiant {
            width: 40%;
            
            border-radius: 5px;
            overflow: hidden;
        }
        .table-header {
            background-color:rgb(63, 66, 68);
            color: white;
            border: 1px;
            border-color: grey;
            font-weight: bold;
        }
        .moyenne {
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <?php 
    require "exercice1.php";
    
    function affichage(Etudiant $e) {
        $nom = $e->getNom();
        $tab = $e->getTab();
        $moyenne = $e->moyenne();
        
        echo '<div class="divetudiant">';
        echo '<table  class="table">';
        echo '<thead><tr class="table-header"><th style="background-color:lightgray;" colspan="2">'.$nom.'</th></tr></thead>';
        echo '<tbody>';
        
        foreach ($tab as $note) {
            $class = ""; 
            if ($note < 10) $class = 'table-danger';
            elseif ($note > 10) $class = 'table-success';
            else $class = 'table-warning';
            
            echo '<tr class="'.$class.'"><td>'.$note.'</td></tr>';
        }
        
    
        echo '<tr class="table-info moyenne"><td colspan="2" class="moyenne">Votre moyenne est: '.$moyenne.'</td></tr>';
        
        echo '</tbody></table>';
        echo '</div>';
    }

    echo '<div class="maindiv">';
    $etudiant1 = new Etudiant("Aymen", 11, 13, 18, 7, 10, 13, 2, 5, 1);
    affichage($etudiant1);
  
    
    $etudiant2 = new Etudiant("Skander", 15, 9, 8, 16);
    affichage($etudiant2);
    
    
    echo '</div>';
    ?>
    
    <script src="node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

