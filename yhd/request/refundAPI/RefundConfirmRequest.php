<?php
/**
*确认退货
*/

class RefundConfirmRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.refund.confirm";
	}
	
	/**退货单编码*/
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
