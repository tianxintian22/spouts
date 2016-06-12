<?php
class Base{
    static function getContent($url, $timeout=60) {
        if (function_exists("curl_init")){
    		$curl = curl_init();
    		curl_setopt($curl, CURLOPT_URL, $url);
    		curl_setopt($curl, CURLOPT_HEADER, 0);
    		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    		curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
    		
    		$data = curl_exec($curl);
    		// 获取状态信息，如果发生错误，不返回数据
    		$curlSuccess = curl_getinfo($curl);
    		if ($curlSuccess['http_code'] >= 400) {
    			$data = false;
    		}
    		curl_close($curl);
    		
    		return $data;
    	}else {
    	    $opts = array('http'=>array('method'=>"GET",'timeout'=>$timeout));
    	    $context = stream_context_create($opts);
    	    $data = file_get_contents($url, false, $context);
    	    return $data;
    	}
    }
}