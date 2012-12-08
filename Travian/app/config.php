<?php
$AppConfig = array (
	'db' 					=> array (
		'host'				=> 'localhost',
		'user'				=> '',
		'password'			=> '',
		'database'			=> '',
	),
	
		    'Game' 			=> array (
	
		
		'capacity'      => '10000', // מחסנים ואסמים
	
		'cranny'        => '10000', // נקיקים


		),
	'page' 		=> array (
		'ar_title'			=> 'Delview Travian',
		'en_title'			=> 'Delview Travian',
		'meta-tag' 			=> '',
		'asset_version'		=> 'c4b7aaaadef'						// this is used to flush any old assets like css file or javascript
	),
	'system' 	=> array (
		'lang'				=> 'en',										// this is the default language, ar = for arabic, en = for english
		'forum_url'			=> '',
		'social_url'		=> '#',
		// admin account info
		'adminName'			=> 'Multihunter',
		'adminPassword'		=> 'travianxiscool1234',
		'admin_email'		=> 'admin@delviewtravian.com',			// the email for admin account (set it before setup)
		'email' 			=> 'admin@delviewtravian.com',			// the email for others (like activation, forget password, ..etc)
		'installkey' 			=> 'install',
		'calltatar' 			=> 'tatarinstall'
	),
    'game'      => array (
        'truce'             => false,
        'truce_text'        => 'Truce: 30.12.11 11:00 - 01.01.12 11:00',
        'rockets'           => false,
    ),
	'plus'			=> array (
		'packages'	=> array (
			array ( 
				'name'		=> 'Package A',
				'gold'		=> 30,
				'cost'		=> 1.99,
				'currency'	=> 'EUR',
				'image'		=> 'package_a.jpg'
			),
			array ( 
				'name'		=> 'Package B',
				'gold'		=> 100,
				'cost'		=> 4.99,
				'currency'	=> 'EUR',
				'image'		=> 'package_b.jpg'
			),
			array ( 
				'name'		=> 'Package C',
				'gold'		=> 250,
				'cost'		=> 9.99,
				'currency'	=> 'EUR',
				'image'		=> 'package_c.jpg'
			),
			array ( 
				'name'		=> 'Package D',
				'gold'		=> 600,
				'cost'		=> 19.99,
				'currency'	=> 'EUR',
				'image'		=> 'package_d.jpg'
			),
		),
		'payments' => array (
			'paypal'	=> array (
				'testMode'		=> false,
				'name'			=> 'PayPal',
				'image'			=> 'paypal_solution_graphic-US.gif',
				'merchant_id'	=> 'paypal@delviewtravian.com',//rebhee62@gmail.com
				'currency'		=> 'EUR'
			)
		)
	)
);