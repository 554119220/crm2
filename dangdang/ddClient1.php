<?php

/**
 * Class Ddclient 
 * @author Nixus
 */
class DangdangClient
{
    private $url = 'http://api.open.dangdang.com/openapi/rest?v=1.0'; // 接口URI

    private $appSecret;

    private $params = array(); // 系统输入级参数

    /*
     * Setter for params
     */
    public function setParams($params)
    {
        $this->params = $params;
        return $this;
    }

    private function generatorSigns()
    {
        ksort($this->params); // 排序系统级参数

        $params_str = '';
        foreach ($this->params as $key=>$val){
            $params_str .= $key.$val;
        }

        $sign = $this->params['sign_method']($this->appSecret.$params_str.$this->appSecret);

        $sign = strtoupper($sign);

        return $sign;
    }

    private function assembled()
    {
        $params_post = '';
        foreach ($this->params as $key=>$val){
            $params_post .= "&$key=".urlencode(mb_convert_encoding(trim($val), 'gbk', 'auto'));
        }
        
        return $params_post;
    }
    

    /*
     * Setter for appSecret
     */
    public function setAppSecret($appSecret)
    {
        $this->appSecret = $appSecret;
    }

    public function execute ($url)
    {
    }
    

    public function curl_get()
	{
        $sign        = $this->generatorSigns();
        $request_str = $this->assembled();
        $url         = $this->url."&sign=$sign$request_str";

		$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new Exception(curl_error($ch), 0);
        } else {
            $http_statys_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if (200 !== $http_statys_code) {
                throw new Exception($response, $http_statys_code);
            }
        }

        curl_close($ch);
		$response = simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOCDATA);
		$response = $this->to_array($response);
        return $response;
	}
    /*
    {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $data = curl_exec($ch);

        curl_close($ch);
        return $data;
    }
     */
    
	public function to_array($o)
    {
        if(is_array($o))
        {
            foreach($o as $k => $v)
            {
                if(is_array($v) || is_object($v))
                {
                    $o[$k] = $this->to_array($v);
                }
                else
                {
                    $o[$k] = $v;
                }
            }
        }
        elseif(is_object($o))
        {
            $r = array();
            foreach(get_object_vars($o) as $k => $v)
            {
                if(is_array($v) || is_object($v))
                {
                    $r[$k] = $this->to_array($v);
                }
                else
                {
                    $r[$k] = $v;
                }
            }
            return $r;
        }
        return $o;
    }
}
