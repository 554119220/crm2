<?php

/**
 * 交易扩展表信息
 * @author auto create
 */
class TradeExt
{
	
	/** 
	 * 关闭订单前扩展标识位
	 **/
	public $beforeCloseFlag;
	
	/** 
	 * 确认收货前扩展标识位
	 **/
	public $beforeConfirmFlag;
	
	/** 
	 * enable前扩展标识位
	 **/
	public $beforeEnableFlag;
	
	/** 
	 * 修改前扩展标识位
	 **/
	public $beforeModifyFlag;
	
	/** 
	 * 付款前扩展标识位
	 **/
	public $beforePayFlag;
	
	/** 
	 * 评价前扩展标识位
	 **/
	public $beforeRateFlag;
	
	/** 
	 * 退款前扩展标识位
	 **/
	public $beforeRefundFlag;
	
	/** 
	 * 发货前扩展标识位
	 **/
	public $beforeShipFlag;
	
	/** 
	 * attributes标记
	 **/
	public $extAttributes;
	
	/** 
	 * 第三方个性化数据
	 **/
	public $extraData;
	
	/** 
	 * 第三方状态，第三方自由定义
	 **/
	public $thirdPartyStatus;	
}
?>