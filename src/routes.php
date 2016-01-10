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

// Routes

// $app->get('/contact/[{name}]', function ($request, $response, $args) {
//     // Sample log message
//     $this->logger->info("Slim-Skeleton '/' contact");

//     $flash = $this->flash;
//     $RouteHelper = new \PayIcam\RouteHelper($this, $request, 'Contact');

//     // Render index view
//     $response = $this->renderer->render($response, 'header.php', array('app' => $this));
//     $response = $response->getBody()->write(var_export($args));
//     $response = $response->getBody()->write(var_export($request->getUri()->getPath()));
//     $this->renderer->render($response, 'home.php', $args);
//     return $this->renderer->render($response, 'footer.php', $args);
// })->setName('contact');
// $app->get('/contact', function ($request, $response, $args) {
//     return $response->withStatus(301)->withHeader('Location', $this->router->pathFor('contact'));
// });

$app->get('/', function ($request, $response, $args) {
    global $Auth, $payutcClient;
    // Sample log message
    $this->logger->info("Slim-Skeleton '/' index");

    $flash = $this->flash;
    $RouteHelper = new \PayIcam\RouteHelper($this, $request, 'Accueil');

    $confSQL = $this->get('settings')['confSQL'];
    $DB = new \PayIcam\DB($confSQL['sql_host'],$confSQL['sql_user'],$confSQL['sql_pass'],$confSQL['sql_db']);

    $UserReservation = $DB->query('SELECT * FROM guests WHERE email = :email', array('email' => $Auth->getUserField('email')));

    // Render index view
    $this->renderer->render($response, 'header.php', compact('flash', 'RouteHelper', 'Auth', $args));

    $this->renderer->render($response, 'home.php', compact('Auth', 'UserReservation', $args));

    return $this->renderer->render($response, 'footer.php', compact('RouteHelper', 'Auth', $args));
})->setName('home');

// $app->get('/flash', function ($request, $response, $args) {

//     $this->flash->addMessage('info', 'This is an info message');
//     $this->flash->addMessage('warning', 'This is an warning message');
//     $this->flash->addMessage('warning', 'This is an other warning message');

//     return $response->withStatus(303)->withHeader('Location', $this->router->pathFor('home'));
// });

$app->get('/about', function ($request, $response, $args) {
    global $Auth, $payutcClient;

    $flash = $this->flash;
    $RouteHelper = new \PayIcam\RouteHelper($this, $request, 'A propos');
    $casUrl = 'https://cas.icam.fr/cas/login?service=' . urlencode($RouteHelper->curPageBaseUrl. '/login');

    $status = $payutcClient->getStatus();

    $this->renderer->render($response, 'header.php', compact('flash', 'RouteHelper', 'Auth', $args));
    $this->renderer->render($response, 'about.php', compact('casUrl', $args));
    return $this->renderer->render($response, 'footer.php', compact('RouteHelper', 'Auth', $args));
})->setName('about');

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
});

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
});