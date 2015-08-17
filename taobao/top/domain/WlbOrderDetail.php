<?php

/**
 * 物流宝订单，并且包含订单详情
 * @author auto create
 */
class WlbOrderDetail
{
	
	/** 
	 * 如果是交易单，则显示交易中买家昵称
	 **/
	public $buyerNick;
	
	/** 
	 * 订单创建时间
	 **/
	public $createTime;
	
	/** 
	 * 是否已全部完成
	 **/
	public $isCompleted;
	
	/** 
	 * 订单最后一次修改时间
	 **/
	public $modifyTime;
	
	/** 
	 * 出库或者入库，IN表示入库，OUT表示出库
	 **/
	public $operateType;
	
	/** 
	 * 订单编码
	 **/
	public $orderCode;
	
	/** 
	 * 物流宝订单对应的商品详情
	 **/
	public $orderItemList;
	
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
	 * 订单备注
	 **/
	public $remark;
	
	/** 
	 * 仓库编码
	 **/
	public $storeCode;
	
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