<?php
return [
    'settings' => [
        'displayErrorDetails' => true,

        // Renderer settings
        'renderer' => [
            'template_path' => __DIR__ . '/templates/'
        ],

        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => __DIR__ . '/logs/app.log'
        ],

        'public_path' => 'public/',
        'webSiteTitle' => 'Inscriptions Gala des Icam 118',
        'emailContactGala' => 'galadesicam@icam.fr',

        'PayIcam' => [
            'payutc_server' => 'http://payicam.dev/server/web/', // URL du serveur payutc (avec le / final)
            'payutc_key' => 'xxx' // Clé de l'application
        ],

        'confSQL' => [
            'sql_host' => "localhost",
            'sql_db'   => "icam_galadesicam",
            'sql_user' => "root",
            'sql_pass' => "root"
        ]

    ]
];
