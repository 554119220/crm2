<?php
/**
*批量获取产品库存信息
*/

class ProductsStockGetRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.products.stock.get";
	}
	
	/**1号店产品ID列表（逗号分隔）与outerIdList二选一,最大长度为100*/
	private  $productIdList; 
	/**外部产品ID列表（逗号分隔）与productIdList二选一,最大长度为100*/
	private  $outerIdList; 
	/**仓库id（不传则是默认仓库）*/
	private  $warehouseId; 

	public function getProductIdList(){
		return $this->productIdList;
	}
	public function getOuterIdList(){
		return $this->outerIdList;
	}
	public function getWarehouseId(){
		return $this->warehouseId;
	}

	public function setProductIdList($productIdList){
		$this->productIdList = $productIdList;
		$this->apiParas["productIdList"] = $productIdList;
	}
	public function setOuterIdList($outerIdList){
		$this->outerIdList = $outerIdList;
		$this->apiParas["outerIdList"] = $outerIdList;
	}
	public function setWarehouseId($warehouseId){
		$this->warehouseId = $warehouseId;
		$this->apiParas["warehouseId"] = $warehouseId;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
