<?php

/**
 * 面单详情
 * @author auto create
 */
class WaybillDetailQueryInfo
{
	
	/** 
	 * 收货人地址
	 **/
	public $consigneeAddress;
	
	/** 
	 * 包裹对应的派件（收件）物流服务商网点（分支机构）代码
	 **/
	public $consigneeBranchCode;
	
	/** 
	 * 包裹对应的派件（收件）物流服务商网点（分支机构）名称
	 **/
	public $consigneeBranchName;
	
	/** 
	 * 收件人姓名
	 **/
	public $consigneeName;
	
	/** 
	 * 收件人联系方式
	 **/
	public $consigneePhone;
	
	/** 
	 * 物流商编码CODE
	 **/
	public $cpCode;
	
	/** 
	 * 创建时间
	 **/
	public $createTime;
	
	/** 
	 * 最后一次打印时间
	 **/
	public $lastPrintTime;
	
	/** 
	 * 物流服务能力集合
	 **/
	public $logisticsServiceList;
	
	/** 
	 * 集包地、目的地中心代码。打
印时根据该 code 生成目的地
中心的条码，条码生成的算法
与对应的电子面单条码一致
	 **/
	public $packageCenterCode;
	
	/** 
	 * 集包地、目的地中心名称
	 **/
	public $packageCenterName;
	
	/** 
	 * ERP订单号或包裹号
	 **/
	public $packageId;
	
	/** 
	 * 包裹里面的商品类型
	 **/
	public $packageItems;
	
	/** 
	 * 揽收时间
	 **/
	public $pickupTime;
	
	/** 
	 * 打印次数
	 **/
	public $printCount;
	
	/** 
	 * 快递服务产品类型编码
	 **/
	public $productType;
	
	/** 
	 * 使用者ID
	 **/
	public $realUserId;
	
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
	 * 发货网点编码
	 **/
	public $shippingBranchCode;
	
	/** 
	 * 发货网点信息
	 **/
	public $shippingBranchName;
	
	/** 
	 * 大头笔信息
	 **/
	public $shortAddress;
	
	/** 
	 * 签收时间
	 **/
	public $signTime;
	
	/** 
	 * 面单状态
	 **/
	public $status;
	
	/** 
	 * 交易订单列表
	 **/
	public $tradeOrderList;
	
	/** 
	 * 包裹重量 单位为G(克)
	 **/
	public $volume;
	
	/** 
	 * 电子面单信息
	 **/
	public $waybillCode;
	
	/** 
	 * 包裹体积 单位为ML(毫升)或立方厘米
	 **/
	public $weight;	
}
?>