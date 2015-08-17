<?php
/**
*添加系列子品图片
*/

class SerialProductImgUploadRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.serial.product.img.upload";
	}
	
	/**系列产品ID,与outerId二选一(productId优先)*/
	private  $productId; 
	/**外部产品ID,与productId二选一*/
	private  $outerId; 
	/**选项ID（颜色属性值ID），从获取产品系列属性接口获取*/
	private  $itemId; 
	/**主图名称*/
	private  $mainImageName; 

	public function getProductId(){
		return $this->productId;
	}
	public function getOuterId(){
		return $this->outerId;
	}
	public function getItemId(){
		return $this->itemId;
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
	public function setItemId($itemId){
		$this->itemId = $itemId;
		$this->apiParas["itemId"] = $itemId;
	}
	public function setMainImageName($mainImageName){
		$this->mainImageName = $mainImageName;
		$this->apiParas["mainImageName"] = $mainImageName;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
