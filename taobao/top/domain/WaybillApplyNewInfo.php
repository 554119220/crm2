<?php

/**
 * 申请面单返回数据
 * @author auto create
 */
class WaybillApplyNewInfo
{
	
	/** 
	 * 包裹对应的派件（收件）物流服务商网点（分支机构）代码
	 **/
	public $consigneeBranchCode;
	
	/** 
	 * 包裹对应的派件（收件）物流服务商网点（分支机构）名称
	 **/
	public $consigneeBranchName;
	
	/** 
	 * 预留字段
	 **/
	public $feature;
	
	/** 
	 * 集包地代码
	 **/
	public $packageCenterCode;
	
	/** 
	 * 集包地名称
	 **/
	public $packageCenterName;
	
	/** 
	 * --
	 **/
	public $result;
	
	/** 
	 * 面单号对应的物流服务商网点（分支机构）代码
	 **/
	public $shippingBranchCode;
	
	/** 
	 * 面单号对于的物流服务商网点（分支机构）名称
	 **/
	public $shippingBranchName;
	
	/** 
	 * 根据收货地址返回大头笔信息
	 **/
	public $shortAddress;
	
	/** 
	 * 面单对应的订单列
	 **/
	public $tradeOrderInfo;
	
	/** 
	 * 返回的面单号
	 **/
	public $waybillCode;	
}
?>