<?php
class Connexionbd{
private static $host="localhost";
private static $db="dbphp";
private static $bdd;
private static $bd;
private static $user="root";
private static $mdp="123456789";
private function __construct()
{
try{
    self::$bd=new PDO('mysql:host='.self::$host.';dbname='.self::$db,self::$user,self::$mdp);
}
catch(PDOException $e){
    echo ($e->getMessage());
}
}
public static function  getInstance(){
    if(!isset(self::$bdd)){self::$bdd=new self();}

    return(self::$bdd);

}
public static function getbase(){return self::$db;}
public function getPDO(){return self::$bd;}
}

