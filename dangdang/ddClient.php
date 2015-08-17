<?php
/**
 * Class Ddclient2 
 * @author Nixus
 */
class ddClient
{
    private $url = 'http://api.open.dangdang.com/openapi/rest?v=1.0'; // 接口地址

    private $app_key = '';

    private $app_secret = '';

    private $session = '';

    private $method = '';

    private $v = '1.0'; // '版本号';

    private $sign_method = 'md5'; // 签名算法

    private $format = 'xml'; // 响应数据格式 可选：xml、json(暂无)

    // 店铺配置
    private $channles = array (
        '2100001198' => array (
            'app_key'    => '2100001198',
            'app_secret' => 'BE9C10CDC41853DBE058E5C9AE15E89B',
            'session'    => 'F72489FD52F20E833A704DCF6772C55918D731ABED61E683B3E76211CF6378C2',
        ),
    );

    public function __construct($app_id)
    {
        $this->channle_set($app_id);
    }

    // 选取店铺配置信息
    private function channle_set($key)
    {
        $this->app_key    = $this->channles[$key]['app_key'];
        $this->app_secret = $this->channles[$key]['app_secret'];
        $this->session    = $this->channles[$key]['session'];
    }

    // 设置系统级调用参数
    private function get_sys_params()
    {
        $sys_params = array (
            'v'           => $this->v,
            'app_key'     => $this->app_key,
            'method'      => $this->method,
            'sign_method' => $this->sign_method,
            'timestamp'   => date('Y-m-d H:i:s'),
            'format'      => $this->format,
            'session'     => $this->session,
        );

        $sys_params['sign'] = $this->make_sign($sys_params);

        return $sys_params;
    }
    

    // 应用级参数

    // 生成签名
    private function make_sign($params)
    {
        $sign_str = $this->app_secret;
        $sign_str .= $this->sort_params($params).$this->app_secret;

        $sign_str = $params['sign_method']($sign_str);
        return strtoupper($sign_str);
    }
    
    // 拼装参数列表
    private function sort_params($params, $connector = false)
    {
        ksort($params);
        $params_str = '';
        if ($connector) {
            foreach ($params as $key=>$val) {
                $params_str .= '&'.trim($key).'='.urlencode(mb_convert_encoding(trim($val), 'gbk', 'auto'));
            }
        } else {
            foreach ($params as $key=>$val) {
                $params_str .= trim($key).mb_convert_encoding(trim($val), 'gbk', 'auto');
            }
        }

        return $params_str;
    }

    // 执行获取数据请求
    public function execute($method = 'POST', $params = null, $dd_method = null)
    {
        if (!is_null($dd_method)) {
            $this->set_method($dd_method);
        }

        $sys_params = $this->get_sys_params();
        if (!is_null($params)) {
            $sys_params = array_merge($sys_params, $params);
        }
        $params_str = $this->sort_params($sys_params, true);

        $url = $this->url.$params_str;

        if ($method == 'POST') {
            $resp = $this->send_http_post($url, $sys_params);
        } elseif ($method == 'GET') {
            $resp = $this->send_http_get($url);
        } elseif ($method == 'POSTXML') {
            $resp = $this->sendMultipartFormRequest($url, $params);
        }

        if (is_null(json_decode($resp))) {
            $resp = simplexml_load_string($resp, 'SimpleXMLElement', LIBXML_NOCDATA);
            $resp = $this->to_array($resp);
        } else {
            $resp = json_decode($resp, true);
        }

        return $resp;
    }
    
    // xml 转 array
	private function to_array($o)
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

    // 向远端接口发送请求 GET
	private function send_http_get($url)
	{
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
        return $response;
	}
	
    // 向远端接口发送请求 POST
	private function send_http_post($url,$file)
	{
		$params = array();
		$ch = curl_init();
		if (isset($file['sendGoods'])) {
			$header[] = "Content-type:text/xml; charset=GBK";
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		}
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_POST, 1 );
		curl_setopt($ch, CURLOPT_POSTFIELDS, $file);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		$response = curl_exec($ch);
		curl_close($ch);
		return $response;
	}

	/**
	 * 发送POST请求的XML数据
	 * @param $request_url 请求地址
	 * @param $apiParams 需要上传的文件(绝对路劲)
	 * return string
	 */
	private function sendMultipartFormRequest($request_url, $apiParams){
		//组装发送参数
		$parse_url = parse_url($request_url);
		parse_str($parse_url['query'], $query);
		if (!is_array($query) || !is_array($apiParams) || count($query) < 1 || count($apiParams) < 1) return false;
		$url = $this->url;
		$postdata = '';
		$mime_boundary = "594016343".md5(uniqid(microtime()));
		reset($query);
		while(list($key,$val) = each($query)) {
			if (is_array($val) || is_object($val)) {
				while (list($cur_key, $cur_val) = each($val)) {
					$postdata .= "--{$mime_boundary}\r\n";
					$postdata .= "Content-Disposition: form-data; name=\"$key\[\]\"\r\n\r\n";
					$postdata .= "{$cur_val}\r\n";
				}
			} else {
				$postdata .= "--{$mime_boundary}\r\n";
				$postdata .= "Content-Disposition: form-data; name=\"$key\"\r\n\r\n";
				$postdata .= "{$val}\r\n";
			}
		}
		reset($apiParams);
		while (list($field_name, $file_names) = each($apiParams)) {
			settype($file_names, "array");
			while (list(, $file_name) = each($file_names)) {
				if (!is_readable($file_name)) continue;
				$fp = fopen($file_name, "r");
				$file_content = fread($fp, filesize($file_name));
				fclose($fp);
				$base_name = basename($file_name);
				$postdata .= "--{$mime_boundary}\r\n";
				$postdata .= "Content-Disposition: form-data; name=\"$field_name\"; filename=\"$base_name\"\r\n\r\n";
				$postdata .= "{$file_content}\r\n";
			}
		}
		$postdata .= "--{$mime_boundary}--\r\n";
		//组装头部信息
		$headers = '';
		$parse_url = parse_url($url);
		$path = isset($parse_url['path']) ? $parse_url['path'] : '/';
		if (isset($parse_url['query'])) $path .= '?'.$parse_url['query'];
		$headers = "POST {$path} HTTP/1.0\r\n";
		if (isset($parse_url['host'])) $headers .= "Host: ".$parse_url['host'].":80\r\n";
		$headers .= "Accept: image/gif, image/x-xbitmap, image/jpeg, image/pjpeg, */*\r\n";
		$headers .= "Content-type: multipart/form-data; boundary={$mime_boundary}\r\n";
		if ($postdata != '') $headers .= "Content-length: ".strlen($postdata)."\r\n";
		$headers .= "\r\n";
		//发送数据并返回结果
		$fp = fsockopen($parse_url['host'], 80, $errno, $errstr, 30);
		if (!$fp){
			echo $errstr."[{$errno}]";
			exit();
		}
		fwrite($fp, $headers.$postdata, strlen($headers.$postdata));
		while($currentHeader = fgets($fp, 500000)){
			if($currentHeader == "\r\n") break;
		}
		$response = '';
		do {
    		$_data = fread($fp, 500000);
    		if (strlen($_data) == 0) break;
    		$response .= $_data;
		} while(true);
		return $response;
	}

    // 修改默认的参数

    /*
     * Setter for v
     */
    public function set_V($v)
    {
        $this->v = $v;
    }

    /*
     * Setter for url
     */
    public function set_url($url)
    {
        $this->url = $url;
    }
    
    /*
     * Setter for sign_method
     */
    public function set_sign_method($sign_method)
    {
        $this->sign_method = $sign_method;
    }
    
    /*
     * Setter for format
     */
    public function setFormat($format)
    {
        $this->format = $format;
    }

    /*
     * Setter for method
     */
    public function set_method($method)
    {
        $this->method = $method;
    }
}
