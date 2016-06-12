<?php
class wechatCallbackapi{
    public function __construct(){
        $this->token = 'weixin';
        $this->appId = 'wx6f00eff1fa549525';
        $this->appsecret = 'ba1b7ebb9abe219398a7984edd4ae494';
        $this->apikey = 'db096c5cc5175f9c01fe7993c8a0d483';//天气查询api的key
    }
    
    public function valid(){
        $echoStr = $_GET["echostr"];
        if($this->checkSignature()){
            header('content-type:text');
            echo $echoStr;
            exit;
        }
    }

    private function checkSignature(){
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $tmpArr = array($this->token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }

    //获取调用凭证,有效时间是两个小时
    public function getAccessToken(){
        $api = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$this->appId}&secret={$this->appsecret}";
        $jsonStr = base::getContent($api);
        $dataObj = json_decode($jsonStr);
        try {
            if($dataObj->access_token){
                return $dataObj->access_token;
            }else{
                throw new Exception($dataObj->errcode);
            }
        } catch (Exception $e) {
            return false;
        }
    }
    //获取微信服务器ip地址
    public function getServerip(){
        $access_token = $this->getAccessToken();
        $api = "https://api.weixin.qq.com/cgi-bin/getcallbackip?access_token={$access_token}";
        $jsonStr = base::getContent($api);
        $dataObj = json_decode($jsonStr);
        try {
            if($dataObj->ip_list){
                $iplist = $dataObj->ip_list;
                return implode('<br>', $iplist);
            }else{
                throw new Exception($dataObj->errcode);
            }
        } catch (Exception $e) {
            return false;
        }
        
        
    }
    
    //发送消息
    public function responseMsg(){
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

        if (!empty($postStr)){
            //微信服务器POST消息的XML数据包
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            
            $params = array();
            $params['postMsgType'] = $postObj->MsgType;//消息类型
            $params['fromUsername'] = $postObj->FromUserName;
            $params['toUsername'] = $postObj->ToUserName;
            if ($postObj->Event){
                $params['event'] = strtolower($postObj->Event);
            }
            if ($postObj->Content){
                $params['content'] = strtolower($postObj->Content);
            }
            
            //推送给用户的消息
            $resultStr = $this->getResponseMsg($params);
            echo $resultStr;
        }else{
            echo "";
            exit;
        }
    }
    //推送给用户的消息
    public function getResponseMsg($params){
        extract($params);
        
        switch ($postMsgType){
            case 'event':
                if ($event == 'subscribe'){
                    $params['createTime'] = time();
                    $params['msgType'] = 'text';
                    $params['contentStr'] = '欢迎关注公众号小白2，更多精彩内容正在继续...';
                    $resultStr = $this->getResStr($params);
                    echo $resultStr;
                }elseif($event == 'unsubscribe'){
                    
                }
                break;
            case 'text':
                $params['content'] = trim($content);
                $params['createTime'] = time();
        
                if ($content == '1'){//文本消息
                    $params['contentStr'] = '你好，我是小白2！/::)<a href="http://www.cnblogs.com/tianxintian22/">点我点我！/:turn/:#-0/:kiss/:kiss</a>';
                    $params['msgType'] = 'text';
                    $resultStr = $this->getResStr($params);
                }elseif ($content == '2'){//单图文消息
                    $articles = array();
                    $articles[0]['title'] = '第一课';
                    $articles[0]['description'] = '小白2课堂开课了~~~';
                    $articles[0]['picUrl'] = 'http://spouts.sinaapp.com/image/cheerup.jpg';
                    $articles[0]['url'] = 'http://www.cnblogs.com/tianxintian22/p/5121530.html';
                    
                    $params['articles'] = $articles;
                    $params['msgType'] = 'news';
                    $resultStr = $this->getResStr($params);
                } elseif ($content == '3'){//多图文消息
                    $articles = array();
                    $articles[0]['title'] = '第一课';
                    $articles[0]['description'] = '小白2课堂开课了~~~';
                    $articles[0]['picUrl'] = 'http://spouts.sinaapp.com/image/study.jpg';
                    $articles[0]['url'] = 'http://www.cnblogs.com/tianxintian22/p/5121530.html';
                    $articles[1]['title'] = '小白2课堂';
                    $articles[1]['description'] = '';
                    $articles[1]['picUrl'] = 'http://spouts.sinaapp.com/image/photo.jpg';
                    $articles[1]['url'] = 'http://www.cnblogs.com/tianxintian22/';
                    $params['articles'] = $articles;
                    $params['msgType'] = 'news';
                    
                    $resultStr = $this->getResStr($params);
                }else{
                    $params['contentStr'] = $this->getCityWeather($content);
                    $params['msgType'] = 'text';
                    $resultStr = $this->getResStr($params);
                }
                echo $resultStr;
                break;
        }
    }
    
    public function getCityWeather($cityname){
        $ch = curl_init();
        $url = 'http://apis.baidu.com/apistore/weatherservice/cityname?cityname='.$cityname;
        $header = array('apikey: '.$this->apikey);
        // 添加apikey到header
        curl_setopt($ch, CURLOPT_HTTPHEADER  , $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // 执行HTTP请求
        curl_setopt($ch , CURLOPT_URL , $url);
        $jsonData = curl_exec($ch);
        $dataArr = json_decode($jsonData, true);
        $weatherCont = '';
        if ($dataArr['errMsg'] == 'success'){
            $weatherCont .= '【'.$cityname.'】天气实况'."\n";
            $weatherCont .= '温度'.$dataArr['retData']['temp'].'℃('.$dataArr['retData']['time'].')'."\n";
            $weatherCont .= $dataArr['retData']['WD'].$dataArr['retData']['WS']."\n";
            $weatherCont .= $dataArr['retData']['weather'].$dataArr['retData']['h_tmp'].'℃~'.$dataArr['retData']['l_tmp']."℃";
        }
        return $weatherCont;
    }
    
    public function getResStr($params){
        extract($params);
        
        switch ($msgType){
            case 'text':
                $msgTpl = '<xml>
                        <ToUserName><![CDATA[%s]]></ToUserName>
                        <FromUserName><![CDATA[%s]]></FromUserName>
                        <CreateTime>%s</CreateTime>
                        <MsgType><![CDATA[%s]]></MsgType>
                        <Content><![CDATA[%s]]></Content>
                        </xml>';
                $resultStr = sprintf($msgTpl, $fromUsername, $toUsername, $createTime, $msgType, $contentStr);
                return $resultStr;
                break;
            case 'news':
                $msgTpl = '<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[%s]]></MsgType>
                            <ArticleCount>'.count($articles).'</ArticleCount>
                            <Articles>';
                foreach ($articles as $val){
                    $msgTpl .= '<item>
                                    <Title><![CDATA['.$val['title'].']]></Title> 
                                    <Description><![CDATA['.$val['description'].']]></Description>
                                    <PicUrl><![CDATA['.$val['picUrl'].']]></PicUrl>
                                    <Url><![CDATA['.$val['url'].']]></Url>
                                </item>';
                }
                $msgTpl .= '</Articles>
                        </xml>';

                $resultStr = sprintf($msgTpl, $fromUsername, $toUsername, $createTime, $msgType);
                return $resultStr;
                break;
        }
    }
    
    
}