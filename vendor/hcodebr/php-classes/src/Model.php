<?php

namespace Hcode;

class Model{

    //Going to have all the data within the object
    private $values = [];

    //$name is the method being called
    public function __call($name, $arguments)
    {
        //Return part of a string
        $method = substr($name, 0, 3);
        $fieldName = substr($name, 3, strlen($name));

        switch ($method){
            case "get": return (isset($this->values[$fieldName])) ? $this->values[$fieldName] :NULL;
                break;
            case "set": $this->values[$fieldName] = $arguments[0];
                break;

        }

    }

    public function setData($data = array())
    {
        foreach($data as $key => $value)
        {
            //setting data dynamically
            //$key: //setiduser //setidperson //deslogin ....
            //curly braces {} make it dynamic in PHP
            $this->{"set".$key}($value);
        }
    }

    public function getValues()
    {
        return $this->values;
    }


}