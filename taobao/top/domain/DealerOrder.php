<?php

/**
 * 采购申请和经销采购单
 * @author auto create
 */
class DealerOrder
{
	
	/** 
	 * 支付宝交易号
	 **/
	public $alipayNo;
	
	/** 
	 * 申请时间
	 **/
	public $appliedTime;
	
	/** 
	 * 分销商nick
	 **/
	public $applierNick;
	
	/** 
	 * 分销商最后一次确认/申请/拒绝的时间
	 **/
	public $auditTimeApplier;
	
	/** 
	 * 供应商最后一次审核通过/修改/驳回的时间
	 **/
	public $auditTimeSupplier;
	
	/** 
	 * 关闭原因
	 **/
	public $closeReason;
	
	/** 
	 * 产品明细
	 **/
	public $dealerOrderDetails;
	
	/** 
	 * 经销采购单编号，API发货使用此字段
	 **/
	public $dealerOrderId;
	
	/** 
	 * 已发货数量
	 **/
	public $deliveredQuantityCount;
	
	/** 
	 * 物流费用(精确到2位小数;单位:元。如:200.07，表示:200元7分 )
	 **/
	public $logisticsFee;
	
	/** 
	 * 物流方式：
SELF_PICKUP（自提）、LOGISTICS（物流发货)
	 **/
	public $logisticsType;
	
	/** 
	 * 修改时间
	 **/
	public $modifiedTime;
	
	/** 
	 * WAIT_FOR_SUPPLIER_AUDIT1：分销商提交申请，待供应商审核；
SUPPLIER_REFUSE：供应商驳回申请，待分销商确认；
WAIT_FOR_APPLIER_AUDIT：供应商修改后，待分销商确认；
WAIT_FOR_SUPPLIER_AUDIT2：分销商拒绝修改，待供应商再审核；
BOTH_AGREE_WAIT_PAY：审核通过下单成功，待分销商付款
WAIT_FOR_SUPPLIER_DELIVER：付款成功，待供应商发货；
WAIT_FOR_APPLIER_STORAGE：供应商发货，待分销商收货；
TRADE_FINISHED：分销商收货，交易成功；
TRADE_CLOSED：经销采购单关闭。
	 **/
	public $orderStatus;
	
	/** 
	 * 付款时间
	 **/
	public $payTime;
	
	/** 
	 * 支付方式：
ALIPAY_SURETY（支付宝担保交易）
TRANSFER（线下转账）
PREPAY（预存款）
IMMEDIATELY（即时到账）
	 **/
	public $payType;
	
	/** 
	 * 总采购数量
	 **/
	public $quantityCount;
	
	/** 
	 * 收货人信息
	 **/
	public $receiver;
	
	/** 
	 * 分销商拒绝供应商修改的原因
	 **/
	public $refuseReasonApplier;
	
	/** 
	 * 供应商驳回申请的原因
	 **/
	public $refuseReasonSupplier;
	
	/** 
	 * 供应商备注。
仅供应商可见。
	 **/
	public $supplierMemo;
	
	/** 
	 * 供应商备注旗帜。
1:红色 2:黄色 3:绿色 4:蓝色 5:粉红色。
仅供应商可见。
	 **/
	public $supplierMemoFlag;
	
	/** 
	 * 供应商nick
	 **/
	public $supplierNick;
	
	/** 
	 * 采购总价(精确到2位小数;单位:元。如:200.07，表示:200元7分 )
	 **/
	public $totalPrice;	
}
?>