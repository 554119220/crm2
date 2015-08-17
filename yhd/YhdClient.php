<?php

class YhdClient
{
    public $appkey;
    public $secretKey;
    public $format        = "json";
    protected $sdkType    = "php";
    protected $apiVersion = "1.0";

    //1号店的APP服务URL
    public $gatewayUrl = "http://openapi.yhd.com/app/api/rest/router";
    public $newGatewayUrl = "http://openapi.yhd.com/app/api/rest/newRouter";
    //读取URL的超时时间
    public $timeout;

    //连接超时时间
    public $connectTimeout;


    /**
     * 对所有请求参数数组，按照KEY值进行排序
     * $allParam
     * 返回：排序后的数组
     */

    public function yhdSort($requestParam) {
        ksort($requestParam);
        reset($requestParam);
        return $requestParam;
    }

    /**
     * 生成sign值的
     * $sysParamsRequest：是系统级应用参数
     * $methodRequest：是具体调用方法的应用级参数
     * $secret:是分配各APP的密钥
     * 
     * 返回值：返回加入sign后的重新排序的数组；
     */
    public function yhdCreatSign($sysParamsArray, $methodParamsArray, $secret)
    {
        //将系统参数和应用级参数进行合并
        if($methodParamsArray != null){
            $paramsArray = array_merge($sysParamsArray, $methodParamsArray);
        }else{
            $paramsArray = $sysParamsArray;
        }

        //对输入参数数组按照Key值进行排序。
        $paramsSortedArray = $this->yhdSort($paramsArray);

        $signStr = '';
        foreach ( $paramsSortedArray as $key => $val) {
            $signStr .= $key.$val;
        }

        $signStr = $secret . $signStr . $secret;

        $sign = md5($signStr);

        $paramsSortedArray["sign"] = $sign;

        $inputsArray = $this->yhdSort($paramsSortedArray);

        //重新进行排序
        return $inputsArray;
    }

    /**输入参数：
     * $url：服务端URL；
     * $sysParamsRequest：是系统级应用参数
     * $methodRequest：是具体调用方法的应用级参数
     * $secret:是分配各APP的密钥
     * 
     * 返回值：返回响应信息
     */
    public function yhdSendByPost($url, $postdata)
    {
        //发送数据
        $urlRs = curl_init();
        curl_setopt($urlRs, CURLOPT_URL, $url);
        curl_setopt($urlRs, CURLOPT_FAILONERROR, false);
        curl_setopt($urlRs, CURLOPT_RETURNTRANSFER, true);

        if ($this->timeout) {
            curl_setopt($urlRs, CURLOPT_TIMEOUT, $this->timeout);
        }

        if ($this->connectTimeout) {
            curl_setopt($urlRs, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);
        }

        //判断是否是HTTPS请求
        if(strlen($url) > 5 && strtolower(substr($url, 0, 5)) == "https" ) 
        {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        curl_setopt($urlRs, CURLOPT_POST, true);

        curl_setopt($urlRs, CURLOPT_POSTFIELDS, $postdata);

        //发送消息，获取响应
        $reponse = curl_exec($urlRs);

        if (curl_errno($urlRs))
        {
            throw new Exception(curl_error($urlRs), 0);
        }
        else
        {
            //获取传输返回码
            $httpStatusCode = curl_getinfo($urlRs, CURLINFO_HTTP_CODE);

            if (200 !== $httpStatusCode)
            {
                throw new Exception($reponse, $httpStatusCode);
            }
        }

        //关闭连接
        curl_close($urlRs);

        return $reponse;
    }




    function execute()
    {
        $name = "execute".func_num_args(); 
        return call_user_func_array(array($this,$name), func_get_args()); 
    }


    public function execute1($request){

        //组装系统参数
        $sysParamsArray["appKey"] = $this->appkey;
        $sysParamsArray["ver"] = $this->apiVersion;
        $sysParamsArray["format"] = $this->format;
        $sysParamsArray["method"] = $request->getApiMethodName();
        $sysParamsArray["timestamp"] = date("Y-m-d H:i:s");
        $sysParamsArray["sdkType"] = $this->sdkType;

        //获取业务参数
        $methodParamsArray = $request->getApiParas();

        if($request->getIsCompatible() == 1){
            $this->gatewayUrl = $this->newGatewayUrl;
        }

        return $this->execute5($sysParamsArray, $methodParamsArray, null, $this->secretKey, $request->getIsCompatible());
    }


    public function execute2($request, $sessionKey = null){

        //组装系统参数
        $sysParamsArray["appKey"]    = $this->appkey;
        $sysParamsArray["ver"]       = $this->apiVersion;
        $sysParamsArray["format"]    = $this->format;
        $sysParamsArray["method"]    = $request->getApiMethodName();
        $sysParamsArray["timestamp"] = date("Y-m-d H:i:s");
        $sysParamsArray["sdkType"]   = $this->sdkType;

        if ($sessionKey != null){
            $sysParamsArray["sessionKey"] = $sessionKey;
        }

        //获取业务参数
        $methodParamsArray = $request->getApiParas();

        if($request->getIsCompatible() == 1){
            $this->gatewayUrl = $this->newGatewayUrl;
        }

        return $this->execute5($sysParamsArray, $methodParamsArray, null, $this->secretKey, $request->getIsCompatible());
    }


    public function execute4($sysParamsArray, $methodParamsArray, $fileArray, $secret){
        execute5($sysParamsArray, $methodParamsArray, $fileArray, $secret, 0);
    }


    /**输入参数：
     * $sysParamsRequest：是系统级应用参数
     * $methodRequest：是具体调用方法的应用级参数
     * $fileArray:要上传的文件数组.
     * 例如：
     * $fileArray["file1"] = "@C:\\My Pictures\\{1E2AFB12-25A1-4D44-8431-010C040188E4}.jpg";
     * $fileArray["file2"] = "@C:\\My Pictures\\{1E2AFB12-25A1-4D44-8431-010C040188E4}.jpg";
     * 
     * $secret:是分配各APP的密钥
     * 
     * 返回值：返回对象格式响应信息。
     */
    public function execute5($sysParamsArray, $methodParamsArray, $fileArray, $secret, $type)
    {
        $url = $this->gatewayUrl;
        $sysParamsArray["sdkType"] = $this->sdkType;

        //设置时间戳
        date_default_timezone_set ('PRC');
        $timestamp = date('Y-m-d H:i:s', time());
        $sysParamsArray["timestamp"] = $timestamp;

        //生成sign值
        $paramsArray = $this->yhdCreatSign($sysParamsArray, $methodParamsArray, $secret);

        //判断是否上传文件
        $fileUploadFlag = false;
        if(is_array($fileArray) && count($fileArray) > 0)
        {
            foreach ($fileArray as $filekey => $filevalue)
            {
                //校验上传的文件URL是否正确
                if("@" != substr($filevalue, 0, 1))
                {
                    trigger_error("upload file URL error. for example, '@C:\\Documents and Settings\\{1E2AFB12-25A1-4D44-8431-010C040188E4}.jpg'");
                }
            }

            $fileUploadFlag = true;
        }

        $postdata = '';

        //文件上传 HTTP的CONTENT_TYPE：multipart/form-data
        if((is_array($paramsArray) && count($paramsArray) > 0 && $fileUploadFlag == true) || $type == 1)
        {
            //将文件的数组合并在一起。
            if($fileArray != null){
                $postdata = array_merge($paramsArray, $fileArray);
            }else{
                $postdata = $paramsArray;
            }
        }

        ////将数组格式的数组转化为1号店固定的拼接格式，作为发送数据。
        //非文件上传类型，HTTP的CONTENT_TYPE：application/x-www-form-urlencoded
        else 
        {
            foreach ($paramsArray as $key => $val) 
            {
                $postdata .= rawurlencode($key) .'='. rawurlencode($val) .'&';
            }
        }

        try 
        {
            //发送HTTP消息
            $response = $this->yhdSendByPost($url, $postdata);
        }
        catch (Exception $e)
        {
            @trigger_error("send msg exception: " + $e->getCode() + "  " + $e->getMessage());
            return null;
        }

        //解析返回的XML和JSON格式
        $isRightFormed = 0;
        $format1 = $sysParamsArray["format"];
        if ("json" == $format1)
        {
            //$response = $this->ctrlCharFilter($response);
            $respObject = json_decode($response);
            if ('yhd.orders.detail.get' == $sysParamsArray['method']) {
            }

            if (null !== $respObject)
            {
                $isRightFormed = 1;
                foreach ($respObject as $propKey => $propValue)
                {
                    $respObject = $propValue;
                }
            }
        }
        else if("xml" == $format1)
        {
            $respObject = @simplexml_load_string($response);
            if ('yhd.orders.detail.get' == $sysParamsArray['method']) {
            }

            if (false !== $respObject)
            {
                $isRightFormed = 1;
            }
        }

        //返回的HTTP文本不是标准JSON或者XML，记下错误日志
        if (!$isRightFormed)
        {
            trigger_error("format error. it should be json or xml.");
            return null;
        }

        return $respObject;
    }


    public function setGatewayUrl($gatewayUrl)
    {
        $this->gatewayUrl = $gatewayUrl;
    }

    public function getGatewayUrl()
    {
        return $this->getGatewayUrl();
    }

    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

    public function getTimeout()
    {
        return $this->timeout;
    }

    public function setConnectTimeout($connectTimeout)
    {
        $this->connectTimeout = $connectTimeout;
    }

    public function getConnectTimeout()
    {
        return $this->connectTimeout;
    }

    /**
     * controlCharFilter 
     * @return $str
     * @author nixus
     **/
    public function ctrlCharFilter ($str)
    {
        for ($i = 0; $i < strlen($str) -1; $i++) {
            $ascii_code = ord($str[$i]);
            if ($ascii_code < 32 || $ascii_code == 127) {
                $str[$i] = '';
            }
        }

        return $str;
    }
}
