<?php
/**
 * Created by PhpStorm.
 * User: mesqu
 * Date: 22/11/2017
 * Time: 18:55
 */

use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Category;


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