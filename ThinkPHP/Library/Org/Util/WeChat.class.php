<?php
namespace Org\Util;
use Think\Model;
class WeChat {
    private $appId="wx133a00915c785dec";
    private $appSecret="WAstSZdvP8MsNHR9SE1uCXg4nAsCb4h7jsEpjQVBhgPqvSHoKeOtBUQuBGVRXcro";

    public function __construct($appId='', $appSecret='') {
    if(!empty($appId)) $this->appId = $appId;
    if(!empty($appSecret)) $this->appSecret = $appSecret;
    }

    public function getSignPackage() {
    $jsapiTicket = $this->getJsApiTicket();

    // 注意 URL 一定要动态获取，不能 hardcode.
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

    $timestamp = time();
    $nonceStr = $this->createNonceStr();

    // 这里参数的顺序要按照 key 值 ASCII 码升序排序
    $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

    $signature = sha1($string);

    $signPackage = array(
      "appId"     => $this->appId,
      "nonceStr"  => $nonceStr,
      "timestamp" => $timestamp,
      "url"       => $url,
      "signature" => $signature,
      "rawString" => $string
    );
    return $signPackage; 
    }

    private function createNonceStr($length = 16) {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $str = "";
    for ($i = 0; $i < $length; $i++) {
      $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
    }
    return $str;
    }

    private function getJsApiTicket() {
    // jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例
    $data = json_decode($this->get_php_file(APP_PATH."/Light/jsapi_ticket.php"));
    if ($data->expire_time < time()) {
      $accessToken = $this->getAccessToken();
      // 如果是企业号用以下 URL 获取 ticket
      $url = "https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token=$accessToken";
      // $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
      $res = json_decode($this->httpGet($url));
      $ticket = $res->ticket;
      if ($ticket) {
        $data->expire_time = time() + 7000;
        $data->jsapi_ticket = $ticket;
        $this->set_php_file(APP_PATH."/Light/jsapi_ticket.php", json_encode($data));
      }
    } else {
      $ticket = $data->jsapi_ticket;
    }

    return $ticket;
    }

    public function getAccessToken() {
    // access_token 应该全局存储与更新，以下代码以写入到文件中做示例
    $data = json_decode($this->get_php_file(APP_PATH."/Light/access_token.php"));
    if ($data->expire_time < time()) {
      // 如果是企业号用以下URL获取access_token
      $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=$this->appId&corpsecret=$this->appSecret";
      // $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$this->appId&secret=$this->appSecret";
      $res = json_decode($this->httpGet($url));
      $access_token = $res->access_token;
      if ($access_token) {
        $data->expire_time = time() + 7000;
        $data->access_token = $access_token;
        $this->set_php_file(APP_PATH."/Light/access_token.php", json_encode($data));
      }
    } else {
      $access_token = $data->access_token;
    }
    return $access_token;
    }

    public function httpGet($url) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, 500);
    // 为保证第三方服务器与微信服务器之间数据传输的安全性，所有微信接口采用https方式调用，必须使用下面2行代码打开ssl安全校验。
    // 如果在部署过程中代码在此处验证失败，请到 http://curl.haxx.se/ca/cacert.pem 下载新的证书判别文件。
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    // curl_setopt($curl,CURLOPT_CAINFO,'cacert.pem');
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_URL, $url);

    $res = curl_exec($curl);
    curl_close($curl);

    return $res;
    }

    public function httpPost($url,$data) {
    $curl = curl_init();
    //关闭直接输出
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, 500);
    // 为保证第三方服务器与微信服务器之间数据传输的安全性，所有微信接口采用https方式调用，必须使用下面2行代码打开ssl安全校验。
    // 如果在部署过程中代码在此处验证失败，请到 http://curl.haxx.se/ca/cacert.pem 下载新的证书判别文件。
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    // curl_setopt($curl,CURLOPT_CAINFO,'cacert.pem');
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_URL, $url);
    //使用post提交数据
    curl_setopt($curl,CURLOPT_POST,1);
    //设置 post提交的数据
    curl_setopt($curl,CURLOPT_POSTFIELDS,$data);
    $res = curl_exec($curl);
    curl_close($curl);

    return $res;
    }

    private function get_php_file($filename) {
    return trim(substr(file_get_contents($filename), 15));
    }
    private function set_php_file($filename, $content) {
    $fp = fopen($filename, "w");
    fwrite($fp, "<?php exit();?>" . $content);
    fclose($fp);
    }

    public function sendMessage($recevier,$content,$agentid='3',$system){
    $accessToken = $this->getAccessToken();
    $sendUrl="https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token=".$accessToken;
    // $sendContent=urlencode(iconv("gb2312","utf-8",$content));
    $sendContent = $content;
    $dataArray=array(
          "touser"=>$recevier,
          "msgtype"=>"text",
          "agentid"=>$agentid,
          "text"=>array("content"=>$sendContent),
          "safe"=>"0"
    );
    $sender = 'defaultSend';
    $dataJson=json_encode($dataArray);
    // $data=urldecode($dataJson);
    // dump($dataJson);
    // exit();
    $data = $dataJson;
    $sendInfo=$this->httpPost($sendUrl,$data);
    $this->saveSendMessage($sender,$recevier,$sendContent,$sendContent,$system,$sendInfo);  //保存系统消息到数据库
    return $sendInfo;
    }

    public function sendNewsMessage($recevier,$title,$description,$url="",$picurl="",$agentid='3',$system){
    $accessToken = $this->getAccessToken();
    $sendUrl="https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token=".$accessToken;
    $sendTitle = $title;
    $sendDescription = $description;

    $dataArray=array(
      "touser"=>$recevier,
      "msgtype"=>"news",
      "agentid"=>$agentid,
      "news"=>array("articles"=>array(array("title"=>$sendTitle,
                                            "description"=>$sendDescription,
                                            "url"=>$url,
                                            "picurl"=>$picurl
                                            )
                                      )
                    )
    );
    $sender = 'newMessage';
    $dataJson=json_encode($dataArray);
    $data=urldecode($dataJson);

    $sendInfo=$this->httpPost($sendUrl,$data);
    //保存系统消息到数据库
    $this->saveSendMessage($sender,$recevier,$sendTitle,$sendDescription,$system,$sendInfo);
    return $sendInfo;
    // return $data;
    }

    public function sendCardMessage($receiver,$title,$description,$url="",$agentid='3',$sender='',$system){
        $accessToken = $this->getAccessToken();
        $sendUrl="https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token=".$accessToken;
        // $sendTitle=urlencode(iconv("gb2312","utf-8",$title));
        // $sendDescription=urlencode(iconv("gb2312","utf-8",$description));
        $sendTitle = $title;
        $sendDescription = $description;
        $dataArray=array(
          "touser"=>$receiver,
          "msgtype"=>"textcard",
          "agentid"=>$agentid,
          "textcard"=>array("title"=>$sendTitle,
                            "description"=>$sendDescription,
                            "url"=>$url,
                        )
        );
        $dataJson=json_encode($dataArray);
        // $data=urldecode($dataJson);
        $data = $dataJson;
        $sendInfo=$this->httpPost($sendUrl,$data);
        //保存系统消息到数据库
        $res = $this->saveSendMessage($sender,$receiver,$title,$description,$system,$sendInfo);
        return $sendInfo;
        // return $data;
    }

    //将数据保存到数据库中
    private function saveSendMessage($sender,$receiver,$title,$content,$system,$sendInfo)
    {
        $sys = M($system.'_sendmsgs');
        $sendtime = date('Y-m-d H:i:s');
        $receivers = explode("|", $receiver);
        $receiversList = implode(",", $receivers);
        $sendStat = json_decode($sendInfo);
        $dataArr = array("sender"=>$sender,
                         "receiver"=>$receiversList,
                         "content"=>$content,
                         "sendTime"=>$sendtime,
                         "state"=>$sendStat->errcode
                        );
        $res = $sys->add($dataArr);
        return $res;
    }

    public function getUserInfo($userid='')
    {
        $accessToken = $this->getAccessToken();
        $url="https://qyapi.weixin.qq.com/cgi-bin/user/get?access_token=".$accessToken."&userid=".$userid;
        $userInfo=$this->httpGet($url);
        return $userInfo;
    }

    public function getDeptInfo($deptid='')
    {
        $accessToken = $this->getAccessToken();
        $url="https://qyapi.weixin.qq.com/cgi-bin/department/list?access_token=".$accessToken."&id=".$deptid;
        $deptInfo=$this->httpGet($url);
        return $deptInfo;
    }

    public function getDeptUserInfo($deptid='', $child=0)
    {
        $accessToken = $this->getAccessToken();
        $url="https://qyapi.weixin.qq.com/cgi-bin/user/list?access_token=".$accessToken."&department_id=".$deptid."&fetch_child=".$child;
        $deptUserInfo=$this->httpGet($url);
        return $deptUserInfo;
    }

}