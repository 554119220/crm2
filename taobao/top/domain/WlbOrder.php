<?php

/**
 * 物流宝订单对象
 * @author auto create
 */
class WlbOrder
{
	
	/** 
	 * 买家nick
	 **/
	public $buyerNick;
	
	/** 
	 * 订单取消状态：
1-取消中； 
2-取消失败；
3-取消完成
	 **/
	public $cancelOrderStatus;
	
	/** 
	 * 确认状态：
(1)不需要确认：NO_NEED_CONFIRM
(2)等待确认：WAIT_CONFIRM
(3)已确认:CONFIRMED
	 **/
	public $confirmStatus;
	
	/** 
	 * 计划送达结束时间
	 **/
	public $expectEndTime;
	
	/** 
	 * 计划送达开始时间
	 **/
	public $expectStartTime;
	
	/** 
	 * 发票信息
	 **/
	public $invoiceInfo;
	
	/** 
	 * 物流宝订单中的商品种类数量
	 **/
	public $itemKindsCount;
	
	/** 
	 * 出库或者入库，IN表示入库，OUT表示出库
	 **/
	public $operateType;
	
	/** 
	 * 订单编码
	 **/
	public $orderCode;
	
	/** 
	 * 第1位:COD,2:限时配送,3:预售,4:需要发票,5:已投诉,第6位:合单,第7位:拆单 第8位：EXCHANGE-换货， 第9位:VISIT-上门 ， 第10位: MODIFYTRANSPORT-是否可改配送方式，第11位：是否物流代理确认发货
	 **/
	public $orderFlag;
	
	/** 
	 * 订单来源:
产生物流订单的原因，比如:

订单来源:1:TAOBAO;2:EXT;3:ERP;4:WMS
	 **/
	public $orderSource;
	
	/** 
	 * 对应创建物流宝订单top接口中的的out_biz_code字段，主要是用来去重用
	 **/
	public $orderSourceCode;
	
	/** 
	 * 物流状态，
订单已创建：0
订单已取消: -1
订单关闭:-3
下发中: 10
已下发: 11
接收方拒签 :-100
已发货:100
签收成功:200
	 **/
	public $orderStatus;
	
	/** 
	 * 卖家取消,仓库取消标识
	 **/
	public $orderStatusReason;
	
	/** 
	 * (1)其它:    OTHER
(2)淘宝交易: TAOBAO
(3)301:调拨: ALLOCATION
(4)401:盘点:CHECK
(5)501:销售采购:PRUCHASE
	 **/
	public $orderSubType;
	
	/** 
	 * 1:正常订单: NARMAL
2:退货订单: RETURN
3:换货订单: CHANGE
	 **/
	public $orderType;
	
	/** 
	 * 原订单编码
	 **/
	public $prevOrderCode;
	
	/** 
	 * 实际入库的种类数量
	 **/
	public $realKindsCount;
	
	/** 
	 * 应收金额
	 **/
	public $receivableAmount;
	
	/** 
	 * 收件人具体地址
	 **/
	public $receiverAddress;
	
	/** 
	 * 区或者县
	 **/
	public $receiverArea;
	
	/** 
	 * 收件人城市
	 **/
	public $receiverCity;
	
	/** 
	 * 接收人电子邮箱
	 **/
	public $receiverMail;
	
	/** 
	 * 接收人手机号码
	 **/
	public $receiverMobile;
	
	/** 
	 * 接收人姓名
	 **/
	public $receiverName;
	
	/** 
	 * 接收人固定电话
	 **/
	public $receiverPhone;
	
	/** 
	 * 收件人省份
	 **/
	public $receiverProvince;
	
	/** 
	 * 接收人旺旺
	 **/
	public $receiverWangwang;
	
	/** 
	 * 收件人邮编
	 **/
	public $receiverZipCode;
	
	/** 
	 * 订单备注
	 **/
	public $remark;
	
	/** 
	 * 发货日期:
(1)1 为工作日
(2)2 为节假日
	 **/
	public $scheduleDay;
	
	/** 
	 * 配送结束时间通常是HH:MM格式
	 **/
	public $scheduleEnd;
	
	/** 
	 * 发货速度  ，
101-当日达，
102-次晨达，
103-次日达
	 **/
	public $scheduleSpeed;
	
	/** 
	 * 配送开始时间通常是HH:MM格式
	 **/
	public $scheduleStart;
	
	/** 
	 * 发件人地址
	 **/
	public $senderAddress;
	
	/** 
	 * 发件人所在区
	 **/
	public $senderArea;
	
	/** 
	 * 发件人城市
	 **/
	public $senderCity;
	
	/** 
	 * 发件人电子邮箱
	 **/
	public $senderEmail;
	
	/** 
	 * 发件人移动电话
	 **/
	public $senderMobile;
	
	/** 
	 * 发件人姓名
	 **/
	public $senderName;
	
	/** 
	 * 发件人联系电话
	 **/
	public $senderPhone;
	
	/** 
	 * 发件人所在省份
	 **/
	public $senderProvince;
	
	/** 
	 * 发件人邮编
	 **/
	public $senderZipCode;
	
	/** 
	 * cod服务费
	 **/
	public $serviceFee;
	
	/** 
	 * 物流运输方式：
MAIL-平邮
EXPRESS-快递
EMS-EMS
OTHER-其他
	 **/
	public $shippingType;
	
	/** 
	 * 仓库编码
	 **/
	public $storeCode;
	
	/** 
	 * 发货物流公司编码，STO,YTO,EMS等
	 **/
	public $tmsTpCode;
	
	/** 
	 * 订单总价
	 **/
	public $totalAmount;
	
	/** 
	 * 卖家ID
	 **/
	public $userId;
	
	/** 
	 * 卖家NICK
	 **/
	public $userNick;	
}
?>