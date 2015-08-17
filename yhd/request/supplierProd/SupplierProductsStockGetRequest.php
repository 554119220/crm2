<?php
/**
*批量查询供应商产品库存信息
*/

class SupplierProductsStockGetRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.supplier.products.stock.get";
	}
	
	/**产品id列表，用英文逗号(,)分隔*/
	private  $productIdList; 
	/**仓库ID*/
	private  $warehouseId; 

	public function getProductIdList(){
		return $this->productIdList;
	}
	public function getWarehouseId(){
		return $this->warehouseId;
	}

	public function setProductIdList($productIdList){
		$this->productIdList = $productIdList;
		$this->apiParas["productIdList"] = $productIdList;
	}
	public function setWarehouseId($warehouseId){
		$this->warehouseId = $warehouseId;
		$this->apiParas["warehouseId"] = $warehouseId;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
