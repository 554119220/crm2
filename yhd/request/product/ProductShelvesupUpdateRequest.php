<?php
/**
*上架单个产品
*/

class ProductShelvesupUpdateRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.product.shelvesup.update";
	}
	
	/**1号店产品ID,与outerId二选一*/
	private  $productId; 
	/**外部产品ID,与productId二选一*/
	private  $outerId; 

	public function getProductId(){
		return $this->productId;
	}
	public function getOuterId(){
		return $this->outerId;
	}

	public function setProductId($productId){
		$this->productId = $productId;
		$this->apiParas["productId"] = $productId;
	}
	public function setOuterId($outerId){
		$this->outerId = $outerId;
		$this->apiParas["outerId"] = $outerId;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
