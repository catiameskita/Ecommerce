<?php
/**
 * Created by PhpStorm.
 * User: mesqu
 * Date: 22/11/2017
 * Time: 18:53
 */


use \Hcode\PageAdmin;
use \Hcode\Model\User;


$app->get("/admin/users", function(){

    User::verifyLogin();

    $users = User::listAll();

    $page = new PageAdmin();

    $page->setTpl("users", array(
        "users" =>$users
    ));

});

$app->get("/admin/users/create", function(){

    User::verifyLogin();

    $page = new PageAdmin();

    $page->setTpl("users-create");

});

//!warning! when accessing URL with the same beginning like the example
//admin/users/:iduser/delete
//admin/users/:iduser
//place the longer one before otherwise it will read the admin/users/:iduser and did not progress never to the /delete


$app->get("/admin/users/:iduser/delete", function($iduser){

    User::verifyLogin();

    $user = new User();

    $user->get((int)$iduser);

    $user->delete();

    header("Location: /admin/users");
    exit;


});

$app->get("/admin/users/:iduser", function($iduser){

    User::verifyLogin();

    $user = new User();

    $user->get((int)$iduser);

    $page = new PageAdmin();

    $page->setTpl("users-update", array(
        "user" => $user->getValues()
    ));


});
//the same route /admin/users/create
//accessing with get it will show the html
//accessing via post it will insert data into the DB
$app->post("/admin/users/create", function(){

    User::verifyLogin();

    $user = new User();

    $_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;

    $user->setData($_POST);

    $user->save();

    header("Location: /admin/users");
    exit;

});

//save the edit changes into DB
$app->post("/admin/users/:iduser", function($iduser){

    User::verifyLogin();

    $user = new User();

    $_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;

    $user->get((int)$iduser);

    $user->setData($_POST);

    $user->update();

    header("Location: /admin/users");
    exit;

});