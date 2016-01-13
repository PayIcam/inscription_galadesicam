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
$app->get('/', function ($request, $response, $args) {
    global $Auth, $payutcClient, $gingerClient, $DB, $canWeRegisterNewGuests, $canWeEditOurReservation;

    $status = $payutcClient->getStatus();
    if (!$Auth->isLogged() || empty($status->user) || empty($status->application)){
        if(isset($_SESSION['Auth'])) unset($_SESSION['Auth']); 
        $this->flash->addMessage('warning', "Vous devez être connecté à PayIcam pour accéder aux inscriptions du Gala de Icam");
        return $response->withStatus(303)->withHeader('Location', $this->router->pathFor('about'));
    }
    $flash = $this->flash;
    $RouteHelper = new \PayIcam\RouteHelper($this, $request, 'Accueil');
    $emailContactGala = $this->get('settings')['emailContactGala'];
    
    // Sample log message
    // $this->logger->info("Slim-Skeleton '/' index");

    // $UserReservation = $DB->query('SELECT * FROM guests WHERE email = :email', array('email' => 'hugo.leandri@2018.icam.fr'));
    $UserReservation = $DB->query('SELECT * FROM guests WHERE email = :email', array('email' => $Auth->getUserField('email')));
    $gingerUserCard = $gingerClient->getUser($Auth->getUserField('email'));
    if (empty($gingerUserCard)) { // l'utilisateur n'avait jamais été ajouté à Ginger O.o
        $gingerUserCard = $gingerClient->getUser($Auth->getUserField('email'));
    }if (empty($gingerUserCard)) { // l'utilisateur n'a pas un mail icam valide // on ne devrait jamais avoir cette erreur car on passe par payutc et lui a besoin d'avoir ginger qui marche ... je crois ...
        $this->flash->addMessage('warning', "Votre Mail Icam n'est pas reconnu par Ginger...");
        return $response->withStatus(303)->withHeader('Location', $this->router->pathFor('about'));
    }
    
    $UserGuests = array();
    if (count($UserReservation) == 1) {
        $UserId = $UserReservation[0]['id'];
        $UserGuests = $DB->query('SELECT * FROM icam_has_guest
                                    LEFT JOIN guests ON guest_id = id
                                  WHERE icam_id = :icam_id', array('icam_id' => $UserId));
    }

    // Render index view
    $this->renderer->render($response, 'header.php', compact('flash', 'RouteHelper', 'Auth', $args));
    $editLink = $this->router->pathFor('edit');
    $this->renderer->render($response, 'home.php', compact('UserReservation', 'UserGuests', 'canWeRegisterNewGuests', 'canWeEditOurReservation', 'emailContactGala', 'editLink', $args));

    return $this->renderer->render($response, 'footer.php', compact('RouteHelper', 'Auth', $args));
})->setName('home');

////////////////////////////////////////////
// Routes pour l'édition des réservations //
////////////////////////////////////////////
function secureEditPart($Auth, $status, $UserReservation, $app, $response, $canWeRegisterNewGuests, $canWeEditOurReservation, $emailContactGala){
    if (!$Auth->isLogged() || empty($status->user) || empty($status->application)){
        if(isset($_SESSION['Auth'])) unset($_SESSION['Auth']); 
        $app->flash->addMessage('warning', "Vous devez être connecté à PayIcam pour accéder aux inscriptions du Gala de Icam");
        return $response->withStatus(303)->withHeader('Location', $app->router->pathFor('about'));
    }
    if (count($UserReservation) > 1){
        $app->flash->addMessage('danger', 'Nous avons plusieurs réservations enregistrées à votre email...<br>
            <a href="mailto:'.$emailContactGala.'" title="'.$emailContactGala.'">Contactez nous</a> svp !');
        return $response->withStatus(303)->withHeader('Location', $app->router->pathFor('home'));
    }elseif (!$canWeRegisterNewGuests && count($UserReservation) == 0){
        $app->flash->addMessage('warning', 'De nouvelles réservations ne sont plus autorisées.<br> Si vous avez un problème, vous pouvez encore contacter le <a href="mailto:'.$emailContactGala.'" title="'.$emailContactGala.'">Gala</a>');
        return $response->withStatus(303)->withHeader('Location', $app->router->pathFor('home'));
    }elseif (!$canWeEditOurReservation && $canWeRegisterNewGuests && count($UserReservation) == 1){
        // On a pas le droit de modifier les infos que l'on a soumis, par contre on peut qd mm ajouter de nouveaux invités!
        $app->flash->addMessage('warning', 'Les modifications des réservations ne sont plus autorisées.<br> Si vous avez un problème, vous pouvez encore contacter le <a href="mailto:'.$emailContactGala.'" title="'.$emailContactGala.'">Gala</a>');
        return $response->withStatus(303)->withHeader('Location', $app->router->pathFor('home'));
    }elseif (!$canWeEditOurReservation && !$canWeRegisterNewGuests && count($UserReservation) == 1){
        $app->flash->addMessage('warning', 'Les inscriptions sont closes, on se retrouve au Gala.<br> Si vous avez un problème, vous pouvez encore contacter le <a href="mailto:'.$emailContactGala.'" title="'.$emailContactGala.'">Gala</a>');
        return $response->withStatus(303)->withHeader('Location', $app->router->pathFor('home'));
    }
    return true;
}

function getPrixPromo($gingerUserCard){
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

function getUserReservationAndGuests($UserReservation, $prixPromo, $gingerUserCard, $DB){
    $UserGuests = array();
    if(count($UserReservation) == 1){ // il y avait déjà une réservation pour cet utilisateur
        $UserReservation = current($UserReservation);
        $UserId = $UserReservation['id'];
        $UserGuests = $DB->query('SELECT * FROM icam_has_guest
                                    LEFT JOIN guests ON guest_id = id
                                  WHERE icam_id = :icam_id', array('icam_id' => $UserId));
    }else{ // Nouvelle réservation
        $UserReservation = array(
            'nom' => $gingerUserCard->nom,
            'prenom' => $gingerUserCard->prenom,
            'is_icam' => 1,
            'promo' => $gingerUserCard->promo,
            'email' => $gingerUserCard->mail,
            'sexe' => $gingerUserCard->sexe,
            'paiement' => 'PayIcam',
            'price' => 0,
            'repas' => 0,
            'buffet' => 0,
            'tickets_boisson' => 0,
            'image' => $gingerUserCard->img_link
        );
        $UserId = -1;
        $emptyUser = array('price' => 0, 'repas' => 0, 'buffet' => 0, 'tickets_boisson' => 0, 'is_icam' => 0);
        for ($i=0; $i < $prixPromo['nbInvites']; $i++) { 
            $UserGuests[] = $emptyUser;
        }
        $emptyUser['repas'] = 1;
    }
    $dataResaForm = array('resa' => array_merge($UserReservation, array('invites'=>$UserGuests)));
    return compact('UserGuests', 'UserReservation', 'UserId', 'dataResaForm');
}

$app->get('/edit', function ($request, $response, $args) {
    // Initialisation, récupération variables utiles
    global $Auth, $payutcClient, $gingerClient, $DB, $canWeRegisterNewGuests, $canWeEditOurReservation;
    $flash = $this->flash;
    $RouteHelper = new \PayIcam\RouteHelper($this, $request, 'Edition réservation');
    $emailContactGala = $this->get('settings')['emailContactGala'];
    $status = $payutcClient->getStatus();
    $editLink = $this->router->pathFor('edit');

    // Récupération infos utilisateur
    $mailPersonne = $Auth->getUserField('email');
    // $mailPersonne = 'hugo.leandri@2018.icam.fr';
    $gingerUserCard = $gingerClient->getUser($mailPersonne);
    $UserReservation = $DB->query('SELECT * FROM guests WHERE email = :email', array('email' => $mailPersonne));

    //Sécurité, on vérifie plusieurs cas où il faudrait rediriger l'utilisateur
    $retourSecure = secureEditPart($Auth, $status, $UserReservation, $this, $response, $canWeRegisterNewGuests, $canWeEditOurReservation, $emailContactGala);
    if ($retourSecure !== true) return $retourSecure;  

    // On continue
    if(count($UserReservation) == 0) $RouteHelper->webSiteTitle = "Nouvelle réservation";
    $prixPromo = getPrixPromo($gingerUserCard);
    extract(getUserReservationAndGuests($UserReservation, $prixPromo, $gingerUserCard, $DB)); // UserGuests, UserReservation, UserId
    
    $Form = new \PayIcam\Forms();
    $Form->set( (isset($_SESSION['newResa'])) ? $_SESSION['newResa'] : $dataResaForm );
    
    if( isset($_SESSION['newResa']) ){ var_dump($_SESSION['newResa']);var_dump($_SESSION['newResa']['resa']['invites']);unset($_SESSION['newResa']); } // On veut pas avoir le formulaire plus longtemps en session, il sera regénéré au pire.

    if( isset($_SESSION['formErrors']) ){
        $Form->errors = $_SESSION['formErrors'];
        unset($_SESSION['formErrors']);
    }

    // Render index view
    $this->renderer->render($response, 'header.php', compact('flash', 'RouteHelper', 'Auth', $args));
    $this->renderer->render($response, 'edit_reservation.php', compact('Auth', 'UserId', 'UserReservation', 'UserGuests', 'canWeRegisterNewGuests', 'canWeEditOurReservation', 'emailContactGala', 'editLink', 'Form', 'prixPromo', 'gingerUserCard', $args));
    return $this->renderer->render($response, 'footer.php', compact('RouteHelper', 'Auth', $args));
})->setName('edit');
$app->get('/edit/', function ($request, $response, $args) {
    return $response->withStatus(301)->withHeader('Location', $this->router->pathFor('edit')); // code 301: redirection, déplacé pour toujours
});

//////////////////////////////
// Traitement du formulaire //
//////////////////////////////
function mergeUserReservations($array1, $array2){
    $retour = $array1['resa'];
    $icamValues2 = $array2['resa'];
    $icamGuests2 = $array2['resa']['invites'];
    unset($icamValues2['invites']);
    $retour = array_merge($retour, $icamValues2);
    foreach ($retour['invites'] as $k => $guest) {
        // On boucle sur les invités du premier tableau, soit la première résa pour que le gars ne puisse pas tricher !!
        // Si il y a plus d'invités dans la 2e résa que la 1 c'est qu'il a du essayer d'en rajouter à la main...
        // et on les prend dans l'ordre !
        if (isset($icamGuests2[$k])) {
            $retour['invites'][$k] = array_merge($guest, $icamGuests2[$k]);
        } // sinon, ba yen a pas, on garde ceux du premier tableau.
    }
    return array('resa'=>$retour);
}
function checkUserFieldsIntegrity($newUser, $oldUser=''){
    $errors = array();
    if (empty($newUser['nom']) && empty($newUser['prenom']) && !empty($oldUser['nom'])) {
        $errors['nom'] = 'Vous ne pouvez pas retirer un invité';
        $errors['prenom'] = 'Vous ne pouvez pas retirer un invité';
    }elseif(empty($newUser['nom']) && !empty($newUser['prenom'])){
        $errors['nom'] = 'Vous devez remplir le nom aussi';
    }elseif(!empty($newUser['nom']) && empty($newUser['prenom'])){
        $errors['prenom'] = 'Vous devez remplir le prénom aussi';
    }elseif(empty($newUser['nom']) && empty($newUser['prenom'])){ // pas besoin d'aller plus loin, on a pas d'invité ! on l'ignorera anyway
        return array();
    }
    $ticket = intval($newUser['tickets_boisson']);
    $oldTicket = (isset($oldUser['tickets_boisson']))?intval($oldUser['tickets_boisson']):0;
    if ($ticket != 0 && $ticket != 10 && $ticket != $oldTicket) {
        $errors['tickets_boisson'] = 'Vous avez un problème avec les tickets boisson, vous avez le choix que entre 0 ou 10 tickets!';
    }
    return $errors;
}

$app->post('/edit', function ($request, $response, $args) {
    // Initialisation, récupération variables utiles
    global $Auth, $payutcClient, $gingerClient, $DB, $canWeRegisterNewGuests, $canWeEditOurReservation;
    $flash = $this->flash;
    $RouteHelper = new \PayIcam\RouteHelper($this, $request, 'Edition réservation');
    $emailContactGala = $this->get('settings')['emailContactGala'];
    $status = $payutcClient->getStatus();
    $editLink = $this->router->pathFor('edit');

    // Récupération infos utilisateur
    $mailPersonne = $Auth->getUserField('email');
    // $mailPersonne = 'hugo.leandri@2018.icam.fr';
    $gingerUserCard = $gingerClient->getUser($mailPersonne);
    $UserReservation = $DB->query('SELECT * FROM guests WHERE email = :email', array('email' => $mailPersonne));

    //Sécurité, on vérifie plusieurs cas où il faudrait rediriger l'utilisateur
    $retourSecure = secureEditPart($Auth, $status, $UserReservation, $this, $response, $canWeRegisterNewGuests, $canWeEditOurReservation, $emailContactGala);
    if ($retourSecure !== true) return $retourSecure;  

    // On continue
    $prixPromo = getPrixPromo($gingerUserCard);
    extract(getUserReservationAndGuests($UserReservation, $prixPromo, $gingerUserCard, $DB)); // UserGuests, UserReservation, UserId, dataResaForm

    $_SESSION['newResa'] = mergeUserReservations( $dataResaForm , $request->getParsedBody() );
    ////////////////////////////////////////////////////////
    // On va vérifier que les données postées sont bonnes //
    ////////////////////////////////////////////////////////
    if (count($_SESSION['newResa']['resa']['invites']) > count($UserGuests)) {
        $this->flash->addMessage('warning', 'Hé oh loulou, tu t\'es cru où ? Cherche pas, tu ne pourras plus gruger et rajouter des invités en plus des quotas !.<br>Tu as le droit qu\'à '.count($UserGuests).' invités, pas à '.count($request->getParsedBody()['resa']['invites']).' !!');
        $_SESSION['formErrors']['hasErrors'] = true;
    }
    // On va regarder si il y a des erreurs dans les réservations des utilisateurs.
    $_SESSION['formErrors']['resa'] = checkUserFieldsIntegrity($_SESSION['newResa']['resa']);
    foreach ($_SESSION['newResa']['resa']['invites'] as $k => $guest) {
        if ($k >= count($UserGuests)) break; // ça sert à rien d'aller plus loin, il a triché !
        $_SESSION['formErrors']['resa']['invites'][$k] = checkUserFieldsIntegrity($guest, $UserGuests[$k]);
    }

    if (isset($_SESSION['formErrors']))
        $this->flash->addMessage('warning', 'Vous avez des erreurs dans le formulaire.');
    else
        $this->flash->addMessage('success', "wahou pas d'erreurs pour le moment !");

    return $response->withStatus(303)->withHeader('Location', $this->router->pathFor('edit'));
    
    $Form = new \PayIcam\Form();
    $validate = array(
        'nom'    => array('rule'=>'notEmpty','message' => 'Entrez votre nom'),
        'prenom' => array('rule'=>'notEmpty','message' => 'Entrez votre prénom'),
        'mail' => array('rule'=>'^[a-z-]+[.]+[a-z-]+([.0-9a-z-]+)?@(mgf\.)?([0-9]{4}[.])?icam[.]fr$','message' => 'Entrez un email Icam valide !')
    );
    $Form->setValidates($validate);

    $d = $Member->checkForm($_POST); // $_POST for invite table : 'login','slug','nom','content','order'
    $Form->set($d);
    if ($Form->validates($d)) { // fin pré-traitement
        if ($d['login'] == -1 && current($DB->queryFirst('SELECT COUNT(*) FROM users WHERE login = :login',array('login'=>$d['mail'])))) {
            $Form->errors['mail'] = 'Utilisateur déjà existant !!';
        }elseif ($d['login'] != -1 && $d['login'] != $d['mail'] && current($DB->queryFirst('SELECT COUNT(*) FROM users WHERE login = :login',array('login'=>$d['mail']))) ) {
            $Form->errors['mail'] = 'Utilisateur déjà existant !! Vous ne pouvez pas changer vers le login/mail : '.$d['mail'];
        }elseif (!empty($d['badge_uid']) && current($DB->queryFirst('SELECT COUNT(*) FROM users WHERE login != :login AND badge_uid = :badge_uid',array('login'=>$d['login'],'badge_uid'=>$d['badge_uid']))) ) {
            $Form->errors['badge_uid'] = 'Badge '.$d['badge_uid'].' déjà utilisé !';
        }else{
            $Member->save();
            header('Location:admin_edit_member.php?login='.$Member->login);exit;
        }
    }

    // return $response->withStatus(303)->withHeader('Location', $this->router->pathFor('edit'));
});


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
            if (strpos($e, 'UserNotFound') !== false ){
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