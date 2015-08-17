<?php

/**
 * 面单查询请求
 * @author auto create
 */
class WaybillDetailQueryRequest
{
	
	/** 
	 * TOP平台请求的ISV APPKEY
	 **/
	public $appKey;
	
	/** 
	 * CP快递公司编码
	 **/
	public $cpCode;
	
	/** 
	 * 0:根据cp_code和waybil_code查询;1:根据订单号查询
	 **/
	public $queryBy;
	
	/** 
	 * 申请者ID
	 **/
	public $sellerId;
	
	/** 
	 * 交易订单号
	 **/
	public $tradeOrderList;
	
	/** 
	 * 电子面单单号
	 **/
	public $waybillCodes;	
}
?>