<?php
/**
*查询卖家已卖出的交易数据（根据创建时间）（兼容淘宝）
*/

class TradesSoldGetRequest {

    private $isCompatible = 1;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.trades.sold.get";
	}
	
	/**Trade中可以指定返回的fields。*/
	private  $fields; 
	/**查询两个月内交易创建时间开始。格式:yyyy-MM-dd HH:mm:ss */
	private  $startCreated; 
	/**查询交易创建时间结束。格式:yyyy-MM-dd HH:mm:ss */
	private  $endCreated; 
	/**订单状态（逗号分隔）: ORDER_WAIT_PAY：已下单（货款未全收）、 ORDER_PAYED：已下单（货款已收）、 ORDER_WAIT_SEND：可以发货（已送仓库）、 ORDER_ON_SENDING：已出库（货在途）、 ORDER_RECEIVED：货物用户已收到、 ORDER_FINISH：订单完成、 ORDER_CANCEL：订单取消*/
	private  $status; 
	/**买家昵称（暂不提供）*/
	private  $buyerNick; 
	/**交易类型列表，同时查询多种交易类型可用逗号分隔。（暂不提供）*/
	private  $type; 
	/**暂不提供*/
	private  $extType; 
	/**评价状态，默认查询所有评价状态的数据，除了默认值外每次只能查询一种状态。（暂不提供）*/
	private  $rateStatus; 
	/**卖家对交易的自定义分组标签，目前可选值为：time_card（点卡软件代充），fee_card（话费软件代充） （暂不提供）*/
	private  $tag; 
	/**页码。取值范围:大于零的整数; 默认值:1 */
	private  $pageNo; 
	/**每页条数*/
	private  $pageSize; 
	/**是否启用has_next的分页方式，如果指定true,则返回的结果中不包含总记录数，但是会新增一个是否存在下一页的的字段。*/
	private  $useHasNext; 
	/**暂不提供*/
	private  $isAcookie; 

	public function getFields(){
		return $this->fields;
	}
	public function getStartCreated(){
		return $this->startCreated;
	}
	public function getEndCreated(){
		return $this->endCreated;
	}
	public function getStatus(){
		return $this->status;
	}
	public function getBuyerNick(){
		return $this->buyerNick;
	}
	public function getType(){
		return $this->type;
	}
	public function getExtType(){
		return $this->extType;
	}
	public function getRateStatus(){
		return $this->rateStatus;
	}
	public function getTag(){
		return $this->tag;
	}
	public function getPageNo(){
		return $this->pageNo;
	}
	public function getPageSize(){
		return $this->pageSize;
	}
	public function getUseHasNext(){
		return $this->useHasNext;
	}
	public function getIsAcookie(){
		return $this->isAcookie;
	}

	public function setFields($fields){
		$this->fields = $fields;
		$this->apiParas["fields"] = $fields;
	}
	public function setStartCreated($startCreated){
		$this->startCreated = $startCreated;
		$this->apiParas["startCreated"] = $startCreated;
	}
	public function setEndCreated($endCreated){
		$this->endCreated = $endCreated;
		$this->apiParas["endCreated"] = $endCreated;
	}
	public function setStatus($status){
		$this->status = $status;
		$this->apiParas["status"] = $status;
	}
	public function setBuyerNick($buyerNick){
		$this->buyerNick = $buyerNick;
		$this->apiParas["buyerNick"] = $buyerNick;
	}
	public function setType($type){
		$this->type = $type;
		$this->apiParas["type"] = $type;
	}
	public function setExtType($extType){
		$this->extType = $extType;
		$this->apiParas["extType"] = $extType;
	}
	public function setRateStatus($rateStatus){
		$this->rateStatus = $rateStatus;
		$this->apiParas["rateStatus"] = $rateStatus;
	}
	public function setTag($tag){
		$this->tag = $tag;
		$this->apiParas["tag"] = $tag;
	}
	public function setPageNo($pageNo){
		$this->pageNo = $pageNo;
		$this->apiParas["pageNo"] = $pageNo;
	}
	public function setPageSize($pageSize){
		$this->pageSize = $pageSize;
		$this->apiParas["pageSize"] = $pageSize;
	}
	public function setUseHasNext($useHasNext){
		$this->useHasNext = $useHasNext;
		$this->apiParas["useHasNext"] = $useHasNext;
	}
	public function setIsAcookie($isAcookie){
		$this->isAcookie = $isAcookie;
		$this->apiParas["isAcookie"] = $isAcookie;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
