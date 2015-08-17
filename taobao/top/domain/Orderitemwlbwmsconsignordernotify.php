<?php

/**
 * 订单商品信息
 * @author auto create
 */
class Orderitemwlbwmsconsignordernotify
{
	
	/** 
	 * 商品成交价格=销售价格-优惠金额
	 **/
	public $actualPrice;
	
	/** 
	 * 商品优惠金额
	 **/
	public $discountAmount;
	
	/** 
	 * 订单商品拓展属性数据
	 **/
	public $extendFields;
	
	/** 
	 * 库存类型
	 **/
	public $inventoryType;
	
	/** 
	 * 交易平台商品编码
	 **/
	public $itemExtCode;
	
	/** 
	 * ERP商品ID
	 **/
	public $itemId;
	
	/** 
	 * 销售价格
	 **/
	public $itemPrice;
	
	/** 
	 * 商品数量
	 **/
	public $itemQuantity;
	
	/** 
	 * ERP订单明细行号ID
	 **/
	public $orderItemId;
	
	/** 
	 * 平台交易编码
	 **/
	public $orderSourceCode;
	
	/** 
	 * 货主ID 代销情况下货主ID和卖家ID不同
	 **/
	public $ownerUserId;
	
	/** 
	 * 货主名称
	 **/
	public $ownerUserName;
	
	/** 
	 * 平台子交易编码
	 **/
	public $subSourceCode;
	
	/** 
	 * 卖家ID,一般情况下，货主ID和卖家ID相同
	 **/
	public $userId;
	
	/** 
	 * 卖家名称(销售店铺名称)
	 **/
	public $userName;	
}
?>