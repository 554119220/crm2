<?php

/**
 * 更新面单数据
 * @author auto create
 */
class WaybillApplyUpdateInfo
{
	
	/** 
	 * 收货网点信息
	 **/
	public $consigneeBranchCode;
	
	/** 
	 * 收货网点编码
	 **/
	public $consigneeBranchName;
	
	/** 
	 * --
	 **/
	public $desc;
	
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
	 * --
	 **/
	public $result;
	
	/** 
	 * 挑拣规则（大头笔信息）
	 **/
	public $shortAddress;
	
	/** 
	 * --
	 **/
	public $tradeOrderInfo;
	
	/** 
	 * --
	 **/
	public $waybillCode;	
}
?>