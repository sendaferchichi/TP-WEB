<?php 
 class Etudiant{
private string $nom;
  private array $tab=[];

 function __construct(String $nom="",...$note)
 {$this->nom=$nom;
foreach( $note as $n){($this->tab)[]=$n;}
}

public function moyenne():float{
    $somme=0;
    foreach($this->tab as $n){
        $somme+=$n;
    }
    return($somme/count($this->tab));
}
public function admis(){
    if($this->moyenne()<10){echo "redoublant!";}
    else{echo "ADMIS!";}
}

public function getNom(){return $this->nom;}
public function getTab(){return $this->tab;}
}

?>
 