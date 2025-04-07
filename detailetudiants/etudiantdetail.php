<?php
require "Connexionbd.php";
if(!isset($_POST['id'])){
    header("location:index.php");
}
else{//utulisation du form de page initiale pdo.php
    $id=$_POST['id'];
    $name=$_POST['name'];
    $birthday=$_POST['birthday'];
    $cn=Connexionbd::getInstance();
    $cnx=$cn->getPDO();
    $query=$cnx->prepare("select section from etudiant where id=?");//jai utulisee prepared pour extraire section depuis la base de donnee
    $query->execute(array($id));
    $result=$query->fetch();}?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Détail étudiant</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails Étudiant</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body>
        <div class="container mt-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5>Fiche étudiant</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        <li class="list-group-item">
                            <strong>ID:</strong> <?= $id ?>
                        </li>
                        <li class="list-group-item">
                            <strong>Nom:</strong> <?= $name ?>
                        </li>
                        <li class="list-group-item">
                            <strong>Date naissance:</strong> <?= $birthday ?>
                        </li>
                        <li class="list-group-item">
                            <strong>Section:</strong> 
                            <span class="badge bg-primary"><?= $result['section'] ?></span>
                        </li>
                    </ul>
                    
                    <div class="mt-3">
                        <a href="index.php" class="btn btn-secondary">
                            Retour
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>