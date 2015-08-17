<?php
/**
*查询商家被授权品牌列表
*/

class CategoryBrandsGetRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.category.brands.get";
	}
	



	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
