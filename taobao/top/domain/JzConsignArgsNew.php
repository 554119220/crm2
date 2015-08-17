<?php

/**
 * 家装物流发货参数
 * @author auto create
 */
class JzConsignArgsNew
{
	
	/** 
	 * 快递运单号，serviceType=20 和serviceType=21时必填
	 **/
	public $mailNo;
	
	/** 
	 * 包裹数目
	 **/
	public $packageNumber;
	
	/** 
	 * 包裹备注信息
	 **/
	public $packageRemark;
	
	/** 
	 * 包裹体积m3
	 **/
	public $packageVolume;
	
	/** 
	 * 包裹重量kg
	 **/
	public $packageWeight;
	
	/** 
	 * 物流公司名称，tmsPartner.virualType=true时必填
	 **/
	public $zyCompany;
	
	/** 
	 * 发货时间，tmsPartner.virualType=true时必填
	 **/
	public $zyConsignTime;
	
	/** 
	 * 运单号，tmsPartner.virualType=true时必填
	 **/
	public $zyMailNo;
	
	/** 
	 * 物流公司电话，tmsPartner.virualType=true时必填
	 **/
	public $zyPhoneNumber;	
}
?>