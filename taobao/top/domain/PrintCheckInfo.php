<?php

/**
 * 面单详情信息
 * @author auto create
 */
class PrintCheckInfo
{
	
	/** 
	 * 收件人地址
	 **/
	public $consigneeAddress;
	
	/** 
	 * 收货网点编码
	 **/
	public $consigneeBranchCode;
	
	/** 
	 * 收货网点信息
	 **/
	public $consigneeBranchName;
	
	/** 
	 * 收件人姓名
	 **/
	public $consigneeName;
	
	/** 
	 * consigneePhone
	 **/
	public $consigneePhone;
	
	/** 
	 * 物流服务能力集合
	 **/
	public $logisticsServiceList;
	
	/** 
	 * 集包地、目的地中心代码。打 印时根据该 code 生成目的地 中心的条码，条码生成的算法 与对应的电子面单条码一致
	 **/
	public $packageCenterCode;
	
	/** 
	 * 集包地、目的地中心名称
	 **/
	public $packageCenterName;
	
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
	 * 发件人地址
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
	 * 拣货规则（大头笔信息）
	 **/
	public $shortAddress;
	
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