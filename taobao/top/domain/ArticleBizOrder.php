<?php

/**
 * 应用订单信息
 * @author auto create
 */
class ArticleBizOrder
{
	
	/** 
	 * 应用收费代码，从合作伙伴后台（my.open.taobao.com）-收费管理-收费项目列表 能够获得该应用的收费代码
	 **/
	public $articleCode;
	
	/** 
	 * 商品模型名称
	 **/
	public $articleItemName;
	
	/** 
	 * 应用名称
	 **/
	public $articleName;
	
	/** 
	 * 订单号
	 **/
	public $bizOrderId;
	
	/** 
	 * 订单类型，1=新订 2=续订 3=升级 4=后台赠送 5=后台自动续订 6=订单审核后生成订购关系（暂时用不到）
	 **/
	public $bizType;
	
	/** 
	 * 订单创建时间（订购时间）
	 **/
	public $create;
	
	/** 
	 * 原价（单位为分）
	 **/
	public $fee;
	
	/** 
	 * 收费项目代码，从合作伙伴后台（my.open.taobao.com）-收费管理-收费项目列表 能够获得收费项目代码
	 **/
	public $itemCode;
	
	/** 
	 * 收费项目名称
	 **/
	public $itemName;
	
	/** 
	 * 淘宝会员名
	 **/
	public $nick;
	
	/** 
	 * 订购周期  1表示年 ，2表示月，3表示天。
	 **/
	public $orderCycle;
	
	/** 
	 * 订购周期结束时间
	 **/
	public $orderCycleEnd;
	
	/** 
	 * 订购周期开始时间
	 **/
	public $orderCycleStart;
	
	/** 
	 * 子订单号
	 **/
	public $orderId;
	
	/** 
	 * 优惠（单位为分）
	 **/
	public $promFee;
	
	/** 
	 * 退款（单位为分；升级时，系统会将升级前老版本按照剩余订购天数退还剩余金额）
	 **/
	public $refundFee;
	
	/** 
	 * 实付（单位为分）
	 **/
	public $totalPayFee;	
}
?>