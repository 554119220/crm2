<?php
/**
 * User: zhangfeng
 * Date: 12-6-11
 * Time: 下午9:12
 */

class JdClient
{
     public $serverUrl;
     public $appKey;
     public $appSecret;
     public $accessToken;

     //todo 将正式环境的换回来
     public $gwUrl = "http://gw.api.360buy.com/routerjson";
     /**
      * api 版本
      * @var string
      */
     protected $version = "2.0";

     protected $CHARSET_UTF8 = "UTF-8";
     protected $JSON_PARAM_KEY = "360buy_param_json";
     protected $connectTimeout = 0;
     protected $readTimeout = 0;

     public function execute($request)
     {
          //组装系统参数
          $sysParams["v"] = $this->version; //版本
          $sysParams["app_key"] = $this->appKey;
          $sysParams["method"] = $request->getApiMethod();
          $sysParams["access_token"] = $this->accessToken;
          $sysParams["timestamp"] = date("Y-m-d H:i:s", time());

          //获取业务参数
          $apiParams["360buy_param_json"] = $request->getAppJsonParams();
          //签名
          $sysParams["sign"] = $this->generateSign(array_merge($sysParams, $apiParams));

          $sysUrl = $this->buildUrl($sysParams);
          $requestUrl = $this->gwUrl;
          $requestUrl .= $sysUrl;

          //发送http请求
          try {
               $resp = $this->sendHttpRequest($requestUrl, $apiParams);
          } catch (Exception $e) {
               //todo  要处理异常，记录日志
               print_r($e->getMessage());
               return false;
          }

          //解析返回结果
          $respObject = json_decode($resp);
          return $respObject;
     }

     /**
      *
      * 发送http请求
      *
      * @param $url
      * @param null $apiParams
      * @return mixed
      * @throws Exception
      */
     public function sendHttpRequest($url, $apiParams = null)
     {
          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL, $url);
          curl_setopt($ch, CURLOPT_FAILONERROR, false);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

          if (is_array($apiParams) && 0 < count($apiParams)) {
               $postBodyString = "";
               foreach ($apiParams as $k => $v) {
                    $postBodyString .= "$k=" . urlencode($v) . "&";
               }
               unset($k, $v);
               curl_setopt($ch, CURLOPT_POST, true);
               curl_setopt($ch, CURLOPT_POSTFIELDS, substr($postBodyString, 0, -1));
          }

          $response = curl_exec($ch);
          if (curl_errno($ch)) {
               throw new Exception(curl_error($ch), 0);
          } else {
               $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
               if (200 !== $httpStatusCode) {
                    throw new Exception($response, $httpStatusCode);
               }
          }
          curl_close($ch);
          return $response;
     }

     /**
      * 拼接系统参数
      * @param $params
      * @return string
      */
     public function buildUrl($params)
     {
          $requestUrl = $this->serverUrl . "?";
          foreach ($params as $sysParamKey => $sysParamValue) {
               $requestUrl .= "$sysParamKey=" . urlencode($sysParamValue) . "&";
          }
          $requestUrl = substr($requestUrl, 0, -1);
          return $requestUrl;
     }

     /**
      * 签名
      * @param  $params 业务参数
      * @return void
      */
     private function generateSign($params)
     {
          if ($params != null) { //所有请求参数按照字母先后顺序排序
               ksort($params);

               //定义字符串开始 结尾所包括的字符串
               $stringToBeSigned = $this->appSecret;
               //把所有参数名和参数值串在一起
               foreach ($params as $k => $v) {
                    $stringToBeSigned .= "$k$v";
               }
               unset($k, $v);

               //把venderKey加在字符串的两端
               $stringToBeSigned .= $this->appSecret;

          }else {
               //定义字符串开始 结尾所包括的字符串
               $stringToBeSigned = $this->appSecret;
               //把venderKey加在字符串的两端
               $stringToBeSigned .= $this->appSecret;

          }

          //使用MD5进行加密，再转化成大写
          return strtoupper(md5($stringToBeSigned));
     }
}
