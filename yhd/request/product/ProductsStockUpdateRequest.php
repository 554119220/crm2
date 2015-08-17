<?php
/**
*批量更新普通产品库存信息
*/

class ProductsStockUpdateRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.products.stock.update";
	}
	
	/**更新类型，默认为1（1：全量更新，2：增量更新）*/
	private  $updateType; 
	/**一号商城产品库存列表（逗号分隔，产品ID、仓库ID、库存数量之间用冒号分隔），与outerStockList二选一。warehouseId从<a href="yhd.logistics.warehouse.info.get.html" target="_blank">yhd.logistics.warehouse.info.get获取</a>
库存值必须小于对应类别库存最大值，请从<a href="yhd.category.products.get.html" target="_blank">yhd.category.products.get获取</a>中，查询对应类别的库存最大值*/
	private  $productStockList; 
	/**外部产品库存列表（逗号分隔，产品ID、仓库ID、库存数量之间用冒号分隔），与productStockList二选一。warehouseId从<a href="yhd.logistics.warehouse.info.get.html" target="_blank">yhd.logistics.warehouse.info.get获取</a>
库存值必须小于对应类别库存最大值，请从<a href="yhd.category.products.get.html" target="_blank">yhd.category.products.get获取</a>中，查询对应类别的库存最大值
*/
	private  $outerStockList; 

	public function getUpdateType(){
		return $this->updateType;
	}
	public function getProductStockList(){
		return $this->productStockList;
	}
	public function getOuterStockList(){
		return $this->outerStockList;
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
