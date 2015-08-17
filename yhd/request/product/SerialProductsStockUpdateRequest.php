<?php
/**
*批量更新系列产品库存信息
*/

class SerialProductsStockUpdateRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.serial.products.stock.update";
	}
	
	/**系列产品Id，与outerId二选一*/
	private  $productId; 
	/**外部产品Id，与productId二选一*/
	private  $outerId; 
	/**更新类型，默认为1（1：全量更新，2：增量更新）*/
	private  $updateType; 
	/**产品库存信息列表(产品库存信息之间逗号分隔,产品Id、仓库Id和库存数之间冒号分隔)与outerStockList二选一;warehouseId从<a href="yhd.logistics.warehouse.info.get.html" target="_blank">yhd.logistics.warehouse.info.get获取</a>
库存值必须小于对应类别库存最大值，请从<a href="yhd.category.products.get.html" target="_blank">yhd.category.products.get获取</a>中，查询对应类别的库存最大值*/
	private  $productStockList; 
	/**产品库存信息列表(产品库存信息之间逗号分隔,外部产品Id、仓库Id和库存数之间冒号分隔)与productStockList二选一;warehouseId从<a href="yhd.logistics.warehouse.info.get.html" target="_blank">yhd.logistics.warehouse.info.get获取</a>
库存值必须小于对应类别库存最大值，请从<a href="yhd.category.products.get.html" target="_blank">yhd.category.products.get获取</a>中，查询对应类别的库存最大值*/
	private  $outerStockList; 

	public function getProductId(){
		return $this->productId;
	}
	public function getOuterId(){
		return $this->outerId;
	}
	public function getUpdateType(){
		return $this->updateType;
	}
	public function getProductStockList(){
		return $this->productStockList;
	}
	public function getOuterStockList(){
		return $this->outerStockList;
	}

	public function setProductId($productId){
		$this->productId = $productId;
		$this->apiParas["productId"] = $productId;
	}
	public function setOuterId($outerId){
		$this->outerId = $outerId;
		$this->apiParas["outerId"] = $outerId;
	}
	public function setUpdateType($updateType){
		$this->updateType = $updateType;
		$this->apiParas["updateType"] = $updateType;
	}
	public function setProductStockList($productStockList){
		$this->productStockList = $productStockList;
		$this->apiParas["productStockList"] = $productStockList;
	}
	public function setOuterStockList($outerStockList){
		$this->outerStockList = $outerStockList;
		$this->apiParas["outerStockList"] = $outerStockList;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
