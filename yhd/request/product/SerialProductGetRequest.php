<?php
/**
*获取单个系列产品的子品信息
*/

class SerialProductGetRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.serial.product.get";
	}
	
	/**1号店产品ID(系列产品id,与productCode,outerId三选一,优先于outerId,productCode)*/
	private  $productId; 
	/**外部产品编码(系列产品outerId,与productId, productCode三选一, 优于productCode)*/
	private  $outerId; 
	/**品牌Id*/
	private  $brandId; 
	/**系列产品编码(与productId, outerId三选一)*/
	private  $productCode; 

	public function getProductId(){
		return $this->productId;
	}
	public function getOuterId(){
		return $this->outerId;
	}
	public function getBrandId(){
		return $this->brandId;
	}
	public function getProductCode(){
		return $this->productCode;
	}

	public function setProductId($productId){
		$this->productId = $productId;
		$this->apiParas["productId"] = $productId;
	}
	public function setOuterId($outerId){
		$this->outerId = $outerId;
		$this->apiParas["outerId"] = $outerId;
	}
	public function setBrandId($brandId){
		$this->brandId = $brandId;
		$this->apiParas["brandId"] = $brandId;
	}
	public function setProductCode($productCode){
		$this->productCode = $productCode;
		$this->apiParas["productCode"] = $productCode;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
