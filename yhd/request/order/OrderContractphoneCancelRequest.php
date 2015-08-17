<?php
/**
*取消合约机订单
*/

class OrderContractphoneCancelRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.order.contractphone.cancel";
	}
	
	/**订单号，多个订单好用“,”隔开*/
	private  $orderCodes; 

	public function getOrderCodes(){
		return $this->orderCodes;
	}

	public function setOrderCodes($orderCodes){
		$this->orderCodes = $orderCodes;
		$this->apiParas["orderCodes"] = $orderCodes;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
