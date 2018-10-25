<?php

return [
    'settings' => [
        // comment this line when deploy to production environment
        'displayErrorDetails' => true,
        // View settings
    	
        // monolog settings
        'logger' => [
            'name' => 'app',
            'path' => __DIR__ . '/../log/app.log',
        ],
		 //database
        'database' => [
			'driver'    => 'mysql',
			'host'      => 'israelaczscultur.mysql.db',
			'database'  => 'israelaczscultur',
			'username'  => 'israelaczscultur',
			'password'  => 'Culturaccess26',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ],
		'defaultLang' => 'en_Fr',
		// app url
		'app_url' => 'http://israel-access.com',
    ],
];