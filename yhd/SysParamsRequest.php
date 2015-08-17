<?php
	
	/**
	 * 系统级参数请求
	 * @author jinxiaowei
	 *
	 */
	class SysParamsRequest
	{
		//分配给应用的AppKey
		private $appKey;
		
		//分配给用户的SessionKey，用户通过登录授权获取
		private $sessionKey;
		
		//验证码
		private $sign;

		//返回数据格式 xml/json
		private $format;
		
		//接口版本1.0
		private $ver;
		
		//接口名称
		private $method;
		
		//时间戳，格式为yyyy-MM-dd HH:mm:ss
		private $timestamp;
		
		public  $apiParas = array();
		
		public function setAppKey($appKey) 
		{
			$this->apiParas["sdkType"] = "php";
			$this->appKey = $appKey;
			$this->apiParas["appKey"] = $appKey;
		}
		
		public function getAppKey() 
		{
			return $this->appKey;
		}
		
		public function setSessionKey($sessionKey) 
		{
			$this->sessionKey = $sessionKey;
			$this->apiParas["sessionKey"] = $sessionKey;
		}
		
		public function getSessionKey() 
		{
			return $this->sessionKey;
		}
		
		public function setSign($sign) 
		{
			$this->sign = $sign;
			$this->apiParas["sign"] = $sign;
		}
		
		public function getSign() 
		{
			return $this->sign;
		}
		
		public function setFormat($format) 
		{
			$this->format = $format;
			$this->apiParas["format"] = $format;
		}
		
		public function getFormat() 
		{
			return $this->format;
		}
		
		public function setMethod($method) 
		{
			$this->method = $method;
			$this->apiParas["method"] = $method;
		}
		
		public function getMethod() 
		{
			return $this->method;
		}
		
		public function setTimestamp($timestamp) 
		{
			$this->timestamp = $timestamp;
			$this->apiParas["timestamp"] = $timestamp;
		}
		
		public function getTimestamp() 
		{
			return $this->timestamp;
		}
		
		public function getApiParas()
		{
			return $this->apiParas;
		}
		
		public function setVer($ver) 
		{
			$this->ver = $ver;
			$this->apiParas["ver"] = $ver;
		}
		
		public function getVer() 
		{
			return $this->ver;
		}
	}