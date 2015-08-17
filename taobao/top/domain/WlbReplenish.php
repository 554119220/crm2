<?php

/**
 * 物流宝补货统计对象
 * @author auto create
 */
class WlbReplenish
{
	
	/** 
	 * 根据历史统计值计算出来的预期值
WarnByLast3Days=1; ByLast3Days=3;
ByLast10Days=10;
ByLast30Days=30;
ByLast90Days=90
WarnByLast3Days为按照过去3天的发出的件数来统计的达到安全库存的天数；其它4项分别为按照过去3、10、30、90天发出的商品件数，现有库存可以维持的天数
	 **/
	public $estimateValue;
	
	/** 
	 * 订单历史统计值
Last3DaysTotal=3; Last10DaysTotal=10;
Last30DaysTotal=30; Last90DaysTotal=90
分别为过去3、10、30、90天发出的商品件数
	 **/
	public $historyValue;
	
	/** 
	 * 商品id
	 **/
	public $itemId;
	
	/** 
	 * 补货周期（单位：天）
	 **/
	public $retrievalCount;
	
	/** 
	 * 可销售库存数
	 **/
	public $sellCount;
	
	/** 
	 * 仓库编码
	 **/
	public $storeCode;
	
	/** 
	 * 在途库存数
	 **/
	public $transportCount;
	
	/** 
	 * 用户id
	 **/
	public $userId;
	
	/** 
	 * 安全库存
	 **/
	public $warnCount;	
}
?>