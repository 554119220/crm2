<?php
/**
*批量获取新品品牌列表
*/

class SupplierBrandsGetRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.supplier.brands.get";
	}
	



	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
