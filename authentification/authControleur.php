<?php

include_once "DatabaseHandler.php";

function estValide($login,$password) {
    $db = DatabaseHandler::getInstance()->getLinkpdo();
    $query = "SELECT * FROM user WHERE login = :login";
    $req = $db->prepare($query);
    try {
        $req->execute(array('login'=>$login));
    } catch (Exception $e) {
        return false;
    }
    if ($req->rowCount()==0) {
        return false;
    }
    $user = $req->fetch();
    return password_verify($password, $user['password']);
}

function getRole($login) {
    $db = DatabaseHandler::getInstance()->getLinkpdo();
    $query = "SELECT role FROM user WHERE login = :login";
    $req = $db->prepare($query);
    $req->execute(array('login'=>$login));
    $user = $req->fetch();
    return $user['role'];
}




