<?php

namespace JAS;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\VarDumper\VarDumper;
use Silex\Application;
use Silex\Api\ControllerProviderInterface;

class Words extends Home implements ControllerProviderInterface {

	public function connect(Application $app) {
		$factory = $app['controllers_factory'];		
		$factory->post('/new','JAS\Words::word_post');
		$factory->get('/data','JAS\Words::word_data');
		return $factory;
	}
	
	//retrieve live summary
	public function word_data(Application $app, Request $request)
	{
		$max_words = 5;
		$db_name = getenv("MONGO_DB");
		
		//all time leaders
		$words_all = array();
		$words_data = $app['mongodb']->$db_name->words->find([], ['limit'=>$max_words,'sort' => ['count' => -1]]);
		if($words_data)
		{
			foreach($words_data as $wd)
				$words_all[] = ['word' => $wd->word, 'count' => $wd->count];
		}
		
		//today's leaders
		$words_today = array();
		/*$query = ["last_used": ]
		$words_data = $app['mongodb']->$db_name->words->find($query, ['limit'=>$max_words,'sort' => ['count' => -1]]);
		if($words_data)
		{
			foreach($words_data as $wd)
				$words_today[] = ['word' => $wd->word, 'count' => $wd->count];
		}*/
		$data['word_data'] = ['all'=>$words_all, 'today'=>$words_today];
		
		return $app['twig']->render('word_data.twig', $data);
	}

	//actual function to put the word in the db
	public function word_post(Application $app, Request $request)
	{
		//setup return
		$resp['success'] = false;
		$resp['error_code'] = "";
		$resp['word'] = $word = (empty($request->get('word'))) ? "" : $request->get('word');
		$token = (empty($request->get('token'))) ? "" : $request->get('token');
		
		//validate recaptcha
		$recaptcha = new \ReCaptcha\ReCaptcha(getenv("RECAPTCHA_SECRET"));
		$recaptcha_resp = $recaptcha->verify($token, $this->get_ip_address());
		if (!$recaptcha_resp->isSuccess() || $recaptcha_resp->getHostName() !== getenv("APP_DOMAIN"))
		{
			$resp['error_code'] = "input";
			return $app->json($resp);
		}
		
		//validate word length
		$word = strtolower(trim(strip_tags($word)));
		$word = trim(preg_replace('/\s+/', ' ', $word));
		if(strlen($word) > intval(getenv("WORD_MAX_LENGTH")))
		{
			$resp['error_code'] = "reqs";
			return $app->json($resp);
		}
		
		//validate word count
		$words = explode(' ', $word);
		if(count($words) > intval(getenv("WORD_MAX_WORDS")))
		{
			$resp['error_code'] = "reqs";
			return $app->json($resp);
		}
		
		//insert into raw data
		$db_name = getenv("MONGO_DB");
		$db_word['word'] = $word;
		$db_word['dtstamp'] = new \MongoDB\BSON\UTCDateTime();
		$db_word['ip'] = $this->get_ip_address();
		$app['mongodb']->$db_name->words_raw->insertOne($db_word);
		
		//query simple warehouse
		$query = ['word' => $word];
		$word_record = $app['mongodb']->$db_name->words->findOne($query);
		
		//upsert simple warehouse
		$wh_word['word'] = $word;
		$wh_word['last_used'] = new \MongoDB\BSON\UTCDateTime();
		$wh_word['count'] = $word_record ? ++$word_record->count : 1;
		
		if($word_record)
			$app['mongodb']->$db_name->words->updateOne(['_id' => new \MongoDB\BSON\ObjectID($word_record->_id)], ['$set' => $wh_word]);
		else	
			$app['mongodb']->$db_name->words->insertOne($wh_word);
		
		$resp['success'] = true;
		$resp['data'] = $wh_word;
		return $app->json($resp);
		
	}	
}