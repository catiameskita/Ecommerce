<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use Hcode\Mailer;
use \Hcode\Model;
use Rain\Tpl\Exception;

class Address extends Model{

    public static function getCEP($nrcep){

        $nrcep = str_replace("-", "", $nrcep);

        //Initialize a cURL session
        $ch = curl_init();

        Echo "Primeiro<br>";
        var_dump($ch);
        Echo"<br>";

        //Set an option for a cURL transfer, @param resource $ch

        curl_setopt($ch, CURLOPT_URL, " https://viacep.com.br/ws/$nrcep/json/");

        //esperando que haja retorno
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        //não é exigido autenticação SSL
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER , false);

        Echo"Segundo<br>";
        var_dump($ch);
        Echo"<br>";

        //Decodes a JSON string
        //curl_exec - @return mixed true on success or false on failure. However, if the CURLOPT_RETURNTRANSFER
        //option is set, it will return the result on success, false on failure.

        //$data = json_decode(curl_exec($ch), true);
        $data = curl_exec($ch);

        curl_close ($ch);

        Echo"Terceiro<br>";
        var_dump($data);


        return $data;

    }

    public function loadFromCEP ($nrCep){

        $data = Address::getCEP($nrCep);


        if(isset($data['logradouro'])&& $data['logradouro']){

            $this->setdesaddress($data['logradouro']);
            $this->setdescomplement($data['complemento']);
            $this->setdesdistrict($data['bairro']);
            $this->setdescity($data['localidade']);
            $this->setdesstate($data['uf']);
            $this->setdescountry($data['Brasil']);
            $this->setnrzipcode($nrCep);

        }

    }




}