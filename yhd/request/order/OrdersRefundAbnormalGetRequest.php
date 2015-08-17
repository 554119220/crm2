<?php
/**
*异常订单退款查询接口
*/

class OrdersRefundAbnormalGetRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.orders.refund.abnormal.get";
	}
	
	/**退款单号*/
	private  $refundOrderCode; 
	/**订单号*/
	private  $orderCode; 
	/**退货单号*/
	private  $refundCode; 
	/**退款单状态*/
	private  $refundStatus; 
	/**收货人手机、电话*/
	private  $receiverPhone; 
	/**最小50，最大100，默认50*/
	private  $pageRows; 
	/**页码*/
	private  $curPage; 
	/**开始时间*/
	private  $startTime; 
	/**结束时间*/
	private  $endTime; 
	/**1表示申请，2表示批准（当dateType=1,startTime表示申请开始时间,endTime表示申请结束时间；dateType=2，startTime表示批准开始时间,endTime表示批准结束时间）*/
	private  $dateType; 

	public function getRefundOrderCode(){
		return $this->refundOrderCode;
	}
	public function getOrderCode(){
		return $this->orderCode;
	}
	public function getRefundCode(){
		return $this->refundCode;
	}
	public function getRefundStatus(){
		return $this->refundStatus;
	}
	public function getReceiverPhone(){
		return $this->receiverPhone;
	}
	public function getPageRows(){
		return $this->pageRows;
	}
	public function getCurPage(){
		return $this->curPage;
	}
	public function getStartTime(){
		return $this->startTime;
	}
	public function getEndTime(){
		return $this->endTime;
	}
	public function getDateType(){
		return $this->dateType;
	}

	public function setRefundOrderCode($refundOrderCode){
		$this->refundOrderCode = $refundOrderCode;
		$this->apiParas["refundOrderCode"] = $refundOrderCode;
	}
	public function setOrderCode($orderCode){
		$this->orderCode = $orderCode;
		$this->apiParas["orderCode"] = $orderCode;
	}
	public function setRefundCode($refundCode){
		$this->refundCode = $refundCode;
		$this->apiParas["refundCode"] = $refundCode;
	}
	public function setRefundStatus($refundStatus){
		$this->refundStatus = $refundStatus;
		$this->apiParas["refundStatus"] = $refundStatus;
	}
	public function setReceiverPhone($receiverPhone){
		$this->receiverPhone = $receiverPhone;
		$this->apiParas["receiverPhone"] = $receiverPhone;
	}
	public function setPageRows($pageRows){
		$this->pageRows = $pageRows;
		$this->apiParas["pageRows"] = $pageRows;
	}
	public function setCurPage($curPage){
		$this->curPage = $curPage;
		$this->apiParas["curPage"] = $curPage;
	}
	public function setStartTime($startTime){
		$this->startTime = $startTime;
		$this->apiParas["startTime"] = $startTime;
	}
	public function setEndTime($endTime){
		$this->endTime = $endTime;
		$this->apiParas["endTime"] = $endTime;
	}
	public function setDateType($dateType){
		$this->dateType = $dateType;
		$this->apiParas["dateType"] = $dateType;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
