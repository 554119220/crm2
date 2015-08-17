<?php
/**
*物流流转信息查询（兼容淘宝）
*/

class LogisticsTraceSearchRequest {

    private $isCompatible = 1;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.logistics.trace.search";
	}
	
	/**1号店交易号，请勿传非1号店交易号*/
	private  $tid; 
	/**卖家昵称(暂不提供)*/
	private  $sellerNick; 
	/**表明是否是拆单，默认值0，1表示拆单(暂不提供)*/
	private  $isSplit; 
	/**拆单子订单列表，对应的数据是：子订单号的列表。可以不传，但是如果传了则必须符合传递的规则。子订单必须是操作的物流订单的子订单的真子集(暂不提供)*/
	private  $subTid; 

	public function getTid(){
		return $this->tid;
	}
	public function getSellerNick(){
		return $this->sellerNick;
	}
	public function getIsSplit(){
		return $this->isSplit;
	}
	public function getSubTid(){
		return $this->subTid;
	}

	public function setTid($tid){
		$this->tid = $tid;
		$this->apiParas["tid"] = $tid;
	}
	public function setSellerNick($sellerNick){
		$this->sellerNick = $sellerNick;
		$this->apiParas["sellerNick"] = $sellerNick;
	}
	public function setIsSplit($isSplit){
		$this->isSplit = $isSplit;
		$this->apiParas["isSplit"] = $isSplit;
	}
	public function setSubTid($subTid){
		$this->subTid = $subTid;
		$this->apiParas["subTid"] = $subTid;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
