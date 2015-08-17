<?php

/**
 * 下游买家退款信息
 * @author auto create
 */
class BuyerRefund
{
	
	/** 
	 * 订单id
	 **/
	public $bizOrderId;
	
	/** 
	 * 下游买家nick
	 **/
	public $buyerNick;
	
	/** 
	 * 货物的状态：
买家已收到货
买家已退货
买家未收到货
	 **/
	public $goodsStatusDesc;
	
	/** 
	 * 退款修改时间。格式:yyyy-MM-dd HH:mm:ss
	 **/
	public $modified;
	
	/** 
	 * 买家是否退货
	 **/
	public $needReturnGoods;
	
	/** 
	 * 退款创建时间
	 **/
	public $refundCreateTime;
	
	/** 
	 * 退款说明
	 **/
	public $refundDesc;
	
	/** 
	 * 交易退款id
	 **/
	public $refundId;
	
	/** 
	 * 退款原因
	 **/
	public $refundReason;
	
	/** 
	 * 退款状态
	 **/
	public $refundStatus;
	
	/** 
	 * 退还买家的金额
	 **/
	public $returnFee;
	
	/** 
	 * 采购单子单id
	 **/
	public $subOrderId;
	
	/** 
	 * 支付分销商的金额
	 **/
	public $toSellerFee;	
}
?>