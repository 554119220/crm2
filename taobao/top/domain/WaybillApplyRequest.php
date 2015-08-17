<?php

/**
 * 面单申请
 * @author auto create
 */
class WaybillApplyRequest
{
	
	/** 
	 * TOP平台请求的ISV APPKEY
	 **/
	public $appKey;
	
	/** 
	 * 物流服务商ID
	 **/
	public $cpCode;
	
	/** 
	 * 商家ID
	 **/
	public $sellerId;
	
	/** 
	 * 发货地址
	 **/
	public $shippingAddress;	
}
?>