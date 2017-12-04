<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use Hcode\Mailer;
use \Hcode\Model;
use Rain\Tpl\Exception;

class User extends Model{

    const SESSION = "User";
    const SECRET = "hcodephp7_secret";
    const ERROR = "UserError";
    const ERROR_REGISTER = "UserErrorRegister";

    public static function getFromSession()
    {
        $user = new User();

        if(isset($_SESSION[User::SESSION]) &&(int)$_SESSION[User::SESSION]['iduser']>0)
        {
            $user->setData($_SESSION[User::SESSION]);
        }
        return $user;
    }

    public static function checkLogin($inadmin = true)
    {
        if(
        !isset($_SESSION[User::SESSION])
        ||
        !$_SESSION[User::SESSION]
        ||
        !(int)$_SESSION[User::SESSION]["iduser"] > 0)
        {
            //não está com login
            return false;

        }else {

            if($inadmin === true && (bool)$_SESSION[User::SESSION]['inadmin']=== true)
            {
                return true;

            }elseif ($inadmin ===false){

                return true;

            }else{

                return false;

            }

        }

    }

    public static function login($login, $password){

        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
           ":LOGIN" => $login
        ));

        if(count($results)===0){

            //When catching an exception inside a namespace it is important that you escape to the global space:

            throw new \Exception("Usuário inexiste ou senha inválida.",1);
        }
        $data = $results[0];


        //@return boolean Returns TRUE if the password and hash match, or FALSE otherwise
        //password_verify returns true or false
        //is comparing the parameter password received with the hash on the DB
        if(password_verify($password, $data["despassword"])===true)
        {
            $user = new User();

            $data['desperson']=utf8_encode($data['desperson']);

            $user->setData($data);

            //to have a login working we need a session

            $_SESSION[User::SESSION] = $user->getValues();

            return $user;


        }else{

            throw new \Exception("Usuário inexiste ou senha inválida.", 1);

        }


    }

    public static function verifyLogin($inadmin = true)
    {
        if(!User::checkLogin($inadmin)){
            if($inadmin)
            {
                header("Location: /admin/login");

            }else{

                header("Location: /login");
            }
            exit;
        }
    }


    public static function logout()
    {

        //or session_unset()
        $_SESSION[User::SESSION] = NULL;

    }

    public static function listAll()
    {
        $sql = new Sql();
        return  $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson");

    }


    public function get($iduser)
    {
        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :IDUSER",
            array(
                ":IDUSER" => $iduser
            ));

        $data['desperson']=utf8_encode($data['desperson']);

        $this->setData($results[0]);

    }

    public function save()
    {
    $sql = new Sql();


    $results = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
        ":desperson"    => utf8_decode($this->getdesperson()),
        ":deslogin"     => $this->getdeslogin(),
        ":despassword"  => User::getPasswordHash($this->getdespassword()),
        ":desemail"     => $this->getdesemail(),
        ":nrphone"      => $this->getnrphone(),
        ":inadmin"      => $this->getinadmin()
    ));



    $this->setData($results[0]);

    }

    public function update()
    {
        $sql = new Sql();


        $results = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
            ":iduser"       => $this->getiduser(),
            ":desperson"    => utf8_decode($this->getdesperson()),
            ":deslogin"     => $this->getdeslogin(),
            ":despassword"  => User::getPasswordHash($this->getdespassword()),
            ":desemail"     => $this->getdesemail(),
            ":nrphone"      => $this->getnrphone(),
            ":inadmin"      => $this->getinadmin()
        ));

        $this->setData($results[0]);


    }

    public function delete()
    {

        $sql = new Sql();
        $sql->select("CALL sp_users_delete(:iduser)", array(
        ":iduser" =>$this->getiduser()
        ));


    }

    public static function getForgot($email)
    {
        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_persons a INNER JOIN tb_users b USING (idperson)
                              WHERE a.desemail = :email", array(
                                  ":email" => $email
        ));

        if(count($results) === 0)
        {
            throw new \Exception("Não foi possível recuperar a senha.");
        }
        else
        {
            $data = $results[0];
            $results2 = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)",
            array(

                ":iduser"   => $data["iduser"],
                ":desip"    => $_SERVER["REMOTE_ADDR"]
        ));

            if (count($results2)===0)
            {
                throw new \Exception("Não foi possível recuperar a senha.");
            }
            else
            {

                $dataRecovery = $results2[0];

                $code = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, User::SECRET,$dataRecovery["idrecovery"], MCRYPT_MODE_ECB));

                $link = "http://ecommerce.app/admin/forgot/reset?code=$code";

                $mailer = new Mailer($data["desemail"], $data["desperson"], "Redefinir senha da Hcode", "forgot",
                array(
                    "name" => $data["desperson"],
                    "link" => $link
                ));

                $mailer->send();

                return $data;



            }

        }
    }

    public static function validForgotDecrypt($code)
    {
        $idRecovery = (int)mcrypt_decrypt(MCRYPT_RIJNDAEL_128, User::SECRET, base64_decode($code), MCRYPT_MODE_ECB);

        $sql = new Sql();

        $results = $sql->select("
                              SELECT * 
                FROM tb_userspasswordsrecoveries a
                INNER JOIN tb_users b USING(iduser)
                INNER JOIN tb_persons c USING (idperson)
                WHERE
                    a.idrecovery = :idrecovery
                    AND
                    a.dtrecovery IS NULL 
                    AND
                    DATE_ADD(a.dtregister, INTERVAL 1 HOUR) >= NOW();", array(
            ":idrecovery" => $idRecovery
        ));


        if (count($results) === 0) {

            throw new \Exception("Não foi possível recuperar a senha.", 1);
        } else {

            return $results[0];

        }
    }

        public static function setForgotUsed($idRecovery)
        {

            $sql = new Sql();

            $sql->query("UPDATE tb_userspasswordsrecoveries SET dtrecovery = NOW() WHERE idrecovery = :idrecovery", array(

                ":idrecovery" => $idRecovery
            ));

    }

        public function setPassword($password){

        $sql = new Sql();

        $sql->query("UPDATE tb_users SET despassword = :password WHERE iduser = :iduser", array(

            ":password" => $password,
            ":iduser" =>$this->getiduser()
        ));

        }

        public static function setError($msg){

            $_SESSION[User::ERROR] = $msg;

        }

        public static function getError(){

            $msg = (isset( $_SESSION[User::ERROR])&&$_SESSION[User::ERROR]) ? $_SESSION[User::ERROR]: '';
            User::clearError();
            return $msg;
        }

        public static function clearError()
        {
            $_SESSION[User::ERROR] = NULL;
        }

        public static function setErrorRegister($msg)
        {

            $_SESSION[User::ERROR_REGISTER] = $msg;
        }

        public static function getErrorRegister()
        {
            $msg = (isset( $_SESSION[User::ERROR_REGISTER])&&$_SESSION[User::ERROR_REGISTER]) ? $_SESSION[User::ERROR_REGISTER]: '';
            User::clearErrorRegister();
            return $msg;
        }

        public static function clearErrorRegister()
        {
            $_SESSION[User::ERROR_REGISTER] = NULL;
        }

        public static function checkLoginExist($login)
        {
            $sql = new Sql();

            $results = $sql->select(
                "SELECT * FROM tb_users WHERE deslogin = :deslogin", [
                    ':deslogin' => $login
            ]);

            //
            return (count($results)>0);
        }


        public static function getPasswordHash($password)
        {

                return password_hash($password, PASSWORD_DEFAULT, [
                    'cost'=>12
                ]);


        }







}