<?php
/**
*审核通过合约机订单接口
*/

class OrderContractphoneConfirmRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.order.contractphone.confirm";
	}
	
	/**订单号，多个订单号用“,”隔开*/
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
