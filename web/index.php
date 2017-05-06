<?php

require('../vendor/autoload.php');

if(file_exists("config_local.php")) include_once('config_local.php');
date_default_timezone_set(getenv("TIMEZONE"));

$app = new Silex\Application();
$app['debug'] = getenv("DEBUG");
if($app['debug'])
{
	error_reporting(E_ALL);
	ini_set('display_errors',1);
}

// register mongo database
$app->register(new Lalbert\Silex\Provider\MongoDBServiceProvider(), [
    'mongodb.config' => [
        'server' => getenv("MONGODB_URI"),
        'options' => [],
        'driverOptions' => [],
    ]
]);

// Register view rendering
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views',
));

$app["twig"]->addGlobal("APP_NAME", getenv('APP_NAME'));
$app["twig"]->addGlobal("APP_URL", getenv('APP_URL'));

// Controllers and routes
$app->mount('/', new JAS\Home());
$app->mount('/words', new JAS\Words());
 
// Go!
$app->run();