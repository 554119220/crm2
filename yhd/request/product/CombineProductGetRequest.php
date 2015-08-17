<?php
/**
*查询单个套餐产品的子品信息
*/

class CombineProductGetRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.combine.product.get";
	}
	
	/**1号店产品id,与outerId二选一*/
	private  $productId; 
	/**外部产品ID与productId二选一*/
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
