<?php
/**
*批量获取订单详情
*/

class OrdersDetailGetRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.orders.detail.get";
	}
	
	/**订单编码列表（逗号分隔）,最大长度为50*/
	private  $orderCodeList; 

	public function getOrderCodeList(){
		return $this->orderCodeList;
	}

	public function setOrderCodeList($orderCodeList){
		$this->orderCodeList = $orderCodeList;
		$this->apiParas["orderCodeList"] = $orderCodeList;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
