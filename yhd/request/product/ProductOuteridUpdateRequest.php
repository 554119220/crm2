<?php
/**
*更新单个产品外部编码
*/

class ProductOuteridUpdateRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.product.outerid.update";
	}
	
	/**1号店产品ID*/
	private  $productId; 
	/**外部产品编码id,最大长度为100*/
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
