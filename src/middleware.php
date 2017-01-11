<?php
// Application middleware

// e.g: $app->add(new \Slim\Csrf\Guard);

// sécuriser l'application
$app->add(function ($request, $response, $next) {
    global $status, $payutcClient, $gingerUserCard, $gingerClient, $Auth;

    if (!in_array($request->getUri()->getPath(), ['about', 'login', 'callback'])) {
        if((!isset($status) || !$status->user)) {
            // Il n'était pas encore connecté en tant qu'icam.
            $this->flash->addMessage('info', "Vous devez être connecté pour accéder au reste de l'application");
            return $response->withStatus(303)->withHeader('Location', $this->router->pathFor('about'));
        }
        if (!empty($status->user)) {
            if (empty($status->application) || isset($status->application->app_url) && strpos($status->application->app_url, 'inscription_gala') === false) { // il était connecté en tant qu'icam mais l'appli non
                try {
                    $payutcClient->loginApp(array("key"=>$this->get('settings')['PayIcam']['payutc_key']));
                    $status = $payutcClient->getStatus();
                } catch (\JsonClient\JsonException $e) {
                    $this->flash->addMessage('info', "error login application, veuillez finir l'installation de l'app");
                    return $response->withStatus(303)->withHeader('Location', $this->router->pathFor('about'));
                }
            }
            // tout va bien
            $gingerUserCard = $gingerClient->getUser($Auth->getUserField('email'));
            if (empty($gingerUserCard)) { // l'utilisateur n'avait jamais été ajouté à Ginger O.o
                $gingerUserCard = $gingerClient->getUser($Auth->getUserField('email'));
            }if (empty($gingerUserCard)) { // l'utilisateur n'a pas un mail icam valide // on ne devrait jamais avoir cette erreur car on passe par payutc et lui a besoin d'avoir ginger qui marche ... je crois ...
                $this->flash->addMessage('warning', "Votre Mail Icam n'est pas reconnu par Ginger...");
                return $response->withStatus(303)->withHeader('Location', $this->router->pathFor('about'));
            }
        }
    }

    // $response->getBody()->write('BEFORE');
    $response = $next($request, $response);
    // $response->getBody()->write('AFTER');

    return $response;
});