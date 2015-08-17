<?php
/**
*查询店铺信息
*/

class StoreGetRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.store.get";
	}
	



	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
