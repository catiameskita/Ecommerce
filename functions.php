<?php
/**
 * Created by PhpStorm.
 * User: mesqu
 * Date: 23/11/2017
 * Time: 11:41
 */

use \Hcode\Model\User;
use \Hcode\Model\Cart;

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


}

function getCartNrQtd(){

    $cart = Cart::getFromSession();

    $totals = $cart->getProductsTotals();

    return $totals['nrqtd'];

}

    function getCartVlSubTotal(){

    $cart = Cart::getFromSession();

    $totals = $cart->getProductsTotals();

    return formatPrice($totals['vlprice']);

}