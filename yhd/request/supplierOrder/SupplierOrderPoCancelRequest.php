<?php
/**
*取消PO单
*/

class SupplierOrderPoCancelRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.supplier.order.po.cancel";
	}
	
	/**被取消的采购单ID*/
	private  $poId; 
	/**备注信息*/
	private  $remark; 
	/**取消的时间*/
	private  $time; 

	public function getPoId(){
		return $this->poId;
	}
	public function getRemark(){
		return $this->remark;
	}
	public function getTime(){
		return $this->time;
	}

	public function setPoId($poId){
		$this->poId = $poId;
		$this->apiParas["poId"] = $poId;
	}
	public function setRemark($remark){
		$this->remark = $remark;
		$this->apiParas["remark"] = $remark;
	}
	public function setTime($time){
		$this->time = $time;
		$this->apiParas["time"] = $time;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
