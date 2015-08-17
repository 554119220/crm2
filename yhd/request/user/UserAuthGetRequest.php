<?php
/**
*用户登录认证
*/

class UserAuthGetRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.user.auth.get";
	}
	
	/**网站指定Id(10:爱彩网)*/
	private  $siteId; 
	/**Md5字串，由siteId+code+sessionId加密而成，code是为网站分配的密钥*/
	private  $md5key; 
	/**1号店用户的唯一标识*/
	private  $sessionId; 

	public function getSiteId(){
		return $this->siteId;
	}
	public function getMd5key(){
		return $this->md5key;
	}
	public function getSessionId(){
		return $this->sessionId;
	}

	public function setSiteId($siteId){
		$this->siteId = $siteId;
		$this->apiParas["siteId"] = $siteId;
	}
	public function setMd5key($md5key){
		$this->md5key = $md5key;
		$this->apiParas["md5key"] = $md5key;
	}
	public function setSessionId($sessionId){
		$this->sessionId = $sessionId;
		$this->apiParas["sessionId"] = $sessionId;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
