<?php

/**
 * CP下的网点信息
 * @author auto create
 */
class WaybillBranchAccount
{
	
	/** 
	 * 已用单数
	 **/
	public $allocatedQuantity;
	
	/** 
	 * 网点ID
	 **/
	public $branchCode;
	
	/** 
	 * 网点名称
	 **/
	public $branchName;
	
	/** 
	 * 取消的面对总数
	 **/
	public $cancelQuantity;
	
	/** 
	 * 物流服务商ID
	 **/
	public $cpCode;
	
	/** 
	 * 已经打印的面单总数
	 **/
	public $printQuantity;
	
	/** 
	 * 可用单数
	 **/
	public $quantity;
	
	/** 
	 * 商家ID
	 **/
	public $sellerId;
	
	/** 
	 * 当前网点下的发货地址
	 **/
	public $shippAddressCols;	
}
?>