<?php
/**
 * Created by PhpStorm.
 * User: mesqu
 * Date: 07/11/2017
 * Time: 13:41
 */

session_start();

//we can use the if s

require_once ("vendor/autoload.php");

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Category;

$app = new Slim();

$app->config('debug', true);


$app->get('/', function(){

$page = new Page();

$page->setTpl("index");

});

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

$app->get("/admin/categories", function(){

    User::verifyLogin();

    $categories = Category::listAll();
    $page = new PageAdmin();

    $page->setTpl("categories", [
        'categories' => $categories
    ]);

});

$app->get("/admin/categories/create", function()
{
    User::verifyLogin();
    $page = new PageAdmin();

    $page->setTpl("categories-create");

});

$app->post("/admin/categories/create", function()
{
    User::verifyLogin();
    $category = new Category();
    $category->setData($_POST);
    $category->save();

    header('Location: /admin/categories');
    exit;

});

$app->get("/admin/categories/:idcategory/delete", function($idcategory)
{
    User::verifyLogin();
    $category = new Category();

    $category->get((int)$idcategory);

    $category->delete();

    header('Location: /admin/categories');
    exit;

});

$app->get("/admin/categories/:idcategory", function($idcategory)
{
    User::verifyLogin();
    $category = new Category();
    //tudo que vem na URL ele converte para texto, logo é necessário fazer o respectivo cast
    $category->get((int)$idcategory);

    $page = new PageAdmin();

    $page->setTpl("categories-update", [
        'category' => $category->getValues()
    ]);

});

$app->post("/admin/categories/:idcategory", function($idcategory)
{
    User::verifyLogin();
    $category = new Category();

    $category->get((int)$idcategory);
    $category->setData($_POST);
    $category->save();

    header('Location: /admin/categories');
    exit;

});
$app->get("/categories/:idcategory", function($idcategory){

    $category = new Category();

    $category->get((int)$idcategory);

    $page = new Page();

    $page->setTpl("category", [
        'category' => $category->getValues(),
        'products' =>[]
    ]);


});

$app->run();
