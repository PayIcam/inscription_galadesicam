<?php

////////////
// Routes //
////////////

// Page ouverte à tous
$app->get('/about', function ($request, $response, $args) {
    global $Auth, $payutcClient;

    $flash = $this->flash;
    $RouteHelper = new \PayIcam\RouteHelper($this, $request, 'A propos');
    $casUrl = $payutcClient->getCasUrl().'login?service=' . urlencode($RouteHelper->curPageBaseUrl. '/login');
    $deconnexionUrl = $this->router->pathFor('logout');;

    $this->renderer->render($response, 'header.php', compact('Auth', 'flash', 'RouteHelper', $args));
    $this->renderer->render($response, 'about.php', compact('Auth', 'casUrl', 'deconnexionUrl', $args));
    return $this->renderer->render($response, 'footer.php', compact('Auth', 'RouteHelper', $args));
})->setName('about');

/////////////////
// Espace Icam //
/////////////////
function getStatsQuotas() {
    global $DB, $settings, $quotas;
    $stats = $DB->queryFirst("SELECT * FROM
        (SELECT SUM( r.soirees ) soireesW,
                SUM( r.repas ) repasW,
                SUM( r.buffets ) buffetsW
            FROM reservations_payicam AS r WHERE r.status = 'W') rW ,
        -- (SELECT ifnull(SUM( r.soirees ), 0) soireesV, ifnull(SUM( r.repas ), 0) repasV, ifnull(SUM( r.buffets ), 0) buffetsV
            -- FROM reservations_payicam AS r WHERE r.status = 'V') rV ,
        (SELECT COUNT( id ) soireesG,
                SUM( repas ) repasG,
                SUM( buffet ) buffetsG,
                count(CASE WHEN plage_horaire_entrees = '17h30-19h30' THEN 1 ELSE null END) creneau_17h30_19h30,
                count(CASE WHEN plage_horaire_entrees = '19h30-20h' THEN 1 ELSE null END) creneau_19h30_20h,
                count(CASE WHEN plage_horaire_entrees = '21h-21h45' THEN 1 ELSE null END) creneau_21h_21h45,
                count(CASE WHEN plage_horaire_entrees = '21h45-22h30' THEN 1 ELSE null END) creneau_21h45_22h30,
                count(CASE WHEN plage_horaire_entrees = '22h30-23h' THEN 1 ELSE null END) creneau_22h30_23h
            FROM guests) g ");
    foreach ($stats as $k => $v)
        $stats[$k] = intval($v);
    return compact('stats', 'quotas');
}

$app->get('/modif_creneau', function($request,$response, $args){
    global $Auth, $payutcClient, $gingerUserCard, $DB, $canWeRegisterNewGuests, $canWeEditOurReservation;

    $flash = $this->flash;
    $RouteHelper = new \PayIcam\RouteHelper($this, $request, 'modif_creneau');
    $emailContactGala = $this->get('settings')['emailContactGala'];

    // Sample log message
    // $this->logger->info("Slim-Skeleton '/' index");

    $fun_id = $this->get('settings')['fun_id'];
    $prixPromo = getPrixPromo($gingerUserCard);

    $mailPersonne = $Auth->getUserField('email');
    // $mailPersonne = 'hugo.leandri@2018.icam.fr';

    $UserReservation = $DB->query('SELECT * FROM guests WHERE email = :email', array('email' => $mailPersonne));
    $casUrl = $payutcClient->getCasUrl().'login?service=' . urlencode($RouteHelper->curPageBaseUrl. '/login');
    $deconnexionUrl = $this->router->pathFor('logout');

    $this->renderer->render($response, 'header.php', compact('Auth', 'flash', 'RouteHelper', $args));
    $this->renderer->render($response, 'modif_creneau.php', compact('Auth','RouteHelper', 'flash', $args));
    return $this->renderer->render($response, 'footer.php', compact('Auth', 'RouteHelper', $args));

})->setName('modification_du_creneau');

$app->get('/', function ($request, $response, $args) {
    global $Auth, $payutcClient, $gingerUserCard, $DB, $canWeRegisterNewGuests, $canWeEditOurReservation;

    $flash = $this->flash;
    $RouteHelper = new \PayIcam\RouteHelper($this, $request, 'Accueil');
    $emailContactGala = $this->get('settings')['emailContactGala'];

    // Sample log message
    // $this->logger->info("Slim-Skeleton '/' index");

    $fun_id = $this->get('settings')['fun_id'];
    $prixPromo = getPrixPromo($gingerUserCard);

    $mailPersonne = $Auth->getUserField('email');
    // $mailPersonne = 'hugo.leandri@2018.icam.fr';

    $UserReservation = $DB->query('SELECT * FROM guests WHERE email = :email', array('email' => $mailPersonne));
    $UserWaitingResa = $DB->query('SELECT * FROM reservations_payicam WHERE login = :login AND status = "W"', array('login' => $mailPersonne));
    $newResa = null;
    if (count($UserWaitingResa) >= 1) {
        $newResa = new \PayIcam\Reservation(current($UserWaitingResa), $gingerUserCard, $prixPromo, $this->get('settings')['articlesPayIcam'], $this);
        // On veut reset les autres
        if (count($UserWaitingResa) >= 1) {
            $data = ['login' => $mailPersonne, 'id' => $newResa->id];
            $DB->query("UPDATE reservations_payicam SET status = 'A' WHERE login = :login AND status = 'W' AND id != :id", $data);
        }
        try {
            $transaction = $payutcClient->getTransactionInfo(array("fun_id" => $fun_id, "tra_id" => $newResa->tra_id_payicam));
            if($transaction->status != $newResa->status) {
                $newResa->updateStatus($transaction->status);
                if ($transaction->status == 'V') {
                    $newResa->registerGuestsToTheGala();
                    $this->flash->addMessage('success', "Votre réservation a bien été prise en compte");
                } else {
                    $this->flash->addMessage('info', "Le status de votre réservation a été mis à jour de ".$newResa->status.' vers '.$transaction->status);
                }
                return $response->withStatus(303)->withHeader('Location', $this->router->pathFor('home'));
            }
        } catch (Exception $e) { var_dump($e->getMessage()); }
    }
    $userResaCount = count($UserReservation);
    extract(getUserReservationAndGuests($UserReservation, $prixPromo, $gingerUserCard, $DB)); // UserGuests, UserReservation, UserId

    extract(getStatsQuotas());//stats & quotas
    // Render index view
    $this->renderer->render($response, 'header.php', compact('flash', 'RouteHelper', 'Auth', $args));
    $editLink = $this->router->pathFor('edit');
    $this->renderer->render($response, 'home.php', compact('userResaCount', 'UserReservation', 'newResa', 'UserGuests', 'stats', 'quotas', 'canWeRegisterNewGuests', 'canWeEditOurReservation', 'emailContactGala', 'editLink', 'RouteHelper', $args));

    return $this->renderer->render($response, 'footer.php', compact('RouteHelper', 'Auth', $args));
})->setName('home');

////////////////////////////////////////////
// Routes pour l'édition des réservations //
////////////////////////////////////////////
function secureEditPart($Auth, $status, $UserReservation, $UserWaitingResa, $app, $response, $canWeRegisterNewGuests, $canWeEditOurReservation, $emailContactGala) {
    if (!$Auth->isLogged() || empty($status->user) || empty($status->application)) {
        if(isset($_SESSION['Auth'])) unset($_SESSION['Auth']);
        $app->flash->addMessage('warning', "Vous devez être connecté à PayIcam pour accéder aux inscriptions du Gala Icam");
        return $response->withStatus(303)->withHeader('Location', $app->router->pathFor('about'));
    }
    if (count($UserWaitingResa) >= 1) {$count = count($UserWaitingResa);
        $app->flash->addMessage('warning', 'Nous avons déjà '.(($count == 1)?'une':$count).' réservations en attente à votre nom... veuillez régler la facture sur PayIcam decelle-ci, ou veuillez l\'annuler si vous voulez éditer le nom de vos invités.');
        return $response->withStatus(303)->withHeader('Location', $app->router->pathFor('home'));
    }
    if (count($UserReservation) > 1) {
        $app->flash->addMessage('danger', 'Nous avons plusieurs réservations enregistrées à votre email...<br>
            <a href="mailto:'.$emailContactGala.'" title="'.$emailContactGala.'">Contactez nous</a> svp !');
        return $response->withStatus(303)->withHeader('Location', $app->router->pathFor('home'));
    } elseif (!$canWeRegisterNewGuests && count($UserReservation) == 0) {
        $app->flash->addMessage('warning', 'De nouvelles réservations ne sont plus autorisées.<br> Si vous avez un problème, vous pouvez encore contacter le <a href="mailto:'.$emailContactGala.'" title="'.$emailContactGala.'">Gala</a>');
        return $response->withStatus(303)->withHeader('Location', $app->router->pathFor('home'));
    } elseif (!$canWeEditOurReservation && $canWeRegisterNewGuests && count($UserReservation) == 1) {
        // On a pas le droit de modifier les infos que l'on a soumis, par contre on peut qd mm ajouter de nouveaux invités!
        $app->flash->addMessage('warning', 'Les modifications des réservations ne sont plus autorisées.<br> Si vous avez un problème, vous pouvez encore contacter le <a href="mailto:'.$emailContactGala.'" title="'.$emailContactGala.'">Gala</a>');
        return $response->withStatus(303)->withHeader('Location', $app->router->pathFor('home'));
    } elseif (!$canWeEditOurReservation && !$canWeRegisterNewGuests && count($UserReservation) == 1) {
        $app->flash->addMessage('warning', 'Les inscriptions sont closes, on se retrouve au Gala.<br> Si vous avez un problème, vous pouvez encore contacter le <a href="mailto:'.$emailContactGala.'" title="'.$emailContactGala.'">Gala</a>');
        return $response->withStatus(303)->withHeader('Location', $app->router->pathFor('home'));
    }
    return true;
}

function getPrixPromo($gingerUserCard) {
    if ($gingerUserCard->filiere == 'Apprentissage' || $gingerUserCard->filiere == 'Intégré') {
        try {
            $prixPromo = \PayIcam\Participant::getPricePromo($gingerUserCard->promo);
            $prixPromo['gameDePrix'] = $gingerUserCard->promo;
        } catch (Exception $e) {
            $prixPromo = \PayIcam\Participant::getPricePromo('Ingenieur');
            $prixPromo['gameDePrix'] = 'Ingenieur';
        }
    }
    return $prixPromo;
}

function getUserReservationAndGuests($UserReservation, $prixPromo, $gingerUserCard, $DB) {
    $UserGuests = array();
    if(count($UserReservation) == 1) { // il y avait déjà une réservation pour cet utilisateur
        $UserReservation = \PayIcam\Reservation::parseGuestData(current($UserReservation));
        $UserId = $UserReservation['id'];
        $UserGuests = $DB->query('SELECT * FROM icam_has_guest
                                    LEFT JOIN guests ON guest_id = id
                                  WHERE icam_id = :icam_id', array('icam_id' => $UserId));
        foreach ($UserGuests as $k => $v) {
            $UserGuests[$k] = \PayIcam\Reservation::parseGuestData($v);
        }
    } else { // Nouvelle réservation
        $UserReservation = array(
            'nom' => $gingerUserCard->nom,
            'prenom' => $gingerUserCard->prenom,
            'is_icam' => 1,
            'promo' => $gingerUserCard->promo,
            'email' => $gingerUserCard->mail,
            'sexe' => $gingerUserCard->sexe,
            'paiement' => 'PayIcam',
            'inscription' => 0,
            'price' => 0,
            'repas' => 0,
            'buffet' => 0,
            'tickets_boisson' => 0,
            'plage_horaire_entrees' => '',
            'image' => $gingerUserCard->img_link
        );
        $UserId = -1;
    }
    // var_dump($prixPromo['nbInvites']);
    // var_dump(count($UserGuests));
    if ($prixPromo['nbInvites'] - count($UserGuests) > 0) {
        $emptyUser = array('price' => 0, 'repas' => 0, 'buffet' => 0, 'tickets_boisson' => 0, 'is_icam' => 0, 'plage_horaire_entrees' => '');
        for ($i=0; $i < ($prixPromo['nbInvites'] - count($UserGuests) +1); $i++) {
            // echo "<p>".$i."</p>";
            $UserGuests[] = $emptyUser;
        }
        $emptyUser['repas'] = 1; // Pour montrer que le tableau en php ne se comporte pas comme un objet comme dans python ou js.
    }
    $dataResaForm = array('resa' => array_merge($UserReservation, array('invites'=>$UserGuests)));
    return compact('UserGuests', 'UserReservation', 'UserId', 'dataResaForm');
}

$app->get('/edit', function ($request, $response, $args) {
    // Initialisation, récupération variables utiles
    global $Auth, $payutcClient, $gingerUserCard, $DB, $canWeRegisterNewGuests, $canWeEditOurReservation,$RouteHelper;
    $flash = $this->flash;
    $RouteHelper = new \PayIcam\RouteHelper($this, $request, 'Edition réservation');
    $emailContactGala = $this->get('settings')['emailContactGala'];
    $status = $payutcClient->getStatus();
    $editLink = $this->router->pathFor('edit');
    $lien_creneau = $this->router->pathFor('modification_du_creneau');

    // Récupération infos utilisateur
    $mailPersonne = $Auth->getUserField('email');
    // $mailPersonne = 'hugo.leandri@2018.icam.fr';

    $UserReservation = $DB->query('SELECT * FROM guests WHERE email = :email', array('email' => $mailPersonne));
    $UserWaitingResa = $DB->query('SELECT * FROM reservations_payicam WHERE login = :login AND status = "W"', array('login' => $mailPersonne));

    //Sécurité, on vérifie plusieurs cas où il faudrait rediriger l'utilisateur
    $retourSecure = secureEditPart($Auth, $status, $UserReservation, $UserWaitingResa, $this, $response, $canWeRegisterNewGuests, $canWeEditOurReservation, $emailContactGala);
    if ($retourSecure !== true) return $retourSecure;

    // On continue
    if(count($UserReservation) == 0) $RouteHelper->webSiteTitle = "Nouvelle réservation";
    $prixPromo = getPrixPromo($gingerUserCard);
    extract(getUserReservationAndGuests($UserReservation, $prixPromo, $gingerUserCard, $DB)); // UserGuests, UserReservation, UserId

    $Form = new \PayIcam\Forms();
    $Form->set( (isset($_SESSION['newResa'])) ? $_SESSION['newResa'] : $dataResaForm );

    if( isset($_SESSION['newResa']) ) { unset($_SESSION['newResa']); } // On veut pas avoir le formulaire plus longtemps en session, il sera regénéré au pire.

    if( isset($_SESSION['formErrors']) ) {
        $Form->errors = $_SESSION['formErrors'];
        unset($_SESSION['formErrors']);
    }

    // Render index view
    $this->renderer->render($response, 'header.php', compact('flash', 'RouteHelper', 'Auth', $args));
    $this->renderer->render($response, 'edit_reservation.php', compact('Auth', 'UserId', 'UserReservation', 'UserGuests', 'canWeRegisterNewGuests', 'canWeEditOurReservation', 'emailContactGala', 'editLink', 'Form', 'prixPromo', 'gingerUserCard','RouteHelper','lien_creneau', $args));
    return $this->renderer->render($response, 'footer.php', compact('RouteHelper', 'Auth', $args));
})->setName('edit');
$app->get('/edit/', function ($request, $response, $args) {
    return $response->withStatus(301)->withHeader('Location', $this->router->pathFor('edit')); // code 301: redirection, déplacé pour toujours
});

//////////////////////////////
// Traitement du formulaire //
//////////////////////////////
function mergeUserReservations($array1, $array2, $prixPromo) {
    $retour = \PayIcam\Reservation::parseGuestData($array1['resa']);
    $icamValues2 = \PayIcam\Reservation::parseGuestData($array2['resa']);
    $icamGuests2 = $array2['resa']['invites'];
    unset($icamValues2['invites']);
    $retour = array_merge($retour, $icamValues2);
    foreach ($icamGuests2 as $k => $guest) {
        $retour['invites'][$k] = isset($retour['invites'][$k]) ? array_merge($retour['invites'][$k], \PayIcam\Reservation::parseGuestData($guest) ) : \PayIcam\Reservation::parseGuestData($guest) ;
    }
    return array('resa'=>$retour);
}
function checkUserFieldsIntegrity($newUser, $oldUser='') {
    $errors = array();
    if (empty($newUser['nom']) && empty($newUser['prenom']) && !empty($oldUser['nom'])) {
        $errors['nom'] = 'Vous ne pouvez pas retirer un invité';
        $errors['prenom'] = 'Vous ne pouvez pas retirer un invité';
    } elseif(empty($newUser['nom']) && !empty($newUser['prenom'])) {
        $errors['nom'] = 'Vous devez remplir le nom aussi';
    } elseif(!empty($newUser['nom']) && empty($newUser['prenom'])) {
        $errors['prenom'] = 'Vous devez remplir le prénom aussi';
    } elseif(empty($newUser['nom']) && empty($newUser['prenom'])) { // pas besoin d'aller plus loin, on a pas d'invité ! on l'ignorera anyway
        return array();
    }
    $ticket = intval($newUser['tickets_boisson']);
    $oldTicket = (isset($oldUser['tickets_boisson']))?intval($oldUser['tickets_boisson']):0;
    if ($ticket != 0 && $ticket != 10 && $ticket != 20 && $ticket != 30 && $ticket != 40 && $ticket != 50 && $ticket != $oldTicket) {
        $errors['tickets_boisson'] = 'Vous avez un problème avec les tickets boisson, vous avez le choix que entre 0, 10, 20, 30, 40 ou 50 tickets!';
    }

    return $errors;
}

function getBoolValue($str) {
    if ($str == '0' || $str == 0 || $str == 'false' || $str == 'False' || empty($str)) return 0;
    else return 1;
}
function guessSexe($prenom) {
    $prenoms = array('caroline','julia','emilie','claire','emmanuelle','camille','anaïs','djilane','josephine','anne-catherine','cécile','clotilde-marie','jeanne','marie','marine','aliénor','aurélie','marion','perrine','ragnheidur','juliette','coline','charlotte','mylène','claire-isabelle','paula','aude','adèle');
    return (in_array(strtolower($prenom), $prenoms))? 2 : 1;
}
function getBoolIntValues($field, $newResa, $curResa, $price=false) {
    $curResa = (!isset($curResa[$field]))? 0 : getBoolValue($curResa[$field]);
    $newResa = (getBoolValue($newResa[$field]) && ($price !== false && !is_null($price)))?1:0;
    return ($newResa>=$curResa)? getBoolValue($newResa): 1;
}
function getPrice($resa, $typeUser, $prixPromo) {
    $prix = $prixPromo[$typeUser]['soiree'];
    $prix += ($resa['repas'])?$prixPromo[$typeUser]['repas']:0;
    $prix += ($resa['buffet'])?$prixPromo[$typeUser]['buffet']:0;
    $prix += ($resa['tickets_boisson'])?$resa['tickets_boisson'] * 0.9 :0;
    return $prix;
}
function getIcamData($gingerUserCard, $prixPromo, $resa, $oldResa="") {
    $icamData = array(
        'nom' => $gingerUserCard->nom,
        'prenom' => $gingerUserCard->prenom,
        'is_icam' => 1,
        'paiement' => $resa['paiement'],
        'telephone' => nettoyer($resa['telephone']),
        'promo' => $gingerUserCard->promo,
        'email' => $gingerUserCard->mail,
        'sexe' => $gingerUserCard->sexe,
        'repas' => getBoolIntValues('repas', $resa, $oldResa, $prixPromo['prixIcam']['repas']),
        'buffet' => getBoolIntValues('buffet', $resa, $oldResa, $prixPromo['prixIcam']['buffet']),
        'tickets_boisson' => intval($resa['tickets_boisson']),
        'plage_horaire_entrees' => $resa['plage_horaire_entrees'],
        'image' => $gingerUserCard->img_link
    );
    if(!empty($oldResa['inscription'])) $icamData['inscription'] = $oldResa['inscription'];
    $icamData['price'] = getPrice($icamData, 'prixIcam', $prixPromo);
    return $icamData;
}
function getGuestData($guest, $prixPromo, $oldResa="") {
    $guestData = array(
        'nom' => nettoyer($guest['nom']),
        'prenom' => nettoyer($guest['prenom']),
        'is_icam' => 0,
        'paiement' => $guest['paiement'],
        'sexe' => guessSexe($guest['prenom']),
        'repas' => getBoolIntValues('repas', $guest, $oldResa, $prixPromo['prixInvite']['repas']),
        'buffet' => getBoolIntValues('buffet', $guest, $oldResa, $prixPromo['prixInvite']['buffet']),
        'plage_horaire_entrees' => $guest['plage_horaire_entrees'],
        'tickets_boisson' => intval($guest['tickets_boisson'])
    );
    if(!empty($oldResa['inscription'])) $guestData['inscription'] = $oldResa['inscription'];
    $guestData['price'] = getPrice($guestData, 'prixInvite', $prixPromo);
    return $guestData;
}
function sumUpNewOptions($newResa, $curResa, $options) {
    $prix += ($newResa['repas'])?$prixPromo[$typeUser]['repas']:0;
    $prix += ($newResa['buffet'])?$prixPromo[$typeUser]['buffet']:0;
    $prix += ($newResa['tickets_boisson'])?$resa['tickets_boisson'] * 0.9 :0;
    return $options;
}
function getUpdatedFields($newResa, $curResa) {
    $updates = array();
    foreach ($newResa as $k => $v) {
        if (!isset($curResa[$k]))
            $updates[] = $k;
        else if ($v != $curResa[$k])
            $updates[] = $k;
    }
    return $updates;
}

function nettoyer($chaine)
{
    $caracteres=array('a', 'Á' => 'a', 'Â' => 'a', 'Ä' => 'a', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ä' => 'a',
        'È' => 'e', 'É' => 'e', 'Ê' => 'e', 'Ë' => 'e', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', '€' => 'e',
        'Ì' => 'i', 'Í' => 'i', 'Î' => 'i', 'Ï' => 'i', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
        'Ò' => 'o', 'Ó' => 'o', 'Ô' => 'o', 'Ö' => 'o', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'ö' => 'o',
        'Ù' => 'u', 'Ú' => 'u', 'Û' => 'u', 'Ü' => 'u', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'µ' => 'u',
        'Œ' => 'oe', 'œ' => 'oe',
        '$' => 's', '0'=>'0');
    $chaine = strtr($chaine, $caracteres);
    $chaine = preg_replace('#[^A-Za-z0-9.-_]+#', '-', $chaine);

    return $chaine;
}

$app->post('/enreg', function($request, $response, $args){

    global $Auth,$settings, $payutcClient, $gingerUserCard, $DB, $canWeRegisterNewGuests, $canWeEditOurReservation;
    $flash = $this->flash;
    $confSQL = $settings['settings']['confSQL'];

    $post=$request->getParsedBody();

    try{
        $bd = new PDO('mysql:host='.$confSQL['sql_host'].';dbname='.$confSQL['sql_db'],$confSQL['sql_user'], $confSQL['sql_pass'], array(
                    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8',
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
        }
    catch(PDOException $e) {
        die('<h1>Impossible de se connecter a la base de donnee</h1><p>'.$e->getMessage().'<p>');
        };

    $participants = $bd->prepare('SELECT COUNT(*) FROM guests');
    $participants->execute();
    $nb_participants = $participants->fetch();

    $premier_creneau = $bd->prepare('SELECT COUNT(*) FROM guests WHERE plage_horaire_entrees =\'21h-21h45\'');
    $premier_creneau->execute();
    $nb_creneau1 = $premier_creneau->fetch();

    $deuxieme_creneau = $bd->prepare('SELECT COUNT(*) FROM guests WHERE plage_horaire_entrees = \'21h45-22h30\'');
    $deuxieme_creneau->execute();
    $nb_creneau2 = $deuxieme_creneau->fetch();

    $troisieme_creneau = $bd->prepare('SELECT COUNT(*) FROM guests WHERE plage_horaire_entrees = \'22h30-23h\'');
    $troisieme_creneau->execute();
    $nb_creneau3 = $troisieme_creneau->fetch();

    $quotat_creneau1 = $bd->prepare('SELECT value FROM configs WHERE name = \'quota_entree_21h_21h45\'');
    $quotat_creneau1->execute();
    $quotat1 = $quotat_creneau1->fetch();

    $quotat_creneau2 = $bd->prepare('SELECT value FROM configs WHERE name = \'quota_entree_21h45_22h30\'');
    $quotat_creneau2->execute();
    $quotat2 = $quotat_creneau2->fetch();

    $quotat_creneau3 = $bd->prepare('SELECT value FROM configs WHERE name = \'quota_entree_22h30_23h\'');
    $quotat_creneau3->execute();
    $quotat3 = $quotat_creneau3->fetch();
     
    if ($quotat1<$nb_creneau1){  //verifie si creneau dispo
        $creneau1=false;
    }else{
        $creneau1=true;
    } 

    if ($quotat2<$nb_creneau2){  //verifie si creneau dispo
        $creneau2=false;
    }else{
        $creneau2=true;
    } 

    if ($quotat3<$nb_creneau3){  //verifie si creneau dispo
        $creneau3=false;
    }else{
        $creneau3=true;
    }

    foreach ($post as $creneau) {
        if ($creneau == '21h-21h45' && $creneau1==false){
            $this->flash->addMessage('info', 'le premier créneau est complet');
            return $response->withStatus(303)->withHeader('Location', $this->router->pathFor('edit'));
        }
        if ($creneau == '21h45-22h30' && $creneau2==false){
            $this->flash->addMessage('info', 'le deuxième créneau est complet');
            return $response->withStatus(303)->withHeader('Location', $this->router->pathFor('edit'));
        }
        if ($creneau == '22h30-23h' && $creneau3==false){
            $this->flash->addMessage('info', 'le troisème créneau est complet');
            return $response->withStatus(303)->withHeader('Location', $this->router->pathFor('edit'));
        }
    }
    foreach ($post as $key => $value) {

        $key=preg_replace('#[^0-9]#',"",$key);

        $new_creneau = $bd->prepare('UPDATE guests SET plage_horaire_entrees = :horaire WHERE id = :login ');
        $new_creneau -> bindParam('horaire', $value, PDO::PARAM_STR);
        $new_creneau -> bindParam('login', $key, PDO::PARAM_INT);
        $new_creneau->execute();

    }

    $this->flash->addMessage('info', 'Vos changements de crénaux ont bien été pris en compte.');
        return $response->withStatus(303)->withHeader('Location', $this->router->pathFor('edit'));
});

$app->post('/edit', function ($request, $response, $args) {
    // Initialisation, récupération variables utiles
    global $Auth, $payutcClient, $gingerUserCard, $DB, $canWeRegisterNewGuests, $canWeEditOurReservation;
    $flash = $this->flash;
    $RouteHelper = new \PayIcam\RouteHelper($this, $request, 'Edition réservation');
    $emailContactGala = $this->get('settings')['emailContactGala'];
    $status = $payutcClient->getStatus();
    $editLink = $this->router->pathFor('edit');
    $lien_creneau = $this->router->pathFor('modification_du_creneau');

    // Récupération infos utilisateur
    $mailPersonne = $Auth->getUserField('email');
    // $mailPersonne = 'hugo.leandri@2018.icam.fr';

    $UserReservation = $DB->query('SELECT * FROM guests WHERE email = :email', array('email' => $mailPersonne));
    $UserWaitingResa = $DB->query('SELECT * FROM reservations_payicam WHERE login = :login AND status = "W"', array('login' => $mailPersonne));

    //Sécurité, on vérifie plusieurs cas où il faudrait rediriger l'utilisateur
    $retourSecure = secureEditPart($Auth, $status, $UserReservation, $UserWaitingResa, $this, $response, $canWeRegisterNewGuests, $canWeEditOurReservation, $emailContactGala);
    if ($retourSecure !== true) return $retourSecure;

    // On continue
    $prixPromo = getPrixPromo($gingerUserCard);
    extract(getUserReservationAndGuests($UserReservation, $prixPromo, $gingerUserCard, $DB)); // UserGuests, UserReservation, UserId, dataResaForm
    $_SESSION['newResa'] = mergeUserReservations( $dataResaForm , $request->getParsedBody(), $prixPromo );
    var_dump($_SESSION['newResa']['resa']['invites']);

    ////////////////////////////////////////////////////////
    // On va vérifier que les données postées sont bonnes //
    ////////////////////////////////////////////////////////
    $countAuthorizedGuests = max($prixPromo['nbInvites'], count($UserGuests)); // si qqn lui avait déjà autorisé plus d'invités on laisse faire
    echo "countAuthorizedGuests:".$countAuthorizedGuests;
    echo "count(_SESSIONnewResaresainvites):".count($_SESSION['newResa']['resa']['invites']);
    if (count($_SESSION['newResa']['resa']['invites']) > $countAuthorizedGuests) {
        $this->flash->addMessage('warning', 'Hé oh loulou, tu t\'es cru où ? Cherche pas, tu ne pourras plus gruger et rajouter des invités en plus des quotas !.<br>Tu as le droit qu\'à '.$prixPromo['nbInvites'].' invités, pas à '.count($request->getParsedBody()['resa']['invites']).' !!');
        $_SESSION['formErrors']['hasErrors'] = true;
    }
    // On va regarder si il y a des erreurs dans les réservations des utilisateurs.
    $errors = checkUserFieldsIntegrity($_SESSION['newResa']['resa']);
    if (!empty($errors)) $_SESSION['formErrors']['resa'] = $errors;
    foreach ($_SESSION['newResa']['resa']['invites'] as $k => $guest) {
        $curResaGuest = (!empty($UserGuests[$k]))?$UserGuests[$k]:'';
        $errors = checkUserFieldsIntegrity($guest, $curResaGuest);
        if (!empty($errors)) $_SESSION['formErrors']['resa']['invites'][$k] = $errors;
    }
    if (isset($_SESSION['formErrors'])) {
        $this->flash->addMessage('warning', 'Vous avez des erreurs dans le formulaire.');
        return $response->withStatus(303)->withHeader('Location', $this->router->pathFor('edit'));
    }

    ///////////////////////////////////////////
    // On continue vers l'INSERT ou l'UPDATE //
    ///////////////////////////////////////////
    // Pas besoin de vérifier si l'utilisateur existe déjà, on aurait son id déjà sinon !
    echo "<h2>Nouvelles données:</h2>";
    $statusFormSubmition = array();

    // Si on va faire une maj des options
    $Reservation = new \PayIcam\Reservation($mailPersonne, $gingerUserCard, $prixPromo, $this->get('settings')['articlesPayIcam'], $this);

    if ($UserId == -1) { // INSERT de l'icam & de ses invités!
        echo "<p>INSERT de l'icam & de ses invités!</p>";
        $icamData = getIcamData($gingerUserCard, $prixPromo, $_SESSION['newResa']['resa']);
        $icamData['paiement'] = 'PayIcam';
        $icamData['inscription'] = date('Y-m-d H:i:s');
        $Reservation->addGuest($icamData);

        var_dump($icamData);
        foreach ($_SESSION['newResa']['resa']['invites'] as $k => $guest) {
            if (empty($guest['nom']) && empty($guest['prenom'])) {
                echo "<p>pas d'invité à ajouter</p>"; continue; }
            $guestData = getGuestData($guest, $prixPromo);
            $guestData['paiement'] = 'PayIcam';
            $guestData['inscription'] = date('Y-m-d H:i:s');
            $Reservation->addGuest($guestData);
            var_dump($guestData);
        }
        $statusFormSubmition = $Reservation->statusMsg;
    } else { // UPDATE
        echo '<p>UPDATE de la personne</p>';
        // UPDATE de la personne
        $icamData = getIcamData($gingerUserCard, $prixPromo, $_SESSION['newResa']['resa'], $UserReservation);
        $icamData['id'] = intval($UserReservation['id']);
        var_dump($icamData);
        $Reservation->addIcamId($icamData['id']);
        if ($icamData['price'] > $UserReservation['price'] ) {
            echo "<p>Oh on a de nouvelles options $$ !</p>";
            $updatedFields = getUpdatedFields($icamData, $UserReservation);
            if (in_array('repas', $updatedFields) || in_array('buffet', $updatedFields) || in_array('tickets_boisson', $updatedFields)) {
                $Reservation->addGuest($icamData, $updatedFields, $UserReservation['price']);
            } else {
                echo "<p>Euhm. vous avez un prix spécial ! ça devrait être".$icamData['price']." au lieu de ".$UserReservation['price']."</p>";
            }
        } else {
            $updatedFields = getUpdatedFields($icamData, $UserReservation);
            if (!empty($updatedFields)) {
                echo "<p>UPDATE des champs ".json_encode($updatedFields)."</p>";
                $statusFormSubmition['updateFields']['data'][] = array_merge($icamData, array('updatedFields'=>$updatedFields));
                $statusFormSubmition['updateFields']['msg'][] = 'MAJ champs'.json_encode($updatedFields).' pour '.$icamData['prenom'].' '.$icamData['nom'];
            } else echo "<p>En fait non rien à mettre à jour !</p>";
        }
        // Checker les invités: update ? insert ?
        foreach ($_SESSION['newResa']['resa']['invites'] as $k => $guest) {
            if (empty($guest['nom']) && empty($guest['prenom'])) {
                echo "<p>pas d'invité #".$k." à ajouter</p>"; continue; }
            elseif(empty($UserGuests[$k]['nom'])) {
                echo "<p>INSERT de l'invité #".$k."</p>";
                $guestData = getGuestData($guest, $prixPromo, isset($UserGuests[$k])? $UserGuests[$k]:'');
                $guestData['paiement'] = 'PayIcam';
                $guestData['inscription'] = date('Y-m-d H:i:s');
                var_dump($guestData);

                $Reservation->addGuest($guestData);
            } else {
                echo "<p>UPDATE de l'invité #".$k."</p>";
                $guestData = getGuestData($guest, $prixPromo, $UserGuests[$k]);
                $guestData['id'] = intval($UserGuests[$k]['id']);
                var_dump($guestData);
                if ($guestData['price'] > $UserGuests[$k]['price'] ) {
                    echo "<p>Oh on a de nouvelles options $$ !</p>";
                    $updatedFields = getUpdatedFields($guestData, $UserGuests[$k]);
                    if (in_array('repas', $updatedFields) || in_array('buffet', $updatedFields) || in_array('tickets_boisson', $updatedFields)) {
                        $Reservation->addGuest($guestData, $updatedFields, $UserGuests[$k]['price']);
                    } else {
                        echo "<p>Euhm. vous avez un prix spécial ! ça devrait être".$guestData['price']." au lieu de ".$UserGuests[$k]['price']."</p>";
                    }
                } else {
                    $updatedFields = getUpdatedFields($guestData, $UserGuests[$k]);
                    if (!empty($updatedFields)) {
                        echo "<p>UPDATE des champs ".json_encode($updatedFields)."</p>";
                        $statusFormSubmition['updateFields']['data'][] = array_merge($guestData, array('updatedFields'=>$updatedFields));
                        $statusFormSubmition['updateFields']['msg'][] = 'MAJ champs'.json_encode($updatedFields).' pour '.$guestData['prenom'].' '.$guestData['nom'];

                    } else echo "<p>En fait non rien à mettre à jour !</p>";
                }

            }
        }
    }

    if (!empty($Reservation->statusMsg['insertGuest'])) {
        $statusFormSubmition['insertGuest'] = $Reservation->statusMsg['insertGuest'];
    }
    if (!empty($Reservation->statusMsg['updateOptions'])) {
        $statusFormSubmition['updateOptions']['msg'] = $Reservation->statusMsg['updateOptions'];
    }

    echo "<hr>";
    echo "<h2>Récap changements</h2>";
    if (empty($statusFormSubmition)) {
        unset($_SESSION['newResa']);
        $this->flash->addMessage('info', "Vous n'avez rien modifié");
        return $response->withStatus(303)->withHeader('Location', $this->router->pathFor('edit'));
    } elseif(isset($statusFormSubmition['insertIcam'])) { // On a déjà une résa pour l'icam !
        echo '<p>'.$statusFormSubmition['insertIcam'].'</p>';
        if (!empty($statusFormSubmition['insertGuest'])) {
            echo '<p>'.implode('<br>', $statusFormSubmition['insertGuest']).'</p>';
        }
    } else { // On a déjà une résa pour l'icam !
        if(isset($statusFormSubmition['updateFields'])) { // On peut faire les MAJ dès maintenant ! il n'y a pas de sous en jeu !
            foreach ($statusFormSubmition['updateFields']['data'] as $guest) {
                $data = array();   $updatedFields = array();
                foreach ($guest['updatedFields'] as $field) {
                    $data[$field] = $guest[$field];
                    $updatedFields[] = $field.' = :'.$field;
                }
                $data['id'] = $guest['id'];
                $DB->query("UPDATE guests SET ".implode(', ', $updatedFields)." WHERE id = :id", $data);
            }
            $this->flash->addMessage('success', 'Les champs ont bien été mit à jours.');//implode('<br>', $statusFormSubmition['updateFields']['msg'])
            if (!isset($statusFormSubmition['updateOptions']) && !isset($statusFormSubmition['insertGuest'])) {
                unset($_SESSION['newResa']);
                return $response->withStatus(303)->withHeader('Location', $this->router->pathFor('home'));
            }
        }
        if(isset($statusFormSubmition['updateOptions'])) {
            echo '<p>'.implode('<br>', $statusFormSubmition['updateOptions']['msg']).'</p>';
        }
        if(isset($statusFormSubmition['insertGuest'])) {
            echo '<p>'.implode('<br>', $statusFormSubmition['insertGuest']).'</p>';
        }
    }

    if ($Reservation->hasNewReservation()) {
        unset($_SESSION['newResa']);
        echo "<hr>";
        echo "<h2>Articles de la nouvelle réservation:</h2>";
        echo '<ul>';
        foreach ($Reservation->articles as $a) {
            echo '<li>'.$a['count'].' x '.$a['article']['nom'].'('.$a['article']['price'].'€) = '.($a['count']*$a['article']['price']).'€</li>';
        }
        echo '</ul>';
        echo "<p>Soit, un total de ".$Reservation->price."€ à payer</p>";
        extract(getStatsQuotas());// stats, quotas
        echo "<p>stats gala:".json_encode($stats)."</p>";
        echo "<p>quotas:".json_encode($quotas)."</p>";
        echo "résa soirees".$Reservation->soirees;
        echo ", résa repas".$Reservation->repas;
        echo ", résa buffets".$Reservation->buffets;
        echo ", Reservation > creneauxEntrees".json_encode($Reservation->creneauxEntrees);
        $placesRestantes = $Reservation->getQuotasRestant($stats, $quotas);
        echo "<p>places restantes:".json_encode($placesRestantes)."</p>";

        if (   ($Reservation->soirees > 0 && $placesRestantes['soirees'] >= 0 || $Reservation->soirees == 0)
            && ($Reservation->repas > 0 && $placesRestantes['repas'] >= 0 || $Reservation->repas == 0)
            && ($Reservation->buffets > 0 && $placesRestantes['buffets'] >= 0 || $Reservation->buffets == 0)
            && ($Reservation->creneauxEntrees['21h-21h45'] > 0 && $placesRestantes['creneau_21h_21h45'] >= 0 || $Reservation->creneauxEntrees['21h-21h45'] == 0)
            && ($Reservation->creneauxEntrees['21h45-22h30'] > 0 && $placesRestantes['creneau_21h45_22h30'] >= 0 || $Reservation->creneauxEntrees['21h45-22h30'] == 0)
            && ($Reservation->creneauxEntrees['22h30-23h'] > 0 && $placesRestantes['creneau_22h30_23h'] >= 0 || $Reservation->creneauxEntrees['22h30-23h'] == 0)
        ) {
            $Reservation->save();
            return $response->withStatus(303)->withHeader('Location', $Reservation->tra_url_payicam);
            echo "<p><strong>Votre réservation est prête à être soumise</strong>, vous allez être redirigé sur PayIcam pour effectuer le paiement:</p>";
            echo '<p><a href="'.$Reservation->tra_url_payicam.'">Valider la commande</a></p>';
        } else {
            $msg = "";
            $msg .= "<p>Votre réservation était prête à être soumise... <br><strong>MAIS</strong> vous n'avez pas été assez vite et certains des quotas ont été atteints:</p>";
            if ($placesRestantes['soirees'] < 0) {
                $msg .= "<p>Plus de places à la soirée</p>";
            }if ($placesRestantes['repas'] < 0) {
                $msg .= "<p>Plus de places pour le repas</p>";
            }if ($placesRestantes['buffets'] < 0) {
                $msg .= "<p>Plus de places pour la conférence</p>";
            }if ($placesRestantes['creneau_21h_21h45'] < 0) {
                $msg .= "<p>Plus de places pour le créneau 21h - 21h35</p>";
            }if ($placesRestantes['creneau_21h45_22h30'] < 0) {
                $msg .= "<p>Plus de places pour le créneau 21h50 - 22h25</p>";
            }if ($placesRestantes['creneau_22h30_23h'] < 0) {
                $msg .= "<p>Plus de places pour le créneau 22h40 - 23h15</p>";
            }
            $this->flash->addMessage('danger', $msg);
            echo $msg;
            echo '<p><a href="'.$this->router->pathFor('edit').'">retour édition</a></p>';
        }
    }
    return $response->withStatus(303)->withHeader('Location', $this->router->pathFor('edit'));
});


$app->get('/cancel', function ($request, $response, $args) {
    // Initialisation, récupération variables utiles
    global $gingerUserCard, $Auth, $DB, $canWeRegisterNewGuests, $canWeEditOurReservation;

    // Récupération infos utilisateur
    $mailPersonne = $Auth->getUserField('email');
    // $mailPersonne = 'hugo.leandri@2018.icam.fr';

    $UserWaitingResa = $DB->query('SELECT * FROM reservations_payicam WHERE login = :login AND status = "W"', array('login' => $mailPersonne));
    if (count($UserWaitingResa) >= 1) {
        $DB->query("UPDATE reservations_payicam SET status = 'A' WHERE login = :login AND status = 'W'", ['login'=>$mailPersonne]);
    }
    $this->flash->addMessage('info', "Vous avez bien annulé votre réservation au Gala");
    return $response->withStatus(303)->withHeader('Location', $this->router->pathFor('home'));
})->setName('cancel');

$app->get('/callback', function ($request, $response, $args) {
    global $payutcClient, $DB, $Auth;
    $fun_id = $this->get('settings')['fun_id'];
    $UserWaitingResa = $DB->query('SELECT * FROM reservations_payicam WHERE status = "W"');
    var_dump($UserWaitingResa);
    if (count($UserWaitingResa) >= 1) {
        foreach ($UserWaitingResa as $resa) {
            try {
                $transaction = $payutcClient->getTransactionInfo(array("fun_id" => $fun_id, "tra_id" => $resa['tra_id_payicam']));
                if($transaction->status != $resa['status']) {
                    $Reservation = new \PayIcam\Reservation($resa, null, null, $this->get('settings')['articlesPayIcam'], $this);
                    $Reservation->updateStatus($transaction->status);
                    if ($transaction->status == 'V') {
                        $Reservation->registerGuestsToTheGala();
                        $this->flash->addMessage('success', "Votre réservation a bien été prise en compte");
                    } else {
                        $this->flash->addMessage('info', "Le status de votre réservation a été mis à jour de ".$curResa['status'].' vers '.$transaction->status);
                    }

                    $data = ['status'=>$transaction->status, 'date_paiement'=>date("Y-m-d H:i:s"), 'id'=>$resa['id']];
                    $DB->query("UPDATE reservations_payicam SET status = :status, date_paiement = :date_paiement WHERE id = :id", $data);
                    if ($Auth->getUserField('email') == $resa['login']) {
                        $this->flash->addMessage('info', "Le status de votre réservation a été mis à jour de ".$resa['status'].' vers '.$transaction->status);
                    }
                }
            } catch (Exception $e) {
                var_dump($e->getMessage());
            }
        }
    }
    return $response->withStatus(303)->withHeader('Location', $this->router->pathFor('home'));
})->setName('callback');


//////////////////////////////
// Routes pour la connexion //
//////////////////////////////
$app->get('/login', function ($request, $response, $args) {
    global $Auth, $payutcClient;
    $RouteHelper = new \PayIcam\RouteHelper($this, $request, 'Login');

    $this->flash->addMessage('info', 'Vous avez bien été connecté avec le CAS.');

    $ticket = $request->getParam('ticket');
    $service = $RouteHelper->curPageBaseUrl. '/login';

    if(empty($ticket)) {
        $casUrl = $payutcClient->getCasUrl()."login?service=".urlencode($service);
        return $response->withStatus(303)->withHeader('Location', $casUrl);
    } else {
        try {
            $result = $payutcClient->loginCas(array("ticket" => $ticket, "service" => $service));
            $status = $payutcClient->getStatus();

            $_SESSION['payutc_cookie'] = $payutcClient->cookie;
            $userRank = $payutcClient->getUserLevel();
            $role = $Auth->getRole($userRank);

            $_SESSION['Auth'] = array(
                'email' => $status->user,
                'firstname' => $status->user_data->firstname,
                'lastname' => $status->user_data->lastname,
                'slug' => $role['slug'],
                'roleName' => $role['name'],
                'level' => $userRank
            );
        } catch (Exception $e) {
            if (strpos($e, 'UserNotFound') !== false ) {
                $this->flash->addMessage('info', 'Vous ne faites pas encore parti de PayIcam, inscrivez vous.');
                return $response->withStatus(303)->withHeader('Location', '../casper');
            }
        }
        try {
            $result = $payutcClient->loginApp(array("key"=>$this->get('settings')['PayIcam']['payutc_key']));
        } catch (\JsonClient\JsonException $e) {
            $this->flash->addMessage('danger', "error login application");
            return $response->withStatus(303)->withHeader('Location', $this->router->pathFor('about'));
        }
        $status = $payutcClient->getStatus();

        return $response->withStatus(303)->withHeader('Location', $this->router->pathFor('home'));
    }
})->setName('login');

$app->get('/logout', function ($request, $response, $args) {
    global $Auth, $payutcClient;
    $RouteHelper = new \PayIcam\RouteHelper($this, $request, 'Login');

    $status = $payutcClient->getStatus();
    if($status->user) {
        $payutcClient->logout();
    }
    if($Auth->isLogged()) {
        $service = $RouteHelper->curPageBaseUrl. '/login';
        $casUrl = $payutcClient->getCasUrl()."logout?url=".urlencode($service);
        session_destroy();
        return $response->withStatus(303)->withHeader('Location', $casUrl);
    } else {
        session_destroy();
        return $response->withStatus(303)->withHeader('Location', $this->router->pathFor('home'));
    }
})->setName('logout');