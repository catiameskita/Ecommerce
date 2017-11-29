<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Model\User;




class Cart extends Model{

    const SESSION = "Cart";

    public static function getFromSession(){

        $cart = new Cart();

        if(isset($_SESSION[Cart::SESSION])&& (int)$_SESSION[Cart::SESSION]['idcart']>0){

            $cart->get((int)$_SESSION[Cart::SESSION]['idcart']);
        }else{

            $cart->getFromSessionID();

            if(!(int)$cart->getidcart() >0 ){

                $data = ['dessessionid' => session_id()];

                if(User::checkLogin(false)){

                    $user = User::getFromSession();

                    $data['iduser'] = $user->getiduser();

                }

                $cart->setData($data);
                $cart->save();
                $cart->setToSession();

            }
        }
        return $cart;

    }

    public function setToSession()
    {

        $_SESSION[Cart::SESSION] = $this->getValues();


    }


    public function get(int $idcart)
    {

        $sql = new Sql;

        $results = $sql->select("SELECT * FROM tb_carts WHERE idcart = :idcart" , [

            ':idcart' => $idcart
        ]);

        if( count($results) >0){

            $this->setData($results[0]);
        }
    }

    public function  getFromSessionID()
    {
        $sql = new Sql;


        $results = $sql->select("SELECT * FROM tb_carts WHERE dessessionid = :dessessionid" , [

            ':dessessionid' => session_id()
        ]);


        if(count($results) > 0)
        {
          $this->setData($results[0]);
        }

    }

    public function save()
    {

        $sql = new Sql();

        $results = $sql->select("CALL sp_carts_save(
        :idcart, :dessessionid, :iduser, :deszipcode, :vlfreight, :nrdays)",
            [
                ':idcart' => $this->getidcart(),
                ':dessessionid' => $this->getdessessionid(),
                ':iduser' => $this->getiduser(),
                ':deszipcode' => $this->getdeszipcode(),
                ':vlfreight' => $this->getvlfreight(),
                ':nrdays' => $this->getnrdays()

            ]);

        if( count($results) >0){

            $this->setData($results[0]);
        }



    }


    public function addProduct(Product $product)
    {
        $sql = new Sql();

        $sql->query("INSERT INTO tb_cartsproducts (idcart, idproduct) VALUES (:idcart, :idproduct)",
            [
               ':idcart' => $this->getidcart(),
                ':idproduct' => $product->getidproduct()
            ]);
    }

    public function removeProduct(Product $product, $all = false)
    {

        $sql = new Sql();

        if($all){

            //remove todos os productos
            $sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW() 
                                 WHERE idcart = :idcart AND idproduct = :idproduct",
                [
                    ':idcart' =>$this->getidcart(),
                    ':idproduct' =>$product->getidproduct()

                ]);
        }else{
            //remove só um producto
            $sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW()
                                  WHERE idcart = :idcart AND idproduct = :idproduct
                                  AND dtremoved IS NULL LIMIT 1",
                [
                    ':idcart' =>$this->getidcart(),
                    ':idproduct' =>$product->getidproduct()

                ]);
        }


    }

    public function getProducts()
    {

        $sql = new Sql();

        //quando trabalhamos com GROUP temos que referir tudo no SELECT exactamento como no GROUP
        //nrqtd - quantidade de um producto do mesmo tipo


        $rows = $sql->select ("SELECT  b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl,
                    COUNT(*) AS nrqtd, SUM(b.vlprice) AS vltotal
                    FROM tb_cartsproducts a 
                    INNER JOIN tb_products b 
                    ON a.idproduct = b.idproduct
                    WHERE a.idcart = :idcart AND a.dtremoved IS NULL 
                    GROUP BY b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl
                    ORDER BY b.desproduct",
            [
                ':idcart'=>$this->getidcart()

              ]);

        return Product::checkList($rows);
    }

    public function getProductsTotals()
    {

        $sql = new Sql();

        $results = $sql->select("SELECT SUM(vlprice) as vlprice, SUM(vlwidth) as vlwidth, SUM(vlheight) as vlheight, SUM(vllength)as vllength, SUM(vlweight) as vlweight, COUNT(*) AS nrqtd
                       FROM tb_products a 
                       INNER JOIN tb_cartsproducts b ON a.idproduct = b.idproduct
                       WHERE b.idcart = :idcart
                       AND dtremoved IS NULL;",
                [

                ':idcart' =>$this->getidcart()

                 ]);

        if(count($results) > 0)
        {
            return $results[0];
        }else{
            return [];
        }


    }

    public function setFreight($nrzipcode)
    {

        $nrzipcode = str_replace("-", " ", $nrzipcode);

        $totals = $this->getProductsTotals();

        if ($totals['nrqtd'] >0 ){

            if($totals['vlheight']<2) $totals['vlheight']==2;
            if($totals['vllength']<16) $totals['vllength']==16;
            //querystring
            //Generate URL-encoded query string
            $qs = http_build_query([
                        'nCdEmpresa' => '',
                        'sDsSenha' => '',
                        'nCdServico' => '40010',
                        'sCepOrigem' => '09853120',
                        'sCepDestino' => $nrzipcode,
                        'nVlPeso' => $totals['vlweight'],
                        'nCdFormato' =>'1',
                        'nVlComprimento' => $totals['vllength'],
                        'nVlAltura' => $totals['vlheight'],
                        'nVlLargura' => $totals['vlwidth'],
                        'nVlDiametro' => '0',
                        'sCdMaoPropria' => 'S',
                        'nVlValorDeclarado' => $totals['vlprice'],
                        'sCdAvisoRecebimento' => 'S'
            ]);

            //simplexml_load_file -Interprets an XML file into an object
           $xml = (array)simplexml_load_file("http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx?".$qs);
           echo json_encode($xml);

        }else{



        }


    }

}