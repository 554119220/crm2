<?php

/**
 * 更新面单信息请求
 * @author auto create
 */
class WaybillApplyFullUpdateRequest
{
	
	/** 
	 * TOP平台请求的ISV APPKEY
	 **/
	public $appKey;
	
	/** 
	 * 收货地址
	 **/
	public $consigneeAddress;
	
	/** 
	 * 收件人姓名
	 **/
	public $consigneeName;
	
	/** 
	 * 收件人电话
	 **/
	public $consigneePhone;
	
	/** 
	 * 快递服务商CODE
	 **/
	public $cpCode;
	
	/** 
	 * 快递服务商的ID
	 **/
	public $cpId;
	
	/** 
	 * 商品名称
	 **/
	public $itemName;
	
	/** 
	 * 物流服务能力集合
	 **/
	public $logisticsServiceList;
	
	/** 
	 * 订单渠道类型
	 **/
	public $orderChannelsType;
	
	/** 
	 * 订单类型
	 **/
	public $orderType;
	
	/** 
	 * ERP 订单号或包裹号
	 **/
	public $packageId;
	
	/** 
	 * 包裹内商品类型
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
	 * 申请者ID
	 **/
	public $sellerId;
	
	/** 
	 * 发件人姓名
	 **/
	public $sendName;
	
	/** 
	 * 发件人联系方式
	 **/
	public $sendPhone;
	
	/** 
	 * 发货地址
	 **/
	public $shippingAddress;
	
	/** 
	 * 交易订单号（组合表示合并订单）
	 **/
	public $tradeOrderList;
	
	/** 
	 * 包裹体积 单位为ML(毫升)或立方厘米
	 **/
	public $volume;
	
	/** 
	 * 电子面单单号
	 **/
	public $waybillCode;
	
	/** 
	 * 包裹重量 单位为G(克)
	 **/
	public $weight;	
}
?>