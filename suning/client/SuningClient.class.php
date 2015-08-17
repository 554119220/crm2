<?php
/**
 * Class SuningClient
 * @author John Doe
 */
class SuningClient
{
    private $method;    // 方法名
    private $appKey;    // appkey
    private $appSecret; // secretKey

    private $format  = 'json';
    private $version = 'v1.2';                     // 版本
    private $appRequestTime;// = date('Y-m-d H:i:s', time()); // 调用时间

    private $url = 'http://open.suning.com/api/http/sopRequest'; // 接口地址
    
    public function execute ($req, $postfield)
    {
        $baseStr   = base64_encode($postfield);

        $md5str = $this->makeSignInfo($baseStr);
        $header = $this->constructHeader($md5str);

        $resp = $this->curlExecute($header, $postfield);
        //$user_agent ="Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)";

        return $resp;
    }

    /**
     * curlExecute
     * @return json $result
     * @author nixus
     **/
    private function curlExecute($header, $postfield)
    {
        $ch = curl_init();                                // 初始化CURL句柄
        curl_setopt($ch, CURLOPT_URL, $this->url);        // 设置请求的URL
        curl_setopt($ch, CURLOPT_HEADER, 1);              // 设置请求头
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);    // 设置头信息的地方
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);       // 设为TRUE把curl_exec()结果转化为字串，而不是直接输出
        curl_setopt($ch, CURLOPT_POST, 1);                // 启用POST提交
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postfield); // 设置POST提交的字符串

        //允许curl提交后,网页重定向  
        //curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0); 

        //将curl提交后的header返回  

        curl_setopt($ch, CURLOPT_HEADER, 0);          

        $result = curl_exec($ch);    // 执行预定义的CURL
        $info   = curl_getinfo($ch); // 得到返回信息的特性

        curl_close($ch);

        return $result;
    }
       
    /**
     * 生成签名
     */
    private function makeSignInfo($baseStr)
    {
        $tomd5 = $this->appSecret.$this->method.$this->appRequestTime.$this->appKey.$this->version;
        $md5str = md5($tomd5.$baseStr);

        return $md5str;
    }

    /**
     * constructHeader 
     * @return header Array
     * @author John Doe
     **/
    private function constructHeader ($md5str)
    {
        $header = array (
            "AppMethod:{$this->method}",
            "AppRequestTime:{$this->appRequestTime}",
            "Format:{$this->format}",
            "AppKey:{$this->appKey}",
            "signInfo:{$md5str}",
            "VersionNo:{$this->version}",
            "Content-Type: text/xml; charset=utf-8"
        );

        return $header;
    }

    /**
     * constructFields 
     * @return json $fields
     * @author nixus
     **/
    private function constructFields ()
    {
        return $fields;
    }

    /**
     * 设置调用的接口名称
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * Setter for AppKey
     */
    public function setAppKey($appKey)
    {
        $this->appKey = $appKey;
    }
    
    /**
     * Setter for appSecret
     */
    public function setAppSecret($appSecret)
    {
        $this->appSecret = $appSecret;
    }

    /**
     * Setter for appRequestTime
     */
    public function setAppRequestTime($dataTime)
    {
        $this->appRequestTime = $dataTime;
    }

    /**
     * Setter for version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }
}
