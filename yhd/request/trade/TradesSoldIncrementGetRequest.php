<?php
/**
*查询卖家已卖出的增量交易数据（根据修改时间,兼容淘宝接口）
*/

class TradesSoldIncrementGetRequest {

    private $isCompatible = 1;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.trades.sold.increment.get";
	}
	
	/** 	需要返回的字段。*/
	private  $fields ; 
	/**查询修改开始时间(修改时间跨度不能大于一天)。格式:yyyy-MM-dd HH:mm:ss */
	private  $startModified; 
	/**查询修改结束时间，必须大于修改开始时间(修改时间跨度不能大于一天)，格式:yyyy-MM-dd HH:mm:ss。*/
	private  $endModified; 
	/**交易状态，默认查询所有交易状态的数据，除了默认值外每次只能查询一种状态订单状态（逗号分隔）: ORDER_WAIT_PAY：已下单（货款未全收）、 ORDER_PAYED：已下单（货款已收）、 ORDER_WAIT_SEND：可以发货（已送仓库）、 ORDER_ON_SENDING：已出库（货在途）、 ORDER_RECEIVED：货物用户已收到、 ORDER_FINISH：订单完成、 ORDER_CANCEL：订单取消*/
	private  $status; 
	/**交易类型列表（暂不支持）*/
	private  $type; 
	/** 	可选值有ershou(二手市场的订单（暂不支持）*/
	private  $extType ; 
	/**卖家对交易的自定义分组标签（暂不支持）*/
	private  $tag; 
	/**页码。取值范围:大于零的整数;默认值:1。*/
	private  $pageNo ; 
	/**每页条数。取值范围：1~100，默认值：40*/
	private  $pageSize; 
	/**是否启用has_next的分页方式（暂不支持）*/
	private  $useHasNext ; 
	/**默认值为false，表示按正常方式查询订单；(暂不支持)*/
	private  $isAcookie ; 

	public function getFields (){
		return $this->fields ;
	}
	public function getStartModified(){
		return $this->startModified;
	}
	public function getEndModified(){
		return $this->endModified;
	}
	public function getStatus(){
		return $this->status;
	}
	public function getType(){
		return $this->type;
	}
	public function getExtType (){
		return $this->extType ;
	}
	public function getTag(){
		return $this->tag;
	}
	public function getPageNo (){
		return $this->pageNo ;
	}
	public function getPageSize(){
		return $this->pageSize;
	}
	public function getUseHasNext (){
		return $this->useHasNext ;
	}
	public function getIsAcookie (){
		return $this->isAcookie ;
	}

	public function setFields ($fields ){
		$this->fields  = $fields ;
		$this->apiParas["fields "] = $fields ;
	}
	public function setStartModified($startModified){
		$this->startModified = $startModified;
		$this->apiParas["startModified"] = $startModified;
	}
	public function setEndModified($endModified){
		$this->endModified = $endModified;
		$this->apiParas["endModified"] = $endModified;
	}
	public function setStatus($status){
		$this->status = $status;
		$this->apiParas["status"] = $status;
	}
	public function setType($type){
		$this->type = $type;
		$this->apiParas["type"] = $type;
	}
	public function setExtType ($extType ){
		$this->extType  = $extType ;
		$this->apiParas["extType "] = $extType ;
	}
	public function setTag($tag){
		$this->tag = $tag;
		$this->apiParas["tag"] = $tag;
	}
	public function setPageNo ($pageNo ){
		$this->pageNo  = $pageNo ;
		$this->apiParas["pageNo "] = $pageNo ;
	}
	public function setPageSize($pageSize){
		$this->pageSize = $pageSize;
		$this->apiParas["pageSize"] = $pageSize;
	}
	public function setUseHasNext ($useHasNext ){
		$this->useHasNext  = $useHasNext ;
		$this->apiParas["useHasNext "] = $useHasNext ;
	}
	public function setIsAcookie ($isAcookie ){
		$this->isAcookie  = $isAcookie ;
		$this->apiParas["isAcookie "] = $isAcookie ;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
