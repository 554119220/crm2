<?php
/**
*删除单品图片
*/

class GeneralProdimgDelRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.general.prodimg.del";
	}
	
	/**1号店产品ID,与outerId二选一(productId优先)*/
	private  $productId; 
	/**外部产品ID,与productId二选一*/
	private  $outerId; 
	/**要删除的图片id列表（不同图片id用逗号分隔），pids为空，表示删除此产品下所有图片*/
	private  $pids; 

	public function getProductId(){
		return $this->productId;
	}
	public function getOuterId(){
		return $this->outerId;
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
	public function setPids($pids){
		$this->pids = $pids;
		$this->apiParas["pids"] = $pids;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
