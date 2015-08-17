<?php

/**
 * 物流公司资费相关信息
 * @author auto create
 */
class CarriageDetail
{
	
	/** 
	 * 续费（单位：元）
	 **/
	public $addFee;
	
	/** 
	 * 续重（单位：千克）
	 **/
	public $addWeight;
	
	/** 
	 * 破损赔付
	 **/
	public $damagePayment;
	
	/** 
	 * 物流公司揽收时间段
	 **/
	public $gotTime;
	
	/** 
	 * 首费（单位：元）
	 **/
	public $initialFee;
	
	/** 
	 * 首重（单位：千克）
	 **/
	public $initialWeight;
	
	/** 
	 * 丢单赔付
	 **/
	public $lostPayment;
	
	/** 
	 * 快件送达所需的时间(单位：天)
	 **/
	public $wayDay;	
}
?>