<?php

/**
 * 物流订单信息
 * @author auto create
 */
class LocOrder
{
	
	/** 
	 * 物流承运商
	 **/
	public $carrier;
	
	/** 
	 * 物流订单号
	 **/
	public $orderCode;
	
	/** 
	 * 运费
	 **/
	public $shippingFee;
	
	/** 
	 * 物流订单状态编码
	 **/
	public $statusCode;
	
	/** 
	 * 物流运单号
	 **/
	public $trackingNo;
	
	/** 
	 * 交易订单号
	 **/
	public $tradeId;	
}
?>