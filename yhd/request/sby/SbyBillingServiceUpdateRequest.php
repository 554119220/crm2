<?php
/**
*更新服务开始计费
*/

class SbyBillingServiceUpdateRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.sby.billing.service.update";
	}
	



	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
