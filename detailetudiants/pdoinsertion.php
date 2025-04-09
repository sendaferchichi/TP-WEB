<?php
require "Connexionbd.php";
$cnx=Connexionbd::getInstance();
$bs=$cnx->getPDO();
$req=$bs->prepare("insert into etudiant(name, birthday) VALUES (?,?)");
$req->execute(array('rakia tsouri', '2006-05-15'));
$req->execute(['Emma Johnson', '2001-09-14']);
$req->execute( ['Lucas Meyer', '2002-04-25']);
$req->execute( ['Olivia Smith', '2000-12-08']);
$req->execute( ['Hugo Garcia', '1999-10-31']);  ?>