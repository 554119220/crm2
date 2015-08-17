<?php
/**
*删除系列子品图片
*/

class SerialProdimgDelRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.serial.prodimg.del";
	}
	
	/**1号店系列产品ID,与outerId二选一(productId优先)*/
	private  $productId; 
	/**系列产品外部ID,与productId二选一*/
	private  $outerId; 
	/**选项ID（颜色属性值ID），从获取产品系列属性接口获取*/
	private  $itemId; 
	/**要删除的图片id列表（不同图片id用逗号分隔），pids为空，表示删除此产品下所有图片*/
	private  $pids; 

	public function getProductId(){
		return $this->productId;
	}
	public function getOuterId(){
		return $this->outerId;
	}
	public function getItemId(){
		return $this->itemId;
	}
	public function getPids(){
		return $this->pids;
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
	public function setPids($pids){
		$this->pids = $pids;
		$this->apiParas["pids"] = $pids;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
