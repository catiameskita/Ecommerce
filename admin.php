<?php
/**
 * Created by PhpStorm.
 * User: mesqu
 * Date: 22/11/2017
 * Time: 18:52
 */


use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Category;


$app->get('/admin', function(){

    User::verifyLogin();

    $page = new PageAdmin();

    $page->setTpl("index");
});

$app->get('/admin/login', function(){

    $page = new PageAdmin([
        "header" => false,
        "footer" => false
    ]);

    $page->setTpl("login");

});

$app->post('/admin/login', function (){

    User::login($_POST["login"], $_POST["password"]);

    header("Location: /admin");

    exit;

});

$app->get('/admin/logout', function(){

    User::logout();

    header("Location: /admin/login");
    exit;

});


$app->get("/admin/forgot", function()
{
    $page = new PageAdmin([
        "header" => false,
        "footer" => false
    ]);

    $page->setTpl("forgot");

});

$app->post("/admin/forgot", function(){

    $_POST["email"];

    $user = User::getForgot($_POST["email"]);

    header("Location: /admin/forgot/sent");
    exit;

});

$app->get("/admin/forgot/sent", function(){

    $page = new PageAdmin([
        "header" => false,
        "footer" => false
    ]);

    $page->setTpl("forgot-sent");

});

$app->get("/admin/forgot/reset", function(){

    $user = User::validForgotDecrypt($_GET["code"]);

    $page = new PageAdmin([
        "header" => false,
        "footer" => false
    ]);

    $page->setTpl("forgot-reset", array(
        "name" => $user["desperson"],
        "code" => $_GET["code"]
    ));

});$app->post("/admin/forgot/reset", function(){


    $forgot = User::validForgotDecrypt($_POST["code"]);
    User::setForgotUsed($forgot["idrecovery"]);

    $user = new User();

    $user->get((int)$forgot["iduser"]);

    //Creates a password hash
    $password =  password_hash($_POST["password"], PASSWORD_DEFAULT, ["cost" => 12]);

    //atributo no name no input de HTML = password
    $user->setPassword($password);

    $page = new PageAdmin([
        "header" => false,
        "footer" => false
    ]);

    $page->setTpl("forgot-reset-success");

});

