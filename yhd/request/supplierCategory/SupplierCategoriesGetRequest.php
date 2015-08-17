<?php
/**
*批量获取新品类别列表
*/

class SupplierCategoriesGetRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.supplier.categories.get";
	}
	



	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
