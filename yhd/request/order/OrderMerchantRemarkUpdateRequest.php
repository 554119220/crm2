<?php
/**
*更新单个订单的卖家备注信息
*/

class OrderMerchantRemarkUpdateRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.order.merchant.remark.update";
	}
	
	/**订单编码*/
	private  $orderCode; 
	/**订单卖家备注,最大长度为150个汉字*/
	private  $remark; 

	public function getOrderCode(){
		return $this->orderCode;
	}
	public function getRemark(){
		return $this->remark;
	}

	public function setOrderCode($orderCode){
		$this->orderCode = $orderCode;
		$this->apiParas["orderCode"] = $orderCode;
	}
	public function setRemark($remark){
		$this->remark = $remark;
		$this->apiParas["remark"] = $remark;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
