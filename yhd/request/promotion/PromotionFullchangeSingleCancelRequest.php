<?php
/**
*取消单个满就换购详情促销
*/

class PromotionFullchangeSingleCancelRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.promotion.fullchange.single.cancel";
	}
	
	/**促销的id*/
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
