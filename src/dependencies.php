<?php
// DIC configuration

$container = $app->getContainer();

// view renderer
$container['renderer'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    return new Slim\Views\PhpRenderer($settings['template_path']);
};

// view renderer
$container['flash'] = function ($c) {
    return new \Slim\Flash\Messages();
};

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], Monolog\Logger::DEBUG));
    return $logger;
};

///////////////////////////
// Autre initialisations //
///////////////////////////

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
$gingerClient = new \Ginger\Client\GingerClient($settings['settings']['PayIcam']['ginger_key'], $settings['settings']['PayIcam']['ginger_server']);

$admin = $payutcClient->isSuperAdmin();
$isAdminFondation = $payutcClient->isAdmin();

$confSQL = $settings['settings']['confSQL'];
$DB = new \PayIcam\DB($confSQL['sql_host'],$confSQL['sql_user'],$confSQL['sql_pass'],$confSQL['sql_db']);

$res = $DB->query("SELECT name, value FROM configs");
$configsDB = [];
foreach ($res as $row)
    $configsDB[$row['name']] = $row['value'];

$canWeRegisterNewGuests = (boolean)$configsDB['inscriptions'];
$canWeEditOurReservation = (boolean)$configsDB['modifications_places'];

$quotas = array(
    'soiree' => (int)$configsDB['quota_soirees']
);

// Sécurité que des icam
$status = $payutcClient->getStatus();
$gingerUserCard = null;

// $_SESSION['Auth']['email'] = "gregoire.dervaux@2019.icam.fr";
// $_SESSION['Auth']['email'] = "gregoire.giraud@2020.icam.fr";