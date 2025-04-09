<?php
require "Connexionbd.php";
$cnx=Connexionbd::getInstance();
$bs=$cnx->getPDO();
$query="select id,name,birthday from etudiant";
$rep=$bs->query($query)->fetchAll();


?>
<html>
<head>
    <title>etudiant</title>
  
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        table.table thead tr th {
            background-color:aquamarine !important;
        }
        h1{
            position: relative;
            margin-left: 20%;
            color:lightblue;
            font-family: Georgia, 'Times New Roman', Times, serif;
            font-style: italic;
            
        }
     
        
    </style>
</head>
<body>
 <div> <?php echo '<h1>Liste des etudiants dans la base  '.$cnx->getbase().'</h1>'; 
?> 

<table  class="table table-bordered table-hover" style="width: 80%;margin-left:10%">
  <thead>
    <tr>
      <th scope="col">id</th>
      <th scope="col">name</th>
      <th scope="col">birthday</th>
    </tr>
  </thead>
  <tbody>
  
  <?php foreach ($rep as $etudiant): ?>
            <tr>
                <td><?= htmlspecialchars($etudiant['id']) ?></td>
                <td><?= htmlspecialchars($etudiant['name']) ?></td>
                <td><?= htmlspecialchars($etudiant['birthday']) ?></td>
                <td>
                    <form method="post" action="etudiantdetail.php" style="display: inline;">
                        <input type="hidden" name="id" value="<?= $etudiant['id'] ?>">
                        <input type="hidden" name="name" value="<?= $etudiant['name'] ?>">
                        <input type="hidden" name="birthday" value="<?= $etudiant['birthday'] ?>">
                        <img src="letter-i.png" style="width: 50px;" 
                             onclick="this.closest('form').submit()"
                             title="Voir détaille etudiant ">
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
</table>

</body>

</html>
