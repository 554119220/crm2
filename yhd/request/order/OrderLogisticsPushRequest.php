<?php
/**
*订单物流推送接口
*/

class OrderLogisticsPushRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.order.logistics.push";
	}
	
	/**批量操作支持，最多50。以json格式输入。{"orderLogisticsInfoList": {"orderStepInfo": [{"orderCode": "130918P3SFBT","expressNbr": "9638527415","stepInfoList": {"stepInfo": [{"content": "22222","status": "333","operator": "jxw","operatorDate": "2013-12-26 9:11:34","remark": "333333333"}]}},{"orderCode": "130308W3HN31","expressNbr": "111111111111","stepInfoList": {"stepInfo": [{"content": "22222","status": "333","operator": "jxw","operatorDate": "2013-12-26 9:11:34","remark": "333333333"}]}}]}}*/
	private  $orderLogisticsInfoList; 

	public function getOrderLogisticsInfoList(){
		return $this->orderLogisticsInfoList;
	}

	public function setOrderLogisticsInfoList($orderLogisticsInfoList){
		$this->orderLogisticsInfoList = $orderLogisticsInfoList;
		$this->apiParas["orderLogisticsInfoList"] = $orderLogisticsInfoList;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
