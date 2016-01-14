<?php

namespace PayIcam;


class Reservation{
    
    private $id;
    private $login;
    private $gingerUserCard;
    private $prixPromo;

    private $icam_id;
    private $icamData;
    private $guestsData;
    
    public $statusMsg;

    function __construct($login, $gingerUserCard, $prixPromo, $articlesPayIcam){
        global $DB;

        $this->gingerUserCard = $gingerUserCard;
        $this->prixPromo = $prixPromo;
        $this->articlesPayIcam = $articlesPayIcam;
        
        $this->login = $login;
        $this->soirees = 0;
        $this->repas = 0;
        $this->buffets = 0;
        $this->price = 0;
        $this->articles = array();
        $this->date_option = date('Y-m-d H:m:s');
        
        $this->statusMsg = array();
    }
    public function addIcamId($icam_id){
        $this->icam_id = $icam_id;
    }

    public function addGuest($data, $updatedFields=false){
        if ($data['is_icam']){ $this->icamData = $data; $msg = 'Icam ('.$data['promo'].') : '; }
        else{ $this->guestsData[] = $data; $msg = 'Invité : '; }
        $msg .= $data['prenom'].' '.$data['nom']. ', ';

        if ($updatedFields === false ) { // INSERT
            $this->soirees += 1;
            $this->addArticle('soiree', $data['is_icam']);
            if($data['repas']){ $this->repas += 1;
                $this->addArticle('repas', $data['is_icam']); }
            if($data['buffet']){ $this->buffets += 1;
                $this->addArticle('buffet', $data['is_icam']); }
            if($data['tickets_boisson']){
                $this->addArticle('tickets_boisson', $data['is_icam']); }
            $this->price += $data['price'];
            $msg = 'Ajout' . $msg .'pour '. $data['price'].'€';
        }else{ // MAJ options
            if (in_array('price', $updatedFields)) {
                $prix = 0;
                if (in_array('repas', $updatedFields))
                    $prix += $this->addArticle('repas', $data['is_icam']);
                if (in_array('buffet', $updatedFields))
                    $prix += $this->addArticle('buffet', $data['is_icam']);
                if (in_array('tickets_boisson', $updatedFields))
                    $prix += $this->addArticle('tickets_boisson', $data['is_icam']);
                $this->price += $prix;
                $msg = 'MAJ options'.json_encode($updatedFields). ' ' . $msg.' avec +'.$prix.'€, soit '.$data['price'].'€ maintenant';
            }
        }
        if ($updatedFields === false) {
            if ($data['is_icam']) $this->statusMsg['insertIcam'] = $msg;
            else $this->statusMsg['insertGuest'][] = $msg;
        }else{
            $this->statusMsg['updateOptions'][] = $msg;
        }
    }

    public function addArticle($type, $is_icam){
        if ($type == 'tickets_boisson')
            $price = 10;
        else
            $price = $this->prixPromo[($is_icam)?'prixIcam':'prixInvite'][$type];
        $article = $this->getPayIcamArticle($type, $price);
        if (!empty($article)){
            if (empty($this->articles[$article['id']]))
                $this->articles[$article['id']] = array('article'=>$article, 'count'=>1);
            else
                $this->articles[$article['id']]['count'] += 1;
        }else{
            exit();
            throw new \Exception($type." non trouvé, vous avez pensé à remplir la conf ?", 1);
        }
        return $price;
    }
    public function getPayIcamArticle($type, $price){
        foreach ($this->articlesPayIcam as $article) {
            if ($article['type'] == $type && $article['price'] == $price)
                return $article;
        }
        return false;
    }

    public function getResaId(){
        if (empty($this->id)) {
            $this->id = $DB->query(
                'INSERT INTO reservations_payicam (date_option, login) VALUES (:date_option, :login)',
                array('date_option'=> date('Y-m-d H:m:s'), 'login' => $this->login)
            );
        }
        return $this->id;
    }

    public function addNewIcam($data){
        global $DB;
        $data['reservation_id'] = $this->id;
        $this->icam_id = $DB->query("INSERT INTO guests_payicam (".implode(', ', array_keys($data)).") VALUES (:".implode(', :', array_keys($data)).")", $data);
        echo 'new id: '.$this->icam_id;
    }
}
 