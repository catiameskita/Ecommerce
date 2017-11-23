<?php
/**
 * Created by PhpStorm.
 * User: mesqu
 * Date: 23/11/2017
 * Time: 11:41
 */

function formatPrice($vlPrice)
{

    return number_format((float)$vlPrice, 2, ",", ".");

}