<?php

/**
 * 订单结构
 * @author auto create
 */
class Order
{
	
	/** 
	 * 手工调整金额.格式为:1.01;单位:元;精确到小数点后两位.
	 **/
	public $adjustFee;
	
	/** 
	 * 捆绑的子订单号，表示该子订单要和捆绑的子订单一起发货，用于卖家子订单捆绑发货
	 **/
	public $bindOid;
	
	/** 
	 * 买家昵称
	 **/
	public $buyerNick;
	
	/** 
	 * 买家是否已评价。可选值：true(已评价)，false(未评价)
	 **/
	public $buyerRate;
	
	/** 
	 * 交易商品对应的类目ID
	 **/
	public $cid;
	
	/** 
	 * 子订单发货时间，当卖家对订单进行了多次发货，子订单的发货时间和主订单的发货时间可能不一样了，那么就需要以子订单的时间为准。（没有进行多次发货的订单，主订单的发货时间和子订单的发货时间都一样）
	 **/
	public $consignTime;
	
	/** 
	 * 子订单级订单优惠金额。精确到2位小数;单位:元。如:200.07，表示:200元7分
	 **/
	public $discountFee;
	
	/** 
	 * 分摊之后的实付金额
	 **/
	public $divideOrderFee;
	
	/** 
	 * 子订单的交易结束时间说明：子订单有单独的结束时间，与主订单的结束时间可能有所不同，在有退款发起的时候或者是主订单分阶段付款的时候，子订单的结束时间会早于主订单的结束时间，所以开放这个字段便于订单结束状态的判断
	 **/
	public $endTime;
	
	/** 
	 * 车牌号码
	 **/
	public $etPlateNumber;
	
	/** 
	 * 天猫汽车服务预约时间
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
	 * 商品的字符串编号(注意：iid近期即将废弃，请用num_iid参数)
	 **/
	public $iid;
	
	/** 
	 * 子订单所在包裹的运单号
	 **/
	public $invoiceNo;
	
	/** 
	 * 表示订单交易是否含有对应的代销采购单。如果该订单中存在一个对应的代销采购单，那么该值为true；反之，该值为false。
	 **/
	public $isDaixiao;
	
	/** 
	 * 是否超卖
	 **/
	public $isOversold;
	
	/** 
	 * 是否是服务订单，是返回true，否返回false。
	 **/
	public $isServiceOrder;
	
	/** 
	 * 子订单是否是www订单
	 **/
	public $isWww;
	
	/** 
	 * 套餐ID
	 **/
	public $itemMealId;
	
	/** 
	 * 套餐的值。如：M8原装电池:便携支架:M8专用座充:莫凡保护袋
	 **/
	public $itemMealName;
	
	/** 
	 * 商品备注
	 **/
	public $itemMemo;
	
	/** 
	 * 子订单发货的快递公司名称
	 **/
	public $logisticsCompany;
	
	/** 
	 * 订单修改时间，目前只有taobao.trade.ordersku.update会返回此字段。
	 **/
	public $modified;
	
	/** 
	 * 购买数量。取值范围:大于零的整数
	 **/
	public $num;
	
	/** 
	 * 商品数字ID
	 **/
	public $numIid;
	
	/** 
	 * 子订单编号
	 **/
	public $oid;
	
	/** 
	 * 子订单来源,如jhs(聚划算)、taobao(淘宝)、wap(无线)
	 **/
	public $orderFrom;
	
	/** 
	 * 商家外部编码(可与商家外部系统对接)。外部商家自己定义的商品Item的id，可以通过taobao.items.custom.get获取商品的Item的信息
	 **/
	public $outerIid;
	
	/** 
	 * 外部网店自己定义的Sku编号
	 **/
	public $outerSkuId;
	
	/** 
	 * 优惠分摊
	 **/
	public $partMjzDiscount;
	
	/** 
	 * 子订单实付金额。精确到2位小数，单位:元。如:200.07，表示:200元7分。对于多子订单的交易，计算公式如下：payment = price * num + adjust_fee - discount_fee ；单子订单交易，payment与主订单的payment一致，对于退款成功的子订单，由于主订单的优惠分摊金额，会造成该字段可能不为0.00元。建议使用退款前的实付金额减去退款单中的实际退款金额计算。
	 **/
	public $payment;
	
	/** 
	 * 商品图片的绝对路径
	 **/
	public $picPath;
	
	/** 
	 * 商品价格。精确到2位小数;单位:元。如:200.07，表示:200元7分
	 **/
	public $price;
	
	/** 
	 * 最近退款ID
	 **/
	public $refundId;
	
	/** 
	 * 退款状态。退款状态。可选值 WAIT_SELLER_AGREE(买家已经申请退款，等待卖家同意) WAIT_BUYER_RETURN_GOODS(卖家已经同意退款，等待买家退货) WAIT_SELLER_CONFIRM_GOODS(买家已经退货，等待卖家确认收货) SELLER_REFUSE_BUYER(卖家拒绝退款) CLOSED(退款关闭) SUCCESS(退款成功)
	 **/
	public $refundStatus;
	
	/** 
	 * 卖家昵称
	 **/
	public $sellerNick;
	
	/** 
	 * 卖家是否已评价。可选值：true(已评价)，false(未评价)
	 **/
	public $sellerRate;
	
	/** 
	 * 卖家类型，可选值为：B（商城商家），C（普通卖家）
	 **/
	public $sellerType;
	
	/** 
	 * 子订单的运送方式（卖家对订单进行多次发货之后，一个主订单下的子订单的运送方式可能不同，用order.shipping_type来区分子订单的运送方式）
	 **/
	public $shippingType;
	
	/** 
	 * 商品的最小库存单位Sku的id.可以通过taobao.item.sku.get获取详细的Sku信息
	 **/
	public $skuId;
	
	/** 
	 * SKU的值。如：机身颜色:黑色;手机套餐:官方标配
	 **/
	public $skuPropertiesName;
	
	/** 
	 * 订单快照详细信息
	 **/
	public $snapshot;
	
	/** 
	 * 订单快照URL
	 **/
	public $snapshotUrl;
	
	/** 
	 * 订单状态（请关注此状态，如果为TRADE_CLOSED_BY_TAOBAO状态，则不要对此订单进行发货，切记啊！）。可选值: <ul><li>TRADE_NO_CREATE_PAY(没有创建支付宝交易) <li>WAIT_BUYER_PAY(等待买家付款) <li>WAIT_SELLER_SEND_GOODS(等待卖家发货,即:买家已付款) <li>WAIT_BUYER_CONFIRM_GOODS(等待买家确认收货,即:卖家已发货) <li>TRADE_BUYER_SIGNED(买家已签收,货到付款专用) <li>TRADE_FINISHED(交易成功) <li>TRADE_CLOSED(付款以后用户退款成功，交易自动关闭) <li>TRADE_CLOSED_BY_TAOBAO(付款以前，卖家或买家主动关闭交易)<li>PAY_PENDING(国际信用卡支付付款确认中)
	 **/
	public $status;
	
	/** 
	 * 发货的仓库编码
	 **/
	public $storeCode;
	
	/** 
	 * 门票有效期的key
	 **/
	public $ticketExpdateKey;
	
	/** 
	 * 对应门票有效期的外部id
	 **/
	public $ticketOuterId;
	
	/** 
	 * 订单超时到期时间。格式:yyyy-MM-dd HH:mm:ss
	 **/
	public $timeoutActionTime;
	
	/** 
	 * 商品标题
	 **/
	public $title;
	
	/** 
	 * 支持家装类物流的类型
	 **/
	public $tmserSpuCode;
	
	/** 
	 * 应付金额（商品价格 * 商品数量 + 手工调整金额 - 子订单级订单优惠金额）。精确到2位小数;单位:元。如:200.07，表示:200元7分
	 **/
	public $totalFee;
	
	/** 
	 * 交易类型
	 **/
	public $type;	
}
?>