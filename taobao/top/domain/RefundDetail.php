<?php

/**
 * 采购单子单退款详情
 * @author auto create
 */
class RefundDetail
{
	
	/** 
	 * 下游买家的退款信息
	 **/
	public $buyerRefund;
	
	/** 
	 * 分销商nick
	 **/
	public $distributorNick;
	
	/** 
	 * 是否退货
	 **/
	public $isReturnGoods;
	
	/** 
	 * 退款修改时间。格式:yyyy-MM-dd HH:mm:ss
	 **/
	public $modified;
	
	/** 
	 * 支付给供应商的金额
	 **/
	public $paySupFee;
	
	/** 
	 * 主采购单id
	 **/
	public $purchaseOrderId;
	
	/** 
	 * 退款创建时间
	 **/
	public $refundCreateTime;
	
	/** 
	 * 退款说明
	 **/
	public $refundDesc;
	
	/** 
	 * 退款的金额
	 **/
	public $refundFee;
	
	/** 
	 * 退款流程类型：
4：发货前退款；
1：发货后退款不退货；
2：发货后退款退货
	 **/
	public $refundFlowType;
	
	/** 
	 * 退款原因
	 **/
	public $refundReason;
	
	/** 
	 * 退款状态
1：买家已经申请退款，等待卖家同意
2：卖家已经同意退款，等待买家退货
3：买家已经退货，等待卖家确认收货
4：退款关闭
5：退款成功
6：卖家拒绝退款
12：同意退款，待打款
9：没有申请退款
10：卖家拒绝确认收货
	 **/
	public $refundStatus;
	
	/** 
	 * 子单id
	 **/
	public $subOrderId;
	
	/** 
	 * 供应商nick
	 **/
	public $supplierNick;
	
	/** 
	 * 超时时间
	 **/
	public $timeout;
	
	/** 
	 * 超时类型：
1：供应商同意退款/同意退货超时；
2：供应商确认收货超时
	 **/
	public $toType;	
}
?>