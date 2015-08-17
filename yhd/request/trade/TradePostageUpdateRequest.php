<?php
/**
*修改订单邮费价格（兼容淘宝） 
*/

class TradePostageUpdateRequest {

    private $isCompatible = 1;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.trade.postage.update";
	}
	
	/**主订单编号*/
	private  $tid; 
	/**邮费价格(邮费单位是元）*/
	private  $postFee; 

	public function getTid(){
		return $this->tid;
	}
	public function getPostFee(){
		return $this->postFee;
	}

	public function setTid($tid){
		$this->tid = $tid;
		$this->apiParas["tid"] = $tid;
	}
	public function setPostFee($postFee){
		$this->postFee = $postFee;
		$this->apiParas["postFee"] = $postFee;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
