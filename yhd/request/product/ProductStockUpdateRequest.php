<?php
/**
*更新单个产品库存信息
*/

class ProductStockUpdateRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.product.stock.update";
	}
	
	/**1号店产品ID,与outerId二选一.存在非法字符默认为空(productId优先)*/
	private  $productId; 
	/**外部产品ID,与productId二选一*/
	private  $outerId; 
	/**虚拟库存。库存值小于对应类别库存最大值。
请从<a href="yhd.category.products.get.html" target="_blank">yhd.category.products.get获取</a>中，查询对应类别的库存最大值*/
	private  $virtualStockNum; 
	/**仓库ID（不传则是默认仓库）*/
	private  $warehouseId; 
	/**更新类型，默认为1（1：全量更新，2：增量更新）*/
	private  $updateType; 

	public function getProductId(){
		return $this->productId;
	}
	public function getOuterId(){
		return $this->outerId;
	}
	public function getVirtualStockNum(){
		return $this->virtualStockNum;
	}
	public function getWarehouseId(){
		return $this->warehouseId;
	}
	public function getUpdateType(){
		return $this->updateType;
	}

	public function setProductId($productId){
		$this->productId = $productId;
		$this->apiParas["productId"] = $productId;
	}
	public function setOuterId($outerId){
		$this->outerId = $outerId;
		$this->apiParas["outerId"] = $outerId;
	}
	public function setVirtualStockNum($virtualStockNum){
		$this->virtualStockNum = $virtualStockNum;
		$this->apiParas["virtualStockNum"] = $virtualStockNum;
	}
	public function setWarehouseId($warehouseId){
		$this->warehouseId = $warehouseId;
		$this->apiParas["warehouseId"] = $warehouseId;
	}
	public function setUpdateType($updateType){
		$this->updateType = $updateType;
		$this->apiParas["updateType"] = $updateType;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
