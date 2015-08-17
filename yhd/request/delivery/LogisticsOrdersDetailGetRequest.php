<?php
/**
*批量查询物流订单,返回详细信息(兼容淘宝)
*/

class LogisticsOrdersDetailGetRequest {

    private $isCompatible = 1;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.logistics.orders.detail.get";
	}
	
	/**需返回的字段列表.可选值:Shipping 物流数据结构中所有字段.fileds中可以指定返回以上任意一个或者多个字段,以","分隔. */
	private  $fields; 
	/**交易ID.如果加入tid参数的话,不用传其他的参数,但是仅会返回一条物流订单信息. */
	private  $tid; 
	/**买家昵称（暂不提供）*/
	private  $buyerNick; 
	/**物流状态.可查看数据结构 Shipping 中的status字段. */
	private  $status; 
	/**卖家是否发货.可选值:yes(是),no(否).如:yes. （暂不提供）*/
	private  $sellerConfirm; 
	/**收货人姓名 */
	private  $receiverName; 
	/**创建时间开始.格式:yyyy-MM-dd HH:mm:ss */
	private  $startCreated; 
	/**创建时间结束.格式:yyyy-MM-dd HH:mm:ss */
	private  $endCreated; 
	/**谁承担运费.可选值:buyer(买家),seller(卖家).如:buyer（暂不提供） */
	private  $freightPayer; 
	/**物流方式.可选值:post(平邮),express(快递),ems(EMS).如:post （暂不提供）*/
	private  $type; 
	/**页码.该字段没传 或 值<1 ,则默认page_no为1 */
	private  $pageNo; 
	/**每页条数.该字段没传 或 值<1 ，则默认page_size为40 支持最大值为：100 */
	private  $pageSize; 

	public function getFields(){
		return $this->fields;
	}
	public function getTid(){
		return $this->tid;
	}
	public function getBuyerNick(){
		return $this->buyerNick;
	}
	public function getStatus(){
		return $this->status;
	}
	public function getSellerConfirm(){
		return $this->sellerConfirm;
	}
	public function getReceiverName(){
		return $this->receiverName;
	}
	public function getStartCreated(){
		return $this->startCreated;
	}
	public function getEndCreated(){
		return $this->endCreated;
	}
	public function getFreightPayer(){
		return $this->freightPayer;
	}
	public function getType(){
		return $this->type;
	}
	public function getPageNo(){
		return $this->pageNo;
	}
	public function getPageSize(){
		return $this->pageSize;
	}

	public function setFields($fields){
		$this->fields = $fields;
		$this->apiParas["fields"] = $fields;
	}
	public function setTid($tid){
		$this->tid = $tid;
		$this->apiParas["tid"] = $tid;
	}
	public function setBuyerNick($buyerNick){
		$this->buyerNick = $buyerNick;
		$this->apiParas["buyerNick"] = $buyerNick;
	}
	public function setStatus($status){
		$this->status = $status;
		$this->apiParas["status"] = $status;
	}
	public function setSellerConfirm($sellerConfirm){
		$this->sellerConfirm = $sellerConfirm;
		$this->apiParas["sellerConfirm"] = $sellerConfirm;
	}
	public function setReceiverName($receiverName){
		$this->receiverName = $receiverName;
		$this->apiParas["receiverName"] = $receiverName;
	}
	public function setStartCreated($startCreated){
		$this->startCreated = $startCreated;
		$this->apiParas["startCreated"] = $startCreated;
	}
	public function setEndCreated($endCreated){
		$this->endCreated = $endCreated;
		$this->apiParas["endCreated"] = $endCreated;
	}
	public function setFreightPayer($freightPayer){
		$this->freightPayer = $freightPayer;
		$this->apiParas["freightPayer"] = $freightPayer;
	}
	public function setType($type){
		$this->type = $type;
		$this->apiParas["type"] = $type;
	}
	public function setPageNo($pageNo){
		$this->pageNo = $pageNo;
		$this->apiParas["pageNo"] = $pageNo;
	}
	public function setPageSize($pageSize){
		$this->pageSize = $pageSize;
		$this->apiParas["pageSize"] = $pageSize;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
