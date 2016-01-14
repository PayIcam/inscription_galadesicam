<?php

namespace PayIcam;


class Reservation{
    
    private $id;
    private $login;

    private $app;
    private $gingerUserCard;
    private $prixPromo;
    private $articlesPayIcam;

    private $icam_id;
    private $icamData;
    private $guestsData;
    
    public $statusMsg;

    function __construct($login, $gingerUserCard, $prixPromo, $articlesPayIcam, $app){
        global $DB;

        $this->app = $app;
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
            $options = array('1 soirée');
            $this->soirees += 1;
            $this->addArticle('soiree', $data['is_icam']);
            if($data['repas']){ $this->repas += 1; $options[] = '1 repas';
                $this->addArticle('repas', $data['is_icam']); }
            if($data['buffet']){ $this->buffets += 1; $options[] = '1 buffet';
                $this->addArticle('buffet', $data['is_icam']); }
            if($data['tickets_boisson']){ $options[] = $data['tickets_boisson'].' tickets';
                $this->addArticle('tickets_boisson', $data['is_icam']); }
            $this->price += $data['price'];
            $msg = 'Ajout' . $msg .'pour '. $data['price'].'€ ['.implode(', ', $options).']';
        }else{ // MAJ options
            if (in_array('price', $updatedFields)) {
                $prix = 0;
                $options = array();
                if (in_array('repas', $updatedFields)){ $options[] = '1 repas';
                    $prix += $this->addArticle('repas', $data['is_icam']);
                }if (in_array('buffet', $updatedFields)){ $options[] = '1 buffet';
                    $prix += $this->addArticle('buffet', $data['is_icam']);
                }if (in_array('tickets_boisson', $updatedFields)){ $options[] = $data['tickets_boisson'].' tickets';
                    $prix += $this->addArticle('tickets_boisson', $data['is_icam']);
                }
                $this->price += $prix;
                $msg = 'MAJ options'.json_encode($updatedFields). ' ' . $msg.' avec +'.$prix.'€, soit '.$data['price'].'€ maintenant ['.implode(', ', $options).']';
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
    public function getArticles(){
        $articles = array();
        foreach ($this->articles as $a) {
            $articles[] = [$a['article']['id'], $a['count']];
        }
        return $articles;
    }
    public function save(){
        global $DB, $payutcClient;
        $vente = $payutcClient->createTransaction(array(
            "items" => json_encode($this->getArticles()),
            "fun_id" => $this->app->get('settings')['fun_id'],
            "mail" => $this->login,
            "return_url" =>  $this->app->request->getUri()->getBaseUrl() . '/callback',
            "callback_url" =>  $this->app->request->getUri()->getBaseUrl() . '/callback'
        ));

        $this->tra_url_payicam = $vente->url;
        $this->tra_id_payicam = $vente->tra_id;

        $data = array(
            'login' => $this->login,
            'soirees' => $this->soirees,
            'repas' => $this->repas,
            'buffets' => $this->buffets,
            'price' => $this->price,
            'articles' => json_encode($this->articles),
            'date_option' => $this->date_option,
            'tra_url_payicam' => $this->tra_url_payicam,
            'tra_id_payicam' => $this->tra_id_payicam,
        );

        $this->id = $DB->query( 'INSERT INTO reservations_payicam ('.implode(', ', array_keys($data)).') VALUES (:'.implode(', :', array_keys($data)).')', $data );

        if (!empty($this->icamData)) {
            $this->saveNewGuest($this->icamData);
        }
        if (!empty($this->guestsData)) {
            foreach ($this->guestsData as $guest) {
                $this->saveNewGuest($guest);
            }
        }
    }

    public function saveNewGuest($data){
        global $DB;
        $data['reservation_id'] = $this->id;
        if (isset($data['id']) && $data['id'] > 0){
            $data['guest_id'] = $data['id']; unset($data['id']);
        }else 
            $data['guest_id'] = -1;
        $DB->query("INSERT INTO guests_payicam (".implode(', ', array_keys($data)).") VALUES (:".implode(', :', array_keys($data)).")", $data);
    }
}
 