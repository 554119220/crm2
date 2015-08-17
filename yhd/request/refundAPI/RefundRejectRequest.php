<?php
/**
*拒绝退货
*/

class RefundRejectRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.refund.reject";
	}
	
	/**退换货编码*/
	private  $refundCode; 
	/**拒绝原因*/
	private  $remark; 

	public function getRefundCode(){
		return $this->refundCode;
	}
	public function getRemark(){
		return $this->remark;
	}

	public function setRefundCode($refundCode){
		$this->refundCode = $refundCode;
		$this->apiParas["refundCode"] = $refundCode;
	}
	public function setRemark($remark){
		$this->remark = $remark;
		$this->apiParas["remark"] = $remark;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
