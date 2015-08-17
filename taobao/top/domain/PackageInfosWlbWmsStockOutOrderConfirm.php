<?php

/**
 * 包裹信息列表
 * @author auto create
 */
class PackageInfosWlbWmsStockOutOrderConfirm
{
	
	/** 
	 * 包裹编号
	 **/
	public $packageCode;
	
	/** 
	 * 包裹高度，单位：毫米
	 **/
	public $packageHeight;
	
	/** 
	 * 包裹里面的商品信息
	 **/
	public $packageItemItems;
	
	/** 
	 * 包裹长度，单位：毫米
	 **/
	public $packageLength;
	
	/** 
	 * 包裹重量，单位：克
	 **/
	public $packageWeight;
	
	/** 
	 * 包裹宽度，单位：毫米
	 **/
	public $packageWidth;
	
	/** 
	 * 快递公司编码
	 **/
	public $tmsCode;
	
	/** 
	 * 运单号
	 **/
	public $tmsOrderCode;	
}
?>