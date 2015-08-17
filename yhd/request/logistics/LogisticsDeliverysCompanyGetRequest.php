<?php
/**
*获取1mall合作物流公司信息
*/

class LogisticsDeliverysCompanyGetRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.logistics.deliverys.company.get";
	}
	



	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
