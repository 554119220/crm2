<?php
/**
*取消价格促销
*/

class PromotionPriceCancelRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.promotion.price.cancel";
	}
	
	/**取消的价格促销id。系列产品的只能取消全部，不能取消单个子品*/
	private  $cancelId; 

	public function getCancelId(){
		return $this->cancelId;
	}

	public function setCancelId($cancelId){
		$this->cancelId = $cancelId;
		$this->apiParas["cancelId"] = $cancelId;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
