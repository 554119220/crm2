<?php

/**
 * 物流宝订单商品
 * @author auto create
 */
class WlbOrderItem
{
	
	/** 
	 * 批次号备注
	 **/
	public $batchRemark;
	
	/** 
	 * 物流宝订单确认状态：
NO_NEED_CONFIRM--不需要确认
WAIT_CONFIRM--待确认
CONFIRMED--已确认
	 **/
	public $confirmStatus;
	
	/** 
	 * 外部实体id
	 **/
	public $extEntityId;
	
	/** 
	 * 外部实体类型
	 **/
	public $extEntityType;
	
	/** 
	 * 第一位标示是否为赠品
	 **/
	public $flag;
	
	/** 
	 * 订单商品id
	 **/
	public $id;
	
	/** 
	 * INVENTORY_TYPE_SELL 可销库存
INVENTORY_TYPE_IMPERFECTIONS 残次库存
INVENTORY_TYPE_FREEZE 冻结库存
INVENTORY_TYPE_ON_PASSAGE 在途库存
INVENTORY_TYPE_ENGINE_DAMAGE 机损
INVENTORY_TYPE_BOX_DAMAGE 箱损
	 **/
	public $inventoryType;
	
	/** 
	 * 订单商品编码
	 **/
	public $itemCode;
	
	/** 
	 * 物流宝订单商品的物流宝商品id
	 **/
	public $itemId;
	
	/** 
	 * 订单商品名称
	 **/
	public $itemName;
	
	/** 
	 * 商品价格
	 **/
	public $itemPrice;
	
	/** 
	 * 物流宝订单编码
	 **/
	public $orderCode;
	
	/** 
	 * 物流宝订单id
	 **/
	public $orderId;
	
	/** 
	 * 子交易号
	 **/
	public $orderSub2code;
	
	/** 
	 * 订单号
	 **/
	public $orderSubCode;
	
	/** 
	 * (1)其它: OTHER (2)淘宝交易: TAOBAO (3)调拨: ALLOCATION (4)盘点:CHECK (5)销售采购:PRUCHASE(6)其它交易：OTHER_TRADE
	 **/
	public $orderSubType;
	
	/** 
	 * 订单商品图片url
	 **/
	public $pictureUrl;
	
	/** 
	 * 计划数量
	 **/
	public $planQuantity;
	
	/** 
	 * 货主id
	 **/
	public $providerTpId;
	
	/** 
	 * 货主nick
	 **/
	public $providerTpNick;
	
	/** 
	 * 商品发布版本号
	 **/
	public $publishVersion;
	
	/** 
	 * 实际数量
	 **/
	public $realQuantity;
	
	/** 
	 * 订单商品备注
	 **/
	public $remark;
	
	/** 
	 * 订单商品用户id
	 **/
	public $userId;
	
	/** 
	 * 订单商品用户昵称
	 **/
	public $userNick;	
}
?>