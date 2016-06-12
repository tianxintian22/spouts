<?php
require './lib/wechatCallbackapi.class.php';
require './lib/base.class.php';

$wechatObj = new wechatCallbackapi();
if (isset($_GET['echostr'])) {//第一次验证服务地址有效性时会接收一个echostr参数
    $wechatObj->valid();
}else{
    $wechatObj->responseMsg();
}


?>