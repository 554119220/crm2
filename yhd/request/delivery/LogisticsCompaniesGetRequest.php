<?php
/**
*查询物流公司信息（兼容淘宝）
*/

class LogisticsCompaniesGetRequest {

    private $isCompatible = 1;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.logistics.companies.get";
	}
	
	/**需返回的字段列表。可选值:LogisticCompany 结构中的所有字段;多个字段间用","逗号隔开. 如:id,code,name,reg_mail_no 说明： id：物流公司ID code：物流公司code name：物流公司名称 reg_mail_no：物流公司对应的运单规则*/
	private  $fields; 
	/**是否查询推荐物流公司.可选值:true,false.如果不提供此参数,将会返回所有支持电话联系的物流公司. */
	private  $isRecommended; 
	/**推荐物流公司的下单方式.*/
	private  $orderMode; 

	public function getFields(){
		return $this->fields;
	}
	public function getIsRecommended(){
		return $this->isRecommended;
	}
	public function getOrderMode(){
		return $this->orderMode;
	}

	public function setFields($fields){
		$this->fields = $fields;
		$this->apiParas["fields"] = $fields;
	}
	public function setIsRecommended($isRecommended){
		$this->isRecommended = $isRecommended;
		$this->apiParas["isRecommended"] = $isRecommended;
	}
	public function setOrderMode($orderMode){
		$this->orderMode = $orderMode;
		$this->apiParas["orderMode"] = $orderMode;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
