<?php

class YoutubeTomp3 {

    public function __construct() {
        $this->ci = & get_instance();
        $this->ci->load->helper("url");
    }

    /*
     * Setá as configurações recebidas pelo controller
     */
    public function get($url,$action='download') {

		if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match)) {
		    $video_id = $match[1];
		}

		$url = 'http://www.youtube-mp3.org/a/itemInfo/?video_id='.$video_id.'&ac=www&t=grp&r='.time();
		$info = $this->getFileInfoCurl($url);
		
		$timeNow = strtotime(date('Y-m-d H:i:s'));

		$var = $video_id.$timeNow;
		//echo $var;

		$cc = $this->cc($var);
		
		$urlMp3 = "http://www.youtube-mp3.org/get?ab=128&video_id=".$video_id."&h=".$info->h."&r=".$timeNow.".".$cc;
		
		if($action == 'download'){
			redirect($urlMp3);
		}else{
			echo $urlMp3;
		}
    }

	public function getFileInfoCurl($url){

	    $ch = curl_init();
	    curl_setopt($ch,CURLOPT_URL,$url);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch, CURLOPT_HEADER, 1);
	    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	    $result = curl_exec($ch);
	    curl_close($ch);

	    $responseJson = explode('info =', substr($result, 0,-1));
	    $arr = json_decode($responseJson[1]);

	    return $arr;
	}

	private function cc($a){
		$AM=65521; 
	    $c = 1; 
	    $b = 0;
	    $d; 
	    $e;
	    for($e = 0; $e < strlen($a); $e++){
	        $d = $this->charCodeAt($a,$e);
	        $c = ($c+$d)%$AM;
	        $b = ($b+$c)%$AM;
	    }
	    return $b<<16|$c;
	}

	private	function charCodeAt($str, $i){
	  return ord(substr($str, $i, 1));
	}

}