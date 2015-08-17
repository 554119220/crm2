<?php

/**
 * 系统自动生成
 * @author auto create
 */
class WlbWmsConsignOrderConfirm
{
	
	/** 
	 * 支持出入库单多次确认 0 最后一次确认或是一次性确认； 1 多次确认；当多次确认时，确认的ITEM种类全部被确认时，确认完成默认值为 0 例如输入2认为是0
	 **/
	public $confirmType;
	
	/** 
	 * 发票信息
	 **/
	public $invoinceConfirms;
	
	/** 
	 * 商家订单编码
	 **/
	public $orderCode;
	
	/** 
	 * 订单出库完成时间
	 **/
	public $orderConfirmTime;
	
	/** 
	 * 拆合单信息，如果仓库合并ERP订单后，将多个ERP订单合并在这个字段中；
	 **/
	public $orderJoin;
	
	/** 
	 * 订单类型 201 一般销售发货订单 202 B2B销售发货订单 502 换货出库单 503 补发出库单
	 **/
	public $orderType;
	
	/** 
	 * 外部业务编码，消息ID，用于去重，一个合作伙伴中要求唯一，多次确认时，每次传入要求唯一
	 **/
	public $outBizCode;
	
	/** 
	 * 仓储订单编码
	 **/
	public $storeOrderCode;
	
	/** 
	 * 运单列表，一个运单对应一个包裹
	 **/
	public $tmsOrders;	
}
?>