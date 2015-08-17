<?php
/**
*获取仓库信息
*/

class LogisticsWarehouseInfoGetRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.logistics.warehouse.info.get";
	}
	
	/**仓库ID*/
	private  $warehouseId; 

	public function getWarehouseId(){
		return $this->warehouseId;
	}

	public function setWarehouseId($warehouseId){
		$this->warehouseId = $warehouseId;
		$this->apiParas["warehouseId"] = $warehouseId;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
