<?php

/**
 * 面单申请
 * @author auto create
 */
class WaybillApplyNewRequest
{
	
	/** 
	 * TOP  appkey
	 **/
	public $appKey;
	
	/** 
	 * 物流服务商编码
	 **/
	public $cpCode;
	
	/** 
	 * --
	 **/
	public $cpId;
	
	/** 
	 * 使用者ID
	 **/
	public $realUserId;
	
	/** 
	 * 商家ID
	 **/
	public $sellerId;
	
	/** 
	 * 发货地址
	 **/
	public $shippingAddress;
	
	/** 
	 * 面单详细信息
	 **/
	public $tradeOrderInfoCols;	
}
?>