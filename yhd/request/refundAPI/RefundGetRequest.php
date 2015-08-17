<?php
/**
*获取退货列表
*/

class RefundGetRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.refund.get";
	}
	
	/**订单code*/
	private  $orderCode; 
	/**产品ID*/
	private  $productId; 
	/**退货状态 '全部':'1000','待审核':'10','待顾客寄回':'110','待确认退款':'120','退款完成':'150','已关闭':'100','客服仲裁':'130*/
	private  $refundStatus; 
	/**查询开始时间*/
	private  $startTime; 
	/**查询结束时间*/
	private  $endTime; 
	/**当前页数（默认为1）*/
	private  $curPage; 
	/**每页显示记录数（默认50.最大100）*/
	private  $pageRows; 
	/** 	时间类型 :1、申请时间 2、更新时间*/
	private  $dateType; 

	public function getOrderCode(){
		return $this->orderCode;
	}
	public function getProductId(){
		return $this->productId;
	}
	public function getRefundStatus(){
		return $this->refundStatus;
	}
	public function getStartTime(){
		return $this->startTime;
	}
	public function getEndTime(){
		return $this->endTime;
	}
	public function getCurPage(){
		return $this->curPage;
	}
	public function getPageRows(){
		return $this->pageRows;
	}
	public function getDateType(){
		return $this->dateType;
	}

	public function setOrderCode($orderCode){
		$this->orderCode = $orderCode;
		$this->apiParas["orderCode"] = $orderCode;
	}
	public function setProductId($productId){
		$this->productId = $productId;
		$this->apiParas["productId"] = $productId;
	}
	public function setRefundStatus($refundStatus){
		$this->refundStatus = $refundStatus;
		$this->apiParas["refundStatus"] = $refundStatus;
	}
	public function setStartTime($startTime){
		$this->startTime = $startTime;
		$this->apiParas["startTime"] = $startTime;
	}
	public function setEndTime($endTime){
		$this->endTime = $endTime;
		$this->apiParas["endTime"] = $endTime;
	}
	public function setCurPage($curPage){
		$this->curPage = $curPage;
		$this->apiParas["curPage"] = $curPage;
	}
	public function setPageRows($pageRows){
		$this->pageRows = $pageRows;
		$this->apiParas["pageRows"] = $pageRows;
	}
	public function setDateType($dateType){
		$this->dateType = $dateType;
		$this->apiParas["dateType"] = $dateType;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
