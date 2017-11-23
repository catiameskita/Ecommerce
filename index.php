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


$app = new Slim();
$app->config('debug', true);

require_once ('site.php');
require_once ('admin.php');
require_once ('admin-users.php');
require_once ('admin-categories.php');
require_once ('admin-products.php');
require_once ('functions.php');


$app->run();
