<?php 


$dsn = "mysql:host=localhost;dbname=delivery_database";
$dbusrn = "root";
$dbpwd = "";


try {
    $pdo = new PDO($dsn,$dbusrn,$dbpwd);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}catch(PDOException $e){
    echo "Database Connection failed: " .$e->getMessage();
}
