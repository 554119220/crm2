<?php
/**
*添加商品图片
*/

class ProductImgUploadRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.product.img.upload";
	}
	
	/**1号店产品ID,与outerId二选一(productId优先)*/
	private  $productId; 
	/**外部产品ID,与productId二选一*/
	private  $outerId; 
	/**主图名称*/
	private  $mainImageName; 

	public function getProductId(){
		return $this->productId;
	}
	public function getOuterId(){
		return $this->outerId;
	}
	public function getMainImageName(){
		return $this->mainImageName;
	}

	public function setProductId($productId){
		$this->productId = $productId;
		$this->apiParas["productId"] = $productId;
	}
	public function setOuterId($outerId){
		$this->outerId = $outerId;
		$this->apiParas["outerId"] = $outerId;
	}
	public function setMainImageName($mainImageName){
		$this->mainImageName = $mainImageName;
		$this->apiParas["mainImageName"] = $mainImageName;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
