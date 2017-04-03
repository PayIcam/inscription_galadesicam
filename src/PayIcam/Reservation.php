<?php

namespace PayIcam;


class Reservation{

    private $id;
    private $soirees;
    private $articles;
    private $date_option;
    private $date_paiement;
    private $status;
    private $tra_id_payicam;
    private $tra_url_payicam;
    private $login;
    private $price;

    private $app;
    private $gingerUserCard;
    private $prixPromo;
    private $articlesPayIcam;

    private $icam_id;
    private $icamData;
    private $guestsData;

    private $statusMsg;

    function __construct($data, $gingerUserCard, $prixPromo, $articlesPayIcam, $app) {
        global $DB;

        $this->app = $app;
        $this->gingerUserCard = $gingerUserCard;
        $this->prixPromo = $prixPromo;
        $this->articlesPayIcam = $articlesPayIcam;

        if (is_array($data) && !empty($data['id'])) { // On a déjà le contenu d'une résa
            $this->id = intval($data['id']);
            $this->soirees = intval($data['soirees']);
            $this->articles = json_decode($data['articles']);
            $this->date_option = $data['date_option'];
            $this->date_paiement = $data['date_paiement'];
            $this->status = $data['status'];
            $this->tra_id_payicam = $data['tra_id_payicam'];
            $this->tra_url_payicam = $data['tra_url_payicam'];
            $this->login = $data['login'];
            $this->price = floatval($data['price']);

            $this->loadResaGuestsData();
        } else {
            $this->login = $data;
            $this->soirees = 0;
            $this->price = 0;
            $this->articles = array();
            $this->date_option = date('Y-m-d H:i:s');
        }

        $this->statusMsg = array();
    }
    public function hasNewReservation() {
        if (empty($this->statusMsg))
            return false;
        else return true;
    }
    public function loadResaGuestsData() {
        global $DB;
        $guests = $DB->query('SELECT * FROM guests_payicam WHERE reservation_id = :reservation_id ORDER BY is_icam DESC', array('reservation_id' => $this->id));
        foreach ($guests as $k => $guest) { $guest = self::parseGuestData($guest);
            if ($k == 0 && !$guest['is_icam']) { // ça veut dire que l'on a déjà une réservation pour cet icam normalement. Allons chercher son id !
                $this->icam_id = $this->lookIcamIdInGuestsTable();
            }
            if ($guest['is_icam']) {
                try {
                    $this->icam_id = $this->lookIcamIdInGuestsTable();
                    $guest['guest_id'] = $this->icam_id; // Il y avait déjà un user avec cet email dans la base, on va écraser sa résa
                } catch (\Exception $e) {
                    $this->icam_id = $guest['guest_id'];
                }
                $this->icamData = $guest;
            } else {
                $this->guestsData[] = $guest;
            }
        }
    }
    public function lookIcamIdInGuestsTable() {
        global $DB;
        $icam_id = $DB->queryFirst('SELECT id FROM guests WHERE email = :email ORDER BY is_icam DESC', array('email' => $this->login));
        if (empty($icam_id)) throw new \Exception("Houston, we have a problem... ".$this->login." n'a pas de réservations au gala!", 1);
        return intval(current($icam_id));
    }
    public function updateStatus($status) {
        global $DB;
        $this->status = $status;
        $this->date_paiement = date("Y-m-d H:i:s");
        $data = ['status'=>$this->status, 'date_paiement'=>$this->date_paiement, 'id'=>$this->id];
        $DB->query("UPDATE reservations_payicam SET status = :status, date_paiement = :date_paiement WHERE id = :id", $data);
    }

    public function addIcamId($icam_id) {
        $this->icam_id = $icam_id;
    }
    public function addGuest($data, $updatedFields=false, $oldPrice=0) {
        if ($data['is_icam']) { $msg = 'Icam ('.$data['promo'].') : '; }
        else { $msg = 'Invité : '; }
        $msg .= $data['prenom'].' '.$data['nom']. ', ';

        if ($updatedFields === false ) { // INSERT
            $options = array('1 soirée');
            $this->soirees += 1;
            $this->addArticle('soiree', $data['is_icam']);
            $this->price += $data['price'];
            $msg = 'Ajout' . $msg .'pour '. $data['price'].'€ ['.implode(', ', $options).']';
            var_dump($data['plage_horaire_entrees']);
            if (isset($this->creneauxEntrees[$data['plage_horaire_entrees']]))
                $this->creneauxEntrees[$data['plage_horaire_entrees']] ++;
        } else { // MAJ options
            if (in_array('price', $updatedFields)) {
                $prix = 0;
                $options = array();
                if (in_array('repas', $updatedFields)) { $options[] = '1 repas'; $this->repas += 1;
                    $prix += $this->addArticle('repas', $data['is_icam']);
                }if (in_array('buffet', $updatedFields)) { $options[] = '1 buffet'; $this->buffets += 1;
                    $prix += $this->addArticle('buffet', $data['is_icam']);
                }if (in_array('tickets_boisson', $updatedFields)) { $options[] = $data['tickets_boisson'].' tickets';
                    var_dump($this->articles);
                    $prix += $this->addArticle('tickets_boisson', $data['is_icam'], intval($data['tickets_boisson']/10));
                    var_dump($this->articles);
                }
                $data['price'] = $oldPrice + $prix;
                $this->price += $prix;
                $msg = 'MAJ options'.json_encode($updatedFields). ' ' . $msg.' avec +'.$prix.'€, soit '.$data['price'].'€ maintenant ['.implode(', ', $options).']';
            }
        }
        if ($updatedFields === false) {
            if ($data['is_icam'])
                $this->statusMsg['insertIcam'] = $msg;
            else
                $this->statusMsg['insertGuest'][] = $msg;
        } else
            $this->statusMsg['updateOptions'][] = $msg;

        if ($data['is_icam'])
            $this->icamData = $data;
        else
            $this->guestsData[] = $data;
    }

    public function addArticle($type, $is_icam, $nb=1) {
        var_dump($type);
        var_dump($nb);
        if ($type == 'tickets_boisson')
            $price = 10*$nb*.9;
        else
            $price = $this->prixPromo[($is_icam)?'prixIcam':'prixInvite'][$type];
        $article = $this->getPayIcamArticle($type, $price);
        if (!empty($article)) {
            if (empty($this->articles[$article['id']]))
                $this->articles[$article['id']] = array('article'=>$article, 'count'=>1*$nb);
            else
                $this->articles[$article['id']]['count'] += 1*$nb;
        } else {
            throw new \Exception($type." non trouvé, vous avez pensé à remplir la conf ?", 1);
            exit();
        }
        return $price;
    }
    public function getPayIcamArticle($type, $price) {
        foreach ($this->articlesPayIcam as $article) {
            if ($article['type'] == $type && $article['price'] == $price)
                return $article;
            elseif ($article['type'] == 'tickets_boisson' && $article['price'] == 9)
                return $article;
        }
        return false;
    }
    public function getArticles() {
        $articles = array();
        foreach ($this->articles as $a) {
            $articles[] = [$a['article']['id'], $a['count']];
        }
        return $articles;
    }

    public function save() {
        global $DB, $payutcClient;

        $vente = $payutcClient->createTransaction(array(
            "items" => json_encode($this->getArticles()),
            "fun_id" => $this->app->get('settings')['fun_id'],
            "mail" => $this->login,
            "return_url" =>  $this->app->request->getUri()->getBaseUrl() . '/',
            "callback_url" =>  $this->app->request->getUri()->getBaseUrl() . '/callback'
        ));

        $this->tra_url_payicam = $vente->url;
        $this->tra_id_payicam = $vente->tra_id;

        $data = array(
            'login' => $this->login,
            'soirees' => $this->soirees,
            'price' => $this->price,
            'articles' => json_encode($this->articles),
            'date_option' => $this->date_option,
            'tra_url_payicam' => $this->tra_url_payicam,
            'tra_id_payicam' => $this->tra_id_payicam,
            'date_paiement' => date("Y-m-d H:i:s")
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

    public function saveNewGuest($data) {
        global $DB;
        $data['reservation_id'] = $this->id;
        if (isset($data['id']) && $data['id'] > 0) {
            $data['guest_id'] = $data['id']; unset($data['id']);
        } else
            $data['guest_id'] = -1;
            var_dump($data);
        $DB->query("INSERT INTO guests_payicam (".implode(', ', array_keys($data)).") VALUES (:".implode(', :', array_keys($data)).")", $data);
    }

    public function registerGuestGala($guest) {
        global $DB;

        echo "<p>je register !</p>";

        $guest_id = $guest['guest_id'];
        unset($guest['id']);
        unset($guest['guest_id']);
        unset($guest['icam_id']);
        unset($guest['reservation_id']);
        unset($guest['bracelet_id']);

        if ($guest['repas'] && $guest['buffet'])
            $guest['plage_horaire_entrees'] = '17h30-19h30';
        else if ($guest['repas'])
            $guest['plage_horaire_entrees'] = '19h30-20h';

        if ($guest_id > 0) { // UPDATE
            $data = array();   $updatedFields = array();
            foreach ($guest as $k => $v) {
                if($k == 'id') continue;
                $data[$k] = $guest[$k];
                $updatedFields[] = $k.' = :'.$k;
            }
            $data['id'] = $guest_id;
            $DB->query("UPDATE guests SET ".implode(', ', $updatedFields)." WHERE id = :id", $data);
        } else { // INSERT
            var_dump($guest);
            var_dump($this->icam_id);

            $guest_id = $DB->query("INSERT INTO guests (".implode(', ', array_keys($guest)).") VALUES (:".implode(', :', array_keys($guest)).")", $guest);
            if (!$guest['is_icam']) {
                if(empty($this->icam_id) || $this->icam_id <= 0)
                    throw new \Exception("Houston, we have a problem... ".$this->login." n'a pas de réservations au gala!<br>Ou bien tu as mal utilisé la classe Reservation !", 1);
                $DB->query("INSERT INTO icam_has_guest (icam_id, guest_id) VALUES (:icam_id, :guest_id)", ['icam_id'=>$this->icam_id, 'guest_id'=>$guest_id]);
            }
        }
        return $guest_id;
    }

    public function registerGuestsToTheGala() {
        if (!empty($this->icamData)) {
            $this->icam_id = $this->registerGuestGala($this->icamData);
        }
        if (!empty($this->guestsData)) {
            foreach ($this->guestsData as $guest) {
                $this->registerGuestGala($guest);
            }
        }
    }

    public function checkQuotas($stats, $quotas) {
        return $stats['soireesG']+$stats['soireesW']+$this->soirees <= $quotas['soiree']
                 && $stats['repasG']+$stats['repasW']+$this->repas <= $quotas['repas']
                    && $stats['buffetsG']+$stats['buffetsW']+$this->buffets <= $quotas['buffet'] ;
    }

    public function getQuotasRestant($stats, $quotas) {
        $soirees = $quotas['soiree'] - ($stats['soireesG']+$stats['soireesW']+$this->soirees);
        return compact('soirees');
    }

    public static function parseGuestData($guest) {
        if (isset($guest['id'])) $guest['id'] = intval($guest['id']);
        if (isset($guest['is_icam'])) $guest['is_icam'] = intval($guest['is_icam']);
        if (isset($guest['sexe'])) $guest['sexe'] = intval($guest['sexe']);
        if (isset($guest['bracelet_id'])) $guest['bracelet_id'] = intval($guest['bracelet_id']);
            if (empty($guest['bracelet_id'])) $guest['bracelet_id'] = 0;
        if (isset($guest['price'])) $guest['price'] = floatval($guest['price']);

        return $guest;
    }


    // -------------------- Getters & Setters -------------------- //
    public function __get($var) {
        if (!isset($this->$var)) {
            if (isset($this->attr[$var])) {
                return $this->attr[$var];
            }
        } else return $this->$var;
    }
    public function __set($var,$val) {
        if (!isset($this->$var)) {
            if (isset($this->attr[$var])) {
                $this->attr[$var] = $val;
            }
        } else $this->$var = $val;
    }
}
