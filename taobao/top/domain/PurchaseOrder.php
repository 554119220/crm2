<?php

/**
 * 采购单及子采购单信息
 * @author auto create
 */
class PurchaseOrder
{
	
	/** 
	 * 支付宝交易号。
	 **/
	public $alipayNo;
	
	/** 
	 * 买家nick，供应商查询不会返回买家昵称，分销商查询才会返回。
	 **/
	public $buyerNick;
	
	/** 
	 * 买家支付给分销商的总金额。注意买家购买的商品可能不是全部来自同一供货商，请同时参考子单上的相关金额。（精确到2位小数;单位:元。如:200.07，表示:200元7分）
	 **/
	public $buyerPayment;
	
	/** 
	 * 加密后的买家淘宝ID，长度为32位
	 **/
	public $buyerTaobaoId;
	
	/** 
	 * 物流发货时间。格式:yyyy-MM-dd HH:mm:ss
	 **/
	public $consignTime;
	
	/** 
	 * 采购单创建时间。格式:yyyy-MM-dd HH:mm:ss
	 **/
	public $created;
	
	/** 
	 * 分销商来源网站（taobao）。
	 **/
	public $distributorFrom;
	
	/** 
	 * 分销商实付金额。(精确到2位小数;单位:元。如:200.07，表示:200元7分 )
	 **/
	public $distributorPayment;
	
	/** 
	 * 分销商在来源网站的帐号名。
	 **/
	public $distributorUsername;
	
	/** 
	 * 交易结束时间
	 **/
	public $endTime;
	
	/** 
	 * 主订单属性信息，key-value形式：
orderNovice ：订单发票抬头；
orderNoviceContent ：代表发票明细
	 **/
	public $features;
	
	/** 
	 * 分销流水号，分销平台产生的主键
	 **/
	public $fenxiaoId;
	
	/** 
	 * 供应商交易ID 非采购单ID，如果改发货状态 是需要该ID，ID在用户未付款前为0，付款后有具体值（发货时使用该ID）
	 **/
	public $id;
	
	/** 
	 * 自定义key
	 **/
	public $isvCustomKey;
	
	/** 
	 * 自定义值
	 **/
	public $isvCustomValue;
	
	/** 
	 * 物流公司
	 **/
	public $logisticsCompanyName;
	
	/** 
	 * 运单号
	 **/
	public $logisticsId;
	
	/** 
	 * 采购单留言。（代销模式下信息包括买家留言和分销商留言）
	 **/
	public $memo;
	
	/** 
	 * 交易修改时间。格式:yyyy-MM-dd HH:mm:ss
	 **/
	public $modified;
	
	/** 
	 * 采购单留言列表
	 **/
	public $orderMessages;
	
	/** 
	 * 付款时间。格式:yyyy-MM-dd HH:mm:ss
	 **/
	public $payTime;
	
	/** 
	 * 支付方式：ALIPAY_SURETY（支付宝担保交易）、ALIPAY_CHAIN（分账交易）、TRANSFER（线下转账）、PREPAY（预存款）、IMMEDIATELY（即时到账）、CASHGOODS（先款后货）
	 **/
	public $payType;
	
	/** 
	 * 采购单邮费。(精确到2位小数;单位:元。如:200.07，表示:200元7分 )
	 **/
	public $postFee;
	
	/** 
	 * 买家详细的信息。
	 **/
	public $receiver;
	
	/** 
	 * 配送方式，FAST(快速)、EMS、ORDINARY(平邮)、SELLER(卖家包邮)
	 **/
	public $shipping;
	
	/** 
	 * 订单快照URL
	 **/
	public $snapshotUrl;
	
	/** 
	 * 采购单交易状态。可选值：<br>
WAIT_BUYER_PAY(等待付款)<br>
WAIT_SELLER_SEND_GOODS(已付款，待发货）<br>
WAIT_BUYER_CONFIRM_GOODS(已付款，已发货)<br>
TRADE_FINISHED(交易成功)<br>
TRADE_CLOSED(交易关闭)<br>
WAIT_BUYER_CONFIRM_GOODS_ACOUNTED(已付款（已分账），已发货。只对代销分账支持)<br>
PAY_ACOUNTED_GOODS_CONFIRM （已分账发货成功）<br>
PAY_WAIT_ACOUNT_GOODS_CONFIRM（已付款，确认收货）
	 **/
	public $status;
	
	/** 
	 * 子订单的详细信息列表。
	 **/
	public $subPurchaseOrders;
	
	/** 
	 * 返回供应商备注旗帜
vlaue在1-5之间。非1-5之间，都采用1作为默认。 1:红色 2:黄色 3:绿色 4:蓝色 5:粉红色
	 **/
	public $supplierFlag;
	
	/** 
	 * 供应商来源网站, values: taobao, alibaba
	 **/
	public $supplierFrom;
	
	/** 
	 * 供应商备注
	 **/
	public $supplierMemo;
	
	/** 
	 * 供应商在来源网站的帐号名。
	 **/
	public $supplierUsername;
	
	/** 
	 * 主订单ID （经销不显示）
	 **/
	public $tcOrderId;
	
	/** 
	 * 采购单总额（不含邮费,精确到2位小数;单位:元。如:200.07，表示:200元7分 )
	 **/
	public $totalFee;
	
	/** 
	 * 分销方式：AGENT（代销）、DEALER（经销）
	 **/
	public $tradeType;
	
	/** 
	 * 采购单类型（代销、零批） values:daixiao,lingpi。
	 **/
	public $type;	
}
?>