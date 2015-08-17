<?php

/**
 * 用户订购信息
 * @author auto create
 */
class UserSubscribe
{
	
	/** 
	 * 订购结束时间。格式:yyyy-MM-dd HH:mm:ss
	 **/
	public $endDate;
	
	/** 
	 * 订购开始时间。格式:yyyy-MM-dd HH:mm:ss
	 **/
	public $startDate;
	
	/** 
	 * 订购状况。应用订购者：subscribeUser;尚未订购：unsubscribeUser；非法用户：invalidateUser
	 **/
	public $status;
	
	/** 
	 * 淘宝箱应用的版本，初级版、中级版和高级版。
	 **/
	public $version;	
}
?>