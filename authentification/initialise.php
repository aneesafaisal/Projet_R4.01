<?php

try {
    include_once 'DatabaseHandler.php';

    $db = DatabaseHandler::getInstance()->getLinkpdo();
    $sql = "INSERT INTO user(login,password,role) values (:login,:password,:role)";
    $stmt = $db->prepare($sql);

    if ($stmt->execute([
        'login'=>'admin',
        'password'=> password_hash('adminabc',PASSWORD_DEFAULT),
        'role'=>'admin'
    ])){
        echo "Admin credentials added<br>";
    }

    if ($stmt->execute([
        'login'=>'coach',
        'password'=> password_hash('coachabc',PASSWORD_DEFAULT),
        'role'=>'user'
    ])){
        echo "Coach credentials added<br>";
    }
    
    if ($stmt->execute([
        'login'=>'directeur',
        'password'=> password_hash('directeurabc',PASSWORD_DEFAULT),
        'role'=>'user'
    ])){
        echo "Directeur credentials added<br>";
    }
} catch (Exception $e){
    echo "An error occured : " . $e->getMessage();
}