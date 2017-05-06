<?php

namespace JAS;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\VarDumper\VarDumper;
use Silex\Application;
use Silex\Api\ControllerProviderInterface;

class Home implements ControllerProviderInterface {

	public function connect(Application $app) {
		$factory = $app['controllers_factory'];		
		$factory->get('/','JAS\Home::home');
		return $factory;
	}

	public function home(Application $app, Request $request)
	{
		$defaults[] = 'Sensitive soul?';
		$defaults[] = 'Good president?';
		$defaults[] = 'Buffoon?';
		$defaulst[] = 'Rich man?';
		$defaults[] = 'Genius?';
		$defaults[] = 'Loser?';
		$defaults[] = 'Daddy moocher?';
		$defaults[] = 'Country killer?';
		$defaults[] = 'Dangerous man?';
		$defaults[] = 'Putin pawn?';
		$defaults[] = 'Egomaniac?';
		
		$data['recaptcha'] = (getenv("RECAPTCHA_ON") == "1");		
		$data['recaptcha_public'] = getenv("RECAPTCHA_PUBLIC");		
		$data['max_words'] = getenv("WORD_MAX_WORDS");		
		$data['max_chars'] = getenv("WORD_MAX_LENGTH");		
		$data['placeholder'] = $defaults[array_rand($defaults, 1)];
		$data['show_data'] = isset($_GET['words']);
				
		return $app['twig']->render('home.twig', $data);
	}	
	
	function get_ip_address()
	{
	    if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
		{
	        $ipAddresses = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
	        return trim(end($ipAddresses));
	    }
	    else
	        return $_SERVER['REMOTE_ADDR'];
	}
}