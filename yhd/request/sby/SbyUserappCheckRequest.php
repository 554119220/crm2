<?php
/**
*验证用户购买的服务是否可用
*/

class SbyUserappCheckRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.sby.userapp.check";
	}
	



	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
