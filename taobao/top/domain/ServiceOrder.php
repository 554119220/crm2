<?php

/**
 * 商城虚拟服务子订单数据结构
 * @author auto create
 */
class ServiceOrder
{
	
	/** 
	 * 卖家昵称
	 **/
	public $buyerNick;
	
	/** 
	 * 车牌号
	 **/
	public $etPlateNumber;
	
	/** 
	 * 天猫汽车预约服务时间
	 **/
	public $etSerTime;
	
	/** 
	 * 电子凭证预约门店地址
	 **/
	public $etShopName;
	
	/** 
	 * 电子凭证核销门店地址
	 **/
	public $etVerifiedShopName;
	
	/** 
	 * 服务所属的交易订单号。如果服务为一年包换，则item_oid这笔订单享受改服务的保护
	 **/
	public $itemOid;
	
	/** 
	 * 购买数量，取值范围为大于0的整数
	 **/
	public $num;
	
	/** 
	 * 虚拟服务子订单订单号
	 **/
	public $oid;
	
	/** 
	 * 子订单实付金额。精确到2位小数，单位:元。如:200.07，表示:200元7分。
	 **/
	public $payment;
	
	/** 
	 * 服务图片地址
	 **/
	public $picPath;
	
	/** 
	 * 服务价格，精确到小数点后两位：单位:元
	 **/
	public $price;
	
	/** 
	 * 最近退款的id
	 **/
	public $refundId;
	
	/** 
	 * 卖家昵称
	 **/
	public $sellerNick;
	
	/** 
	 * 服务详情的URL地址
	 **/
	public $serviceDetailUrl;
	
	/** 
	 * 服务数字id
	 **/
	public $serviceId;
	
	/** 
	 * 商品名称
	 **/
	public $title;
	
	/** 
	 * 支持家装类物流的类型
	 **/
	public $tmserSpuCode;
	
	/** 
	 * 服务子订单总费用
	 **/
	public $totalFee;	
}
?>