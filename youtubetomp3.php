<?php

class YoutubeToMP3 {
	const DOWNLOAD = 1;
	const LINK = 2;

	/**
	 * Obtem o link de download do MP3 de um video do YouTube
	 * @param  string  $url    URL do video no YouTube
	 * @param  integer $action Acão que será executada com o link (YoutubeToMP3::DOWNLOAD, YoutubeToMP3::LINK) 
	 * @return mixed           Quando o parametro $action for definido como YoutubeToMP3::DOWNLOAD redireciona 
	 *                         para o download do arquivo, quando YoutubeToMP3::LINK traz o link como retorno
	 */
	public static function get($url, $action = self::LINK) {
		$currentTime = time();
		$videoId = self::getYoutubeId($url);

		$itemInfoUrl = "http://www.youtube-mp3.org/a/itemInfo/?video_id={$videoId}&ac=www&t=grp&r={$currentTime}";
		$itemInfo = self::httpRequest($itemInfoUrl);

		$sequence = $videoId . $currentTime;
		$requestId = $itemInfo['h'];
		$cc = self::cc($sequence);
		
		$MP3URL = "http://www.youtube-mp3.org/get?ab=128&video_id={$videoId}&h={$requestId}&r={$currentTime}.{$cc}";
		
		if ($action == self::DOWNLOAD):
			self::redirect($MP3URL);
		else:
			return $MP3URL;
		endif;
	}

	/**
	 * Realiza uma requisição do tipo GET para uma url dada
	 * @param  string $url Url que será requisitada
	 * @return array       Array associativo do JSON retornado
	 */
	private static function httpRequest ($url) {
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

		$result = curl_exec($ch);
		curl_close($ch);

		// Clear response string
		$jsonString = str_replace(array('info = ', ';'), '', $result);
		$parsedJSON = json_decode($jsonString, true);

		return $parsedJSON;
	}

	/**
	 * Obtem o ID do um video do YouTube a partir de uma URL
	 * @param  string $url URL do video
	 * @return mixed       String com o id caso seja uma URL válida ou false do contrário
	 */
	private static function getYoutubeId ($url) {
		$pattern = '%^(?:https?://)?(?:www\.)?(?:youtu\.be/|youtube\.com(?:/embed/|/v/|.*v=))([\w-]{10,12})($|&).*$%x';
		$result = preg_match($pattern, $url, $matches);
		
		if ($result !== false):
			return $matches[1];
		endif;

		return false;
	}

	/**
	 * Gera uma sequencia necessária para a API do youtube-mp3.org
	 * @param  string $a Id do video concatenado com o timestamp atual
	 * @return string    Sequencia
	 */
	private static function cc ($a) {
		$AM = 65521; 
		$c = 1; 
		$b = 0;
		$d; 
		$e;

		for ($e = 0; $e < strlen($a); $e++):
			$d = self::charCodeAt($a,$e);
		$c = ($c + $d) % $AM;
		$b = ($b + $c) % $AM;
		endfor;

		return $b << 16 | $c;
	}

	/**
	 * Metodo utilitário
	 */
	private	static function charCodeAt($str, $i){
		return ord(substr($str, $i, 1));
	}

	/**
	 * Header Redirect
	 *
	 * Header redirect in two flavors
	 * For very fine grained control over headers, you could use the Output
	 * Library's set_header() function.
	 *
	 * @param string $uri URL
	 * @param string $method Redirect method  'auto', 'location' or 'refresh'
	 * @param int    $code HTTP Response status code
	 * @return void
	 */
	private static function redirect ($uri = '', $method = 'auto', $code = NULL) {
		if (!preg_match('#^(\w+:)?//#i', $uri)) {
			$uri = site_url($uri);
		}

		if ($method === 'auto' && isset($_SERVER['SERVER_SOFTWARE']) && strpos($_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS') !== FALSE) {
			$method = 'refresh';
		} elseif ($method !== 'refresh' && (empty($code) OR ! is_numeric($code))) {
			if (isset($_SERVER['SERVER_PROTOCOL'], $_SERVER['REQUEST_METHOD']) && $_SERVER['SERVER_PROTOCOL'] === 'HTTP/1.1') {
				$code = ($_SERVER['REQUEST_METHOD'] !== 'GET')? 303 : 307;
			} else {
				$code = 302;
			}
		}

		switch ($method) {
			case 'refresh':
				header('Refresh:0;url='.$uri);
			break;
			default:
				header('Location: '.$uri, TRUE, $code);
			break;
		}
		exit;
	}
}

echo YoutubeToMP3::get('http://www.youtube.com/watch?v=B2m_WnXjqnM', YoutubeToMP3::LINK);
