<?php
/**
*根据用户类别和ID获取SBY用户ID
*/

class SbyUseridGetRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.sby.userid.get";
	}
	
	/**用户类型对应的id*/
	private  $id; 
	/**用户类型 1:商家 2：内部用户 3:供应商*/
	private  $userType; 

	public function getId(){
		return $this->id;
	}
	public function getUserType(){
		return $this->userType;
	}

	public function setId($id){
		$this->id = $id;
		$this->apiParas["id"] = $id;
	}
	public function setUserType($userType){
		$this->userType = $userType;
		$this->apiParas["userType"] = $userType;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
