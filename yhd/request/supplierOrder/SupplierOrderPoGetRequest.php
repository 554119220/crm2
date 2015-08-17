<?php
/**
*获取供应商Po单数据
*/

class SupplierOrderPoGetRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.supplier.order.po.get";
	}
	
	/**PO单类型0:采购1：退货*/
	private  $poType; 
	/**PO单ID*/
	private  $id; 
	/**下单开始日期*/
	private  $startDate; 
	/**下单结束日期*/
	private  $endDate; 
	/**PO状态： 0待批准，1批准，2待收货，3部分收货，4拒绝收货，5完成，6 终止，7等待取消，8退货完成，9作废*/
	private  $poStatus; 
	/**查询页大小，不能大于100*/
	private  $pageRows; 
	/**查询页数*/
	private  $curPage; 

	public function getPoType(){
		return $this->poType;
	}
	public function getId(){
		return $this->id;
	}
	public function getStartDate(){
		return $this->startDate;
	}
	public function getEndDate(){
		return $this->endDate;
	}
	public function getPoStatus(){
		return $this->poStatus;
	}
	public function getPageRows(){
		return $this->pageRows;
	}
	public function getCurPage(){
		return $this->curPage;
	}

	public function setPoType($poType){
		$this->poType = $poType;
		$this->apiParas["poType"] = $poType;
	}
	public function setId($id){
		$this->id = $id;
		$this->apiParas["id"] = $id;
	}
	public function setStartDate($startDate){
		$this->startDate = $startDate;
		$this->apiParas["startDate"] = $startDate;
	}
	public function setEndDate($endDate){
		$this->endDate = $endDate;
		$this->apiParas["endDate"] = $endDate;
	}
	public function setPoStatus($poStatus){
		$this->poStatus = $poStatus;
		$this->apiParas["poStatus"] = $poStatus;
	}
	public function setPageRows($pageRows){
		$this->pageRows = $pageRows;
		$this->apiParas["pageRows"] = $pageRows;
	}
	public function setCurPage($curPage){
		$this->curPage = $curPage;
		$this->apiParas["curPage"] = $curPage;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
