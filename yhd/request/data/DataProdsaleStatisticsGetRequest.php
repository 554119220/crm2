<?php
/**
*获取产品的销售统计信息
*/

class DataProdsaleStatisticsGetRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.data.prodsale.statistics.get";
	}
	
	/**产品ID*/
	private  $productId; 
	/**查询开始时间*/
	private  $startTime; 
	/**查询结束时间*/
	private  $endTime; 

	public function getProductId(){
		return $this->productId;
	}
	public function getStartTime(){
		return $this->startTime;
	}
	public function getEndTime(){
		return $this->endTime;
	}

	public function setProductId($productId){
		$this->productId = $productId;
		$this->apiParas["productId"] = $productId;
	}
	public function setStartTime($startTime){
		$this->startTime = $startTime;
		$this->apiParas["startTime"] = $startTime;
	}
	public function setEndTime($endTime){
		$this->endTime = $endTime;
		$this->apiParas["endTime"] = $endTime;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
