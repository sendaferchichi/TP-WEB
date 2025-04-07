<?php

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        $this->pdo = new PDO(
            'mysql:host=localhost;dbname=dbphp',
            'root',
            '123456789',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance->pdo;
    }
}



class repository{
    public static function getAll($table){
        
        $cnx=Database::getInstance();
        $rep=$cnx->prepare('select * from '.$table);
        $rep->execute();
        $res=$rep->fetchAll();
        return $res;

    }

    public static function findById($table,$id){
        $cnx=Database::getInstance();
        $rep=$cnx->prepare("select * from $table where id= :id");
        $rep->execute(array(":id"=>$id));
        $res=$rep->fetchAll();
        return $res;
    }

    public static function create($table,$dict){
        $keys=implode(',',array_keys($dict));
        $values=implode(',',array_values($dict));
        $query="insert into $table($keys)values($values)";
        $cnx=Database::getInstance();
        $res=$cnx->query($query);
        return $res;
    }
    public static function delete($table,$id){
        $cnx=Database::getInstance();
        $rep=$cnx->prepare("delete from $table where id= :id");
        $rep->execute(array(":id"=>$id));
        return $rep;
    }
}

$res=repository::getAll("sections");
echo ' pour getAll("sections")</br>
<div style="background-color:grey">';
foreach($res as $row){
    echo $row['id'].''.$row['designation'].'  '.$row['description'].'</br>';
}
echo '</div>';

$r=repository::findById("sections",4);
foreach($r as $row){
    echo '</br> pour findbyId("sections","4")</br>';
    echo $row['id'].''.$row['designation'].'  '.$row['description'].'</br>';
}
echo 'apres $l=repository::create("sections",["id"=>"7","designation"=>"bioooo","description"=>"nouvelle bio"]); ';
$l=repository::create("sections",["id"=>"7","designation"=>"'bioooo'","description"=>"'nouvelle bio'"]);
$res=repository::getAll("sections");
echo ' pour getAll("sections")</br>
<div style="background-color:grey">';
foreach($res as $row){
    echo $row['id'].''.$row['designation'].'  '.$row['description'].'</br>';
}
echo '</div>';


$res=repository::getAll("utilisateurs");
echo ' pour getAll("utulisateurs")</br>
<div style="background-color:grey">';
foreach($res as $row){
    echo $row['id'].''.$row['username'].'  '.$row['email'].'</br>';
}
echo '</div>';


echo ' apres $k=repository::create("utulisateurs",["id"=>"7","username"=>"rousia","email"=>"trh@gamail.com","password"=>"333","role"=>"user"]);
 ';
$k=repository::create("utilisateurs",["id"=>"7","username"=>"'rousia'","email"=>"'trh@gamail.com'","password"=>"'333'","role"=>"'user'"]);

$res=repository::getAll("utilisateurs");
echo ' pour getAll("utilisateurs")</br>
<div style="background-color:grey">';
foreach($res as $row){
    echo $row['id'].''.$row['username'].'  '.$row['email'].'</br>';
}
echo '</div>';

echo 'delete ce qu on a ajoute</br>';
$d=repository::delete("utilisateurs","7");
$res=repository::getAll("utilisateurs");
echo ' pour getAll("utulisateurs")</br>
<div style="background-color:grey">';
foreach($res as $row){
    echo $row['id'].''.$row['username'].'  '.$row['email'].'</br>';
}
echo '</div>';

?>