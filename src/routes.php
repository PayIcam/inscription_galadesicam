<?php


$Auth = new \PayIcam\Auth();


// get payutcClient
function getPayutcClient($service) {
    global $app, $settings;
    return new \JsonClient\AutoJsonClient(
        $settings['settings']['PayIcam']['payutc_server'],
        $service,
        array(),
        "PayIcam Json PHP Client",
        isset($_SESSION['payutc_cookie']) ? $_SESSION['payutc_cookie'] : "");
}
$payutcClient = getPayutcClient("WEBSALE");

$admin = $payutcClient->isSuperAdmin();
$isAdminFondation = $payutcClient->isAdmin();

$confSQL = $settings['settings']['confSQL'];
$DB = new \PayIcam\DB($confSQL['sql_host'],$confSQL['sql_user'],$confSQL['sql_pass'],$confSQL['sql_db']);

$canWeRegisterNewGuests = 1*(current($DB->queryFirst('SELECT value FROM configs WHERE name = :name', array('name'=>'inscriptions'))));
$canWeEditOurReservation = 1*(current($DB->queryFirst('SELECT value FROM configs WHERE name = :name', array('name'=>'modifications_places'))));

////////////
// Routes //
////////////

// Page ouverte à tous
$app->get('/about', function ($request, $response, $args) {
    global $Auth, $payutcClient;

    $flash = $this->flash;
    $RouteHelper = new \PayIcam\RouteHelper($this, $request, 'A propos');
    $casUrl = 'https://cas.icam.fr/cas/login?service=' . urlencode($RouteHelper->curPageBaseUrl. '/login');
    $deconnexionUrl = $editLink = $this->router->pathFor('logout');;

    $this->renderer->render($response, 'header.php', compact('Auth', 'flash', 'RouteHelper', $args));
    $this->renderer->render($response, 'about.php', compact('Auth', 'casUrl', 'deconnexionUrl', $args));
    return $this->renderer->render($response, 'footer.php', compact('Auth', 'RouteHelper', $args));
})->setName('about');

/////////////////
// Espace Icam //
/////////////////
$app->get('/', function ($request, $response, $args) {
    global $Auth, $payutcClient, $DB, $canWeRegisterNewGuests, $canWeEditOurReservation;

    $status = $payutcClient->getStatus();
    if (!$Auth->isLogged() && !empty($status['user'])){
        if(isset($_SESSION['Auth'])) unset($_SESSION['Auth']); 
        $this->flash->addMessage('warning', "Vous devez être connecté à PayIcam pour accéder aux inscriptions du Gala de Icam");
        return $response->withStatus(303)->withHeader('Location', $this->router->pathFor('about'));
    }
    $flash = $this->flash;
    $RouteHelper = new \PayIcam\RouteHelper($this, $request, 'Accueil');
    $emailContactGala = $this->get('settings')['emailContactGala'];
    
    // Sample log message
    // $this->logger->info("Slim-Skeleton '/' index");

    // $UserReservation = $DB->query('SELECT * FROM guests WHERE email = :email', array('email' => 'hugo.leandri@2018.icam.fr '));
    $UserReservation = $DB->query('SELECT * FROM guests WHERE email = :email', array('email' => $Auth->getUserField('email')));
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


$app->get('/edit', function ($request, $response, $args) {
    global $Auth, $payutcClient, $DB, $canWeRegisterNewGuests, $canWeEditOurReservation;
    $emailContactGala = $this->get('settings')['emailContactGala'];
    $status = $payutcClient->getStatus();
    if (!$Auth->isLogged() && !empty($status['user'])){
        if(isset($_SESSION['Auth'])) unset($_SESSION['Auth']); 
        $this->flash->addMessage('warning', "Vous devez être connecté à PayIcam pour accéder aux inscriptions du Gala de Icam");
        return $response->withStatus(303)->withHeader('Location', $this->router->pathFor('about'));
    }

    // $UserReservation = $DB->query('SELECT * FROM guests WHERE email = :email', array('email' => 'hugo.leandri@2018.icam.fr '));
    $UserReservation = $DB->query('SELECT * FROM guests WHERE email = :email', array('email' => $Auth->getUserField('email')));
    if (count($UserReservation) > 1){
        $this->flash->addMessage('danger', 'Nous avons plusieurs réservations enregistrées à votre email...<br>
            <a href="mailto:'.$emailContactGala.'" title="'.$emailContactGala.'">Contactez nous</a> svp !');
        return $response->withStatus(303)->withHeader('Location', $this->router->pathFor('home'));
    }elseif (!$canWeRegisterNewGuests && count($UserReservation) == 0){
        $this->flash->addMessage('warning', 'De nouvelles réservations ne sont plus autorisées.<br> Si vous avez un problème, vous pouvez encore contacter le <a href="mailto:'.$emailContactGala.'" title="'.$emailContactGala.'">Gala</a>');
        return $response->withStatus(303)->withHeader('Location', $this->router->pathFor('home'));
    }elseif (!$canWeEditOurReservation && $canWeRegisterNewGuests && count($UserReservation) == 1){
        // On a pas le droit de modifier les infos que l'on a soumis, par contre on peut qd mm ajouter de nouveaux invités!
        $this->flash->addMessage('warning', 'Les modifications des réservations ne sont plus autorisées.<br> Si vous avez un problème, vous pouvez encore contacter le <a href="mailto:'.$emailContactGala.'" title="'.$emailContactGala.'">Gala</a>');
        return $response->withStatus(303)->withHeader('Location', $this->router->pathFor('home'));
    }elseif (!$canWeEditOurReservation && !$canWeRegisterNewGuests && count($UserReservation) == 1){
        $this->flash->addMessage('warning', 'Les inscriptions sont closes, on se retrouve au Gala.<br> Si vous avez un problème, vous pouvez encore contacter le <a href="mailto:'.$emailContactGala.'" title="'.$emailContactGala.'">Gala</a>');
        return $response->withStatus(303)->withHeader('Location', $this->router->pathFor('home'));
    }
    $flash = $this->flash;
    $RouteHelper = new \PayIcam\RouteHelper($this, $request, 'Edition réservation');
    
    if(count($UserReservation) == 1){ // il y avait déjà une réservation pour cet utilisateur
        $UserGuests = array();
        if (count($UserReservation) == 1) {
            $UserId = $UserReservation[0]['id'];
            $UserGuests = $DB->query('SELECT * FROM icam_has_guest
                                        LEFT JOIN guests ON guest_id = id
                                      WHERE icam_id = :icam_id', array('icam_id' => $UserId));
        }
    }else{ // Nouvelle réservation
        $UserReservation = array();
        $UserGuests = array();
        $UserId = -1;
    }

    // Render index view
    $this->renderer->render($response, 'header.php', compact('flash', 'RouteHelper', 'Auth', $args));
    $editLink = $this->router->pathFor('edit');
    $this->renderer->render($response, 'edit_reservation.php', compact('UserId', 'UserReservation', 'UserGuests', 'canWeRegisterNewGuests', 'emailContactGala', 'editLink', $args));
    return $this->renderer->render($response, 'footer.php', compact('RouteHelper', 'Auth', $args));
})->setName('edit');
$app->get('/edit/', function ($request, $response, $args) {
    return $response->withStatus(301)->withHeader('Location', $this->router->pathFor('edit')); // code 301: redirection, déplacé pour toujours
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