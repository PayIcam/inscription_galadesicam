<?php

namespace PayIcam;


class Auth{

    private $roles;

    function __construct() {
        global $DB;
        $this->roles = array(
            array('id'=>0, "name"=>"Membre", 'slug'=> "member", 'level'=>0),
            array('id'=>1, "name"=>"Serveur et autre responsabilités", 'slug'=> "member++", 'level'=>1),
            array('id'=>2, "name"=>"Administrateur de Fondation", 'slug'=> "admin", 'level'=>2),
            array('id'=>3, "name"=>"Super Admin", 'slug'=> "super-admin", 'level'=>3)
        );
    }

    function loginUsingCas($ticket, $service) {
        global $DB;
        global $payutcClient;
        require 'class/Cas.class.php';

        try {
            $result = $payutcClient->loginCas(array("ticket" => $ticket, "service" => $service));
            $status = $payutcClient->getStatus();
            $_SESSION['payutc_cookie'] = $payutcClient->cookie;

            $userRank = $payutcClient->getUserLevel();

            $_SESSION['Auth'] = array(
                'email' => $status->user,
                'firstname' => $status->user_data->firstname,
                'lastname' => $status->user_data->lastname,
                'slug' => $this->roles[$userRank]['slug'],
                'roleName' => $this->roles[$userRank]['name'],
                'level' => $userRank
            );
            return true;
        } catch (Exception $e) {
            if (strpos($e, 'UserNotFound') !== false ) {
                header('Location:../casper');exit;
            }
            Functions::setFlash($e->getMessage(),'danger');
            return false;
        }
    }

    public function logOut() {
        global $payutcClient;
        $status = $payutcClient->getStatus();
        if($status->user) {
            $payutcClient->logout();
        }
        $service = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . "{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
        $casUrl = $payutcClient->getCasUrl()."logout?url=".urlencode($service);
        $_SESSION = array();
        session_destroy();

        return $casUrl;
    }

    /**
     * Autorise un rang à accéder à une page, redirige vers forbidden sinon
     * */
    function allow($rang) {
        $roles = $this->getLevels();
        if(!$this->getUserField('slug')) {
            $this->forbidden();
        } else {
            if($roles[$rang] > $this->getUserField('level')) {
                $this->forbidden();
            } else {
                return true;
            }
        }
    }

    function hasRole($rang) {
        $roles = $this->getLevels();
        if(!$this->getUserField('slug')) {
            return false;
        } else {
            if($roles[$rang] > $this->getUserField('level')) {
                return false;
            } else {
                return true;
            }
        }
    }

    /**
     * Récupère une info utilisateur
     ***/
    function getUserField($field) {
        if($field == 'role') $field = 'slug';
        if(isset($_SESSION['Auth'][$field])) {
            return $_SESSION['Auth'][$field];
        } else {
            return false;
        }
    }

    /**
     * Récupère une info utilisateur
     ***/
    function getUser() {
        return $_SESSION['Auth'];
    }

    /**
     * Redirige un utilisateur
     * */
    function forbidden() {
        Functions::setFlash('<strong>Identification requise</strong> Vous ne pouvez accéder à cette page.','danger');
        header('Location:connection.php'.((!empty($_GET['ticket']))?'?ticket='.$_GET['ticket']:''));exit;
    }

    // -------------------- Security & Token functions -------------------- //
    // public static function generateToken($nom = '') {
    //     $token = md5(uniqid(rand(147,1753), true));
    //     $_SESSION['tokens'][$nom.'_token'] = $token;
    //     $_SESSION['tokens'][$nom.'_token_time'] = time();
    //     return $token;
    // }

    // public static function validateToken($token, $nom = '', $temps = 600, $referer = '') {
    //     if (empty($referer)) {
    //         $referer = Config::get('accueil-payicam').basename($_SERVER['REQUEST_URI']);
    //     }
    //     if(isset($_SESSION['tokens'][$nom.'_token']) && isset($_SESSION['tokens'][$nom.'_token_time']) && !empty($token))
    //         if($_SESSION['tokens'][$nom.'_token'] == $token)
    //             if($_SESSION['tokens'][$nom.'_token_time'] >= (time() - $temps)) {
    //                 if(!empty($_SERVER['HTTP_REFERER']) && dirname($_SERVER['HTTP_REFERER']) == dirname($referer))
    //                     return true;
    //                 elseif(empty($_SERVER['HTTP_REFERER']))
    //                     return true;
    //             }
    //     return false;
    // }

    // -------------------- isXXX functions -------------------- //
    function isLogged() { // vérification de de l'existence d'une session "Auth", d'une session ouverte
        if ($this->getUserField('level') !== false && $this->getUserField('level') >= 0)
            return true;
        else
            return false;
    }
    function isAdmin() { //vérification que l'utilisateur loggué est administrateur
        if ($this->getUserField('role') == 'admin' || $this->getUserField('role') == 'super-admin')
            return true;
        else
            return false;
    }

    // -------------------- Getters -------------------- //
    public function getLevels($key = 'slug') {
        global $DB;
        if ($key != 'slug' || $key != 'id')
            $key = 'slug';

        $roles = array();
        foreach($this->roles as $d) {
            $roles[$d[$key]] = $d['level'];
        }
        return $roles;
    }
    public function getRoles($key = 'id') {
        global $DB;
        if ($key != 'slug' || $key != 'id')
            $key = 'id';

        $roles = array();
        foreach($this->roles as $d) {
            $roles[$d[$key]] = $d['name'];
        }
        return $roles;
    }
    public function getRole($key) {
        if (isset($this->roles[$key])) {
            return $this->roles[$key];
        } else { // C'est surement son slug
            foreach($this->roles as $d) {
                if ($d['slug'] == $key) {
                    return $d;
                }
            }
            return null;
        }
    }
    public function getRoleName($id) {
        $role = $this->getRole($id);
        return $role['name'];
    }
}
