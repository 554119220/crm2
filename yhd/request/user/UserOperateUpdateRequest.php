<?php
/**
*用户操作结果回写
*/

class UserOperateUpdateRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.user.operate.update";
	}
	
	/**网站指定Id(10:爱彩网)*/
	private  $siteId; 
	/**Md5字串，由siteId+code+sessionId加密而成，code是为网站分配的密钥*/
	private  $md5key; 
	/**1号店用户的唯一标识*/
	private  $sessionId; 
	/**唯一标识*/
	private  $uniqueMark; 
	/**请求类型。0：回写购彩结果1：回写冻结状态*/
	private  $requestType; 
	/**操作结果。0：成功，1：失败。请求类型为0时，为必选参数；请求参数为1时，无需填写。*/
	private  $result; 

	public function getSiteId(){
		return $this->siteId;
	}
	public function getMd5key(){
		return $this->md5key;
	}
	public function getSessionId(){
		return $this->sessionId;
	}
	public function getUniqueMark(){
		return $this->uniqueMark;
	}
	public function getRequestType(){
		return $this->requestType;
	}
	public function getResult(){
		return $this->result;
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
	public function setUniqueMark($uniqueMark){
		$this->uniqueMark = $uniqueMark;
		$this->apiParas["uniqueMark"] = $uniqueMark;
	}
	public function setRequestType($requestType){
		$this->requestType = $requestType;
		$this->apiParas["requestType"] = $requestType;
	}
	public function setResult($result){
		$this->result = $result;
		$this->apiParas["result"] = $result;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
