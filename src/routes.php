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
function mergeUserReservations($array1, $array2, $prixPromo){
    $retour = $array1['resa'];
    $icamValues2 = $array2['resa'];
    $icamGuests2 = $array2['resa']['invites'];
    unset($icamValues2['invites']);
    $retour = array_merge($retour, $icamValues2);
    foreach ($icamGuests2 as $k => $guest) {
        // On boucle sur les invités du premier tableau, soit la première résa pour que le gars ne puisse pas tricher !!
        // Si il y a plus d'invités dans la 2e résa que la 1 c'est qu'il a du essayer d'en rajouter à la main...
        // et on les prend dans l'ordre !
        if ($k < $prixPromo['nbInvites']) {
            $retour['invites'][$k] = isset($retour['invites'][$k]) ? array_merge($retour['invites'][$k], $icamGuests2[$k]) : $icamGuests2[$k];
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

function getBoolValue($str){
    if ($str == '0' || $str == 0 || $str == 'false' || $str == 'False' || empty($str)) return 0;
    else return 1;
}
function guessSexe($prenom){
    $prenoms = array('caroline','julia','emilie','claire','emmanuelle','camille','anaïs','djilane','josephine','anne-catherine','cécile','clotilde-marie','jeanne','marie','marine','aliénor','aurélie','marion','perrine','ragnheidur','juliette','coline','charlotte','mylène','claire-isabelle','paula','aude','adèle');
    return (in_array(strtolower($prenom), $prenoms))? 2 : 1;
}
function getGuestValues($field, $newResa='', $curResa=''){
    if ($field == 'repas'){
        $curResa = (!isset($curResa['repas']))? 0 : getBoolValue($curResa['repas']);
        return (getBoolValue($newResa['repas'])>=$curResa)? getBoolValue($newResa['repas']): 1;
    }
    if ($field == 'buffet'){
        $curResa = (!isset($curResa['buffet']))? 0 : getBoolValue($curResa['buffet']);
        return (getBoolValue($newResa['buffet'])>=$curResa)? getBoolValue($newResa['buffet']): 1;
    }
}
function getPrice($resa, $typeUser, $prixPromo){
    $prix = $prixPromo[$typeUser]['soiree'];
    $prix += ($resa['repas'])?$prixPromo[$typeUser]['repas']:0;
    $prix += ($resa['buffet'])?$prixPromo[$typeUser]['buffet']:0;
    $prix += ($resa['tickets_boisson'])?$resa['tickets_boisson']:0;
    return $prix;
}
function getIcamData($gingerUserCard, $prixPromo, $resa, $oldResa=""){
    $icamData = array(
        'nom' => $gingerUserCard->nom,
        'prenom' => $gingerUserCard->prenom,
        'is_icam' => 1,
        'telephone' => $resa['telephone'],
        'promo' => $gingerUserCard->promo,
        'email' => $gingerUserCard->mail,
        'sexe' => $gingerUserCard->sexe,
        'repas' => getGuestValues('repas', $resa, $oldResa),
        'buffet' => getGuestValues('buffet', $resa, $oldResa),
        'tickets_boisson' => intval($resa['tickets_boisson']),
        'image' => $gingerUserCard->img_link
    );
    $icamData['price'] = getPrice($icamData, 'prixIcam', $prixPromo);
    return $icamData;
}
function getGuestData($guest, $prixPromo, $oldResa=""){
    $guestData = array(
        'nom' => $guest['nom'],
        'prenom' => $guest['prenom'],
        'is_icam' => 0,
        'sexe' => guessSexe($guest['prenom']),
        'repas' => getGuestValues('repas', $guest, $oldResa),
        'buffet' => getGuestValues('buffet', $guest, $oldResa),
        'tickets_boisson' => intval($guest['tickets_boisson'])
    );
    $guestData['price'] = getPrice($guestData, 'prixInvite', $prixPromo);
    return $guestData;
}
function sumUpNewOptions($newResa, $curResa, $options){
    $prix += ($newResa['repas'])?$prixPromo[$typeUser]['repas']:0;
    $prix += ($newResa['buffet'])?$prixPromo[$typeUser]['buffet']:0;
    $prix += ($newResa['tickets_boisson'])?$resa['tickets_boisson']:0;
    return $options;
}
function getUpdatedFields($newResa, $curResa){
    $updates = array();
    foreach ($newResa as $k => $v) {
        if (!isset($curResa[$k]))
            $updates[] = $k;
        else if ($v != $curResa[$k])
            $updates[] = $k;
    }
    return $updates;
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

    $_SESSION['newResa'] = mergeUserReservations( $dataResaForm , $request->getParsedBody(), $prixPromo );
    ////////////////////////////////////////////////////////
    // On va vérifier que les données postées sont bonnes //
    ////////////////////////////////////////////////////////
    if (count($_SESSION['newResa']['resa']['invites']) > $prixPromo['nbInvites']) {
        $this->flash->addMessage('warning', 'Hé oh loulou, tu t\'es cru où ? Cherche pas, tu ne pourras plus gruger et rajouter des invités en plus des quotas !.<br>Tu as le droit qu\'à '.$prixPromo['nbInvites'].' invités, pas à '.count($request->getParsedBody()['resa']['invites']).' !!');
        $_SESSION['formErrors']['hasErrors'] = true;
    }
    // On va regarder si il y a des erreurs dans les réservations des utilisateurs.
    $errors = checkUserFieldsIntegrity($_SESSION['newResa']['resa']);
    if (!empty($errors)) $_SESSION['formErrors']['resa'] = $errors;
    foreach ($_SESSION['newResa']['resa']['invites'] as $k => $guest) {
        if ($k >= count($UserGuests)) break; // ça sert à rien d'aller plus loin, il a triché !
        $errors = checkUserFieldsIntegrity($guest, $UserGuests[$k]);
        if (!empty($errors)) $_SESSION['formErrors']['resa']['invites'][$k] = $errors;
    }
    if (isset($_SESSION['formErrors'])){   
        $this->flash->addMessage('warning', 'Vous avez des erreurs dans le formulaire.');
        return $response->withStatus(303)->withHeader('Location', $this->router->pathFor('edit'));
    }

    ///////////////////////////////////////////
    // On continue vers l'INSERT ou l'UPDATE //
    ///////////////////////////////////////////
    // Pas besoin de vérifier si l'utilisateur existe déjà, on aurait son id déjà sinon !
    echo "<h2>Nouvelles données:</h2>";
    $statusFormSubmition = array();

    if ($UserId == -1) { // INSERT de l'icam & de ses invités!
        echo "<p>INSERT de l'icam & de ses invités!</p>";
        $icamData = getIcamData($gingerUserCard, $prixPromo, $_SESSION['newResa']['resa']);
        $icamData['paiement'] = 'PayIcam';
        $icamData['inscription'] = date('Y-m-d H:m:s');
        $statusFormSubmition['insertIcam'] = 'Ajout Icam: '.$icamData['prenom'].' '.$icamData['nom'].' '.$icamData['promo'].' pour '.$icamData['price'].'€';
        var_dump($icamData);
        foreach ($_SESSION['newResa']['resa']['invites'] as $k => $guest) {
            if (empty($guest['nom']) && empty($guest['prenom'])) {
                echo "<p>pas d'invité à ajouter</p>"; continue; }
            $guestData = getGuestData($guest, $prixPromo);
            $guestData['paiement'] = 'PayIcam';
            $guestData['inscription'] = date('Y-m-d H:m:s');
            $statusFormSubmition['insertGuest'][] = 'Ajout Invité: '.$guestData['prenom'].' '.$guestData['nom'].' pour '.$guestData['price'].'€';
            var_dump($guestData);
        }
    }else{ // UPDATE
        echo '<p>UPDATE de la personne</p>';
        // UPDATE de la personne
        $icamData = getIcamData($gingerUserCard, $prixPromo, $_SESSION['newResa']['resa'], $UserReservation);
        var_dump($icamData);
        if ($icamData['price'] > $UserReservation['price'] ) {
            echo "<p>Oh on a de nouvelles options $$ !</p>";
            $updatedFields = getUpdatedFields($icamData, $UserReservation);
            $statusFormSubmition['updateOptions'][] = 'MAJ options'.json_encode($updatedFields).' pour '.$icamData['prenom'].' '.$icamData['nom'].' de '.$UserReservation['price'].' à '.$icamData['price'].'€';
        }else{
            $updatedFields = getUpdatedFields($icamData, $UserReservation);
            if (!empty($updatedFields)) {
                echo "<p>UPDATE des champs ".json_encode($updatedFields)."</p>";
                $statusFormSubmition['updateFields'][] = 'MAJ champs'.json_encode($updatedFields).' pour '.$icamData['prenom'].' '.$icamData['nom'];
                // $DB->query('SELECT * FROM guests WHERE email = :email', array('email' => $mailPersonne));
            }else echo "<p>En fait non rien à mettre à jour !</p>";
        }
        // Checker les invités: update ? insert ?
        foreach ($_SESSION['newResa']['resa']['invites'] as $k => $guest) {
            if (empty($guest['nom']) && empty($guest['prenom'])) {
                echo "<p>pas d'invité #".$k." à ajouter</p>"; continue; }
            elseif(empty($UserGuests[$k]['nom'])){
                echo "<p>INSERT de l'invité #".$k."</p>";
                $guestData = getGuestData($guest, $prixPromo, isset($UserGuests[$k])? $UserGuests[$k]:'');
                $guestData['paiement'] = 'PayIcam';
                $guestData['inscription'] = date('Y-m-d H:m:s');
                var_dump($guestData);
                $statusFormSubmition['insertGuest'][] = 'Ajout Invité: '.$guestData['prenom'].' '.$guestData['nom'].' pour '.$guestData['price'].'€';
            }else{
                echo "<p>UPDATE de l'invité #".$k."</p>";
                $guestData = getGuestData($guest, $prixPromo, $UserGuests[$k]);
                $guestData['id'] = intval($UserGuests[$k]['id']);
                var_dump($guestData);
                if ($guestData['price'] > $UserGuests[$k]['price'] ) {
                    echo "<p>Oh on a de nouvelles options $$ !</p>";
                    $updatedFields = getUpdatedFields($guestData, $UserGuests[$k]);
                    $statusFormSubmition['updateOptions'][] = 'MAJ options'.json_encode($updatedFields).' pour '.$guestData['prenom'].' '.$guestData['nom'].' de '.$UserGuests[$k]['price'].' à '.$guestData['price'].'€';
                }else{
                    $updatedFields = getUpdatedFields($guestData, $UserGuests[$k]);
                    if (!empty($updatedFields)) {
                        echo "<p>UPDATE des champs ".json_encode($updatedFields)."</p>";
                        $statusFormSubmition['updateFields'][] = 'MAJ champs'.json_encode($updatedFields).' pour '.$guestData['prenom'].' '.$guestData['nom'];
                        
                    }else echo "<p>En fait non rien à mettre à jour !</p>";
                }

            }
        }        
    }

    echo "<hr>";
    echo "<h2>Récap</h2>";
    if (empty($statusFormSubmition)) {
        echo "<p>Vous n'avez rien modifié</p>";
    }else{
        if(isset($statusFormSubmition['updateFields']))
            echo '<p>'.implode('<br>', $statusFormSubmition['updateFields']).'</p>';
        if(isset($statusFormSubmition['updateOptions']))
            echo '<p>'.implode('<br>', $statusFormSubmition['updateOptions']).'</p>';
        if(isset($statusFormSubmition['insertIcam']))
            echo '<p>'.$statusFormSubmition['insertIcam'].'</p>';
        if(isset($statusFormSubmition['insertGuest']))
            echo '<p>'.implode('<br>', $statusFormSubmition['insertGuest']).'</p>';
    }

    return $response;//->withStatus(303)->withHeader('Location', $this->router->pathFor('edit'));
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