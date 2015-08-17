<?php
/**
*获取单个退货详情
*/

class RefundDetailGetRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.refund.detail.get";
	}
	
	/**退货单号*/
	private  $refundCode; 

	public function getRefundCode(){
		return $this->refundCode;
	}

	public function setRefundCode($refundCode){
		$this->refundCode = $refundCode;
		$this->apiParas["refundCode"] = $refundCode;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
