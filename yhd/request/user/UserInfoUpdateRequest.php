<?php
/**
*用户信息回传
*/

class UserInfoUpdateRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.user.info.update";
	}
	
	/**网站指定Id(10:爱彩网)*/
	private  $siteId; 
	/**Md5字串，由siteId+code+sessionId加密而成，code是为网站分配的密钥*/
	private  $md5key; 
	/**1号店用户的唯一标识*/
	private  $sessionId; 
	/**用户真实姓名*/
	private  $realUserName; 
	/**用户身份证号*/
	private  $idCard; 
	/**电话号码*/
	private  $mobile; 
	/**邮箱*/
	private  $email; 

	public function getSiteId(){
		return $this->siteId;
	}
	public function getMd5key(){
		return $this->md5key;
	}
	public function getSessionId(){
		return $this->sessionId;
	}
	public function getRealUserName(){
		return $this->realUserName;
	}
	public function getIdCard(){
		return $this->idCard;
	}
	public function getMobile(){
		return $this->mobile;
	}
	public function getEmail(){
		return $this->email;
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
	public function setRealUserName($realUserName){
		$this->realUserName = $realUserName;
		$this->apiParas["realUserName"] = $realUserName;
	}
	public function setIdCard($idCard){
		$this->idCard = $idCard;
		$this->apiParas["idCard"] = $idCard;
	}
	public function setMobile($mobile){
		$this->mobile = $mobile;
		$this->apiParas["mobile"] = $mobile;
	}
	public function setEmail($email){
		$this->email = $email;
		$this->apiParas["email"] = $email;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
