<?php

/**
 * 订单数据
 * @author auto create
 */
class TradeOrderInfo
{
	
	/** 
	 * 是否阿里系订单
	 **/
	public $aliOrder;
	
	/** 
	 * 收货人地址
	 **/
	public $consigneeAddress;
	
	/** 
	 * 收货人
	 **/
	public $consigneeName;
	
	/** 
	 * 收货人联系方式
	 **/
	public $consigneePhone;
	
	/** 
	 * 商品名称
	 **/
	public $itemName;
	
	/** 
	 * 物流服务能力集合
	 **/
	public $logisticsServiceList;
	
	/** 
	 * 订单渠道
	 **/
	public $orderChannelsType;
	
	/** 
	 * 订单渠道来源
	 **/
	public $orderType;
	
	/** 
	 * 包裹号(或者ERP订单号)
	 **/
	public $packageId;
	
	/** 
	 * 包裹中的商品类型
	 **/
	public $packageItems;
	
	/** 
	 * 快递服务产品类型编码
	 **/
	public $productType;
	
	/** 
	 * 使用者ID
	 **/
	public $realUserId;
	
	/** 
	 * 发货人姓名
	 **/
	public $sendName;
	
	/** 
	 * 发货人联系方式
	 **/
	public $sendPhone;
	
	/** 
	 * 大头笔
	 **/
	public $shortAddress;
	
	/** 
	 * 交易订单列表
	 **/
	public $tradeOrderList;
	
	/** 
	 * 包裹体积（立方厘米）
	 **/
	public $volume;
	
	/** 
	 * 面单号
	 **/
	public $waybillCode;
	
	/** 
	 * 包裹重量（克）
	 **/
	public $weight;	
}
?>