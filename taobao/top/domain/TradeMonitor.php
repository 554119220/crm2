<?php

/**
 * 经销订单监控记录信息
 * @author auto create
 */
class TradeMonitor
{
	
	/** 
	 * 地区
	 **/
	public $area;
	
	/** 
	 * 交易订单的商品购买数量
	 **/
	public $buyAmount;
	
	/** 
	 * 收货人姓名
	 **/
	public $buyerFullName;
	
	/** 
	 * 买家的淘宝账号昵称
	 **/
	public $buyerNick;
	
	/** 
	 * 城市
	 **/
	public $city;
	
	/** 
	 * 经销商的淘宝账号昵称
	 **/
	public $distributorNick;
	
	/** 
	 * 交易订单的商品id
	 **/
	public $itemId;
	
	/** 
	 * 交易订单的商品的商家编码
	 **/
	public $itemNumber;
	
	/** 
	 * 交易订单的商品价格
	 **/
	public $itemPrice;
	
	/** 
	 * 交易订单的商品的sku名称
	 **/
	public $itemSkuName;
	
	/** 
	 * 交易订单的商品的sku商家编码
	 **/
	public $itemSkuNumber;
	
	/** 
	 * 交易订单的商品标题
	 **/
	public $itemTitle;
	
	/** 
	 * 交易订单的商品总价格（单价×数量+改价+优惠）
	 **/
	public $itemTotalPrice;
	
	/** 
	 * 交易订单的付款时间
	 **/
	public $payTime;
	
	/** 
	 * 供应商的产品id
	 **/
	public $productId;
	
	/** 
	 * 供应商的产品的商家编码
	 **/
	public $productNumber;
	
	/** 
	 * 供应商的产品的sku商家编码
	 **/
	public $productSkuNumber;
	
	/** 
	 * 供应商的产品标题
	 **/
	public $productTitle;
	
	/** 
	 * 省份
	 **/
	public $provence;
	
	/** 
	 * 交易订单的商品最高零售价
	 **/
	public $retailPriceHigh;
	
	/** 
	 * 交易订单的商品最低零售价
	 **/
	public $retailPriceLow;
	
	/** 
	 * 收货人地址
	 **/
	public $shippingAddress;
	
	/** 
	 * 交易订单的状态：
WAIT_SELLER_SEND_GOODS(已付款，待发货）<br>WAIT_BUYER_CONFIRM_GOODS(已付款，已发货)<br>TRADE_FINISHED(交易成功)
TRADE_CLOSED(交易关闭)<br>TRADE_REFUNDING（退款中）
	 **/
	public $status;
	
	/** 
	 * 交易订单的子订单号
	 **/
	public $subTcOrderId;
	
	/** 
	 * 供应商的淘宝账号昵称
	 **/
	public $supplierNick;
	
	/** 
	 * 商品的卖出金额调整，金额增加时为正数，金额减少时为负数，单位是分，不带小数
	 **/
	public $tcAdjustFee;
	
	/** 
	 * 优惠金额，始终为正数，单位是分，不带小数
	 **/
	public $tcDiscountFee;
	
	/** 
	 * 交易订单号
	 **/
	public $tcOrderId;
	
	/** 
	 * 商品优惠类型：聚划算、秒杀或其他
	 **/
	public $tcPreferentialType;
	
	/** 
	 * 主键id
	 **/
	public $tradeMonitorId;	
}
?>