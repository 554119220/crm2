<?php
/**
*获取单笔交易的部分信息(性能高)（兼容淘宝）
*/

class TradeGetRequest {

    private $isCompatible = 1;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.trade.get";
	}
	
	/**Trade中可以指定返回的fields。*/
	private  $fields; 
	/**交易编号 */
	private  $tid; 

	public function getFields(){
		return $this->fields;
	}
	public function getTid(){
		return $this->tid;
	}

	public function setFields($fields){
		$this->fields = $fields;
		$this->apiParas["fields"] = $fields;
	}
	public function setTid($tid){
		$this->tid = $tid;
		$this->apiParas["tid"] = $tid;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
