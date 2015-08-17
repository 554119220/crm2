<?php

/**
 * 取消接口入参
 * @author auto create
 */
class WaybillApplyCancelRequest
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
	 * ERP订单号或包裹号
	 **/
	public $packageId;
	
	/** 
	 * 面单使用者编号
	 **/
	public $realUserId;
	
	/** 
	 * 申请者ID
	 **/
	public $sellerId;
	
	/** 
	 * 交易订单列表
	 **/
	public $tradeOrderList;
	
	/** 
	 * 电子面单号码
	 **/
	public $waybillCode;	
}
?>