<?php
/**
*更新单个产品价格信息
*/

class ProductPriceUpdateRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.product.price.update";
	}
	
	/**1号店产品ID,与outerId二选一*/
	private  $productId; 
	/**外部产品ID,与productId二选一*/
	private  $outerId; 
	/**1号店价格。price和marketPrice不能同时为空。*/
	private  $price; 
	/**市场价。price和marketPrice不能同时为空。*/
	private  $marketPrice; 

	public function getProductId(){
		return $this->productId;
	}
	public function getOuterId(){
		return $this->outerId;
	}
	public function getPrice(){
		return $this->price;
	}
	public function getMarketPrice(){
		return $this->marketPrice;
	}

	public function setProductId($productId){
		$this->productId = $productId;
		$this->apiParas["productId"] = $productId;
	}
	public function setOuterId($outerId){
		$this->outerId = $outerId;
		$this->apiParas["outerId"] = $outerId;
	}
	public function setPrice($price){
		$this->price = $price;
		$this->apiParas["price"] = $price;
	}
	public function setMarketPrice($marketPrice){
		$this->marketPrice = $marketPrice;
		$this->apiParas["marketPrice"] = $marketPrice;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
