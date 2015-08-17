<?php
/**
*批量查询供应商产品价格信息
*/

class SupplierProductsPriceGetRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.supplier.products.price.get";
	}
	
	/**产品id列表，用英文逗号(,)分隔*/
	private  $productIdList; 

	public function getProductIdList(){
		return $this->productIdList;
	}

	public function setProductIdList($productIdList){
		$this->productIdList = $productIdList;
		$this->apiParas["productIdList"] = $productIdList;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
