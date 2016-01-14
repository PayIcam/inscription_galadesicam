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
            'payutc_key' => 'xxx', // Clé de l'application
            'ginger_server' => 'http://payicam.dev/ginger/index.php/v1/',
            'ginger_key' => 'xxx'
        ],

        'confSQL' => [
            'sql_host' => "localhost",
            'sql_db'   => "icam_galadesicam",
            'sql_user' => "root",
            'sql_pass' => "root"
        ],

        'quotas' => [
            'soiree'=>2500,
            'repas'=>300,
            'conference'=>280
        ],

        'articlesPayIcam' => [
            ['id' => 200, 'fun_id'=>5, 'price' => 18, 'quotas'=>2500, 'type' => 'soiree', 'cat_id'=>199, 'nom' => 'Soirée prix Icam'],
            ['id' => 201, 'fun_id'=>5, 'price' => 20, 'quotas'=>2500, 'type' => 'soiree', 'cat_id'=>199, 'nom' => 'Soirée prix ingé'],
            ['id' => 203, 'fun_id'=>5, 'price' => 0, 'quotas'=>2500, 'type' => 'soiree', 'cat_id'=>199, 'nom' => 'Soirée prix VIP'],
            ['id' => 202, 'fun_id'=>5, 'price' => 20, 'quotas'=>300, 'type' => 'repas', 'cat_id'=>199, 'nom' => 'Repas'],
            ['id' => 204, 'fun_id'=>5, 'price' => 2, 'quotas'=>280, 'type' => 'conference', 'cat_id'=>199, 'nom' => 'Conférence prix icam'],
            ['id' => 205, 'fun_id'=>5, 'price' => 3, 'quotas'=>280, 'type' => 'conference', 'cat_id'=>199, 'nom' => 'Conférence prix ingé']
        ]

    ]
];
