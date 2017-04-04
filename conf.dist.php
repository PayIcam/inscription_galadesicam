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
        'webSiteTitle' => 'Spring Festival',
        'emailContact' => 'spring.icam@gmail.com',

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
            'soiree'=>800
        ],
        'fun_id'=>6,

        'articlesPayIcam' => [
            ['id' => 71, 'fun_id'=>6, 'price' => 15, 'quotas'=>800, 'type' => 'soiree', 'cat_id'=>70, 'nom' => 'Inscription en ligne']
        ]

    ]
];
