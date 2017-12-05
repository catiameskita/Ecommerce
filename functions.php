<?php
/**
 * Created by PhpStorm.
 * User: mesqu
 * Date: 23/11/2017
 * Time: 11:41
 */

use \Hcode\Model\User;

function formatPrice($vlPrice)
{
    if(!$vlPrice >0) $vlPrice=0;

    return number_format((float)$vlPrice, 2, ",", ".");

}

function checkLogin($inadmin)

{
    return User::checkLogin($inadmin);
}

function getUserName()
{
    $user = User::getFromSession();
    return $user->getdesperson();

    //return $user->getdeslogin();

}