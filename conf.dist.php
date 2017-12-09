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
        'webSiteTitle' => 'Inscriptions Gala des Icam 120',
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
        'fun_id'=>5,

        'articlesPayIcam' => [
            ['id' => 175, 'fun_id'=>5, 'price' => 21, 'quotas'=>2500, 'type' => 'soiree', 'cat_id'=>174, 'nom' => 'Soirée prix Icam'],
            ['id' => 176, 'fun_id'=>5, 'price' => 23, 'quotas'=>2500, 'type' => 'soiree', 'cat_id'=>174, 'nom' => 'Soirée prix ingé'],
            ['id' => 177, 'fun_id'=>5, 'price' => 0, 'quotas'=>2500, 'type' => 'soiree', 'cat_id'=>174, 'nom' => 'Soirée prix VIP'],
            ['id' => 178, 'fun_id'=>5, 'price' => 4, 'quotas'=>300, 'type' => 'repas', 'cat_id'=>174, 'nom' => 'Repas'],
            ['id' => 179, 'fun_id'=>5, 'price' => 3, 'quotas'=>280, 'type' => 'buffet', 'cat_id'=>174, 'nom' => 'Conférence prix icam'],
            ['id' => 180, 'fun_id'=>5, 'price' => 3, 'quotas'=>280, 'type' => 'buffet', 'cat_id'=>174, 'nom' => 'Conférence prix ingé'],
            ['id' => 181, 'fun_id'=>5, 'price' => 10, 'quotas'=>9999, 'type' => 'tickets_boisson', 'cat_id'=>174, 'nom' => '10 tickets boisson']
        ]

    ]
];
