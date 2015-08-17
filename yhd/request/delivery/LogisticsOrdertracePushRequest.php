<?php
/**
*物流订单流转信息推送接口（兼容淘宝）
*/

class LogisticsOrdertracePushRequest {

    private $isCompatible = 1;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.logistics.ordertrace.push";
	}
	
	/**快递单号。各个快递公司的运单号格式不同。*/
	private  $mailNo; 
	/**流转节点发生时间*/
	private  $occureTime; 
	/**流转节点的详细地址及操作描述*/
	private  $operateDetail; 
	/**物流公司名称*/
	private  $companyName; 
	/**快递业务员名称*/
	private  $operatorName; 
	/**快递业务员联系方式，手机号码或电话。*/
	private  $operatorContact; 
	/**流转节点的当前城市*/
	private  $currentCity; 
	/**流转节点的下一个城市*/
	private  $nextCity; 
	/**网点名称*/
	private  $facilityName; 
	/**描述当前节点的操作，操作是“揽收”、“派送”或“签收”。*/
	private  $nodeDescription; 

	public function getMailNo(){
		return $this->mailNo;
	}
	public function getOccureTime(){
		return $this->occureTime;
	}
	public function getOperateDetail(){
		return $this->operateDetail;
	}
	public function getCompanyName(){
		return $this->companyName;
	}
	public function getOperatorName(){
		return $this->operatorName;
	}
	public function getOperatorContact(){
		return $this->operatorContact;
	}
	public function getCurrentCity(){
		return $this->currentCity;
	}
	public function getNextCity(){
		return $this->nextCity;
	}
	public function getFacilityName(){
		return $this->facilityName;
	}
	public function getNodeDescription(){
		return $this->nodeDescription;
	}

	public function setMailNo($mailNo){
		$this->mailNo = $mailNo;
		$this->apiParas["mailNo"] = $mailNo;
	}
	public function setOccureTime($occureTime){
		$this->occureTime = $occureTime;
		$this->apiParas["occureTime"] = $occureTime;
	}
	public function setOperateDetail($operateDetail){
		$this->operateDetail = $operateDetail;
		$this->apiParas["operateDetail"] = $operateDetail;
	}
	public function setCompanyName($companyName){
		$this->companyName = $companyName;
		$this->apiParas["companyName"] = $companyName;
	}
	public function setOperatorName($operatorName){
		$this->operatorName = $operatorName;
		$this->apiParas["operatorName"] = $operatorName;
	}
	public function setOperatorContact($operatorContact){
		$this->operatorContact = $operatorContact;
		$this->apiParas["operatorContact"] = $operatorContact;
	}
	public function setCurrentCity($currentCity){
		$this->currentCity = $currentCity;
		$this->apiParas["currentCity"] = $currentCity;
	}
	public function setNextCity($nextCity){
		$this->nextCity = $nextCity;
		$this->apiParas["nextCity"] = $nextCity;
	}
	public function setFacilityName($facilityName){
		$this->facilityName = $facilityName;
		$this->apiParas["facilityName"] = $facilityName;
	}
	public function setNodeDescription($nodeDescription){
		$this->nodeDescription = $nodeDescription;
		$this->apiParas["nodeDescription"] = $nodeDescription;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
